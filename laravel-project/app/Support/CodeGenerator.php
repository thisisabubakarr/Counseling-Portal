<?php

namespace App\Support;

use Illuminate\Support\Str;

class CodeGenerator
{
    public static function make(string $prefix): string
    {
        return $prefix.'-'.now()->format('YmdHis').'-'.Str::lower(Str::random(6));
    }
}
