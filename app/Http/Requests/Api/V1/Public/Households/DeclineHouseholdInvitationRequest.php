<?php

namespace App\Http\Requests\Api\V1\Public\Households;

use Illuminate\Foundation\Http\FormRequest;

class DeclineHouseholdInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invitation_id' => ['required', 'integer', 'exists:household_invitations,id'],
        ];
    }
}
