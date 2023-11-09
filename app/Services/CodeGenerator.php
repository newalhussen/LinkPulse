<?php

namespace App\Services;

use App\Models\Link;

class CodeGenerator
{
    /** Lowercase letters and digits without look-alikes (no 0/o, 1/l/i). */
    private const ALPHABET = 'abcdefghjkmnpqrstuvwxyz23456789';

    /** Paths the application itself uses; they can never be short codes. */
    public const RESERVED = ['login', 'register', 'logout', 'dashboard', 'links', 'up', 'qr', 'api', 'admin', 'build', 'storage', 'assets', 'favicon.ico', 'robots.txt'];

    public function isReserved(string $code): bool
    {
        return in_array(strtolower($code), self::RESERVED, true);
    }

    public function generate(int $length = 7): string
    {
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
            }
            if (! $this->isReserved($code) && ! Link::where('code', $code)->exists()) {
                return $code;
            }
        }

        return $this->generate($length + 1); // extremely unlikely: widen the space
    }
}
