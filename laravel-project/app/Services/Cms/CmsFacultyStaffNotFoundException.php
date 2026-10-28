<?php

namespace App\Services\Cms;

use RuntimeException;

class CmsFacultyStaffNotFoundException extends RuntimeException
{
    public static function forId(string $cmsEmployeeId): self
    {
        return new self("No CMS faculty/staff profile found for id [{$cmsEmployeeId}].");
    }
}
