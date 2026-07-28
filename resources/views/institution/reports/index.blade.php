@extends('institution.layouts.app')

@section('title', config('app.name').' | Signalements')
@section('page-title', 'File des signaux')
@section('page-description', 'Signalements reçus par votre institution.')

@section('content')
    @php
        $canViewPaymentInfo = in_array('INSTITUTION_PAYMENT_INFO', $features ?? [], true);
        $canViewDamageInfo = in_array('INSTITUTION_REPORT_DAMAGE_ACCESS', $features ?? [], true);
        $label = \App\Support\Ui\InstitutionLabel::class;
        $statusClass = fn ($status) => match ($status) {
            'resolved', 'paid' => 'chip-success',
            'in_progress' => 'chip-warning',
            'rejected', 'failed' => 'chip-danger',
            default => 'chip-neutral',
        };
    @endphp

    <section class="panel-card">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <div>
                <div class="fw-bold">Signalements</div>
            </div>
            <span class="status-chip">{{ $reports->total() }} élément(s)</span>
        </div>

        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Référence, signal, description">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Commune</label>
                    <select name="commune_id" class="form-select">
                        <option value="">Toutes</option>
                        @foreach ($communes as $commune)
                            <option value="{{ $commune->id }}" @selected((string) request('commune_id') === (string) $commune->id)>{{ $commune->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Identifiant</label>
                    <select name="meter_id" class="form-select">
                        <option value="">Tous</option>
                        @foreach ($meters as $meter)
                            <option value="{{ $meter->id }}" @selected((string) request('meter_id') === (string) $meter->id)>
                                {{ $meter->meter_number ?: 'Sans numéro' }}@if($meter->label) · {{ $meter->label }}@endif
                            </option>
                        @endforeach
                    </select>
                </div>
                @if ($canViewPaymentInfo)
                    <div class="col-md-2">
                        <label class="form-label small text-secondary">Paiement</label>
                        <select name="payment_status" class="form-select">
                            <option value="">Tous</option>
                            <option value="pending" @selected(request('payment_status') === 'pending')>En attente</option>
                            <option value="paid" @selected(request('payment_status') === 'paid')>Payé</option>
                        </select>
                    </div>
                @endif
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Traitement</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="submitted" @selected(request('status') === 'submitted')>Soumis</option>
                        <option value="in_progress" @selected(request('status') === 'in_progress')>En cours</option>
                        <option value="resolved" @selected(request('status') === 'resolved')>Résolus</option>
                        <option value="rejected" @selected(request('status') === 'rejected')>Rejetés</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-dark w-100">Filtrer</button>
                    <a href="{{ route('institution.reports.index') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                </div>
            </div>
        </form>

        <div class="table-toolbar">
            <div class="table-meta">{{ $reports->total() }} signalement(s)</div>
            <div class="table-meta">Page {{ $reports->currentPage() }} / {{ $reports->lastPage() }}</div>
        </div>
        <div class="table-card">
            <div class="table-responsive">
                <table class="table table-modern align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Référence</th>
                            <th>Signal</th>
                            <th>Identifiant</th>
                            <th>Traitement</th>
                            @if ($canViewPaymentInfo)
                                <th>Paiement</th>
                            @endif
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reports as $report)
                            <tr>
                                <td>
                                    <div class="meta-stack">
                                        <span class="meta-title">{{ $report->reference }}</span>
                                        <span class="meta-subtitle">{{ $report->created_at?->format('d/m/Y H:i') }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="meta-stack">
                                        <span class="meta-title">{{ $report->signal_label ?? $report->incident_type }}</span>
                                        <span class="meta-subtitle">
                                            {{ $report->commune?->name ?: '-' }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="meta-stack">
                                        <span class="meta-title">{{ $report->meter?->meter_number ?: '-' }}</span>
                                        <span class="meta-subtitle">{{ $report->meter?->label ?: 'Sans libellé' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="surface-soft">
                                        <div class="d-flex flex-column gap-2">
                                            <span class="status-chip {{ $statusClass($report->status) }}">{{ $label::status($report->status) }}</span>
                                            <span class="meta-subtitle">{{ $report->assignedTo?->name ?: 'Non assigné' }}</span>
                                        </div>
                                    </div>
                                </td>
                                @if ($canViewPaymentInfo)
                                    <td>
                                        <span class="status-chip {{ $statusClass($report->payment_status) }}">{{ $label::payment($report->payment_status) }}</span>
                                    </td>
                                @endif
                                <td class="text-end">
                                    <div class="report-actions">
                                        <a href="{{ route('institution.reports.show', $report) }}" class="btn btn-sm btn-outline-dark">Détails</a>

                                        @if ($report->status === 'submitted')
                                            <form method="POST" action="{{ route('institution.reports.take-over', $report) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-outline-dark">Prendre en charge</button>
                                            </form>
                                        @endif

                                        @if (in_array($report->status, ['submitted', 'in_progress'], true))
                                            <form method="POST" action="{{ route('institution.reports.resolve', $report) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="official_response" value="Signalement résolu par l’institution.">
                                                <button class="btn btn-sm btn-outline-success">Résoudre</button>
                                            </form>
                                            <form method="POST" action="{{ route('institution.reports.reject', $report) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="official_response" value="Signalement rejeté après analyse institutionnelle.">
                                                <button class="btn btn-sm btn-outline-danger">Rejeter</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ $canViewPaymentInfo ? 6 : 5 }}" class="text-center text-secondary py-4">Aucun signalement disponible.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $reports->currentPage() }} sur {{ $reports->lastPage() }}</div>
            {{ $reports->links() }}
        </div>
    </section>
@endsection
