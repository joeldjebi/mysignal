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
            'logoText' => '',
            'foregroundColor' => config('wallet.apple.foreground_color'),
            'backgroundColor' => config('wallet.apple.background_color'),
            'labelColor' => config('wallet.apple.label_color'),
            'expirationDate' => $card->expires_at?->toIso8601String(),
            'storeCard' => [
                'primaryFields' => [
                    ['key' => 'tier', 'label' => 'NIVEAU', 'value' => $type?->name ?? 'Carte privilège'],
                ],
                'secondaryFields' => [
                    ['key' => 'cardNumber', 'label' => 'N° CARTE', 'value' => $card->card_number],
                ],
                'auxiliaryFields' => [
                    ['key' => 'discount', 'label' => 'RÉDUCTION', 'value' => $this->discountLabel($card)],
                    ['key' => 'expires', 'label' => 'EXPIRE LE', 'value' => $this->expirationLabel($card)],
                ],
                'backFields' => [
                    ['key' => 'cardNumberBack', 'label' => 'Numéro de carte', 'value' => $card->card_number],
                    ['key' => 'discountBack', 'label' => 'Réduction', 'value' => $this->discountLabel($card)],
                    ['key' => 'expiresBack', 'label' => 'Expiration', 'value' => $this->expirationLabel($card)],
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
        $logoPath = $this->firstExistingPath([
            config('wallet.apple.logo_path'),
            'public/image/logo/ufc.jpg',
            'public/image/ufc.jpg',
            'public/image/logo/logo-my-signal.png',
        ]);
        $assetFiles = [
            'icon.png' => [29, 29, $logoPath, 'contain'],
            'icon@2x.png' => [58, 58, $logoPath, 'contain'],
            'icon@3x.png' => [87, 87, $logoPath, 'contain'],
            'logo.png' => [160, 50, $logoPath, 'contain'],
            'logo@2x.png' => [320, 100, $logoPath, 'contain'],
            'logo@3x.png' => [480, 150, $logoPath, 'contain'],
        ];

        foreach ($assetFiles as $asset => [$width, $height, $sourcePath, $fit]) {
            $source = $assetPath.'/'.$asset;

            if (is_file($source)) {
                copy($source, $tmpDir.'/'.$asset);

                continue;
            }

            if (! is_file($sourcePath)) {
                throw new WalletConfigurationException("Fichier Apple Wallet manquant: {$asset}.");
            }

            $this->renderPngAsset($sourcePath, $tmpDir.'/'.$asset, $width, $height, $fit);
        }
    }

    private function renderPngAsset(string $sourcePath, string $destinationPath, int $width, int $height, string $fit): void
    {
        [$sourceWidth, $sourceHeight, $sourceType] = getimagesize($sourcePath) ?: [0, 0, 0];

        if ($sourceWidth <= 0 || $sourceHeight <= 0) {
            throw new WalletConfigurationException('Image Apple Wallet invalide.');
        }

        $sourceImage = match ($sourceType) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG => imagecreatefrompng($sourcePath),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($sourcePath) : false,
            default => false,
        };

        if (! $sourceImage) {
            throw new WalletConfigurationException('Format d’image Apple Wallet non supporté.');
        }

        $canvas = imagecreatetruecolor($width, $height);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 255, 255, 255, 127));
        imagealphablending($canvas, true);

        if ($fit === 'watermark') {
            $this->renderWatermarkAsset($canvas, $sourceImage, $sourceWidth, $sourceHeight, $width, $height);
        } else {
            $this->renderContainedAsset($canvas, $sourceImage, $sourceWidth, $sourceHeight, $width, $height);
        }

        imagepng($canvas, $destinationPath);
        imagedestroy($canvas);
        imagedestroy($sourceImage);
    }

    private function renderContainedAsset($canvas, $sourceImage, int $sourceWidth, int $sourceHeight, int $width, int $height): void
    {
        $scale = min($width / $sourceWidth, $height / $sourceHeight);
        $targetWidth = max(1, (int) floor($sourceWidth * $scale));
        $targetHeight = max(1, (int) floor($sourceHeight * $scale));
        $targetX = (int) floor(($width - $targetWidth) / 2);
        $targetY = (int) floor(($height - $targetHeight) / 2);

        imagecopyresampled($canvas, $sourceImage, $targetX, $targetY, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);
    }

    private function renderWatermarkAsset($canvas, $sourceImage, int $sourceWidth, int $sourceHeight, int $width, int $height): void
    {
        $scale = min(($width * 0.78) / $sourceWidth, ($height * 0.78) / $sourceHeight);
        $targetWidth = max(1, (int) floor($sourceWidth * $scale));
        $targetHeight = max(1, (int) floor($sourceHeight * $scale));
        $targetX = (int) floor(($width - $targetWidth) / 2);
        $targetY = (int) floor(($height - $targetHeight) / 2);

        $watermark = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($watermark, false);
        imagesavealpha($watermark, true);
        imagefill($watermark, 0, 0, imagecolorallocatealpha($watermark, 255, 255, 255, 127));
        imagecopyresampled($watermark, $sourceImage, 0, 0, 0, 0, $targetWidth, $targetHeight, $sourceWidth, $sourceHeight);
        imagefilter($watermark, IMG_FILTER_COLORIZE, 255, 255, 255, 72);
        imagecopymerge($canvas, $watermark, $targetX, $targetY, 0, 0, $targetWidth, $targetHeight, 24);
        imagedestroy($watermark);
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

    private function expirationLabel(PrivilegeCard $card): string
    {
        return $card->expires_at?->format('d/m/Y') ?? 'Non définie';
    }

    private function path(string $path): string
    {
        return str_starts_with($path, '/')
            ? $path
            : base_path($path);
    }

    private function firstExistingPath(array $paths): string
    {
        foreach ($paths as $path) {
            if (! is_string($path) || $path === '') {
                continue;
            }

            $resolvedPath = $this->path($path);

            if (is_file($resolvedPath)) {
                return $resolvedPath;
            }
        }

        throw new WalletConfigurationException('Les images Apple Wallet sont introuvables.');
    }
}
