@extends('institution.layouts.app')

@section('title', config('app.name').' | Detail usager public')
@section('page-title', 'Detail usager public')
@section('page-description', 'Vue detaillee d un usager public, de ses compteurs et de ses signalements par compteur.')

@section('content')
    @php
        $canViewPaymentInfo = in_array('INSTITUTION_PAYMENT_INFO', $features ?? [], true);
    @endphp
    <div class="row g-4">
        <div class="col-xl-4">
            <section class="panel-card h-100">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <div class="fw-bold fs-5">{{ $reportUser->first_name }} {{ $reportUser->last_name }}</div>
                        <div class="text-secondary small">{{ $reportUser->phone }}</div>
                        <div class="text-secondary small">{{ $reportUser->email ?: '-' }}</div>
                    </div>
                    <span class="status-chip">{{ $reportUser->status }}</span>
                </div>

                <div class="vstack gap-3">
                    <div>
                        <div class="small text-secondary">Commune</div>
                        <div class="fw-semibold">{{ $reportUser->commune ?: '-' }}</div>
                    </div>
                    <div>
                        <div class="small text-secondary">Nombre de compteurs</div>
                        <div class="fw-semibold">{{ $reportUser->meters->count() }}</div>
                    </div>
                    <div>
                        <div class="small text-secondary">Nombre de signalements</div>
                        <div class="fw-semibold">{{ $reportUser->incidentReports->count() }}</div>
                    </div>
                </div>
            </section>
        </div>
        <div class="col-xl-8">
            <section class="panel-card mb-4">
                <div class="fw-bold mb-3">Liste des compteurs</div>
                <div class="table-responsive">
                    <table class="table table-modern align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Numero</th>
                                <th>Reseau</th>
                                <th>Libelle</th>
                                <th>Commune</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($reportUser->meters as $meter)
                                <tr>
                                    <td class="fw-semibold">{{ $meter->meter_number }}</td>
                                    <td>{{ $meter->network_type }}</td>
                                    <td>{{ $meter->label ?: '-' }}</td>
                                    <td>{{ $meter->commune ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-secondary">Aucun compteur pour cet usager sur ce reseau.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="panel-card">
                <div class="fw-bold mb-3">Historique des signalements par compteur</div>

                @forelse ($reportsByMeter as $group)
                    <div class="border rounded-4 p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <div>
                                <div class="fw-bold">
                                    {{ $group['meter']?->meter_number ?: 'Sans compteur associe' }}
                                </div>
                                <div class="small text-secondary">
                                    {{ $group['meter']?->network_type ?: '-' }}
                                    @if ($group['meter']?->label)
                                        · {{ $group['meter']->label }}
                                    @endif
                                </div>
                            </div>
                            <span class="status-chip">{{ $group['reports']->count() }} signalement(s)</span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-modern align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Reference</th>
                                        <th>Signal</th>
                                        <th>Commune</th>
                                        <th>Traitement</th>
                                        @if ($canViewPaymentInfo)
                                            <th>Paiement</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($group['reports'] as $report)
                                        <tr>
                                            <td class="fw-semibold">{{ $report->reference }}</td>
                                            <td>
                                                <div>{{ $report->signal_label ?: $report->signal_code }}</div>
                                                <div class="small text-secondary">{{ $report->created_at?->format('d/m/Y H:i') }}</div>
                                            </td>
                                            <td>{{ $report->commune?->name ?: '-' }}</td>
                                            <td>
                                                <div><span class="status-chip">{{ $report->status }}</span></div>
                                                <div class="small text-secondary mt-1">{{ $report->assignedTo?->name ?: 'Non assigne' }}</div>
                                            </td>
                                            @if ($canViewPaymentInfo)
                                                <td><span class="status-chip">{{ $report->payment_status }}</span></td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <div class="text-secondary">Aucun signalement disponible pour cet usager sur ce reseau.</div>
                @endforelse
            </section>
        </div>
    </div>
@endsection
