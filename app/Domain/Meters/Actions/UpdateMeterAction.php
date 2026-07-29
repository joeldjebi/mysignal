<?php

namespace App\Domain\Meters\Actions;

use App\Models\Meter;
use App\Models\PublicUser;
use App\Services\WasabiService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateMeterAction
{
    public function __construct(private readonly WasabiService $wasabiService) {}

    public function handle(PublicUser $user, Meter $meter, array $payload, ?UploadedFile $identifierPhoto = null): Meter
    {
        return DB::transaction(function () use ($user, $meter, $payload, $identifierPhoto): Meter {
            $identifierPhotoPath = $this->storeIdentifierPhoto($user, $meter, $identifierPhoto);

            $meter->fill([
                'application_id' => $payload['application_id'] ?? $meter->application_id,
                'organization_id' => $payload['organization_id'] ?? $meter->organization_id,
                'network_type' => $payload['network_type'] ?? $meter->network_type,
                'label' => $payload['label'] ?? $meter->label,
                'city' => array_key_exists('city', $payload) ? $payload['city'] : $meter->city,
                'commune' => $payload['commune'] ?? $meter->commune,
                'neighborhood' => $payload['neighborhood'] ?? $meter->neighborhood,
                'sub_neighborhood' => $payload['sub_neighborhood'] ?? $meter->sub_neighborhood,
                'address' => $payload['address'] ?? $meter->address,
                'latitude' => array_key_exists('latitude', $payload) ? $payload['latitude'] : $meter->latitude,
                'longitude' => array_key_exists('longitude', $payload) ? $payload['longitude'] : $meter->longitude,
                'location_accuracy' => array_key_exists('location_accuracy', $payload) ? $payload['location_accuracy'] : $meter->location_accuracy,
                'location_source' => array_key_exists('location_source', $payload) ? $payload['location_source'] : $meter->location_source,
                'identifier_photo_path' => $identifierPhotoPath ?? $meter->identifier_photo_path,
            ]);
            $meter->save();

            if (array_key_exists('is_primary', $payload)) {
                if ($payload['is_primary'] === true) {
                    $user->meterAssignments()->update(['is_primary' => false]);
                }

                $user->meterAssignments()
                    ->where('meter_id', $meter->id)
                    ->update(['is_primary' => (bool) $payload['is_primary']]);
            }

            return $meter->fresh();
        });
    }

    private function storeIdentifierPhoto(PublicUser $user, Meter $meter, ?UploadedFile $identifierPhoto): ?string
    {
        if (! $identifierPhoto instanceof UploadedFile) {
            return null;
        }

        $application = $meter->application;

        if (! $application?->requires_public_user_identifier) {
            throw ValidationException::withMessages([
                'identifier_photo' => ['La photo est disponible uniquement pour une catégorie qui demande un identifiant.'],
            ]);
        }

        if (filled($meter->identifier_photo_path)) {
            $this->wasabiService->deleteFile($meter->identifier_photo_path);
        }

        return $this->wasabiService->uploadFile(
            $identifierPhoto,
            'meters/identifier-photos/'.$user->id,
            'identifier-photo',
        );
    }
}
