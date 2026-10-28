<?php

namespace Tests\Feature\Cms;

use App\Services\Cms\CmsClientInterface;
use App\Services\Cms\CmsCounselorNotFoundException;
use App\Services\Cms\CmsFacultyStaffNotFoundException;
use App\Services\Cms\CmsStudentNotFoundException;
use App\Services\Cms\MockCmsClient;
use Tests\TestCase;

class MockCmsClientTest extends TestCase
{
    public function test_resolves_from_container_as_mock_by_default(): void
    {
        $client = $this->app->make(CmsClientInterface::class);

        $this->assertInstanceOf(MockCmsClient::class, $client);
    }

    public function test_returns_identity_fields_for_a_known_student(): void
    {
        $client = $this->app->make(CmsClientInterface::class);

        $profile = $client->getStudentProfile('F2022-015');

        $this->assertSame('F2022-015', $profile['cms_student_id']);
        $this->assertSame('Ayesha Khan', $profile['name']);
        $this->assertArrayHasKey('assigned_counselor', $profile);
    }

    public function test_throws_for_an_unknown_student(): void
    {
        $client = $this->app->make(CmsClientInterface::class);

        $this->expectException(CmsStudentNotFoundException::class);

        $client->getStudentProfile('F1999-999');
    }

    public function test_returns_identity_fields_for_a_known_counselor(): void
    {
        $client = $this->app->make(CmsClientInterface::class);

        $profile = $client->getCounselorProfile('wajeeha.nabeel');

        $this->assertSame('wajeeha.nabeel', $profile['cms_counselor_id']);
        $this->assertSame('Wajeeha Nabeel, Campus Counselor', $profile['name']);
        $this->assertSame('wajeeha.nabeel@bnu.edu.pk', $profile['email']);
    }

    public function test_throws_for_an_unknown_counselor(): void
    {
        $client = $this->app->make(CmsClientInterface::class);

        $this->expectException(CmsCounselorNotFoundException::class);

        $client->getCounselorProfile('nobody.here');
    }

    public function test_returns_identity_fields_for_a_known_faculty_staff_member(): void
    {
        $client = $this->app->make(CmsClientInterface::class);

        $profile = $client->getFacultyStaffProfile('naveed.iqbal');

        $this->assertSame('naveed.iqbal', $profile['cms_employee_id']);
        $this->assertSame('Dr. Naveed Iqbal', $profile['name']);
        $this->assertSame('Associate Professor', $profile['designation']);
    }

    public function test_throws_for_an_unknown_faculty_staff_member(): void
    {
        $client = $this->app->make(CmsClientInterface::class);

        $this->expectException(CmsFacultyStaffNotFoundException::class);

        $client->getFacultyStaffProfile('nobody.here');
    }
}
