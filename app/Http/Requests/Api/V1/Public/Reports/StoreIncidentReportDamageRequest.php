<?php

namespace App\Http\Requests\Api\V1\Public\Reports;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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
            'purchase_receipt_id' => ['nullable', 'integer', 'exists:purchase_receipts,id'],
            'receipt_material_name' => ['nullable', 'string', 'max:160'],
            'receipt_purchase_date' => ['nullable', 'date', 'before_or_equal:today'],
            'receipt_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'receipt_attachment' => ['nullable', 'file', 'max:10240', 'mimetypes:image/jpeg,image/png,image/webp,image/gif,image/heic,image/heif,application/pdf'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $hasInlineReceipt = $this->filled('receipt_material_name')
                || $this->filled('receipt_purchase_date')
                || $this->filled('receipt_amount')
                || $this->hasFile('receipt_attachment');

            if (! $hasInlineReceipt) {
                return;
            }

            foreach (['receipt_material_name', 'receipt_purchase_date', 'receipt_amount'] as $field) {
                if (! $this->filled($field)) {
                    $validator->errors()->add($field, 'Les trois champs du reçu sont requis pour enregistrer un reçu pendant la déclaration.');
                }
            }
        });
    }
}
