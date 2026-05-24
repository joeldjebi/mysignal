<?php

namespace App\Http\Requests\Api\V1\Public\PurchaseReceipts;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePurchaseReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'material_name' => ['sometimes', 'required', 'string', 'max:160'],
            'purchase_date' => ['sometimes', 'required', 'date', 'before_or_equal:today'],
            'amount' => ['sometimes', 'required', 'numeric', 'min:0', 'max:999999999.99'],
        ];
    }
}
