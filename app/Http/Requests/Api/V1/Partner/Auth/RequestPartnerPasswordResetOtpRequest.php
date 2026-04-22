<?php

namespace App\Http\Requests\Api\V1\Partner\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RequestPartnerPasswordResetOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('phone')) {
            $this->merge([
                'phone' => preg_replace('/\D+/', '', (string) $this->input('phone')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'regex:/^[0-9]{8,20}$/'],
        ];
    }
}
