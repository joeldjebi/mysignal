<?php

namespace App\Http\Controllers\Api\V1\Notifications;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Support\Api\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeviceTokenController extends Controller
{
    public function storePublic(Request $request)
    {
        $this->store($request, 'public', 'public_api');

        return ApiResponse::success([], 'Token de notification enregistre.');
    }

    public function destroyPublic(Request $request)
    {
        $this->destroy($request, 'public', 'public_api');

        return ApiResponse::success([], 'Token de notification supprime.');
    }

    public function storePartner(Request $request)
    {
        $this->store($request, 'partner', 'partner_api');

        return ApiResponse::success([], 'Token de notification enregistre.');
    }

    public function destroyPartner(Request $request)
    {
        $this->destroy($request, 'partner', 'partner_api');

        return ApiResponse::success([], 'Token de notification supprime.');
    }

    private function store(Request $request, string $recipientType, string $guard): void
    {
        $attributes = $request->validate([
            'token' => ['required', 'string', 'max:4096'],
            'platform' => ['nullable', 'string', Rule::in(['android', 'ios', 'web'])],
            'device_name' => ['nullable', 'string', 'max:120'],
            'app_version' => ['nullable', 'string', 'max:40'],
        ]);

        $user = $request->user($guard);
        $tokenHash = DeviceToken::hashToken($attributes['token']);

        DeviceToken::query()->updateOrCreate(
            ['token_hash' => $tokenHash],
            [
                'recipient_type' => $recipientType,
                'recipient_id' => $user->id,
                'guard' => $guard,
                'token' => $attributes['token'],
                'platform' => $attributes['platform'] ?? null,
                'device_name' => $attributes['device_name'] ?? null,
                'app_version' => $attributes['app_version'] ?? null,
                'last_seen_at' => now(),
                'revoked_at' => null,
            ]
        );
    }

    private function destroy(Request $request, string $recipientType, string $guard): void
    {
        $attributes = $request->validate([
            'token' => ['required', 'string', 'max:4096'],
        ]);

        DeviceToken::query()
            ->where('recipient_type', $recipientType)
            ->where('recipient_id', $request->user($guard)->id)
            ->where('token_hash', DeviceToken::hashToken($attributes['token']))
            ->update(['revoked_at' => now()]);
    }
}
