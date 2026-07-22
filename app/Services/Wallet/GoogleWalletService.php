<?php

namespace App\Services\Wallet;

use App\Models\PrivilegeCard;

class GoogleWalletService
{
    public function buildSaveUrl(PrivilegeCard $card): string
    {
        $card->loadMissing('type');

        $serviceAccount = $this->serviceAccount();
        $issuerId = (string) config('wallet.google.issuer_id');
        $classId = (string) config('wallet.google.class_id');

        if ($issuerId === '' || $classId === '') {
            throw new WalletConfigurationException('La configuration Google Wallet est incomplète.');
        }

        $objectId = $issuerId.'.'.preg_replace('/[^A-Za-z0-9._-]/', '_', (string) $card->card_uuid.'-'.$this->objectVersion());
        $type = $card->type;

        $genericObject = [
            'id' => $objectId,
            'classId' => $classId,
            'genericType' => 'GENERIC_TYPE_UNSPECIFIED',
            'hexBackgroundColor' => config('wallet.google.background_color', '#C9A227'),
            'logo' => $this->image((string) config('wallet.google.logo_url'), 'Logo My Signal'),
            'wideLogo' => $this->image((string) config('wallet.google.logo_url'), 'Logo My Signal'),
            'cardTitle' => ['defaultValue' => ['language' => 'fr', 'value' => 'My Signal']],
            'subheader' => ['defaultValue' => ['language' => 'fr', 'value' => $this->walletSubtitle($card)]],
            'header' => ['defaultValue' => ['language' => 'fr', 'value' => $this->walletHeader($card)]],
            'barcode' => [
                'type' => 'QR_CODE',
                'value' => $card->card_uuid,
            ],
        ];

        $jwt = $this->encodeJwt([
            'iss' => $serviceAccount['client_email'],
            'aud' => 'google',
            'typ' => 'savetowallet',
            'iat' => time(),
            'payload' => ['genericObjects' => [$genericObject]],
        ], $serviceAccount['private_key']);

        return 'https://pay.google.com/gp/v/save/'.$jwt;
    }

    public function classPayload(): array
    {
        $classId = (string) config('wallet.google.class_id');

        return [
            'id' => $classId,
            'classTemplateInfo' => [
                'listTemplateOverride' => [
                    'firstRowOption' => [
                        'fieldOption' => [
                            'fields' => [
                                ['fieldPath' => 'object.header'],
                            ],
                        ],
                    ],
                    'secondRowOption' => [
                        'fields' => [
                            ['fieldPath' => 'object.subheader'],
                        ],
                    ],
                ],
            ],
            'enableSmartTap' => false,
            'logo' => $this->image((string) config('wallet.google.logo_url'), 'Logo My Signal'),
            'wideLogo' => $this->image((string) config('wallet.google.logo_url'), 'Logo My Signal'),
            'hexBackgroundColor' => config('wallet.google.background_color', '#C9A227'),
        ];
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
            throw new WalletConfigurationException('La clé de service Google Wallet est invalide ou absente.');
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
            throw new WalletConfigurationException('Signature Google Wallet impossible.');
        }

        return $unsignedToken.'.'.$this->base64UrlEncode($signature);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function discountLabel(PrivilegeCard $card): string
    {
        $type = $card->type;

        if ($type === null || $type->discount_value === null) {
            return 'Avantages carte privilège';
        }

        if ($type->discount_type === 'fixed_amount') {
            return number_format((float) $type->discount_value, 0, ',', ' ').' '.$type->currency;
        }

        return number_format((float) $type->discount_value, 0, ',', ' ').'%';
    }

    private function expirationLabel(PrivilegeCard $card): string
    {
        return $card->expires_at?->format('d/m/Y') ?? 'Non définie';
    }

    private function walletHeader(PrivilegeCard $card): string
    {
        $number = (string) $card->card_number;
        $parts = array_values(array_filter(explode('-', $number)));
        $suffix = end($parts);

        if (is_string($suffix) && $suffix !== '' && strlen($suffix) <= 8) {
            return $suffix;
        }

        return strlen($number) > 12 ? substr($number, -12) : $number;
    }

    private function walletSubtitle(PrivilegeCard $card): string
    {
        $typeName = $card->type?->name ?? 'Carte';

        return $typeName.' · '.$this->discountLabel($card).' · Exp. '.$this->shortExpirationLabel($card);
    }

    private function shortExpirationLabel(PrivilegeCard $card): string
    {
        return $card->expires_at?->format('d/m/y') ?? '-';
    }

    private function objectVersion(): string
    {
        $version = (string) config('wallet.google.object_version', 'v2');

        return preg_replace('/[^A-Za-z0-9._-]/', '_', $version) ?: 'v2';
    }

    private function image(string $url, string $description): array
    {
        $url = $url !== '' ? $url : $this->publicAssetUrl('logo');

        return [
            'sourceUri' => ['uri' => $url],
            'contentDescription' => [
                'defaultValue' => [
                    'language' => 'fr',
                    'value' => $description,
                ],
            ],
        ];
    }

    private function publicAssetUrl(string $asset): string
    {
        $path = (string) config('wallet.google.logo_path', 'public/image/logo/ufc.jpg');

        $path = str_starts_with($path, 'public/')
            ? substr($path, 7)
            : $path;

        return asset($path);
    }

    private function path(string $path): string
    {
        return str_starts_with($path, '/')
            ? $path
            : base_path($path);
    }
}
