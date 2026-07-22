<?php

namespace App\Http\Requests\Api\V1\Public\Auth;

use App\Models\Commune;
use App\Models\PublicUser;
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
        $existingPublicUser = PublicUser::query()
            ->where('phone', $this->input('phone'))
            ->first();

        return [
            'public_user_type_id' => ['required', 'integer', 'exists:public_user_types,id'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^[0-9]{8,15}$/'],
            'is_whatsapp_number' => ['nullable', 'boolean'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('public_users', 'email')->ignore($existingPublicUser?->id)],
            'country_id' => ['required', 'integer', Rule::exists('countries', 'id')->where('status', 'active')],
            'city_id' => ['required', 'integer', Rule::exists('cities', 'id')->where('status', 'active')],
            'commune_id' => ['required', 'integer', Rule::exists('communes', 'id')->where('status', 'active')],
            'company_name' => ['nullable', 'string', 'max:180'],
            'company_registration_number' => ['nullable', 'string', 'max:120'],
            'tax_identifier' => ['nullable', 'string', 'max:120'],
            'business_sector' => ['nullable', 'string', 'max:120', 'exists:business_sectors,name'],
            'company_address' => ['nullable', 'string', 'max:255'],
            'commune' => ['nullable', 'string', 'max:120'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'verification_token' => ['required', 'uuid'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Cette adresse email est déjà utilisée par un autre compte.',
            'phone.regex' => 'Le numéro de téléphone doit contenir entre 8 et 15 chiffres.',
            'verification_token.required' => 'Veuillez vérifier votre numéro avec le code OTP avant de créer le compte.',
            'verification_token.uuid' => 'La vérification du numéro est invalide. Veuillez vérifier à nouveau le code OTP.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('public_user_type_id')) {
            return;
        }

        $defaultTypeId = PublicUserType::query()
            ->where('code', 'UP')
            ->where('status', 'active')
            ->value('id');

        if ($defaultTypeId !== null) {
            $this->merge([
                'public_user_type_id' => $defaultTypeId,
            ]);
        }
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $typeId = (int) $this->input('public_user_type_id');

            if ($typeId <= 0) {
                return;
            }

            $commune = Commune::query()
                ->with('city.country')
                ->whereKey($this->input('commune_id'))
                ->where('status', 'active')
                ->first();

            if ($commune !== null) {
                $city = $commune->city;
                $country = $city?->country;

                if ($city === null || $country === null || $city->status !== 'active' || $country->status !== 'active') {
                    $validator->errors()->add('commune_id', 'La commune sélectionnée est inactive ou invalide.');
                } elseif ((int) $this->input('city_id') !== (int) $city->id || (int) $this->input('country_id') !== (int) $country->id) {
                    $validator->errors()->add('commune_id', 'La commune sélectionnée ne correspond pas au pays et à la ville.');
                }
            }

            $publicUserType = PublicUserType::query()->find($typeId);

            if ($publicUserType === null || $publicUserType->status !== 'active') {
                $validator->errors()->add('public_user_type_id', 'Le type d’usager public sélectionné est invalide.');

                return;
            }

            $typeCode = strtoupper((string) $publicUserType->code);

            if ($typeCode !== 'UPE') {
                return;
            }

            foreach ([
                'company_name' => 'La raison sociale est obligatoire.',
                'company_registration_number' => 'Le RCCM ou numéro d’immatriculation est obligatoire.',
            ] as $field => $message) {
                if (! filled($this->input($field))) {
                    $validator->errors()->add($field, $message);
                }
            }
        });
    }
}
