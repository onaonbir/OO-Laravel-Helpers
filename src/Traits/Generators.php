<?php

namespace OnaOnbir\OOLaravelHelpers\Traits;

use Illuminate\Support\Str;

trait Generators
{
    public static function createUniqueTextKey($model, string $column, int $length = 20, string $prefix = '', bool $strUpper = false): string
    {
        $modelClass = is_string($model) ? $model : get_class($model);

        $random = Str::random($length);
        $key = $prefix.($strUpper ? strtoupper($random) : $random);

        if ($modelClass::where($column, $key)->exists()) {
            return self::createUniqueTextKey($modelClass, $column, $length, $prefix, $strUpper);
        }

        return $key;
    }

    public static function createUniqueUuidKey($model, string $column = 'uuid'): string
    {
        $modelClass = is_string($model) ? $model : get_class($model);

        do {
            $uuid = (string) Str::uuid();
        } while ($modelClass::where($column, $uuid)->exists());

        return $uuid;
    }
}
