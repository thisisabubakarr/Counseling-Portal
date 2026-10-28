<?php

namespace App\Services\Cms;

use RuntimeException;

class CmsCounselorNotFoundException extends RuntimeException
{
    public static function forId(string $cmsCounselorId): self
    {
        return new self("No CMS counselor profile found for id [{$cmsCounselorId}].");
    }
}
