<?php

namespace OnaOnbir\OOLaravelHelpers\Support;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Support\Carbon as IlluminateCarbon;
use NumberFormatter;
use TypeError;

class Readable
{
    public static function getNumber(int $input, string $delimiter = ','): string
    {
        return number_format($input, 0, '.', $delimiter);
    }

    public static function getHumanNumber(int $input, bool $showDecimal = false, int $decimals = 0): string
    {
        $decimals = $showDecimal && $decimals == 0 ? 1 : $decimals;
        $floorNumber = 0;

        if ($input >= 0 && $input < 1000) {
            $getFloor = floor($input);
            $suffix = '';
        } elseif ($input < 1000000) {
            $getFloor = floor($input / 1000);
            $floorNumber = 1000;
            $suffix = 'K';
        } elseif ($input < 1000000000) {
            $getFloor = floor($input / 1000000);
            $floorNumber = 1000000;
            $suffix = 'M';
        } elseif ($input < 1000000000000) {
            $getFloor = floor($input / 1000000000);
            $floorNumber = 1000000000;
            $suffix = 'B';
        } else {
            $getFloor = floor($input / 1000000000000);
            $floorNumber = 1000000000000;
            $suffix = 'T';
        }

        if ($showDecimal && $floorNumber > 0) {
            $input -= ($getFloor * $floorNumber);
            if ($input > 0) {
                $input /= $floorNumber;
                $getFloor += $input;
            }
        }

        return ! empty($getFloor.$suffix) ? number_format($getFloor, $decimals).$suffix : '0';
    }

    public static function getNumberToString($input, string $lang = 'tr'): string
    {
        if (! in_array(gettype($input), ['integer', 'double', 'float'])) {
            throw new TypeError('Wrong Input Type.');
        }

        $digit = new NumberFormatter($lang, NumberFormatter::SPELLOUT);

        return $digit->format($input);
    }

    public static function getDecimal($input, int $decimals = 2, string $point = '.', string $delimiter = ','): ?string
    {
        if (! in_array(gettype($input), ['integer', 'double', 'float'])) {
            throw new TypeError('Wrong Input Type.');
        }

        return number_format($input, $decimals, $point, $delimiter);
    }

    public static function getDecInt($input, int $decimals_length = 2, string $point = '.', string $delimiter = ','): ?string
    {
        if (! in_array(gettype($input), ['integer', 'double', 'float'])) {
            throw new TypeError('Wrong Input Type.');
        }

        if (is_float($input)) {
            $decInt = $input - (int) $input;
            if ($decInt == 0) {
                $input = (int) $input;
                $decimals_length = 0;
            }
        } elseif (is_int($input)) {
            $decimals_length = 0;
        }

        return number_format($input, $decimals_length, $point, $delimiter);
    }

    public static function prepareDateTime(&$input, ?string $tz = null)
    {
        if (! ($input instanceof Carbon) && ! ($input instanceof IlluminateCarbon)) {
            $input = Carbon::parse($input);
        }

        if ($tz) {
            $input->setTimezone($tz);
        }
    }

    public static function getDate($input, ?string $timezone = null): ?string
    {
        self::prepareDateTime($input, $timezone);

        return $input->day.' '.$input->monthName.' '.$input->year;
    }

    public static function getDateWoYear($input, ?string $timezone = null): ?string
    {
        self::prepareDateTime($input, $timezone);

        return $input->day.' '.$input->monthName;
    }

    public static function getTime($input, $is12 = false, bool $hasSeconds = false, ?string $timezone = null): ?string
    {
        self::prepareDateTime($input, $timezone);

        if ($is12) {
            return $input->format('h:i'.($hasSeconds ? ':s' : '').' ').$input->meridiem();
        }

        return $input->format('H:i'.($hasSeconds ? ':s' : ''));
    }

    public static function getDateTime($input, $is12 = false, bool $hasSeconds = false, ?string $timezone = null, ?string $customFormat = null): ?string
    {
        self::prepareDateTime($input, $timezone);

        if ($customFormat) {
            return $input->isoFormat($customFormat);
        }

        return $input->isoFormat('dddd, MMMM DD, YYYY '.($is12 ? 'hh:mm'.($hasSeconds ? ':ss' : '').' A' : 'HH:mm'.($hasSeconds ? ':ss' : '')));
    }

    public static function getDiffDateTime($old, $new = null, ?string $timezone = null): ?string
    {
        self::prepareDateTime($old, $timezone);
        self::prepareDateTime($new, $timezone);

        return $old->diffForHumans($new);
    }

    public static function getTimeLength(int $input, string $comma = ' ', bool $short = false): ?string
    {
        $years = floor($input / 31104000);
        $input -= $years * 31104000;
        $months = floor($input / 2592000);
        $input -= $months * 2592000;
        $days = floor($input / 86400);
        $input -= $days * 86400;
        $hours = floor($input / 3600);
        $input -= $hours * 3600;
        $minutes = floor($input / 60);
        $input -= $minutes * 60;
        $seconds = $input % 60;

        $interval = new CarbonInterval($years, $months, null, $days, $hours, $minutes, $seconds);

        return $interval->forHumans(['join' => $comma, 'short' => $short]);
    }

    public static function getDateTimeLength($old, $new = null, bool $full = false, string $comma = ' ', ?string $timezone = null): ?string
    {
        self::prepareDateTime($old, $timezone);
        self::prepareDateTime($new, $timezone);

        return $old->diffForHumans($new, ['parts' => $full ? 7 : 0, 'join' => $comma]);
    }

    public static function getSize(int $bytes, bool $decimal = true): ?string
    {
        if ($bytes <= 0) {
            return null;
        }

        $calcBase = $decimal ? 1000 : 1024;
        $base = log($bytes) / log($calcBase);
        $suffixes = $decimal ? ['B', 'KB', 'MB', 'GB', 'TB'] : ['B', 'KiB', 'MiB', 'GiB', 'TiB'];

        return round(pow($calcBase, $base - floor($base)), 2).' '.$suffixes[floor($base)];
    }
}
