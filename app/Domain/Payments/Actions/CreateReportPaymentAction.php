<?php

namespace App\Domain\Payments\Actions;

use App\Domain\Payments\Enums\PaymentStatus;
use App\Models\IncidentReport;
use App\Models\Payment;
use App\Models\PricingRule;
use App\Models\PublicUser;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateReportPaymentAction
{
    public function handle(PublicUser $user, IncidentReport $report): Payment
    {
        $user->loadMissing('publicUserType.pricingRule');

        if ((int) $report->public_user_id !== (int) $user->id) {
            throw ValidationException::withMessages([
                'report' => ['Vous ne pouvez pas payer ce signalement.'],
            ]);
        }

        if ($report->payment_status === PaymentStatus::Paid->value) {
            throw ValidationException::withMessages([
                'report' => ['Ce signalement a deja ete paye.'],
            ]);
        }

        $existingPending = $report->payments()
            ->where('status', PaymentStatus::Pending->value)
            ->latest('id')
            ->first();

        if ($existingPending !== null) {
            return $existingPending;
        }

        $pricingRule = $user->publicUserType?->pricingRule;

        if ($pricingRule === null || $pricingRule->status !== 'active') {
            $pricingRule = PricingRule::query()
                ->where('code', 'public_signal_report')
                ->where('status', 'active')
                ->first();
        }

        if ($pricingRule === null) {
            throw ValidationException::withMessages([
                'pricing_rule' => ['Aucune tarification active n est disponible pour ce signalement.'],
            ]);
        }

        return DB::transaction(function () use ($user, $report, $pricingRule): Payment {
            return Payment::query()->create([
                'public_user_id' => $user->id,
                'incident_report_id' => $report->id,
                'pricing_rule_id' => $pricingRule->id,
                'reference' => $this->generateReference(),
                'amount' => $pricingRule->amount,
                'currency' => $pricingRule->currency,
                'status' => PaymentStatus::Pending->value,
                'provider' => 'simulated',
                'initiated_at' => CarbonImmutable::now(),
            ]);
        });
    }

    private function generateReference(): string
    {
        return 'PAY-'.CarbonImmutable::now()->format('YmdHis').'-'.strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
    }
}
