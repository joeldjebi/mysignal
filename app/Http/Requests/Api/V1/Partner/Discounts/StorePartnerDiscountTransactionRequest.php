<?php

namespace App\Http\Requests\Api\V1\Partner\Discounts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePartnerDiscountTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'card_uuid' => ['required', 'string', 'size:36'],
            'offer_id' => ['required', 'integer', 'exists:partner_discount_offers,id'],
            'original_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'final_amount' => ['nullable', 'numeric', 'min:0'],
            'metadata' => ['nullable', 'array'],
            'metadata.note' => ['nullable', 'string', 'max:255'],
            'metadata.source' => ['nullable', 'string', 'max:60'],
            'metadata.device_id' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(['validated'])],
        ];
    }
}
