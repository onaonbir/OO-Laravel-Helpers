<?php

namespace OnaOnbir\OOLaravelHelpers\Support;

use Random\RandomException;
use Throwable;

class TokenCrypt
{
    /**
     * @throws RandomException
     */
    public static function encrypt(string $plainText): string
    {
        $key = substr(hash('sha256', env('TOKENN_CRYPT_SECRET','password')), 0, 32);
        $iv = random_bytes(16);

        $encrypted = openssl_encrypt($plainText, 'AES-256-CBC', $key, 0, $iv);

        return base64_encode($iv.'::'.$encrypted);
    }

    public static function decrypt(string $encoded): ?string
    {
        try {
            $decoded = base64_decode($encoded);
            [$iv, $encrypted] = explode('::', $decoded, 2);

            $key = substr(hash('sha256', env('TOKENN_CRYPT_SECRET','password')), 0, 32);

            return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
        } catch (Throwable $e) {
            return null;
        }
    }
}
