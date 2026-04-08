@extends('institution.layouts.app')

@section('title', config('app.name').' | Detail compteur')
@section('page-title', 'Detail compteur')
@section('page-description', 'Vue detaillee du compteur et de son historique recent.')

@section('content')
    @php
        $meterGoogleMapsUrl = ($meter->latitude && $meter->longitude)
            ? 'https://www.google.com/maps/search/?api=1&query='.$meter->latitude.','.$meter->longitude
            : null;
    @endphp
    <div class="row g-4">
        <div class="col-xl-5">
            <section class="panel-card h-100">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="fw-bold fs-5">{{ $meter->label ?: $meter->meter_number }}</div>
                        <div class="text-secondary small">{{ $meter->network_type }} · {{ $meter->meter_number }}</div>
                    </div>
                    <span class="status-chip">{{ $meter->status }}</span>
                </div>

                <div class="vstack gap-3">
                    <div>
                        <div class="small text-secondary">Commune</div>
                        <div class="fw-semibold">{{ $meter->commune ?: '-' }}</div>
                    </div>
                    <div>
                        <div class="small text-secondary">Adresse</div>
                        <div class="fw-semibold">{{ $meter->address ?: '-' }}</div>
                    </div>
                    <div>
                        <div class="small text-secondary">Position GPS</div>
                        <div class="fw-semibold">
                            @if ($meter->latitude && $meter->longitude)
                                {{ $meter->latitude }}, {{ $meter->longitude }}
                            @else
                                Non renseignee
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="small text-secondary">Source localisation</div>
                        <div class="fw-semibold">{{ $meter->location_source ?: '-' }}</div>
                    </div>
                    @if ($meterGoogleMapsUrl)
                        <div>
                            <a href="{{ $meterGoogleMapsUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-dark w-100">Ouvrir dans Google Maps</a>
                        </div>
                    @endif
                </div>
            </section>
        </div>
        <div class="col-xl-7">
            <section class="panel-card mb-4">
                <div class="fw-bold mb-3">Usagers rattaches</div>
                <div class="table-responsive">
                    <table class="table table-modern align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Telephone</th>
                                <th>Commune</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($meter->publicUsers as $user)
                                <tr>
                                    <td>{{ $user->first_name }} {{ $user->last_name }}</td>
                                    <td>{{ $user->phone }}</td>
                                    <td>{{ $user->commune ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-secondary">Aucun usager rattache.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="panel-card">
                <div class="fw-bold mb-3">Derniers signalements sur ce compteur</div>
                <div class="table-responsive">
                    <table class="table table-modern align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Signal</th>
                                <th>Commune</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($meter->incidentReports as $report)
                                <tr>
                                    <td>{{ $report->reference }}</td>
                                    <td>{{ $report->signal_label ?: $report->signal_code }}</td>
                                    <td>{{ $report->commune?->name ?: '-' }}</td>
                                    <td><span class="status-chip">{{ $report->status }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-secondary">Aucun signalement lie a ce compteur.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
@endsection
