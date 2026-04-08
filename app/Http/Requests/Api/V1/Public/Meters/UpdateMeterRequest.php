<?php

namespace App\Http\Requests\Api\V1\Public\Meters;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMeterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'application_id' => ['sometimes', 'integer', 'exists:applications,id'],
            'organization_id' => ['sometimes', 'integer', 'exists:organizations,id'],
            'label' => ['sometimes', 'nullable', 'string', 'max:120'],
            'commune' => ['sometimes', 'nullable', 'string', 'max:120', 'exists:communes,name'],
            'neighborhood' => ['sometimes', 'nullable', 'string', 'max:120'],
            'sub_neighborhood' => ['sometimes', 'nullable', 'string', 'max:120'],
            'address' => ['sometimes', 'nullable', 'string', 'max:255'],
            'latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
            'location_accuracy' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:10000'],
            'location_source' => ['sometimes', 'nullable', 'string', 'max:30'],
            'is_primary' => ['sometimes', 'boolean'],
        ];
    }
}
