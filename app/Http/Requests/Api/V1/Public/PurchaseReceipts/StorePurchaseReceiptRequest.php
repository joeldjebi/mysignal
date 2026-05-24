<?php

namespace App\Http\Requests\Api\V1\Public\PurchaseReceipts;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'material_name' => ['required', 'string', 'max:160'],
            'purchase_date' => ['required', 'date', 'before_or_equal:today'],
            'amount' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
            'receipt_file' => ['nullable', 'file', 'max:10240', 'mimetypes:image/jpeg,image/png,image/webp,image/gif,image/heic,image/heif,application/pdf'],
        ];
    }
}
