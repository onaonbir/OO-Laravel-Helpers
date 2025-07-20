<?php

namespace OnaOnbir\OOLaravelHelpers\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Facades\Crypt;
use InvalidArgumentException;

class EncryptedBase64JsonCast implements CastsAttributes
{

    public function get($model, string $key, $value, array $attributes)
    {

        return $value ? json_decode(base64_decode(Crypt::decryptString($value)), true) : [];
    }


    public function set($model, string $key, $value, array $attributes)
    {
        if (! is_array($value)) {
            throw new InvalidArgumentException('The given value is not an array.');
        }

        return Crypt::encryptString(base64_encode(json_encode($value)));
    }
}
