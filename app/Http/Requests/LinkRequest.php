<?php

namespace App\Http\Requests;

use App\Models\Link;
use App\Rules\SafeTargetUrl;
use App\Services\CodeGenerator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

/** Create and update share one request; the short code can only be chosen on create. */
class LinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'target_url' => ['required', 'string', 'max:2048', new SafeTargetUrl],
            'title' => ['nullable', 'string', 'max:120'],
            'expires_on' => ['nullable', 'date', 'after_or_equal:today'],
        ];
        if ($this->isMethod('POST')) {
            $rules['code'] = ['nullable', 'string', 'min:3', 'max:32', 'regex:/^[A-Za-z0-9_-]+$/', function ($attr, $value, $fail) {
                if (app(CodeGenerator::class)->isReserved($value)) {
                    $fail('That alias is reserved. Choose another.');
                } elseif (Link::whereRaw('lower(code) = ?', [strtolower($value)])->exists()) {
                    $fail('That alias is already taken.');
                }
            }];
        } else {
            $rules['is_active'] = ['sometimes', 'boolean'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'code.regex' => 'Use letters, numbers, dashes and underscores only.',
            'expires_on.after_or_equal' => 'The expiry date cannot be in the past.',
        ];
    }

    /** Expiry is the end of the chosen day in the application time zone. */
    public function expiresAt(): ?Carbon
    {
        $d = $this->input('expires_on');

        return $d ? Carbon::parse($d)->endOfDay() : null;
    }
}
