<?php

namespace App\Http\Controllers\Api\V1\Public\Profile;

use App\Domain\PublicUsers\Actions\UpdatePublicProfileAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Public\Profile\UpdatePublicPasswordRequest;
use App\Http\Requests\Api\V1\Public\Profile\UpdatePublicProfileRequest;
use App\Http\Resources\Api\V1\Public\Auth\PublicUserResource;
use App\Support\Api\ApiResponse;
use App\Support\Audit\ActivityLogger;
use App\Services\WasabiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PublicProfileController extends Controller
{
    public function show(Request $request)
    {
        return ApiResponse::success([
            'user' => new PublicUserResource($request->user('public_api')->loadMissing(['publicUserType.pricingRule', 'countryReference', 'cityReference', 'communeReference'])),
        ]);
    }

    public function update(UpdatePublicProfileRequest $request, UpdatePublicProfileAction $action)
    {
        $user = $action->handle($request->user('public_api'), $request->validated());

        return ApiResponse::success([
            'user' => new PublicUserResource($user->loadMissing(['publicUserType.pricingRule', 'countryReference', 'cityReference', 'communeReference'])),
        ], 'Profil mis à jour avec succès.');
    }

    public function updatePhoto(Request $request, WasabiService $wasabiService)
    {
        $attributes = $request->validate([
            'profile_photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $user = $request->user('public_api');

        if (filled($user->profile_photo_path) && str_starts_with((string) $user->profile_photo_path, 'public-users/')) {
            $wasabiService->deleteFile($user->profile_photo_path);
        }

        $path = $wasabiService->uploadFile(
            $attributes['profile_photo'],
            'public-users/profile-photos/'.$user->id,
            'profile-photo'
        );

        $user->update([
            'profile_photo_path' => $path,
        ]);

        return ApiResponse::success([
            'user' => new PublicUserResource($user->fresh(['publicUserType.pricingRule', 'countryReference', 'cityReference', 'communeReference'])),
        ], 'Photo de profil mise à jour avec succès.');
    }

    public function updatePassword(UpdatePublicPasswordRequest $request, ActivityLogger $activityLogger)
    {
        $user = $request->user('public_api');

        if (! Hash::check($request->string('current_password')->value(), (string) $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Le mot de passe actuel est invalide.'],
            ]);
        }

        $user->update([
            'password' => $request->string('password')->value(),
        ]);

        $activityLogger->log(
            'public.password.updated',
            'Mise à jour du mot de passe UP.',
            $user,
            [
                'public_user_type_id' => $user->public_user_type_id,
                'phone' => $user->phone,
            ],
            $request,
            $user,
            'public',
        );

        return ApiResponse::success([], 'Mot de passe mis à jour avec succès.');
    }
}
