<?php

namespace App\Http\Controllers\Web\Institution;

use App\Http\Controllers\Controller;
use App\Models\InstitutionActivationLetter;
use App\Support\Audit\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstitutionActivationController extends Controller
{
    public function show(Request $request): View
    {
        $code = trim((string) $request->query('code'));
        $letter = filled($code)
            ? InstitutionActivationLetter::query()
                ->with('organization')
                ->where('activation_code', $code)
                ->first()
            : null;

        return view('institution.activation', [
            'code' => $code,
            'letter' => $letter,
            'isExpired' => $letter?->expires_at !== null && $letter->expires_at->isPast(),
        ]);
    }

    public function store(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $request->merge([
            'focal_latitude' => $this->normalizeCoordinate($request->input('focal_latitude')),
            'focal_longitude' => $this->normalizeCoordinate($request->input('focal_longitude')),
        ]);

        $attributes = $request->validate([
            'activation_code' => ['required', 'string', 'max:40', 'exists:institution_activation_letters,activation_code'],
            'focal_last_name' => ['required', 'string', 'max:120'],
            'focal_first_names' => ['required', 'string', 'max:180'],
            'focal_position' => ['required', 'string', 'max:180'],
            'focal_phone' => ['required', 'string', 'max:30'],
            'focal_email' => ['required', 'email', 'max:255'],
            'focal_location' => ['nullable', 'string', 'max:255'],
            'focal_latitude' => ['required', 'numeric', 'between:-90,90'],
            'focal_longitude' => ['required', 'numeric', 'between:-180,180'],
            'location_accuracy' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ]);

        $letter = InstitutionActivationLetter::query()
            ->with('organization')
            ->where('activation_code', $attributes['activation_code'])
            ->firstOrFail();

        if ($letter->expires_at !== null && $letter->expires_at->isPast()) {
            return back()->withErrors([
                'activation_code' => 'Ce code d’activation a expiré. Veuillez contacter l’équipe My-Signal.',
            ])->withInput();
        }

        if (in_array($letter->status, ['submitted', 'approved'], true)) {
            return back()->withErrors([
                'activation_code' => 'Les informations du point focal ont déjà été transmises pour cette institution.',
            ])->withInput();
        }

        $letter->update([
            'focal_last_name' => $attributes['focal_last_name'],
            'focal_first_names' => $attributes['focal_first_names'],
            'focal_position' => $attributes['focal_position'],
            'focal_phone' => $attributes['focal_phone'],
            'focal_email' => $attributes['focal_email'],
            'focal_location' => $attributes['focal_location'] ?? 'Latitude '.$attributes['focal_latitude'].', longitude '.$attributes['focal_longitude'],
            'focal_latitude' => $attributes['focal_latitude'] ?? null,
            'focal_longitude' => $attributes['focal_longitude'] ?? null,
            'location_accuracy' => $attributes['location_accuracy'] ?? null,
            'status' => 'submitted',
            'submitted_at' => now(),
            'metadata' => [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ],
        ]);

        $activityLogger->log(
            'institution_activation.submitted',
            'Soumission des informations du point focal institutionnel.',
            $letter,
            [
                'organization_id' => $letter->organization_id,
                'activation_code' => $letter->activation_code,
                'focal_email' => $letter->focal_email,
                'focal_phone' => $letter->focal_phone,
            ],
            $request,
        );

        return redirect()
            ->route('institution.activation.show', ['code' => $letter->activation_code])
            ->with('success', 'Vos informations ont été transmises avec succès. L’équipe My-Signal procédera à la vérification.');
    }

    private function normalizeCoordinate(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        return str_replace(',', '.', trim($value));
    }
}
