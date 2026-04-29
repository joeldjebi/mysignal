<?php

namespace App\Http\Controllers\Web\Institution;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeviceTokenController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $attributes = $request->validate([
            'token' => ['required', 'string', 'max:4096'],
            'platform' => ['nullable', 'string', Rule::in(['android', 'ios', 'web'])],
            'device_name' => ['nullable', 'string', 'max:120'],
            'app_version' => ['nullable', 'string', 'max:40'],
        ]);

        $token = DeviceToken::query()->updateOrCreate(
            ['token_hash' => DeviceToken::hashToken($attributes['token'])],
            [
                'recipient_type' => 'institution',
                'recipient_id' => $request->user()->id,
                'guard' => 'web',
                'token' => $attributes['token'],
                'platform' => $attributes['platform'] ?? 'web',
                'device_name' => $attributes['device_name'] ?? null,
                'app_version' => $attributes['app_version'] ?? 'institution-web',
                'last_seen_at' => now(),
                'revoked_at' => null,
            ],
        );

        return response()->json([
            'success' => true,
            'device_token' => [
                'id' => $token->id,
                'platform' => $token->platform,
                'device_name' => $token->device_name,
                'app_version' => $token->app_version,
                'last_seen_at' => $token->last_seen_at?->toIso8601String(),
            ],
        ]);
    }
}
