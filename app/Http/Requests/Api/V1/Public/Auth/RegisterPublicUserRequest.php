<?php

namespace App\Http\Requests\Api\V1\Public\Auth;

use App\Models\PublicUserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class RegisterPublicUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'public_user_type_id' => ['required', 'integer', 'exists:public_user_types,id'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^[0-9]{8,15}$/', 'unique:public_users,phone'],
            'is_whatsapp_number' => ['nullable', 'boolean'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('public_users', 'email')],
            'company_name' => ['nullable', 'string', 'max:180'],
            'company_registration_number' => ['nullable', 'string', 'max:120'],
            'tax_identifier' => ['nullable', 'string', 'max:120'],
            'business_sector' => ['nullable', 'string', 'max:120', 'exists:business_sectors,name'],
            'company_address' => ['nullable', 'string', 'max:255'],
            'commune' => ['required', 'string', 'max:120', 'exists:communes,name'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'verification_token' => ['required', 'uuid'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $typeId = (int) $this->input('public_user_type_id');

            if ($typeId <= 0) {
                return;
            }

            $publicUserType = PublicUserType::query()->find($typeId);

            if ($publicUserType === null || $publicUserType->status !== 'active') {
                $validator->errors()->add('public_user_type_id', 'Le type d usager public selectionne est invalide.');

                return;
            }

            $typeCode = strtoupper((string) $publicUserType->code);

            if (in_array($typeCode, ['UPE', 'UPTI'], true) && ! filled($this->input('business_sector'))) {
                $validator->errors()->add('business_sector', 'Le secteur d activite est obligatoire.');
            }

            if ($typeCode !== 'UPE') {
                return;
            }

            foreach ([
                'company_name' => 'La raison sociale est obligatoire.',
                'company_registration_number' => 'Le RCCM ou numero d immatriculation est obligatoire.',
                'tax_identifier' => 'L identifiant fiscal est obligatoire.',
                'company_address' => 'L adresse de l entreprise est obligatoire.',
            ] as $field => $message) {
                if (! filled($this->input($field))) {
                    $validator->errors()->add($field, $message);
                }
            }
        });
    }
}
