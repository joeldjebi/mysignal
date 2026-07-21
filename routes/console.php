<?php

use App\Domain\Discounts\Actions\IssueUpDiscountCardAction;
use App\Domain\Subscriptions\Enums\UpSubscriptionStatus;
use App\Services\Wallet\GoogleWalletAuth;
use App\Services\Wallet\WalletConfigurationException;
use App\Models\UpSubscription;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('discounts:backfill-cards {--dry-run : Simule la creation sans ecrire en base}', function (IssueUpDiscountCardAction $issueUpDiscountCardAction) {
    $dryRun = (bool) $this->option('dry-run');

    $activeSubscriptions = UpSubscription::query()
        ->where('status', UpSubscriptionStatus::Active->value)
        ->with(['publicUser', 'discountCard'])
        ->orderBy('id')
        ->get();

    $missingCards = $activeSubscriptions
        ->filter(fn (UpSubscription $subscription) => $subscription->discountCard === null)
        ->values();

    if ($activeSubscriptions->isEmpty()) {
        $this->warn('Aucun abonnement UP actif trouve.');

        return self::SUCCESS;
    }

    $this->info('Abonnements actifs analyses : '.$activeSubscriptions->count());
    $this->line('Cartes manquantes detectees : '.$missingCards->count());

    if ($missingCards->isEmpty()) {
        $this->info('Aucune carte a generer. Tous les abonnes actifs ont deja une carte.');

        return self::SUCCESS;
    }

    if ($dryRun) {
        $this->table(
            ['Subscription ID', 'UP ID', 'Nom', 'Telephone', 'Fin abonnement'],
            $missingCards->map(fn (UpSubscription $subscription) => [
                $subscription->id,
                $subscription->public_user_id,
                trim((string) ($subscription->publicUser?->first_name.' '.$subscription->publicUser?->last_name)),
                $subscription->publicUser?->phone,
                optional($subscription->end_date)->toDateTimeString(),
            ])->all()
        );

        $this->comment('Simulation terminee. Aucune carte n a ete creee.');

        return self::SUCCESS;
    }

    $createdCount = 0;
    $this->withProgressBar($missingCards, function (UpSubscription $subscription) use ($issueUpDiscountCardAction, &$createdCount): void {
        $issueUpDiscountCardAction->handle($subscription);
        $createdCount++;
    });
    $this->newLine(2);

    $this->info($createdCount.' carte(s) de reduction ont ete generees avec succes.');

    return self::SUCCESS;
})->purpose('Genere les cartes de reduction manquantes pour les abonnements UP deja actifs');

Artisan::command('wallet:google:create-class', function (GoogleWalletAuth $googleWalletAuth) {
    $classId = (string) config('wallet.google.class_id');

    if ($classId === '') {
        $this->error('GOOGLE_WALLET_CLASS_ID est manquant.');

        return self::FAILURE;
    }

    try {
        $token = $googleWalletAuth->accessToken();
    } catch (WalletConfigurationException $exception) {
        $this->error($exception->getMessage());

        return self::FAILURE;
    }

    $payload = [
        'id' => $classId,
        'classTemplateInfo' => [
            'cardTemplateOverride' => [
                'cardRowTemplateInfos' => [],
            ],
        ],
    ];

    $response = Http::withToken($token)
        ->acceptJson()
        ->post('https://walletobjects.googleapis.com/walletobjects/v1/genericClass', $payload);

    if ($response->status() === 409) {
        $this->info('La classe Google Wallet existe deja: '.$classId);

        return self::SUCCESS;
    }

    if (! $response->successful()) {
        $this->error('Creation Google Wallet echouee: HTTP '.$response->status());
        $this->line($response->body());

        return self::FAILURE;
    }

    $this->info('Classe Google Wallet creee: '.$classId);

    return self::SUCCESS;
})->purpose('Cree la classe Google Wallet des cartes privileges');
