<?php

namespace App\Http\Requests\Api\V1\Public\Reports;

use Illuminate\Foundation\Http\FormRequest;

class StoreIncidentReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'meter_id' => ['required', 'integer', 'exists:meters,id'],
            'application_id' => ['nullable', 'integer', 'exists:applications,id'],
            'organization_id' => ['nullable', 'integer', 'exists:organizations,id'],
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'commune_id' => ['required', 'integer', 'exists:communes,id'],
            'signal_code' => ['required', 'string'],
            'description' => ['nullable', 'string', 'max:1000'],
            'address' => ['nullable', 'string', 'max:255'],
            'occurred_at' => ['nullable', 'date'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'location_accuracy' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'location_source' => ['nullable', 'string', 'max:30'],
            'signal_payload' => ['nullable', 'array'],
        ];
    }
}
