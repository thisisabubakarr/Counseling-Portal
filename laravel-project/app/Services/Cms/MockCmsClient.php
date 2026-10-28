<?php

namespace App\Services\Cms;

/**
 * Stand-in for the real CMS API, which does not exist yet (see
 * bnu-counselor-cms-integration-architecture.md, Section 3). Fixture data
 * only — swap the CmsClientInterface binding in AppServiceProvider once a
 * real client is built.
 */
class MockCmsClient implements CmsClientInterface
{
    /**
     * Student identity fixtures, keyed by CMS student id.
     *
     * BNU student id format, confirmed by the client: `F2022-015` —
     * intake term letter (F = Fall), four-digit degree start year, a
     * mandatory hyphen, then the registration number. This is the same
     * value students already hold as `roll_number`, which is what the
     * `cms_student_id` backfill in migration 2026_08_26_000001 relies on.
     *
     * @var array<string, array>
     */
    private array $fixtures = [
        'F2022-015' => [
            'cms_student_id' => 'F2022-015',
            'name' => 'Ayesha Khan',
            'dob' => '2003-05-14',
            'phone' => '0301-1234567',
            'department' => 'Computer Science',
            'semester' => '4th Semester',
            'cgpa' => 3.42,
            'assigned_counselor' => null,
        ],
        'F2023-042' => [
            'cms_student_id' => 'F2023-042',
            'name' => 'Bilal Ahmed',
            'dob' => '2002-11-02',
            'phone' => '0302-7654321',
            'department' => 'Business Administration',
            'semester' => '6th Semester',
            'cgpa' => 3.05,
            'assigned_counselor' => null,
        ],
    ];

    /**
     * Counselor identity fixtures, keyed by CMS counselor id.
     *
     * BNU staff id format, confirmed by the client: `firstname.lastname`, e.g.
     * `muhammad.ali`. This is the local part of the BNU address, so it is derived
     * from email rather than built from the name — BNU's mail system has already
     * resolved duplicate names, which a name-derived id would collide on.
     *
     * @var array<string, array>
     */
    private array $counselorFixtures = [
        'wajeeha.nabeel' => [
            'cms_counselor_id' => 'wajeeha.nabeel',
            'name' => 'Wajeeha Nabeel, Campus Counselor',
            'email' => 'wajeeha.nabeel@bnu.edu.pk',
            'active' => true,
        ],
        'hareem.jamil' => [
            'cms_counselor_id' => 'hareem.jamil',
            'name' => 'Hareem Jamil, Campus Counselor',
            'email' => 'hareem.jamil@bnu.edu.pk',
            'active' => true,
        ],
    ];

    public function getStudentProfile(string $cmsStudentId): array
    {
        if (! isset($this->fixtures[$cmsStudentId])) {
            throw CmsStudentNotFoundException::forId($cmsStudentId);
        }

        return $this->fixtures[$cmsStudentId];
    }

    public function getCounselorProfile(string $cmsCounselorId): array
    {
        if (! isset($this->counselorFixtures[$cmsCounselorId])) {
            throw CmsCounselorNotFoundException::forId($cmsCounselorId);
        }

        return $this->counselorFixtures[$cmsCounselorId];
    }

    /**
     * Faculty/staff identity fixtures, keyed by CMS employee id. Same
     * `firstname.lastname` format as counselors.
     *
     * @var array<string, array>
     */
    private array $facultyStaffFixtures = [
        'naveed.iqbal' => [
            'cms_employee_id' => 'naveed.iqbal',
            'name' => 'Dr. Naveed Iqbal',
            'designation' => 'Associate Professor',
            'department' => 'Computer Science',
            'email' => 'naveed.iqbal@bnu.edu.pk',
            'phone' => '0303-1112223',
        ],
        'fatima.raza' => [
            'cms_employee_id' => 'fatima.raza',
            'name' => 'Ms. Fatima Raza',
            'designation' => 'Administrative Officer',
            'department' => 'Registrar Office',
            'email' => 'fatima.raza@bnu.edu.pk',
            'phone' => '0304-4445556',
        ],
    ];

    public function getFacultyStaffProfile(string $cmsEmployeeId): array
    {
        if (! isset($this->facultyStaffFixtures[$cmsEmployeeId])) {
            throw CmsFacultyStaffNotFoundException::forId($cmsEmployeeId);
        }

        return $this->facultyStaffFixtures[$cmsEmployeeId];
    }
}
