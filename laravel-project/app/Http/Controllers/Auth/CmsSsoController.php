<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Counselor;
use App\Models\FacultyStaff;
use App\Models\Student;
use App\Services\Cms\CmsClientInterface;
use App\Services\Cms\CmsCounselorNotFoundException;
use App\Services\Cms\CmsFacultyStaffNotFoundException;
use App\Services\Cms\CmsStudentNotFoundException;
use App\Services\Cms\DevSsoKeyPair;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;

/**
 * Entry point *from* the CMS — the only way into this app now that
 * self-registration/login has been removed. The CMS authenticates the user
 * and hands off here with a signed token carrying who they are and which
 * role they're arriving as (student, counselor via the CMS's "Counselor"
 * sidebar button, or faculty_staff for faculty/staff members). The signed
 * token is the only auth proof; there is no CSRF token to check here
 * because the request originates from a different origin entirely (see
 * bootstrap/app.php CSRF exemption).
 */
class CmsSsoController extends Controller
{
    public function callback(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
        ]);

        $publicKey = config('services.cms.sso_public_key') ?: $this->localDevPublicKeyFallback();

        abort_if(blank($publicKey), 503, 'CMS SSO is not configured.');

        try {
            $payload = JWT::decode($request->string('token')->toString(), new Key($publicKey, 'RS256'));
        } catch (Throwable $e) {
            abort(401, 'Invalid or expired SSO token.');
        }

        $expectedAudience = config('services.cms.sso_audience');

        if (($payload->aud ?? null) !== $expectedAudience) {
            abort(401, 'Invalid SSO token audience.');
        }

        // Issuer pinning. Optional until BNU IT tells us what the CMS will put in
        // `iss`; once CMS_SSO_ISSUER is set, a token from anything else is refused
        // even if it carries a valid signature.
        $expectedIssuer = config('services.cms.sso_issuer');

        if (filled($expectedIssuer) && ($payload->iss ?? null) !== $expectedIssuer) {
            abort(401, 'Invalid SSO token issuer.');
        }

        if (empty($payload->sub)) {
            abort(401, 'SSO token missing subject.');
        }

        if (empty($payload->jti)) {
            abort(401, 'SSO token missing jti.');
        }

        // firebase/php-jwt only enforces `exp` when the claim is actually present
        // (see JWT::decode). A token minted without one would therefore never
        // expire, and the replay TTL derived from it below would collapse to a
        // single second. Require it explicitly.
        if (empty($payload->exp) || ! is_numeric($payload->exp)) {
            abort(401, 'SSO token missing expiry.');
        }

        // A CMS handoff is redeemed immediately on arrival. Anything valid for
        // longer than the configured ceiling is treated as malformed rather than
        // trusted, so a long-lived token cannot be minted and sat on.
        $secondsRemaining = (int) $payload->exp - time();

        if ($secondsRemaining > (int) config('services.cms.sso_max_lifetime', 300)) {
            abort(401, 'SSO token lifetime exceeds the permitted maximum.');
        }

        $role = $payload->role ?? null;

        if (! in_array($role, ['student', 'counselor', 'faculty_staff'], true)) {
            abort(401, 'SSO token missing or invalid role.');
        }

        // Replay protection: each jti may be redeemed exactly once, and the marker
        // is held for the token's full remaining lifetime. Cache::add() is atomic
        // (add-if-absent), so two simultaneous redemptions of the same jti cannot
        // both pass the check the way a has()-then-put() pair could.
        $cacheKey = 'cms_sso_jti:'.$payload->jti;

        if (! Cache::add($cacheKey, true, max(1, $secondsRemaining))) {
            abort(401, 'SSO token already used.');
        }

        // A real CMS handoff always arrives as exactly one identity — end any
        // other guard's session in this browser so a student, counselor, and
        // faculty/staff sidebar never stack together in one visit.
        $this->logoutOtherGuards($role);

        return match ($role) {
            'counselor' => $this->loginCounselor($request, (string) $payload->sub),
            'faculty_staff' => $this->loginFacultyStaff($request, (string) $payload->sub),
            default => $this->loginStudent($request, (string) $payload->sub),
        };
    }

    private function loginStudent(Request $request, string $cmsStudentId)
    {
        try {
            $profile = app(CmsClientInterface::class)->getStudentProfile($cmsStudentId);
        } catch (CmsStudentNotFoundException $e) {
            abort(404, 'No matching student found in CMS.');
        }

        $student = Student::firstOrNew(['cms_student_id' => $profile['cms_student_id']]);

        if (! $student->exists) {
            $student->roll_number = $profile['cms_student_id'];
            $student->password = Hash::make(Str::random(40));
        }

        // Identity fields only — never clinical/consent/session columns, per the
        // hard constraint in CLAUDE.md.
        $student->name = $profile['name'];
        $student->dob = $profile['dob'] ?? $student->dob;
        $student->phone = $profile['phone'] ?? $student->phone;
        $student->department = $profile['department'] ?? $student->department;
        $student->semester = $profile['semester'] ?? $student->semester;

        if (array_key_exists('cgpa', $profile)) {
            $student->cgpa = $profile['cgpa'];
        }

        $student->save();

        Auth::guard('student')->login($student);
        $request->session()->regenerate();

        return redirect()->route('student.dashboard');
    }

    private function loginCounselor(Request $request, string $cmsCounselorId)
    {
        try {
            $profile = app(CmsClientInterface::class)->getCounselorProfile($cmsCounselorId);
        } catch (CmsCounselorNotFoundException $e) {
            abort(404, 'No matching counselor found in CMS.');
        }

        $counselor = Counselor::firstOrNew(['cms_counselor_id' => $profile['cms_counselor_id']]);

        if (! $counselor->exists) {
            $counselor->counselor_code = $profile['cms_counselor_id'];
            $counselor->password = Hash::make(Str::random(40));
        }

        $counselor->name = $profile['name'];
        $counselor->email = $profile['email'];

        if (array_key_exists('active', $profile) && $profile['active'] !== null) {
            $counselor->active = $profile['active'];
        }

        $counselor->save();

        Auth::guard('counselor')->login($counselor);
        $request->session()->regenerate();

        return redirect()->route('counselor.dashboard');
    }

    private function loginFacultyStaff(Request $request, string $cmsEmployeeId)
    {
        try {
            $profile = app(CmsClientInterface::class)->getFacultyStaffProfile($cmsEmployeeId);
        } catch (CmsFacultyStaffNotFoundException $e) {
            abort(404, 'No matching faculty/staff member found in CMS.');
        }

        $facultyStaff = FacultyStaff::firstOrNew(['cms_employee_id' => $profile['cms_employee_id']]);

        if (! $facultyStaff->exists) {
            $facultyStaff->employee_id = $profile['cms_employee_id'];
            $facultyStaff->password = Hash::make(Str::random(40));
        }

        $facultyStaff->name = $profile['name'];
        $facultyStaff->designation = $profile['designation'] ?? $facultyStaff->designation;
        $facultyStaff->department = $profile['department'] ?? $facultyStaff->department;
        $facultyStaff->email = $profile['email'] ?? $facultyStaff->email;
        $facultyStaff->phone = $profile['phone'] ?? $facultyStaff->phone;

        $facultyStaff->save();

        Auth::guard('faculty_staff')->login($facultyStaff);
        $request->session()->regenerate();

        return redirect()->route('faculty.dashboard');
    }

    private function logoutOtherGuards(string $activeRole): void
    {
        foreach (['student', 'counselor', 'faculty_staff'] as $guard) {
            if ($guard !== $activeRole && Auth::guard($guard)->check()) {
                Auth::guard($guard)->logout();
            }
        }
    }

    /**
     * Local-only convenience: when no real CMS public key is configured and
     * we're not in production, fall back to the throwaway keypair the dev
     * SSO simulator signs test tokens with (see DevSsoKeyPair). Never
     * applies outside the local environment.
     */
    private function localDevPublicKeyFallback(): ?string
    {
        // Two independent gates. APP_ENV is easy to get wrong when a folder is
        // copied onto a server; an opt-in flag defaulting to false is not. Both
        // must hold, so a deployed copy will not trust the throwaway dev keypair
        // even if it somehow still reports APP_ENV=local.
        if (! app()->environment('local') || ! config('services.cms.sso_allow_dev_key')) {
            return null;
        }

        return DevSsoKeyPair::publicKeyIfExists();
    }
}
