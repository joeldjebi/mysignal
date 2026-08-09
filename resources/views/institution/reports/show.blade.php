@extends('institution.layouts.app')

@section('title', config('app.name').' | Détail signalement')
@section('page-title', 'Détail signalement')
@section('page-description', 'Informations utiles pour faciliter la prise en charge et la résolution.')

@section('content')
    @php
        $canViewPaymentInfo = in_array('INSTITUTION_PAYMENT_INFO', $features ?? [], true);
        $canViewDamageInfo = in_array('INSTITUTION_REPORT_DAMAGE_ACCESS', $features ?? [], true);
        $canResolveDamage = in_array('INSTITUTION_REPORT_DAMAGE_RESOLUTION', $features ?? [], true);
        $label = \App\Support\Ui\InstitutionLabel::class;
        $damageStatusLabel = $label::damage($report->damage_resolution_status ?? 'submitted');
        $damageStatusClass = match ($report->damage_resolution_status ?? 'submitted') {
            'resolved' => 'chip-success',
            'in_progress' => 'chip-warning',
            'rejected' => 'chip-danger',
            default => 'chip-neutral',
        };
        $chipClass = match ($slaState['code']) {
            'within' => 'chip-success',
            'risk' => 'chip-warning',
            'breached' => 'chip-danger',
            default => 'chip-neutral',
        };
        $reportGoogleMapsUrl = ($report->latitude && $report->longitude)
            ? 'https://www.google.com/maps/search/?api=1&query='.$report->latitude.','.$report->longitude
            : null;
        $meterGoogleMapsUrl = ($report->meter?->latitude && $report->meter?->longitude)
            ? 'https://www.google.com/maps/search/?api=1&query='.$report->meter->latitude.','.$report->meter->longitude
            : null;
    @endphp

    <div class="row g-4">
        <div class="col-xl-4">
            <section class="panel-card h-100">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <div class="fw-bold fs-5">{{ $report->reference }}</div>
                        <div class="text-secondary small">{{ $report->signal_label ?: 'Signalement' }}</div>
                    </div>
                    <span class="status-chip {{ $chipClass }}">{{ $slaState['label'] }}</span>
                </div>

                <div class="vstack gap-3">
                    <div>
                        <div class="small text-secondary">Signalement</div>
                        <div class="fw-semibold">{{ $report->signal_label ?: 'Signalement' }}</div>
                    </div>
                    <div>
                        <div class="small text-secondary">Date de création</div>
                        <div class="fw-semibold">{{ $report->created_at?->format('d/m/Y H:i') }}</div>
                    </div>
                    <div>
                        <div class="small text-secondary">Délai attendu</div>
                        <div class="fw-semibold">{{ $report->target_sla_hours ?: '-' }} h</div>
                    </div>
                    <div>
                        <div class="small text-secondary">Temps écoulé</div>
                        <div class="fw-semibold">{{ $slaState['elapsed_hours'] !== null ? $slaState['elapsed_hours'].' h' : '-' }}</div>
                    </div>
                    <div>
                        <div class="small text-secondary">Traitement</div>
                        <div class="fw-semibold">{{ $label::status($report->status) }}</div>
                        <div class="text-secondary small">{{ $report->assignedTo?->name ?: 'Non assigné' }}</div>
                    </div>
                    @if ($canViewPaymentInfo)
                        <div>
                            <div class="small text-secondary">Paiement</div>
                            <div class="fw-semibold">{{ $label::payment($report->payment_status) }}</div>
                        </div>
                    @endif
                    @if ($reportGoogleMapsUrl)
                        <div>
                            <a href="{{ $reportGoogleMapsUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-dark w-100">Ouvrir la position du signalement dans Google Maps</a>
                        </div>
                    @endif
                </div>
            </section>
        </div>

        <div class="col-xl-8">
            <section class="panel-card mb-4">
                <div class="fw-bold mb-3">Déclarant</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="surface-soft">
                            <div class="meta-title">{{ $report->publicUser?->first_name }} {{ $report->publicUser?->last_name }}</div>
                            <div class="meta-subtitle">{{ $report->publicUser?->phone }}</div>
                            <div class="meta-subtitle">{{ $report->publicUser?->email ?: '-' }}</div>
                            <div class="meta-subtitle mt-2">Commune: {{ $report->publicUser?->commune ?: '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="surface-soft">
                            <div class="meta-title">Foyer</div>
                            <div class="meta-subtitle">{{ $report->publicUser?->ownedHousehold?->name ?: 'Aucun foyer principal' }}</div>
                            <div class="meta-subtitle">{{ $report->publicUser?->ownedHousehold?->address ?: '-' }}</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="panel-card mb-4">
                <div class="fw-bold mb-3">Identifiant et localisation</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="surface-soft">
                            <div class="meta-title">{{ $report->meter?->meter_number ?: '-' }}</div>
                            <div class="meta-subtitle">{{ $label::humanize($report->meter?->network_type) }} · {{ $report->meter?->label ?: 'Sans libellé' }}</div>
                            <div class="meta-subtitle mt-2">Commune: {{ $report->meter?->commune ?: '-' }}</div>
                            <div class="meta-subtitle">Adresse: {{ $report->meter?->address ?: '-' }}</div>
                            @if ($meterGoogleMapsUrl)
                                <a href="{{ $meterGoogleMapsUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-dark mt-3">Ouvrir l’identifiant dans Google Maps</a>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="surface-soft">
                            <div class="meta-title">{{ $report->country?->name }} · {{ $report->city?->name }}</div>
                            <div class="meta-subtitle">{{ $report->commune?->name ?: '-' }}</div>
                            <div class="meta-subtitle mt-2">
                                @if ($report->latitude && $report->longitude)
                                    GPS signalement: {{ $report->latitude }}, {{ $report->longitude }}
                                @else
                                    Position GPS non renseignée
                                @endif
                            </div>
                            @if ($reportGoogleMapsUrl)
                                <a href="{{ $reportGoogleMapsUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-dark mt-3">Ouvrir le signalement dans Google Maps</a>
                            @endif
                        </div>
                    </div>
                </div>
            </section>

            <section class="panel-card mb-4">
                <div class="fw-bold mb-3">Contenu du signalement</div>
                <div class="surface-soft mb-3">
                    <div class="meta-subtitle mb-2">Description</div>
                    <div>{{ $report->description ?: 'Aucune description fournie.' }}</div>
                </div>

                @include('partials.report-signal-attachment', [
                    'attachment' => $resolvedSignalAttachment,
                    'title' => 'Fichier joint',
                ])

                <div class="surface-soft">
                    <div class="meta-subtitle mb-2">Informations complémentaires</div>
                    @if (!empty($resolvedSignalPayload))
                        <div class="table-responsive">
                            <table class="table table-modern align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Champ</th>
                                        <th>Valeur</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($resolvedSignalPayload as $key => $value)
                                        <tr>
                                            <td>{{ $label::humanize($key) }}</td>
                                            <td>
                                                @if (is_array($value) && ($value['type'] ?? null) === 'image' && filled($value['temporary_url'] ?? null))
                                                    <div class="vstack gap-2">
                                                        <div class="small text-secondary">{{ $value['name'] ?? 'Image jointe' }}</div>
                                                        <img src="{{ $value['temporary_url'] }}" alt="{{ $key }}" style="max-width: 240px; max-height: 240px; object-fit: cover; border-radius: 16px; border: 1px solid rgba(24, 52, 71, 0.12);">
                                                    </div>
                                                @else
                                                    {{ is_array($value) ? (collect($value)->filter(fn ($item) => is_scalar($item))->map(fn ($item, $itemKey) => $label::humanize($itemKey).': '.$item)->implode(' · ') ?: 'Information jointe') : $value }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-secondary">Aucune information complémentaire.</div>
                    @endif
                </div>
            </section>

            @if ($canViewPaymentInfo)
                <section class="panel-card mb-4">
                    <div class="fw-bold mb-3">Paiements associés</div>
                    <div class="table-responsive">
                        <table class="table table-modern align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Référence</th>
                                    <th>Montant</th>
                                    <th>Statut</th>
                                    <th>Service</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($report->payments as $payment)
                                    <tr>
                                        <td>{{ $payment->reference }}</td>
                                        <td>{{ number_format($payment->amount, 0, ',', ' ') }} {{ $payment->currency }}</td>
                                        <td><span class="status-chip">{{ $label::payment($payment->status) }}</span></td>
                                        <td>{{ $label::humanize($payment->provider) }}</td>
                                        <td>{{ $payment->paid_at?->format('d/m/Y H:i') ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-secondary">Aucun paiement associé.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif

            @if ($canViewDamageInfo)
                <section class="panel-card mb-4">
                    <div class="fw-bold mb-3">Dommages déclarés par l’usager</div>

                    @if ($report->damage_summary || $report->damage_declared_at || $report->damage_notes || !empty($report->damage_attachment))
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="surface-soft h-100">
                                    <div class="meta-subtitle mb-2">Synthèse</div>
                                    <div class="fw-semibold">{{ $report->damage_summary ?: 'Déclaration de dommage enregistrée' }}</div>
                                    <div class="mt-2"><span class="status-chip {{ $damageStatusClass }}">{{ $damageStatusLabel }}</span></div>
                                    <div class="meta-subtitle mt-2">Date de déclaration</div>
                                    <div>{{ $report->damage_declared_at?->format('d/m/Y H:i') ?: '-' }}</div>
                                    <div class="meta-subtitle mt-2">Date de clôture du dommage</div>
                                    <div>{{ $report->damage_resolved_at?->format('d/m/Y H:i') ?: '-' }}</div>
                                    <div class="meta-subtitle mt-2">Montant estimé</div>
                                    <div>
                                        {{ $report->damage_amount_estimated !== null
                                            ? number_format((float) $report->damage_amount_estimated, 0, ',', ' ').' FCFA'
                                            : 'Non renseigné' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="surface-soft h-100">
                                    <div class="meta-subtitle mb-2">Commentaires de l’usager</div>
                                    <div>{{ $report->damage_notes ?: 'Aucun détail complémentaire fourni.' }}</div>
                                    <div class="meta-subtitle mt-3 mb-2">Réponse institutionnelle sur le dommage</div>
                                    <div>{{ $report->damage_resolution_notes ?: 'Aucune réponse institutionnelle sur le dommage pour le moment.' }}</div>
                                </div>
                            </div>
                            @if (!empty($resolvedDamageAttachment))
                                <div class="col-12">
                                    <div class="surface-soft">
                                        <div class="meta-subtitle mb-2">Justificatif joint</div>
                                        @if (str_starts_with((string) ($resolvedDamageAttachment['mime_type'] ?? ''), 'image/') && filled($resolvedDamageAttachment['temporary_url'] ?? null))
                                            <div class="vstack gap-2">
                                                <div class="small text-secondary">{{ $resolvedDamageAttachment['name'] ?? 'Image jointe' }}</div>
                                                <img
                                                    src="{{ $resolvedDamageAttachment['temporary_url'] }}"
                                                    alt="Justificatif dommage"
                                                    style="max-width: 100%; max-height: 420px; object-fit: contain; background: #f7f9fc; border-radius: 16px; border: 1px solid rgba(24, 52, 71, 0.12);"
                                                >
                                            </div>
                                        @elseif (filled($resolvedDamageAttachment['temporary_url'] ?? null))
                                            <div class="d-flex flex-wrap align-items-center gap-3">
                                                <div>
                                                    <div class="fw-semibold">{{ $resolvedDamageAttachment['name'] ?? 'Document joint' }}</div>
                                                    <div class="small text-secondary">{{ $resolvedDamageAttachment['mime_type'] ?? 'Fichier' }}</div>
                                                </div>
                                                <a
                                                    href="{{ $resolvedDamageAttachment['temporary_url'] }}"
                                                    download="{{ $resolvedDamageAttachment['name'] ?? 'justificatif-dommage' }}"
                                                    class="btn btn-outline-dark btn-sm"
                                                >
                                                    Télécharger le justificatif
                                                </a>
                                            </div>
                                        @else
                                            <div class="text-secondary">Justificatif présent mais non disponible.</div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-secondary">Aucun dommage n’a été déclaré pour ce signalement.</div>
                    @endif

                    @if ($canResolveDamage && $report->damage_declared_at)
                        <form method="POST" action="{{ route('institution.reports.damage-resolution', $report) }}" class="mt-3">
                            @csrf
                            @method('PATCH')
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label small text-secondary">Statut de résolution du dommage</label>
                                    <select name="damage_resolution_status" class="form-select">
                                        <option value="submitted" @selected(($report->damage_resolution_status ?? 'submitted') === 'submitted')>Soumis</option>
                                        <option value="in_progress" @selected(($report->damage_resolution_status ?? 'submitted') === 'in_progress')>En cours</option>
                                        <option value="resolved" @selected(($report->damage_resolution_status ?? 'submitted') === 'resolved')>Résolu</option>
                                        <option value="rejected" @selected(($report->damage_resolution_status ?? 'submitted') === 'rejected')>Rejeté</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small text-secondary">Notes institutionnelles</label>
                                    <textarea name="damage_resolution_notes" class="form-control" rows="3" placeholder="Précisions sur l’analyse, la prise en charge ou la décision institutionnelle.">{{ old('damage_resolution_notes', $report->damage_resolution_notes) }}</textarea>
                                </div>
                                <div class="col-md-2 d-grid">
                                    <button class="btn btn-outline-dark">Mettre à jour</button>
                                </div>
                            </div>
                        </form>
                    @endif
                </section>
            @endif

            <section class="panel-card">
                <div class="fw-bold mb-3">Actions de traitement</div>
                <div class="report-actions justify-content-start">
                    @if ($report->status === 'submitted')
                        <form method="POST" action="{{ route('institution.reports.take-over', $report) }}">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-outline-dark">Prendre en charge</button>
                        </form>
                    @endif

                    @if (in_array($report->status, ['submitted', 'in_progress'], true))
                        <form method="POST" action="{{ route('institution.reports.resolve', $report) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="official_response" value="Signalement résolu par l’institution.">
                            <button class="btn btn-outline-success">Marquer comme résolu</button>
                        </form>
                        <form method="POST" action="{{ route('institution.reports.reject', $report) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="official_response" value="Signalement rejeté après analyse institutionnelle.">
                            <button class="btn btn-outline-danger">Rejeter</button>
                        </form>
                    @endif
                </div>

                @if ($report->official_response)
                    <div class="surface-soft mt-3">
                        <div class="meta-subtitle mb-2">Réponse officielle enregistrée</div>
                        <div>{{ $report->official_response }}</div>
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection
