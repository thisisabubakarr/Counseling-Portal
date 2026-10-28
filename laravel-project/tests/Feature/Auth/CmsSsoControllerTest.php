<?php

namespace Tests\Feature\Auth;

use App\Models\Counselor;
use App\Models\FacultyStaff;
use App\Models\Student;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Tests\TestCase;

class CmsSsoControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $privateKey;

    private string $publicKey;

    protected function setUp(): void
    {
        parent::setUp();

        $keyPair = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        openssl_pkey_export($keyPair, $privateKeyPem);
        $this->privateKey = $privateKeyPem;
        $this->publicKey = openssl_pkey_get_details($keyPair)['key'];

        Config::set('services.cms.sso_public_key', $this->publicKey);
        Config::set('services.cms.sso_audience', 'counselor-system');
    }

    private function signToken(array $overrides = []): string
    {
        $payload = array_merge([
            'sub' => 'F2022-015',
            'role' => 'student',
            'iat' => time(),
            'exp' => time() + 60,
            'aud' => 'counselor-system',
            'jti' => (string) Str::uuid(),
        ], $overrides);

        return JWT::encode($payload, $this->privateKey, 'RS256');
    }

    /**
     * Signs an exact payload with no defaults merged in, so a test can assert
     * on a token that is *missing* a claim rather than one that merely has an
     * unusual value. signToken() always supplies `exp`, which is why a token
     * without one went unnoticed.
     *
     * @param  array<string, mixed>  $payload
     */
    private function signPayload(array $payload): string
    {
        return JWT::encode($payload, $this->privateKey, 'RS256');
    }

    public function test_valid_signed_student_token_logs_in_and_redirects_to_dashboard(): void
    {
        $token = $this->signToken();

        $response = $this->post('/sso/callback', ['token' => $token]);

        $response->assertRedirect(route('student.dashboard'));
        $this->assertAuthenticated('student');

        $student = Student::where('cms_student_id', 'F2022-015')->first();
        $this->assertNotNull($student);
        $this->assertSame('Ayesha Khan', $student->name);
    }

    public function test_valid_signed_counselor_token_logs_in_and_redirects_to_dashboard(): void
    {
        $token = $this->signToken(['sub' => 'wajeeha.nabeel', 'role' => 'counselor']);

        $response = $this->post('/sso/callback', ['token' => $token]);

        $response->assertRedirect(route('counselor.dashboard'));
        $this->assertAuthenticated('counselor');

        $counselor = Counselor::where('cms_counselor_id', 'wajeeha.nabeel')->first();
        $this->assertNotNull($counselor);
        $this->assertSame('Wajeeha Nabeel, Campus Counselor', $counselor->name);
    }

    public function test_valid_signed_faculty_staff_token_logs_in_and_redirects_to_dashboard(): void
    {
        $token = $this->signToken(['sub' => 'naveed.iqbal', 'role' => 'faculty_staff']);

        $response = $this->post('/sso/callback', ['token' => $token]);

        $response->assertRedirect(route('faculty.dashboard'));
        $this->assertAuthenticated('faculty_staff');

        $facultyStaff = FacultyStaff::where('cms_employee_id', 'naveed.iqbal')->first();
        $this->assertNotNull($facultyStaff);
        $this->assertSame('Dr. Naveed Iqbal', $facultyStaff->name);
    }

    public function test_tampered_token_is_rejected_with_no_session(): void
    {
        $token = $this->signToken();
        $tampered = substr($token, 0, -5).'AAAAA';

        $response = $this->post('/sso/callback', ['token' => $tampered]);

        $response->assertStatus(401);
        $this->assertGuest('student');
    }

    public function test_expired_token_is_rejected_with_no_session(): void
    {
        $token = $this->signToken(['iat' => time() - 120, 'exp' => time() - 60]);

        $response = $this->post('/sso/callback', ['token' => $token]);

        $response->assertStatus(401);
        $this->assertGuest('student');
    }

    public function test_token_without_expiry_is_rejected_with_no_session(): void
    {
        // firebase/php-jwt skips its expiry check when `exp` is absent, so an
        // unexpiring token must be refused by the controller itself.
        $token = $this->signPayload([
            'sub' => 'F2022-015',
            'role' => 'student',
            'iat' => time(),
            'aud' => 'counselor-system',
            'jti' => (string) Str::uuid(),
        ]);

        $response = $this->post('/sso/callback', ['token' => $token]);

        $response->assertStatus(401);
        $this->assertGuest('student');
        $this->assertNull(Student::where('cms_student_id', 'F2022-015')->first());
    }

    public function test_token_with_excessive_lifetime_is_rejected_with_no_session(): void
    {
        Config::set('services.cms.sso_max_lifetime', 300);

        $token = $this->signToken(['exp' => time() + 86400]);

        $response = $this->post('/sso/callback', ['token' => $token]);

        $response->assertStatus(401);
        $this->assertGuest('student');
        $this->assertNull(Student::where('cms_student_id', 'F2022-015')->first());
    }

    public function test_replay_protection_lasts_the_full_token_lifetime(): void
    {
        // Regression guard: the replay marker used to be stored with a TTL
        // derived from a possibly-absent `exp`, which floored it at one second
        // and let the same token be redeemed again moments later.
        $token = $this->signToken(['exp' => time() + 60]);

        $this->post('/sso/callback', ['token' => $token])
            ->assertRedirect(route('student.dashboard'));

        Auth::guard('student')->logout();

        $this->travel(5)->seconds();

        $response = $this->post('/sso/callback', ['token' => $token]);

        $response->assertStatus(401);
        $this->assertGuest('student');
    }

    public function test_missing_role_is_rejected_with_no_session(): void
    {
        $token = $this->signToken(['role' => null]);

        $response = $this->post('/sso/callback', ['token' => $token]);

        $response->assertStatus(401);
        $this->assertGuest('student');
        $this->assertGuest('counselor');
    }

    public function test_invalid_role_is_rejected_with_no_session(): void
    {
        $token = $this->signToken(['role' => 'admin']);

        $response = $this->post('/sso/callback', ['token' => $token]);

        $response->assertStatus(401);
        $this->assertGuest('student');
        $this->assertGuest('counselor');
    }

    public function test_token_cannot_be_replayed(): void
    {
        $token = $this->signToken();

        $this->post('/sso/callback', ['token' => $token])->assertRedirect(route('student.dashboard'));
        Auth::guard('student')->logout();

        $response = $this->post('/sso/callback', ['token' => $token]);

        $response->assertStatus(401);
        $this->assertGuest('student');
    }

    public function test_unknown_cms_student_is_rejected_with_no_session(): void
    {
        $token = $this->signToken(['sub' => 'F1999-999']);

        $response = $this->post('/sso/callback', ['token' => $token]);

        $response->assertStatus(404);
        $this->assertGuest('student');
    }

    public function test_unknown_cms_counselor_is_rejected_with_no_session(): void
    {
        $token = $this->signToken(['sub' => 'nobody.here', 'role' => 'counselor']);

        $response = $this->post('/sso/callback', ['token' => $token]);

        $response->assertStatus(404);
        $this->assertGuest('counselor');
    }

    public function test_unknown_cms_faculty_staff_is_rejected_with_no_session(): void
    {
        $token = $this->signToken(['sub' => 'nobody.here', 'role' => 'faculty_staff']);

        $response = $this->post('/sso/callback', ['token' => $token]);

        $response->assertStatus(404);
        $this->assertGuest('faculty_staff');
    }
}
