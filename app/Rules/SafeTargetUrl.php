<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** A destination must be a plain http(s) URL, without credentials, and must not point back at this service (redirect loops). */
class SafeTargetUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $parts = is_string($value) ? parse_url($value) : false;
        if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
            $fail('Enter a full web address, for example https://example.com/page.');

            return;
        }
        if (! in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            $fail('Only http and https addresses can be shortened.');

            return;
        }
        if (isset($parts['user']) || isset($parts['pass'])) {
            $fail('Addresses with a username or password are not allowed.');

            return;
        }
        $own = parse_url((string) config('app.url'), PHP_URL_HOST);
        if ($own && strtolower($parts['host']) === strtolower($own)) {
            $fail('That address already points to this service.');
        }
    }
}
