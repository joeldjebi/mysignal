<?php

namespace App\Http\Requests\Api\V1\Public\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RequestPublicPasswordResetOtpRequest extends FormRequest
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
            'phone' => [
                'required',
                'string',
                'regex:/^[0-9]{8,20}$/',
                Rule::exists('public_users', 'phone')->where('status', 'active'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.exists' => 'Aucun compte UP actif n a ete trouve pour ce numero.',
        ];
    }
}
