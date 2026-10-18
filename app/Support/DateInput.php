<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class DateInput
{
    /** Parse a Y-m-d query value; invalid or missing falls back to `$default` (today). */
    public static function parse(?string $value, ?CarbonImmutable $default = null): CarbonImmutable
    {
        $default ??= CarbonImmutable::today();

        if (! $value || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $default;
        }

        try {
            $date = CarbonImmutable::createFromFormat('!Y-m-d', $value);
        } catch (\Throwable) {
            return $default;
        }

        return $date && $date->format('Y-m-d') === $value ? $date : $default;
    }

    /** A day to view: never in the future (nothing can be recorded for tomorrow). */
    public static function day(Request $request, string $key = 'date'): CarbonImmutable
    {
        $date = self::parse($request->query($key));

        return $date->greaterThan(CarbonImmutable::today()) ? CarbonImmutable::today() : $date;
    }
}
