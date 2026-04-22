<?php

namespace App\Http\Requests\Api\V1\Partner\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class PartnerLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('phone')) {
            $normalizedPhone = preg_replace('/\D+/', '', (string) $this->input('phone'));

            if ($normalizedPhone !== null) {
                $this->merge([
                    'phone' => $normalizedPhone,
                ]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'regex:/^[0-9]{8,20}$/'],
            'password' => ['required', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->filled('email') && ! $this->filled('phone')) {
                $validator->errors()->add('phone', 'Renseignez un numero ou un email.');
            }
        });
    }
}
