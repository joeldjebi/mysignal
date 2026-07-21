<?php

namespace App\Services\Wallet;

use Illuminate\Support\Facades\Http;

class GoogleWalletAuth
{
    public function accessToken(): string
    {
        $serviceAccount = $this->serviceAccount();
        $now = time();

        $assertion = $this->encodeJwt([
            'iss' => $serviceAccount['client_email'],
            'scope' => 'https://www.googleapis.com/auth/wallet_object.issuer',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ], $serviceAccount['private_key']);

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $assertion,
        ]);

        if (! $response->successful() || blank($response->json('access_token'))) {
            throw new WalletConfigurationException('Authentification Google Wallet impossible.');
        }

        return (string) $response->json('access_token');
    }

    private function serviceAccount(): array
    {
        $json = config('wallet.google.service_account_json');

        if (blank($json) && filled(config('wallet.google.service_account_base64'))) {
            $json = base64_decode((string) config('wallet.google.service_account_base64'), true) ?: null;
        }

        if (blank($json) && filled(config('wallet.google.service_account_path'))) {
            $path = $this->path((string) config('wallet.google.service_account_path'));
            $json = is_file($path) ? file_get_contents($path) : null;
        }

        $serviceAccount = is_string($json) ? json_decode($json, true) : null;

        if (! is_array($serviceAccount) || blank($serviceAccount['client_email'] ?? null) || blank($serviceAccount['private_key'] ?? null)) {
            throw new WalletConfigurationException('La cle de service Google Wallet est invalide ou absente.');
        }

        return $serviceAccount;
    }

    private function encodeJwt(array $payload, string $privateKey): string
    {
        $header = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
        $body = $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR));
        $unsignedToken = $header.'.'.$body;

        $key = str_replace('\\n', "\n", $privateKey);
        $signed = openssl_sign($unsignedToken, $signature, $key, OPENSSL_ALGO_SHA256);

        if (! $signed) {
            throw new WalletConfigurationException('Signature OAuth Google Wallet impossible.');
        }

        return $unsignedToken.'.'.$this->base64UrlEncode($signature);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function path(string $path): string
    {
        return str_starts_with($path, '/')
            ? $path
            : base_path($path);
    }
}
