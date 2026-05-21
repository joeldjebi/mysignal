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

class PaymentController extends Controller
{
    public function index(): View
    {
        $query = Payment::query()
            ->with([
                'publicUser.publicUserType',
                'incidentReport.application',
                'incidentReport.organization',
                'pricingRule',
            ]);

        if (filled(request('search'))) {
            $search = trim((string) request('search'));

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

        if (filled(request('status'))) {
            $query->where('status', request('status'));
        }

        if (filled(request('provider'))) {
            $query->where('provider', request('provider'));
        }

        if (filled(request('payment_context'))) {
            $query->where('payment_context', request('payment_context'));
        }

        if (filled(request('application_id'))) {
            $applicationId = (int) request('application_id');
            $query->whereHas('incidentReport', fn ($reportQuery) => $reportQuery->where('application_id', $applicationId));
        }

        if (filled(request('organization_id'))) {
            $organizationId = (int) request('organization_id');
            $query->whereHas('incidentReport', fn ($reportQuery) => $reportQuery->where('organization_id', $organizationId));
        }

        $sessionQuery = IncidentReportPaymentSession::query()
            ->with([
                'publicUser.publicUserType',
                'pricingRule',
                'incidentReport.application',
                'incidentReport.organization',
            ]);

        if (filled(request('session_status'))) {
            $sessionQuery->where('status', request('session_status'));
        } else {
            $sessionQuery->whereIn('status', [
                PaymentStatus::Pending->value,
                PaymentStatus::Failed->value,
            ]);
        }

        if (filled(request('session_context'))) {
            $sessionQuery->where('payment_context', request('session_context'));
        }

        if (filled(request('session_search'))) {
            $sessionSearch = trim((string) request('session_search'));

            $sessionQuery->where(function ($builder) use ($sessionSearch): void {
                $builder->where('sync_ref', 'like', '%'.$sessionSearch.'%')
                    ->orWhere('provider_reference', 'like', '%'.$sessionSearch.'%')
                    ->orWhereHas('publicUser', function ($publicUserQuery) use ($sessionSearch): void {
                        $publicUserQuery->where('first_name', 'like', '%'.$sessionSearch.'%')
                            ->orWhere('last_name', 'like', '%'.$sessionSearch.'%')
                            ->orWhere('phone', 'like', '%'.$sessionSearch.'%')
                            ->orWhere('email', 'like', '%'.$sessionSearch.'%');
                    });
            });
        }

        $applications = Application::query()->orderBy('name')->get(['id', 'name']);
        $organizations = Organization::query()->orderBy('name')->get(['id', 'name']);

        return view('super-admin.payments.index', [
            'payments' => $query->latest('initiated_at')->latest('id')->paginate(15)->withQueryString(),
            'paymentSessions' => $sessionQuery->latest('initiated_at')->latest('id')->paginate(10, ['*'], 'sessions_page')->withQueryString(),
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

    public function validateSession(Request $request, IncidentReportPaymentSession $paymentSession, ConfirmIncidentReportFineoPaymentAction $action, ActivityLogger $activityLogger): RedirectResponse
    {
        $attributes = $request->validate([
            'confirmation' => ['required', 'string', 'in:VALIDER'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        if ($paymentSession->status === PaymentStatus::Paid->value && $paymentSession->incident_report_id !== null) {
            return redirect()
                ->route('super-admin.payments.index')
                ->with('success', 'Cette session est deja payee et traitee.');
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
            'Validation manuelle SA d une session de paiement FineoPay.',
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
            ->with('success', 'Paiement valide manuellement. La session est maintenant traitee.');
    }
}
