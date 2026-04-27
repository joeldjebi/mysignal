<?php

namespace App\Services\Notifications;

use App\Models\DeviceToken;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class FirebaseCloudMessagingClient
{
    public function sendToTokens(array $tokens, string $title, ?string $body = null, array $data = []): void
    {
        $tokens = collect($tokens)
            ->filter()
            ->unique()
            ->values();

        if ($tokens->isEmpty() || ! config('services.firebase.enabled')) {
            return;
        }

        $projectId = (string) config('services.firebase.project_id');
        $accessToken = $this->accessToken();
        $stringData = collect($data)
            ->mapWithKeys(fn ($value, $key) => [(string) $key => is_scalar($value) || $value === null ? (string) $value : json_encode($value)])
            ->all();

        foreach ($tokens as $token) {
            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                    'message' => [
                        'token' => $token,
                        'notification' => [
                            'title' => $title,
                            'body' => $body ?? '',
                        ],
                        'data' => $stringData,
                    ],
                ]);

            if ($response->successful()) {
                continue;
            }

            $this->handleFailedToken($token, $response->json(), $response->status());
        }
    }

    private function accessToken(): string
    {
        $projectId = (string) config('services.firebase.project_id');

        if ($projectId === '') {
            throw new RuntimeException('FIREBASE_PROJECT_ID is not configured.');
        }

        return Cache::remember("firebase_access_token:{$projectId}", now()->addMinutes(50), function (): string {
            $credentials = $this->credentials();
            $now = time();
            $assertion = $this->signedJwt([
                'iss' => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => $credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600,
            ], $credentials['private_key']);

            $response = Http::asForm()->post($credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $assertion,
            ]);

            if (! $response->successful()) {
                throw new RuntimeException('Unable to authenticate with Firebase: '.$response->body());
            }

            return (string) $response->json('access_token');
        });
    }

    private function credentials(): array
    {
        $path = (string) config('services.firebase.credentials');

        if ($path === '' || ! is_file($path)) {
            throw new RuntimeException('FIREBASE_CREDENTIALS must point to a readable service account JSON file.');
        }

        $credentials = json_decode((string) file_get_contents($path), true);

        if (! is_array($credentials) || empty($credentials['client_email']) || empty($credentials['private_key'])) {
            throw new RuntimeException('Firebase credentials JSON is invalid.');
        }

        return $credentials;
    }

    private function signedJwt(array $payload, string $privateKey): string
    {
        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $segments = [
            $this->base64UrlEncode(json_encode($header, JSON_THROW_ON_ERROR)),
            $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR)),
        ];
        $signatureInput = implode('.', $segments);
        openssl_sign($signatureInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        $segments[] = $this->base64UrlEncode($signature);

        return implode('.', $segments);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function handleFailedToken(string $token, ?array $payload, int $status): void
    {
        $errorStatus = data_get($payload, 'error.status');

        if (in_array($errorStatus, ['INVALID_ARGUMENT', 'NOT_FOUND'], true)) {
            DeviceToken::query()
                ->where('token_hash', DeviceToken::hashToken($token))
                ->update(['revoked_at' => now()]);
        }

        Log::warning('Firebase push notification failed.', [
            'status' => $status,
            'firebase_status' => $errorStatus,
            'message' => data_get($payload, 'error.message'),
        ]);
    }
}
