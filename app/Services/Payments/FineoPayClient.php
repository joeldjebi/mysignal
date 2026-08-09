<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class FineoPayClient
{
    public function createCheckoutLink(array $payload): string
    {
        $baseUrl = rtrim((string) config('services.fineopay.base_url'), '/');
        $checkoutPath = '/'.ltrim((string) config('services.fineopay.checkout_path'), '/');
        $businessCode = (string) config('services.fineopay.business_code');
        $apiKey = (string) config('services.fineopay.api_key');

        if ($baseUrl === '' || $checkoutPath === '/' || $businessCode === '' || $apiKey === '') {
            throw ValidationException::withMessages([
                'payment' => ['La configuration FineoPay est incomplète.'],
            ]);
        }

        $url = $baseUrl.$checkoutPath;

        Log::info('FineoPay checkout-link request.', [
            'url' => $url,
            'headers' => [
                'businessCode' => $businessCode,
                'apiKey' => $apiKey,
                'Content-Type' => 'application/json',
            ],
            'curl' => $this->curlCommand($url, $businessCode, $apiKey, $payload),
            'payload' => $payload,
        ]);

        $response = $this->postJsonWithCurl($url, $payload, [
            'businessCode: '.$businessCode,
            'apiKey: '.$apiKey,
            'Accept: application/json',
            'Content-Type: application/json',
        ]);

        Log::info('FineoPay checkout-link response.', [
            'url' => $url,
            'http_status' => $response['status'],
            'json' => $response['json'],
            'body' => $response['json'] ? null : $response['body'],
        ]);

        if ($response['status'] < 200 || $response['status'] >= 300 || ! (bool) ($response['json']['success'] ?? false)) {
            throw ValidationException::withMessages([
                'payment' => [($response['json']['message'] ?? null) ?: 'Impossible de générer le lien de paiement.'],
                'fineopay_debug' => [
                    json_encode([
                        'request' => [
                            'url' => $url,
                            'headers' => [
                                'businessCode' => $businessCode,
                                'apiKey' => $apiKey,
                                'Content-Type' => 'application/json',
                            ],
                            'curl' => $this->curlCommand($url, $businessCode, $apiKey, $payload),
                            'payload' => $payload,
                        ],
                        'response' => [
                            'http_status' => $response['status'],
                            'json' => $response['json'],
                            'body' => $response['json'] ? null : $response['body'],
                        ],
                    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                ],
            ]);
        }

        $checkoutLink = $response['json']['data']['checkoutLink'] ?? null;

        if (! is_string($checkoutLink) || trim($checkoutLink) === '') {
            throw ValidationException::withMessages([
                'payment' => ['FineoPay n’a retourné aucun lien de paiement.'],
            ]);
        }

        return $checkoutLink;
    }

    private function postJsonWithCurl(string $url, array $payload, array $headers): array
    {
        $curl = curl_init($url);

        if ($curl === false) {
            throw ValidationException::withMessages([
                'payment' => ['Impossible d’initialiser la requête FineoPay.'],
            ]);
        }

        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_THROW_ON_ERROR),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => max(1, (int) config('services.fineopay.connect_timeout', 10)),
            CURLOPT_TIMEOUT => max(1, (int) config('services.fineopay.timeout', 45)),
        ]);

        $body = curl_exec($curl);
        $error = curl_error($curl);
        $errno = (int) curl_errno($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $totalTime = (float) curl_getinfo($curl, CURLINFO_TOTAL_TIME);

        curl_close($curl);

        if ($body === false) {
            if ($errno === CURLE_OPERATION_TIMEDOUT || $totalTime >= (float) config('services.fineopay.timeout', 45)) {
                $error = 'délai d’attente FineoPay dépassé';
            }

            throw ValidationException::withMessages([
                'payment' => ['Erreur FineoPay: '.$error],
            ]);
        }

        $json = json_decode((string) $body, true);

        return [
            'status' => $status,
            'body' => (string) $body,
            'json' => is_array($json) ? $json : null,
        ];
    }

    private function curlCommand(string $url, string $businessCode, string $apiKey, array $payload): string
    {
        return sprintf(
            "curl -X POST %s \\\n  -H %s \\\n  -H %s \\\n  -H %s \\\n  -d %s",
            escapeshellarg($url),
            escapeshellarg('businessCode: '.$businessCode),
            escapeshellarg('apiKey: '.$apiKey),
            escapeshellarg('Content-Type: application/json'),
            escapeshellarg(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)),
        );
    }
}
