<?php

namespace App\Http\Requests\Api\V1\Public\Reports;

use Illuminate\Foundation\Http\FormRequest;

class StoreIncidentReportDamageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'report_id' => ['nullable', 'integer', 'exists:incident_reports,id'],
            'damage_summary' => ['nullable', 'string', 'max:255'],
            'damage_amount_estimated' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'damage_notes' => ['nullable', 'string', 'max:3000'],
            'damage_attachment' => ['required', 'file', 'max:10240', 'mimetypes:image/jpeg,image/png,image/webp,image/gif,image/heic,image/heif,application/pdf'],
        ];
    }
}
