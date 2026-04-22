<?php

namespace App\Http\Requests\Api\V1\Partner\Discounts;

use Illuminate\Foundation\Http\FormRequest;

class VerifyPartnerDiscountCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'card_uuid' => ['required', 'string', 'size:36'],
            'offer_id' => ['nullable', 'integer', 'exists:partner_discount_offers,id'],
        ];
    }
}
