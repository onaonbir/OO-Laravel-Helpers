<?php

use OnaOnbir\OOLaravelHelpers\Support\Readable;

function ReadableNumber(int $input, string $delimiter = ','): string
{
    return Readable::getNumber($input, $delimiter);
}

function ReadableNumberToString($input, string $lang = 'en'): string
{
    return Readable::getNumberToString($input, $lang);
}

function ReadableHumanNumber(int $input, bool $showDecimal = false, int $decimals = 0): string
{
    return Readable::getHumanNumber($input, $showDecimal, $decimals);
}

function ReadableDecimal($input, int $decimals = 2, string $point = '.', string $delimiter = ','): ?string
{
    return Readable::getDecimal($input, $decimals, $point, $delimiter);
}

function ReadableDecInt($input, int $decimals = 2, string $point = '.', string $delimiter = ','): ?string
{
    return Readable::getDecInt($input, $decimals, $point, $delimiter);
}

function ReadableDate($input, ?string $timezone = null): ?string
{
    return Readable::getDate($input, $timezone);
}

function ReadableTime($input, $is12 = false, bool $hasSeconds = false, ?string $timezone = null): ?string
{
    return Readable::getTime($input, $is12, $hasSeconds, $timezone);
}

function ReadableDateTime($input, $is12 = false, bool $hasSeconds = false, ?string $timezone = null): ?string
{
    return Readable::getDateTime($input, $is12, $hasSeconds, $timezone);
}

function ReadableDiffDateTime($old, $new = null, ?string $timezone = null): ?string
{
    return Readable::getDiffDateTime($old, $new, $timezone);
}

function ReadableTimeLength(int $input, string $comma = ' ', bool $short = false): ?string
{
    return Readable::getTimeLength($input, $comma, $short);
}

function ReadableDateTimeLength($old, $new = null, bool $full = false, string $comma = ' ', ?string $timezone = null): ?string
{
    return Readable::getDateTimeLength($old, $new, $full, $comma, $timezone);
}

function ReadableSize(int $bytes, bool $decimal = true): ?string
{
    return Readable::getSize($bytes, $decimal);
}
