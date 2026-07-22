<?php

namespace App\Http\Requests\Api\V1\Public\Profile;

use App\Models\Commune;
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
            'country_id' => ['sometimes', 'required', 'integer', Rule::exists('countries', 'id')->where('status', 'active')],
            'city_id' => ['sometimes', 'required', 'integer', Rule::exists('cities', 'id')->where('status', 'active')],
            'commune_id' => ['sometimes', 'required', 'integer', Rule::exists('communes', 'id')->where('status', 'active')],
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
                $validator->errors()->add('public_user_type_id', 'Le type d’usager public ne peut pas être modifié depuis le profil.');

                return;
            }

            if ($this->hasAny(['country_id', 'city_id', 'commune_id']) && ! $this->has(['country_id', 'city_id', 'commune_id'])) {
                $validator->errors()->add('commune_id', 'Le pays, la ville et la commune doivent être envoyés ensemble.');
            }

            if ($this->has(['country_id', 'city_id', 'commune_id'])) {
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
            }

            $typeId = (int) $this->user('public_api')?->public_user_type_id;

            if ($typeId <= 0) {
                return;
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
                $incomingValue = $this->input($field);
                $currentValue = $this->user('public_api')?->{$field};

                if (! filled($incomingValue) && ! filled($currentValue)) {
                    $validator->errors()->add($field, $message);
                }
            }
        });
    }
}
