<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Domain\Payments\Actions\ConfirmIncidentReportFineoPaymentAction;
use App\Domain\Payments\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\IncidentReportPaymentSession;
use App\Models\Organization;
use App\Models\Payment;
use App\Support\Audit\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(): View
    {
        $request = request();
        $perPage = min(max((int) $request->integer('per_page', 15), 1), 100);
        $paymentQuery = $this->filteredPaymentsQuery($request);
        $sessionQuery = $this->filteredSessionsQuery($request);
        $paymentRowsQuery = (clone $paymentQuery)
            ->selectRaw("'payment' as source_type")
            ->selectRaw('payments.id as source_id')
            ->selectRaw('payments.initiated_at as sort_date');
        $sessionRowsQuery = (clone $sessionQuery)
            ->selectRaw("'session' as source_type")
            ->selectRaw('incident_report_payment_sessions.id as source_id')
            ->selectRaw('incident_report_payment_sessions.initiated_at as sort_date');
        $unifiedRows = DB::query()
            ->fromSub($paymentRowsQuery->unionAll($sessionRowsQuery), 'payment_rows')
            ->orderByDesc('sort_date')
            ->orderByDesc('source_id')
            ->paginate($perPage)
            ->withQueryString();
        $sourceRows = collect($unifiedRows->items());
        $paymentsById = Payment::query()
            ->with([
                'publicUser.publicUserType',
                'incidentReport.application',
                'incidentReport.organization',
                'incidentReport.reparationCase',
                'pricingRule',
                'latestCallCenterContact.calledBy',
            ])
            ->whereIn('id', $sourceRows->where('source_type', 'payment')->pluck('source_id')->all())
            ->get()
            ->keyBy('id');
        $sessionsById = IncidentReportPaymentSession::query()
            ->with([
                'publicUser.publicUserType',
                'pricingRule',
                'incidentReport.application',
                'incidentReport.organization',
                'latestCallCenterContact.calledBy',
            ])
            ->whereIn('id', $sourceRows->where('source_type', 'session')->pluck('source_id')->all())
            ->get()
            ->keyBy('id');
        $transactions = $sourceRows
            ->map(function ($row) use ($paymentsById, $sessionsById): ?array {
                if ($row->source_type === 'payment') {
                    $payment = $paymentsById->get((int) $row->source_id);

                    return $payment ? ['type' => 'payment', 'model' => $payment] : null;
                }

                $session = $sessionsById->get((int) $row->source_id);

                return $session ? ['type' => 'session', 'model' => $session] : null;
            })
            ->filter()
            ->values();
        $unifiedRows->setCollection($transactions);
        $paymentStats = $this->paymentStats(clone $paymentQuery, clone $sessionQuery);
        $statusBreakdown = $this->statusBreakdown(clone $paymentQuery, clone $sessionQuery);
        $contextBreakdown = $this->contextBreakdown(clone $paymentQuery, clone $sessionQuery);
        $trend = $this->paymentTrend(clone $paymentQuery, clone $sessionQuery);

        $applications = Application::query()->orderBy('name')->get(['id', 'name']);
        $organizations = Organization::query()->orderBy('name')->get(['id', 'name']);

        return view('super-admin.payments.index', [
            'transactions' => $unifiedRows,
            'paymentStats' => $paymentStats,
            'statusBreakdown' => $statusBreakdown,
            'contextBreakdown' => $contextBreakdown,
            'trend' => $trend,
            'perPage' => $perPage,
            'applications' => $applications,
            'organizations' => $organizations,
            'canManuallyValidatePayments' => (bool) (auth()->user()?->is_super_admin || auth()->user()?->effectivePermissionCodes()->contains('SA_PAYMENTS_MANUAL_VALIDATE')),
            'providers' => Payment::query()
                ->select('provider')
                ->whereNotNull('provider')
                ->where('provider', '!=', '')
                ->distinct()
                ->orderBy('provider')
                ->pluck('provider'),
        ]);
    }

    private function filteredPaymentsQuery(Request $request)
    {
        $query = Payment::query();

        if (filled($request->input('search'))) {
            $search = trim((string) $request->input('search'));

            $query->where(function ($builder) use ($search): void {
                $builder->where('reference', 'like', '%'.$search.'%')
                    ->orWhere('provider_reference', 'like', '%'.$search.'%')
                    ->orWhere('provider', 'like', '%'.$search.'%')
                    ->orWhereHas('publicUser', function ($publicUserQuery) use ($search): void {
                        $publicUserQuery->where('first_name', 'like', '%'.$search.'%')
                            ->orWhere('last_name', 'like', '%'.$search.'%')
                            ->orWhere('phone', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    })
                    ->orWhereHas('incidentReport', function ($reportQuery) use ($search): void {
                        $reportQuery->where('reference', 'like', '%'.$search.'%')
                            ->orWhere('signal_label', 'like', '%'.$search.'%')
                            ->orWhere('signal_code', 'like', '%'.$search.'%')
                            ->orWhere('incident_type', 'like', '%'.$search.'%');
                    });
            });
        }

        if (filled($request->input('status'))) {
            $query->where('status', $request->input('status'));
        }

        if (filled($request->input('provider'))) {
            $query->where('provider', $request->input('provider'));
        }

        if (filled($request->input('payment_context'))) {
            $query->where('payment_context', $request->input('payment_context'));
        }

        $this->applyOrganizationFilters($query, $request, false);
        $this->applyPeriodFilter($query, $request, 'payments.initiated_at');

        return $query;
    }

    private function filteredSessionsQuery(Request $request)
    {
        $query = IncidentReportPaymentSession::query()
            ->where(function ($builder): void {
                $builder
                    ->whereIn('status', [PaymentStatus::Pending->value, PaymentStatus::Failed->value])
                    ->orWhere(function ($paidButPendingTreatmentQuery): void {
                        $paidButPendingTreatmentQuery
                            ->where('status', PaymentStatus::Paid->value)
                            ->whereNull('incident_report_id');
                    });
            });

        if (filled($request->input('status'))) {
            $query->where('status', $request->input('status'));
        }

        if (filled($request->input('provider'))) {
            $query->where('provider', $request->input('provider'));
        }

        if (filled($request->input('payment_context'))) {
            $query->where('payment_context', $request->input('payment_context'));
        }

        if (filled($request->input('search'))) {
            $search = trim((string) $request->input('search'));

            $query->where(function ($builder) use ($search): void {
                $builder->where('sync_ref', 'like', '%'.$search.'%')
                    ->orWhere('provider_reference', 'like', '%'.$search.'%')
                    ->orWhereHas('publicUser', function ($publicUserQuery) use ($search): void {
                        $publicUserQuery->where('first_name', 'like', '%'.$search.'%')
                            ->orWhere('last_name', 'like', '%'.$search.'%')
                            ->orWhere('phone', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    });
            });
        }

        $this->applyOrganizationFilters($query, $request, true);
        $this->applyPeriodFilter($query, $request, 'incident_report_payment_sessions.initiated_at');

        return $query;
    }

    private function applyOrganizationFilters($query, Request $request, bool $supportsPayload): void
    {
        if (filled($request->input('application_id'))) {
            $applicationId = (int) $request->input('application_id');

            $query->where(function ($builder) use ($applicationId, $supportsPayload): void {
                $builder->whereHas('incidentReport', fn ($reportQuery) => $reportQuery->where('application_id', $applicationId));

                if ($supportsPayload) {
                    $builder->orWhere('report_payload->application_id', $applicationId);
                }
            });
        }

        if (filled($request->input('organization_id'))) {
            $organizationId = (int) $request->input('organization_id');

            $query->where(function ($builder) use ($organizationId, $supportsPayload): void {
                $builder->whereHas('incidentReport', fn ($reportQuery) => $reportQuery->where('organization_id', $organizationId));

                if ($supportsPayload) {
                    $builder->orWhere('report_payload->organization_id', $organizationId);
                }
            });
        }
    }

    private function applyPeriodFilter($query, Request $request, string $column): void
    {
        [$startDate, $endDate] = $this->periodBounds($request);

        if ($startDate !== null) {
            $query->where($column, '>=', $startDate);
        }

        if ($endDate !== null) {
            $query->where($column, '<=', $endDate);
        }
    }

    private function periodBounds(Request $request): array
    {
        $period = (string) $request->input('period', '30d');

        if ($period === 'today') {
            return [now()->startOfDay(), now()->endOfDay()];
        }

        if ($period === '7d') {
            return [now()->subDays(6)->startOfDay(), now()->endOfDay()];
        }

        if ($period === 'month') {
            return [now()->startOfMonth(), now()->endOfMonth()];
        }

        if ($period === 'year') {
            return [now()->startOfYear(), now()->endOfYear()];
        }

        if ($period === 'custom') {
            $start = filled($request->input('date_from')) ? Carbon::parse($request->input('date_from'))->startOfDay() : null;
            $end = filled($request->input('date_to')) ? Carbon::parse($request->input('date_to'))->endOfDay() : null;

            return [$start, $end];
        }

        return [now()->subDays(29)->startOfDay(), now()->endOfDay()];
    }

    private function paymentStats($paymentQuery, $sessionQuery): array
    {
        $paidPayments = (clone $paymentQuery)->where('status', PaymentStatus::Paid->value);
        $paidSessions = (clone $sessionQuery)->where('status', PaymentStatus::Paid->value);

        return [
            'total' => (clone $paymentQuery)->count() + (clone $sessionQuery)->count(),
            'paid' => (clone $paidPayments)->count() + (clone $paidSessions)->count(),
            'pending' => (clone $paymentQuery)->where('status', PaymentStatus::Pending->value)->count() + (clone $sessionQuery)->where('status', PaymentStatus::Pending->value)->count(),
            'failed' => (clone $paymentQuery)->where('status', PaymentStatus::Failed->value)->count() + (clone $sessionQuery)->where('status', PaymentStatus::Failed->value)->count(),
            'paid_amount' => (float) (clone $paidPayments)->sum('amount') + (float) (clone $paidSessions)->sum('amount'),
        ];
    }

    private function statusBreakdown($paymentQuery, $sessionQuery): array
    {
        $statuses = [
            PaymentStatus::Pending->value => 'En attente',
            PaymentStatus::Paid->value => 'Payés',
            PaymentStatus::Failed->value => 'Échoués',
        ];

        return collect($statuses)
            ->map(fn (string $label, string $status): array => [
                'label' => $label,
                'value' => (clone $paymentQuery)->where('status', $status)->count() + (clone $sessionQuery)->where('status', $status)->count(),
            ])
            ->values()
            ->all();
    }

    private function contextBreakdown($paymentQuery, $sessionQuery): array
    {
        $contexts = [
            'report' => 'Signalements',
            'damage' => 'Dommages',
        ];

        return collect($contexts)
            ->map(fn (string $label, string $context): array => [
                'label' => $label,
                'value' => (clone $paymentQuery)->where('payment_context', $context)->count() + (clone $sessionQuery)->where('payment_context', $context)->count(),
            ])
            ->values()
            ->all();
    }

    private function paymentTrend($paymentQuery, $sessionQuery): array
    {
        $paymentRows = (clone $paymentQuery)
            ->where('status', PaymentStatus::Paid->value)
            ->selectRaw('DATE(payments.initiated_at) as period_label')
            ->selectRaw('COUNT(*) as payments_count')
            ->selectRaw('SUM(amount) as payments_amount')
            ->groupByRaw('DATE(payments.initiated_at)')
            ->pluck('payments_amount', 'period_label');
        $sessionRows = (clone $sessionQuery)
            ->where('status', PaymentStatus::Paid->value)
            ->selectRaw('DATE(incident_report_payment_sessions.initiated_at) as period_label')
            ->selectRaw('SUM(amount) as payments_amount')
            ->groupByRaw('DATE(incident_report_payment_sessions.initiated_at)')
            ->pluck('payments_amount', 'period_label');

        return $paymentRows
            ->mergeRecursive($sessionRows)
            ->map(fn ($value): float => is_array($value) ? array_sum(array_map('floatval', $value)) : (float) $value)
            ->sortKeys()
            ->map(fn (float $amount, string $label): array => [
                'label' => Carbon::parse($label)->format('d/m'),
                'amount' => $amount,
            ])
            ->values()
            ->all();
    }

    public function validateSession(Request $request, IncidentReportPaymentSession $paymentSession, ConfirmIncidentReportFineoPaymentAction $action, ActivityLogger $activityLogger): RedirectResponse
    {
        $attributes = $request->validate([
            'confirmation' => ['required', 'string', 'in:VALIDER'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        if ($paymentSession->status === PaymentStatus::Paid->value && $paymentSession->incident_report_id !== null) {
            return redirect()
                ->route('super-admin.payments.index')
                ->with('success', 'Cette session est déjà payée et traitée.');
        }

        $validatedSession = $action->handle([
            'syncRef' => $paymentSession->sync_ref,
            'reference' => 'MANUAL-SA-'.$paymentSession->sync_ref,
            'amount' => (int) $paymentSession->amount,
            'status' => 'success',
            'clientAccountNumber' => null,
            'timestamp' => Carbon::now()->toIso8601String(),
            'manual_validation' => true,
            'validated_by_user_id' => $request->user()?->id,
            'validated_by_user_email' => $request->user()?->email,
            'manual_validation_reason' => $attributes['reason'] ?? null,
        ], $request);

        $activityLogger->log(
            'payment_session.manually_validated',
            'Validation manuelle d’une session de paiement FineoPay.',
            $validatedSession,
            [
                'sync_ref' => $validatedSession->sync_ref,
                'payment_context' => $validatedSession->payment_context,
                'incident_report_id' => $validatedSession->incident_report_id,
                'amount' => $validatedSession->amount,
                'currency' => $validatedSession->currency,
                'reason' => $attributes['reason'] ?? null,
            ],
            $request,
            portal: 'super_admin',
        );

        return redirect()
            ->route('super-admin.payments.index')
            ->with('success', 'Paiement validé manuellement. La session est maintenant traitée.');
    }
}
