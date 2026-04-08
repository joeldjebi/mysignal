<?php

namespace App\Http\Requests\Api\V1\Public\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginPublicUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'regex:/^[0-9]{8,15}$/'],
            'password' => ['required', 'string'],
        ];
    }
}
