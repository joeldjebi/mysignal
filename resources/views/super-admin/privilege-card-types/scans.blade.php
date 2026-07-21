@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Scans de cartes privilèges')
@section('page-title', 'Historique des scans')
@section('page-description', 'Réductions appliquées par les agents partenaires avec les cartes privilèges.')

@section('header-badges')
    <span class="badge-soft">{{ $scans->total() }} passage{{ $scans->total() > 1 ? 's' : '' }}</span>
@endsection

@section('content')
    <section class="panel-card">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
            <div>
                <div class="fw-bold">Historique des scans</div>
                <div class="small text-secondary">Réductions appliquées par les agents partenaires avec les cartes privilèges.</div>
            </div>
            <span class="badge-soft">{{ $scans->total() }} passage{{ $scans->total() > 1 ? 's' : '' }}</span>
        </div>

        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input class="form-control" name="scan_search" value="{{ request('scan_search') }}" placeholder="Numéro, carte, usager, agent, institution">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Carte</label>
                    <select class="form-select" name="scan_type_id">
                        <option value="">Toutes</option>
                        @foreach ($cardTypes as $cardType)
                            <option value="{{ $cardType->id }}" @selected((string) request('scan_type_id') === (string) $cardType->id)>{{ $cardType->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-secondary">État</label>
                    <select class="form-select" name="scan_status">
                        <option value="">Tous</option>
                        <option value="validated" @selected(request('scan_status') === 'validated')>Validé</option>
                        <option value="cancelled" @selected(request('scan_status') === 'cancelled')>Annulé</option>
                        <option value="reversed" @selected(request('scan_status') === 'reversed')>Annulé après contrôle</option>
                        <option value="rejected" @selected(request('scan_status') === 'rejected')>Rejeté</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-dark w-100">OK</button>
                    <a href="{{ route('super-admin.privilege-card-types.scans') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Passage</th>
                        <th>Agent / institution</th>
                        <th>Usager / carte</th>
                        <th>Réduction</th>
                        <th>Montants</th>
                        <th>État</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($scans as $scan)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $scan->scan_reference }}</div>
                                <div class="small text-secondary">{{ $scan->applied_at?->format('d/m/Y H:i') ?: '-' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $scan->partnerUser?->name ?: '-' }}</div>
                                <div class="small text-secondary">{{ $scan->partnerUser?->email ?: '-' }}</div>
                                <div class="small text-secondary">{{ $scan->organization?->name ?: '-' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ trim(($scan->publicUser?->first_name ?? '').' '.($scan->publicUser?->last_name ?? '')) ?: '-' }}</div>
                                <div class="small text-secondary">{{ $scan->publicUser?->phone ?: '-' }}</div>
                                <div class="small text-secondary">{{ $scan->privilegeCard?->card_number ?: '-' }}</div>
                                <div class="small text-secondary">Code à scanner: <span class="font-monospace">{{ $scan->privilegeCard?->card_uuid ?: '-' }}</span></div>
                                <div class="small text-secondary">État de la carte: {{ $scan->privilegeCard ? ($cardStatusLabels[$scan->privilegeCard->status] ?? $scan->privilegeCard->status) : '-' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $scan->privilegeCard?->type?->name ?: '-' }}</div>
                                <div class="small text-secondary">
                                    {{ $scan->discount_type_snapshot === 'fixed_amount' ? number_format((float) $scan->discount_value_snapshot, 0, ',', ' ').' '.($scan->privilegeCard?->type?->currency ?? 'FCFA') : number_format((float) $scan->discount_value_snapshot, 0, ',', ' ').'%' }}
                                </div>
                            </td>
                            <td>
                                <div class="small">Montant initial: {{ $scan->original_amount ?? 'Non saisi' }}</div>
                                <div class="small">
                                    Réduction:
                                    {{ $scan->discount_amount ?? ($scan->discount_type_snapshot === 'fixed_amount' ? number_format((float) $scan->discount_value_snapshot, 0, ',', ' ').' '.($scan->privilegeCard?->type?->currency ?? 'FCFA') : number_format((float) $scan->discount_value_snapshot, 0, ',', ' ').'%') }}
                                </div>
                                <div class="small">Montant final: {{ $scan->final_amount ?? 'Non calculé' }}</div>
                            </td>
                            <td>
                                <span class="status-chip">{{ $scanStatusLabels[$scan->status] ?? $scan->status }}</span>
                                <div class="small text-secondary">{{ $verificationStatusLabels[$scan->verification_status] ?? $scan->verification_status }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-secondary">Aucun scan de carte privilège enregistré.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $scans->currentPage() }} sur {{ $scans->lastPage() }}</div>
            {{ $scans->links() }}
        </div>
    </section>
@endsection
