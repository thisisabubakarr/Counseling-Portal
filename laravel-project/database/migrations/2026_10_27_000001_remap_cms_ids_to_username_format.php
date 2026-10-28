<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Realigns `cms_counselor_id` / `cms_employee_id` with the identifier format BNU CMS
 * actually sends for staff: `firstname.lastname`, e.g. `muhammad.ali` (confirmed by
 * the client, 2026-10-27).
 *
 * Why this is needed: migration 2026_09_23_000001 backfilled `cms_counselor_id` from
 * `counselor_code`, which holds app-internal codes like `CNS-WAJEEHA`. Those never
 * match what CMS sends, so `CmsSsoController::loginCounselor()` would treat an existing
 * counselor as new, attempt an insert, and fail on the unique index on `counselors.email`
 * — a 500 on the first real CMS login rather than a silent duplicate.
 *
 * The CMS identifier is the local part of the BNU address, so it is derived from `email`
 * rather than constructed from `name`. That matters: BNU's mail system has already
 * resolved duplicate names (two people called Muhammad Ali cannot share an address),
 * whereas building `firstname.lastname` from a name string would collide.
 *
 * Rows whose email is missing, malformed, or would collide with another row's derived
 * value are left untouched rather than guessed at. Re-runnable.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->remap('counselors', 'cms_counselor_id');
        $this->remap('faculty_staff', 'cms_employee_id');
    }

    public function down(): void
    {
        // Restore the previous backfill source so this is reversible.
        foreach ([['counselors', 'cms_counselor_id', 'counselor_code'],
                  ['faculty_staff', 'cms_employee_id', 'employee_id']] as [$table, $cmsColumn, $source]) {
            DB::table($table)->orderBy('id')->each(function ($row) use ($table, $cmsColumn, $source) {
                DB::table($table)->where('id', $row->id)->update([
                    $cmsColumn => $row->{$source},
                ]);
            });
        }
    }

    private function remap(string $table, string $cmsColumn): void
    {
        $rows = DB::table($table)->orderBy('id')->get(['id', 'email', $cmsColumn]);

        // Work out every target value first so collisions can be detected before writing.
        $derived = [];

        foreach ($rows as $row) {
            $username = $this->usernameFromEmail($row->email ?? null);

            if ($username === null) {
                continue;
            }

            $derived[$username][] = $row->id;
        }

        foreach ($derived as $username => $ids) {
            if (count($ids) > 1) {
                // Two rows derive the same CMS identifier. Writing either would be a guess
                // about which staff member CMS means, and the unique index would reject the
                // second anyway. Left for a human to resolve.
                continue;
            }

            $id = $ids[0];

            // Don't fight an existing correct value, and don't collide with another row
            // that already holds this identifier.
            $takenByAnother = DB::table($table)
                ->where($cmsColumn, $username)
                ->where('id', '!=', $id)
                ->exists();

            if ($takenByAnother) {
                continue;
            }

            DB::table($table)->where('id', $id)->update([$cmsColumn => $username]);
        }
    }

    /**
     * `wajeeha.nabeel@bnu.edu.pk` -> `wajeeha.nabeel`. Null when there is nothing
     * dependable to derive from.
     */
    private function usernameFromEmail(?string $email): ?string
    {
        if ($email === null || ! str_contains($email, '@')) {
            return null;
        }

        $local = strtolower(trim(explode('@', $email, 2)[0]));

        return $local === '' ? null : $local;
    }
};
