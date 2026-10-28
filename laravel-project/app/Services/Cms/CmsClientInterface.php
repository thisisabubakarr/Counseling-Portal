<?php

namespace App\Services\Cms;

interface CmsClientInterface
{
    /**
     * Fetch a student's identity profile from the CMS by their CMS-issued
     * student identifier. Returns identity fields only — never clinical
     * or counseling data, which the CMS has no concept of.
     *
     * Expected shape:
     * [
     *     'cms_student_id' => string,
     *     'name' => string,
     *     'dob' => string|null,        // Y-m-d
     *     'phone' => string|null,
     *     'department' => string|null,
     *     'semester' => string|null,
     *     'cgpa' => float|null,
     *     'assigned_counselor' => string|null, // CMS-side counselor identifier, if CMS tracks this at all
     * ]
     *
     * @throws CmsStudentNotFoundException if no student matches $cmsStudentId
     */
    public function getStudentProfile(string $cmsStudentId): array;

    /**
     * Fetch a counselor's identity profile from the CMS by their CMS-issued
     * counselor identifier. Identity fields only — counselors have no
     * clinical/consent columns to begin with, but this stays scoped to
     * identity the same way getStudentProfile() does.
     *
     * Expected shape:
     * [
     *     'cms_counselor_id' => string,
     *     'name' => string,
     *     'email' => string,
     *     'active' => bool|null,
     * ]
     *
     * @throws CmsCounselorNotFoundException if no counselor matches $cmsCounselorId
     */
    public function getCounselorProfile(string $cmsCounselorId): array;

    /**
     * Fetch a faculty/staff member's identity profile from the CMS by their
     * CMS-issued employee identifier. Identity fields only.
     *
     * Expected shape:
     * [
     *     'cms_employee_id' => string,
     *     'name' => string,
     *     'designation' => string|null,
     *     'department' => string|null,
     *     'email' => string|null,
     *     'phone' => string|null,
     * ]
     *
     * @throws CmsFacultyStaffNotFoundException if no faculty/staff member matches $cmsEmployeeId
     */
    public function getFacultyStaffProfile(string $cmsEmployeeId): array;
}
