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
            'damage_summary' => ['required', 'string', 'max:255'],
            'damage_amount_estimated' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'damage_notes' => ['nullable', 'string', 'max:3000'],
            'damage_attachment' => ['nullable', 'array'],
            'damage_attachment.name' => ['nullable', 'string', 'max:255'],
            'damage_attachment.mime_type' => ['nullable', 'string', 'max:100'],
            'damage_attachment.data_url' => ['nullable', 'string', 'max:10000000'],
        ];
    }
}
