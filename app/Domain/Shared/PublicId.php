<?php

namespace App\Domain\Shared;

use Illuminate\Support\Str;

final class PublicId
{
    public static function make(string $prefix): string
    {
        return strtoupper($prefix).'_'.strtoupper((string) Str::ulid());
    }
}
