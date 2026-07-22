<?php

namespace App\Services\Wallet;

use App\Models\PrivilegeCard;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;

class ApplePassService
{
    private array $temporaryFiles = [];

    public function build(PrivilegeCard $card): string
    {
        $card->loadMissing('type');
        $this->assertConfigured();

        $tmpDir = storage_path('app/private/tmp/pkpass-'.Str::uuid());
        File::ensureDirectoryExists($tmpDir, 0700);

        try {
            file_put_contents($tmpDir.'/pass.json', json_encode($this->passJson($card), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
            $this->copyAssets($tmpDir);
            file_put_contents($tmpDir.'/manifest.json', json_encode($this->manifest($tmpDir), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
            $this->signManifest($tmpDir);

            $pkpassPath = storage_path('app/private/tmp/'.$card->card_uuid.'.pkpass');
            $zip = new ZipArchive();

            if ($zip->open($pkpassPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new WalletConfigurationException('Création du fichier Apple Wallet impossible.');
            }

            foreach (File::files($tmpDir) as $file) {
                $zip->addFile($file->getPathname(), $file->getFilename());
            }

            $zip->close();

            return $pkpassPath;
        } finally {
            File::deleteDirectory($tmpDir);
            $this->cleanupTemporaryFiles();
        }
    }

    private function passJson(PrivilegeCard $card): array
    {
        $type = $card->type;

        return [
            'formatVersion' => 1,
            'passTypeIdentifier' => config('wallet.apple.pass_type_identifier'),
            'teamIdentifier' => config('wallet.apple.team_identifier'),
            'serialNumber' => $card->card_uuid,
            'organizationName' => config('wallet.apple.organization_name'),
            'description' => config('wallet.apple.description'),
            'logoText' => config('wallet.apple.logo_text'),
            'foregroundColor' => config('wallet.apple.foreground_color'),
            'backgroundColor' => config('wallet.apple.background_color'),
            'labelColor' => config('wallet.apple.label_color'),
            'storeCard' => [
                'primaryFields' => [
                    ['key' => 'tier', 'label' => 'NIVEAU', 'value' => $type?->name ?? 'Carte privilège'],
                ],
                'secondaryFields' => [
                    ['key' => 'cardNumber', 'label' => 'N° CARTE', 'value' => $card->card_number],
                    ['key' => 'expires', 'label' => 'EXPIRE LE', 'value' => $card->expires_at?->toDateString() ?? '-', 'dateStyle' => 'PKDateStyleMedium'],
                ],
                'auxiliaryFields' => [
                    ['key' => 'discount', 'label' => 'RÉDUCTION', 'value' => $this->discountLabel($card)],
                ],
            ],
            'barcodes' => [
                [
                    'message' => $card->card_uuid,
                    'format' => 'PKBarcodeFormatQR',
                    'messageEncoding' => 'iso-8859-1',
                ],
            ],
        ];
    }

    private function copyAssets(string $tmpDir): void
    {
        $assetPath = $this->path((string) config('wallet.apple.asset_path'));
        $fallbackLogo = public_path('image/logo/logo-my-signal.png');

        foreach (['icon.png', 'icon@2x.png', 'icon@3x.png', 'logo.png'] as $asset) {
            $source = $assetPath.'/'.$asset;

            if (! is_file($source) && is_file($fallbackLogo)) {
                $source = $fallbackLogo;
            }

            if (! is_file($source)) {
                throw new WalletConfigurationException("Fichier Apple Wallet manquant: {$asset}.");
            }

            copy($source, $tmpDir.'/'.$asset);
        }
    }

    private function manifest(string $tmpDir): array
    {
        $manifest = [];

        foreach (File::files($tmpDir) as $file) {
            $manifest[$file->getFilename()] = sha1_file($file->getPathname());
        }

        ksort($manifest);

        return $manifest;
    }

    private function signManifest(string $tmpDir): void
    {
        $cert = $this->credentialFile('cert_path', 'cert_base64', 'passcertificate.pem');
        $key = $this->credentialFile('key_path', 'key_base64', 'passkey.pem');
        $wwdr = $this->credentialFile('wwdr_path', 'wwdr_base64', 'wwdr.pem');
        $passphrase = (string) config('wallet.apple.key_passphrase', '');

        $cmd = sprintf(
            'openssl smime -binary -sign -certfile %s -signer %s -inkey %s %s -in %s -out %s -outform DER',
            escapeshellarg($wwdr),
            escapeshellarg($cert),
            escapeshellarg($key),
            $passphrase !== '' ? '-passin '.escapeshellarg('pass:'.$passphrase) : '',
            escapeshellarg($tmpDir.'/manifest.json'),
            escapeshellarg($tmpDir.'/signature')
        );

        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0) {
            throw new WalletConfigurationException('Signature Apple Wallet impossible.');
        }
    }

    private function assertConfigured(): void
    {
        if (blank(config('wallet.apple.pass_type_identifier')) || blank(config('wallet.apple.team_identifier'))) {
            throw new WalletConfigurationException('La configuration Apple Wallet est incomplète.');
        }

        if (! extension_loaded('zip')) {
            throw new WalletConfigurationException('L’extension PHP ZipArchive est requise pour Apple Wallet.');
        }
    }

    private function credentialFile(string $pathKey, string $base64Key, string $filename): string
    {
        $path = config('wallet.apple.'.$pathKey);

        if (filled($path)) {
            $path = $this->path((string) $path);

            if (is_file($path)) {
                return $path;
            }
        }

        $base64 = config('wallet.apple.'.$base64Key);

        if (filled($base64)) {
            $content = base64_decode((string) $base64, true);

            if ($content !== false) {
                $tmpPath = storage_path('app/private/tmp/wallet-'.$filename.'-'.Str::uuid());
                File::ensureDirectoryExists(dirname($tmpPath), 0700);
                file_put_contents($tmpPath, $content);
                $this->temporaryFiles[] = $tmpPath;

                return $tmpPath;
            }
        }

        throw new WalletConfigurationException("Identifiant Apple Wallet manquant: {$filename}.");
    }

    private function cleanupTemporaryFiles(): void
    {
        foreach ($this->temporaryFiles as $file) {
            File::delete($file);
        }

        $this->temporaryFiles = [];
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

    private function path(string $path): string
    {
        return str_starts_with($path, '/')
            ? $path
            : base_path($path);
    }
}
