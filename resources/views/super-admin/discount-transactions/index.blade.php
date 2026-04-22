@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Reductions appliquees')
@section('page-title', 'Reductions appliquees')
@section('page-description', 'Consulter l historique des reductions appliquees par les partenaires, avec filtres sur les offres, agents et periodes.')

@section('header-badges')
    <span class="badge-soft">{{ $transactions->total() }} reduction{{ $transactions->total() > 1 ? 's' : '' }}</span>
@endsection

@section('content')
    <section class="panel-card">
        <div class="fw-bold mb-3">Historique des reductions appliquees</div>
        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Reference, carte, UP, agent, offre">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        @foreach (['validated', 'cancelled', 'reversed', 'rejected'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Verification</label>
                    <select name="verification_status" class="form-select">
                        <option value="">Toutes</option>
                        @foreach (['verified', 'rejected'] as $status)
                            <option value="{{ $status }}" @selected(request('verification_status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Partenaire</label>
                    <select name="organization_id" class="form-select">
                        <option value="">Tous</option>
                        @foreach ($organizations as $organization)
                            <option value="{{ $organization->id }}" @selected((string) request('organization_id') === (string) $organization->id)>{{ $organization->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Agent</label>
                    <select name="partner_user_id" class="form-select">
                        <option value="">Tous</option>
                        @foreach ($partnerUsers as $partnerUser)
                            <option value="{{ $partnerUser->id }}" @selected((string) request('partner_user_id') === (string) $partnerUser->id)>{{ $partnerUser->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row g-2 align-items-end mt-1">
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Offre</label>
                    <select name="offer_id" class="form-select">
                        <option value="">Toutes</option>
                        @foreach ($offers as $offer)
                            <option value="{{ $offer->id }}" @selected((string) request('offer_id') === (string) $offer->id)>{{ $offer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Type UP</label>
                    <select name="public_user_type_id" class="form-select">
                        <option value="">Tous</option>
                        @foreach ($publicUserTypes as $type)
                            <option value="{{ $type->id }}" @selected((string) request('public_user_type_id') === (string) $type->id)>{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Du</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-secondary">Au</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-dark w-100">Filtrer</button>
                    <a href="{{ route('super-admin.discount-transactions.index') }}" class="btn btn-outline-secondary">RAZ</a>
                </div>
            </div>
        </form>

        <div class="table-toolbar">
            <div class="table-meta">{{ $transactions->total() }} resultat{{ $transactions->total() > 1 ? 's' : '' }}</div>
        </div>
        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Reduction</th>
                        <th>Partenaire / agent</th>
                        <th>UP / carte</th>
                        <th>Offre</th>
                        <th>Montants</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $transaction)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $transaction->scan_reference }}</div>
                                <div class="small text-secondary">{{ optional($transaction->applied_at)->format('d/m/Y H:i') ?: '-' }}</div>
                                <div class="small text-secondary">Verification: {{ $transaction->verification_status }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $transaction->organization?->name ?: '-' }}</div>
                                <div class="small text-secondary">{{ $transaction->partnerUser?->name ?: '-' }}</div>
                                <div class="small text-secondary">{{ $transaction->partnerUser?->email ?: '-' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ trim(($transaction->publicUser?->first_name ?? '').' '.($transaction->publicUser?->last_name ?? '')) ?: '-' }}</div>
                                <div class="small text-secondary">{{ $transaction->publicUser?->phone ?: '-' }}</div>
                                <div class="small text-secondary">{{ $transaction->discountCard?->card_number ?: '-' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $transaction->offer?->name ?: '-' }}</div>
                                <div class="small text-secondary">{{ $transaction->offer?->code ?: '-' }}</div>
                                <div class="small text-secondary">{{ $transaction->discount_type_snapshot ?: '-' }} {{ $transaction->discount_value_snapshot ?? '' }}</div>
                            </td>
                            <td>
                                <div class="small">Initial: {{ $transaction->original_amount ?? '-' }}</div>
                                <div class="small">Reduction: {{ $transaction->discount_amount ?? '-' }}</div>
                                <div class="small">Final: {{ $transaction->final_amount ?? '-' }}</div>
                            </td>
                            <td><span class="status-chip">{{ $transaction->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-secondary">Aucune reduction appliquee trouvee.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $transactions->currentPage() }} sur {{ $transactions->lastPage() }}</div>
            {{ $transactions->links() }}
        </div>
    </section>
@endsection
