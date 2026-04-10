<?php

namespace App\Http\Requests\Api\V1\Public\Households;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InviteHouseholdMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => [
                'required',
                'string',
                'regex:/^[0-9]{8,15}$/',
                Rule::exists('public_users', 'phone'),
                'different:actor_phone',
            ],
            'relationship' => ['required', 'string', 'max:50'],
            'meter_id' => ['nullable', 'integer', 'exists:meters,id'],
            'actor_phone' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'actor_phone' => $this->user('public_api')?->phone,
        ]);
    }

    public function messages(): array
    {
        return [
            'phone.exists' => 'Ce numero ne correspond a aucun compte public existant.',
            'phone.different' => 'Vous ne pouvez pas vous inviter vous-meme dans votre propre foyer.',
        ];
    }
}
