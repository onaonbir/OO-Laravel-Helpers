<?php

namespace OnaOnbir\OOLaravelHelpers\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;

class Base64JsonCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        return $value ? json_decode(base64_decode($value), true) : [];
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if (! is_array($value)) {
            throw new InvalidArgumentException('The given value is not an array.');
        }

        return base64_encode(json_encode($value));
    }
}
