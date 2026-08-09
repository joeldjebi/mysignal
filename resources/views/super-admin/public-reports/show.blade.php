@extends('super-admin.layouts.app')

@section('title', 'Détail du signalement')

@section('content')
    @php
        $label = \App\Support\Ui\InstitutionLabel::class;
        $position = $report->latitude && $report->longitude
            ? [$report->latitude, $report->longitude]
            : ($report->meter?->latitude && $report->meter?->longitude ? [$report->meter->latitude, $report->meter->longitude] : null);
        $mapUrl = $position ? 'https://www.google.com/maps/search/?api=1&query='.$position[0].','.$position[1] : null;
    @endphp

    <div class="container-fluid py-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
            <div>
                <div class="page-kicker">Signalement</div>
                <h1 class="page-title mb-1">{{ $report->reference ?: 'Signalement #'.$report->id }}</h1>
                <div class="text-secondary">{{ $report->signal_label ?: $report->incident_type ?: 'Signalement public' }}</div>
            </div>
            <a href="{{ route('super-admin.public-reports.index') }}" class="btn btn-outline-secondary">Retour à la liste</a>
        </div>

        <div class="row g-3">
            <div class="col-xl-8">
                <div class="panel-card h-100">
                    <div class="fw-bold mb-3">Informations principales</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="small text-secondary">Catégorie</div>
                            <div class="fw-semibold">{{ $report->application?->name ?: '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-secondary">Institution</div>
                            <div class="fw-semibold">{{ $report->organization?->name ?: '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-secondary">État</div>
                            <span class="status-chip">{{ $label::status($report->status) }}</span>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-secondary">Paiement</div>
                            <span class="status-chip">{{ $label::payment($report->payment_status) }}</span>
                        </div>
                        <div class="col-md-4">
                            <div class="small text-secondary">Date</div>
                            <div class="fw-semibold">{{ $report->created_at?->format('d/m/Y H:i') ?: '-' }}</div>
                        </div>
                        <div class="col-12">
                            <div class="small text-secondary">Description</div>
                            <div class="fw-semibold">{{ $report->description ?: 'Aucune description renseignée.' }}</div>
                        </div>
                        <div class="col-12">
                            @include('partials.report-signal-attachment', [
                                'attachment' => $resolvedSignalAttachment ?? null,
                                'title' => 'Fichier joint par l’usager',
                            ])
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="panel-card h-100">
                    <div class="fw-bold mb-3">Usager et localisation</div>
                    <div class="vstack gap-3">
                        <div>
                            <div class="small text-secondary">Usager</div>
                            <div class="fw-semibold">{{ trim(($report->publicUser?->first_name ?? '').' '.($report->publicUser?->last_name ?? '')) ?: '-' }}</div>
                            <div class="text-secondary small">{{ $report->publicUser?->phone ?: '-' }}</div>
                        </div>
                        <div>
                            <div class="small text-secondary">Commune</div>
                            <div class="fw-semibold">{{ $report->commune?->name ?: $report->commune ?: '-' }}</div>
                        </div>
                        <div>
                            <div class="small text-secondary">Adresse</div>
                            <div class="fw-semibold">{{ $report->address ?: $report->gps_location ?: '-' }}</div>
                        </div>
                        <div>
                            <div class="small text-secondary">Identifiant</div>
                            <div class="fw-semibold">{{ $report->meter?->meter_number ?: '-' }}</div>
                        </div>
                        @if ($mapUrl)
                            <a href="{{ $mapUrl }}" target="_blank" rel="noopener" class="btn btn-premium">Ouvrir la position</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
