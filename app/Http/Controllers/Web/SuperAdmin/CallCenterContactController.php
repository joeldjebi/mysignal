<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\IncidentReport;
use App\Models\IncidentReportPaymentSession;
use App\Models\Payment;
use App\Models\PublicUser;
use App\Support\Audit\ActivityLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CallCenterContactController extends Controller
{
    public function publicUser(Request $request, PublicUser $publicUser, ActivityLogger $activityLogger): RedirectResponse
    {
        $this->storeContact($request, $publicUser, $publicUser, 'public_user', $activityLogger);

        return back()->with('success', 'L’appel de l’usager a été enregistré.');
    }

    public function report(Request $request, IncidentReport $report, ActivityLogger $activityLogger): RedirectResponse
    {
        $this->storeContact($request, $report, $report->publicUser, 'report', $activityLogger);

        return back()->with('success', 'L’appel lié au signalement a été enregistré.');
    }

    public function payment(Request $request, Payment $payment, ActivityLogger $activityLogger): RedirectResponse
    {
        $this->storeContact($request, $payment, $payment->publicUser, 'payment', $activityLogger);

        return back()->with('success', 'L’appel lié au paiement a été enregistré.');
    }

    public function paymentSession(Request $request, IncidentReportPaymentSession $paymentSession, ActivityLogger $activityLogger): RedirectResponse
    {
        $this->storeContact($request, $paymentSession, $paymentSession->publicUser, 'payment', $activityLogger);

        return back()->with('success', 'L’appel lié à la session de paiement a été enregistré.');
    }

    private function storeContact(Request $request, Model $contactable, ?PublicUser $publicUser, string $context, ActivityLogger $activityLogger): void
    {
        $attributes = $request->validate([
            'comment' => ['required', 'string', 'max:1200'],
        ]);

        $contact = $contactable->callCenterContacts()->create([
            'public_user_id' => $publicUser?->id,
            'called_by_user_id' => $request->user()?->id,
            'context' => $context,
            'comment' => $attributes['comment'],
            'called_at' => now(),
            'metadata' => [
                'public_user_phone' => $publicUser?->phone,
                'public_user_name' => trim(($publicUser?->first_name ?? '').' '.($publicUser?->last_name ?? '')) ?: null,
            ],
        ]);

        $activityLogger->log(
            'call_center.contact_recorded',
            'Enregistrement d’un appel call center.',
            $contact,
            [
                'context' => $context,
                'contactable_type' => $contactable::class,
                'contactable_id' => $contactable->getKey(),
                'public_user_id' => $publicUser?->id,
            ],
            $request,
            $request->user(),
            'super_admin',
        );
    }
}
