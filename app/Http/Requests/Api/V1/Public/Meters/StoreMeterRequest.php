<?php

namespace App\Http\Requests\Api\V1\Public\Meters;

use App\Models\Application;
use App\Models\Organization;
use App\Support\ApplicationCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreMeterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('application_id') && $this->filled('organization_id')) {
            return;
        }

        $networkType = strtoupper((string) $this->input('network_type'));

        if ($networkType === '') {
            return;
        }

        $applicationId = $this->input('application_id');

        if (! $applicationId) {
            $applicationId = ApplicationCatalog::findByNetworkType($networkType)?->id;
        }

        $organizationId = $this->input('organization_id');

        if (! $organizationId) {
            $organizationId = Organization::query()
                ->where('code', $networkType)
                ->when($applicationId, fn ($query) => $query->where('application_id', $applicationId))
                ->value('id');
        }

        $payload = [];

        if ($applicationId) {
            $payload['application_id'] = $applicationId;
        }

        if ($organizationId) {
            $payload['organization_id'] = $organizationId;
        }

        if ($payload !== []) {
            $this->merge($payload);
        }
    }

    public function rules(): array
    {
        return [
            'application_id' => ['nullable', 'integer', 'exists:applications,id'],
            'organization_id' => ['nullable', 'integer', 'exists:organizations,id'],
            'network_type' => ['nullable', 'string', 'max:60'],
            'meter_number' => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9\\-]+$/i'],
            'label' => ['nullable', 'string', 'max:120'],
            'commune' => ['nullable', 'string', 'max:120', 'exists:communes,name'],
            'neighborhood' => ['nullable', 'string', 'max:120'],
            'sub_neighborhood' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'location_accuracy' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'location_source' => ['nullable', 'string', 'max:30'],
            'is_primary' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $applicationId = (int) $this->input('application_id');
            $organizationId = (int) $this->input('organization_id');

            if ($applicationId <= 0 || $organizationId <= 0) {
                return;
            }

            $application = Application::query()->whereKey($applicationId)->where('status', 'active')->first();
            $organization = Organization::query()->whereKey($organizationId)->where('status', 'active')->first();

            if ($application === null) {
                $validator->errors()->add('application_id', 'L application selectionnee est invalide.');
                return;
            }

            if ($organization === null || (int) $organization->application_id !== $application->id) {
                $validator->errors()->add('organization_id', 'L organisation selectionnee n appartient pas a l application choisie.');
            }
        });
    }
}
