<?php

namespace App\Http\Requests\Api\V1\Partner\Discounts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePartnerDiscountOfferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:60', 'unique:partner_discount_offers,code'],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'discount_type' => ['required', Rule::in(['percentage', 'fixed_amount', 'custom'])],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'minimum_purchase_amount' => ['nullable', 'numeric', 'min:0'],
            'maximum_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'max_uses_per_card' => ['nullable', 'integer', 'min:1'],
            'max_uses_per_day' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['nullable', Rule::in(['draft', 'active', 'inactive', 'archived'])],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
