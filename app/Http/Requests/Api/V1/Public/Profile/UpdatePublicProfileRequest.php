<?php

namespace App\Http\Requests\Api\V1\Public\Profile;

use App\Models\PublicUserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdatePublicProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user('public_api')?->id;

        return [
            'first_name' => ['sometimes', 'required', 'string', 'max:100'],
            'last_name' => ['sometimes', 'required', 'string', 'max:100'],
            'is_whatsapp_number' => ['sometimes', 'nullable', 'boolean'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255', Rule::unique('public_users', 'email')->ignore($userId)],
            'company_name' => ['sometimes', 'nullable', 'string', 'max:180'],
            'company_registration_number' => ['sometimes', 'nullable', 'string', 'max:120'],
            'tax_identifier' => ['sometimes', 'nullable', 'string', 'max:120'],
            'business_sector' => ['sometimes', 'nullable', 'string', 'max:120', 'exists:business_sectors,name'],
            'company_address' => ['sometimes', 'nullable', 'string', 'max:255'],
            'commune' => ['sometimes', 'required', 'string', 'max:120', 'exists:communes,name'],
            'address' => ['sometimes', 'nullable', 'string', 'max:255'],
            'latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
            'location_accuracy' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:10000'],
            'location_source' => ['sometimes', 'nullable', 'string', 'max:30'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->has('public_user_type_id')) {
                $validator->errors()->add('public_user_type_id', 'Le type d usager public ne peut pas etre modifie depuis le profil.');

                return;
            }

            $typeId = (int) $this->user('public_api')?->public_user_type_id;

            if ($typeId <= 0) {
                return;
            }

            $publicUserType = PublicUserType::query()->find($typeId);

            if ($publicUserType === null || $publicUserType->status !== 'active') {
                $validator->errors()->add('public_user_type_id', 'Le type d usager public selectionne est invalide.');

                return;
            }

            if ($publicUserType->profile_kind !== 'business') {
                return;
            }

            foreach ([
                'company_name' => 'La raison sociale est obligatoire.',
                'company_registration_number' => 'Le RCCM ou numero d immatriculation est obligatoire.',
                'tax_identifier' => 'L identifiant fiscal est obligatoire.',
                'business_sector' => 'Le secteur d activite est obligatoire.',
                'company_address' => 'L adresse de l entreprise est obligatoire.',
            ] as $field => $message) {
                $incomingValue = $this->input($field);
                $currentValue = $this->user('public_api')?->{$field};

                if (! filled($incomingValue) && ! filled($currentValue)) {
                    $validator->errors()->add($field, $message);
                }
            }
        });
    }
}
