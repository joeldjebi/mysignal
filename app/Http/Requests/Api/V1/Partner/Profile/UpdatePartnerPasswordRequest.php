<?php

namespace App\Http\Requests\Api\V1\Partner\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePartnerPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
