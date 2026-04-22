<?php

use App\Domain\Discounts\Actions\IssueUpDiscountCardAction;
use App\Domain\Subscriptions\Enums\UpSubscriptionStatus;
use App\Models\UpSubscription;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

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
