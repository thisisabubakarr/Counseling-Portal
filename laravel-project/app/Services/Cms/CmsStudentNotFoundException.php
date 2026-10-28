<?php

namespace App\Services\Cms;

use RuntimeException;

class CmsStudentNotFoundException extends RuntimeException
{
    public static function forId(string $cmsStudentId): self
    {
        return new self("No CMS student profile found for id [{$cmsStudentId}].");
    }
}
