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
            'meter_id' => ['nullable', 'integer', 'exists:meters,id'],
            'application_id' => ['nullable', 'integer', 'exists:applications,id'],
            'organization_type_id' => ['nullable', 'integer', 'exists:organization_types,id'],
            'organization_id' => ['nullable', 'integer', 'exists:organizations,id'],
            'signal_code' => ['required', 'string'],
            'signal_sub_type_code' => ['nullable', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:1000'],
            'occurred_at' => ['nullable', 'date'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'location_accuracy' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'location_source' => ['nullable', 'string', 'max:30'],
            'signal_attachment' => ['nullable', 'file', 'max:51200', 'mimetypes:image/jpeg,image/png,image/webp,image/gif,image/heic,image/heif,video/mp4,video/quicktime,video/x-msvideo,video/mpeg'],
        ];
    }
}
