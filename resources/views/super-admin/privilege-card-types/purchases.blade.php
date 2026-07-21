@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Achats de cartes privilèges')
@section('page-title', 'Historique des achats')
@section('page-description', 'Suivi des achats de cartes privilèges et des paiements sécurisés.')

@section('header-badges')
    <span class="badge-soft">{{ $purchases->total() }} achat{{ $purchases->total() > 1 ? 's' : '' }}</span>
@endsection

@section('content')
    <section class="panel-card">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
            <div>
                <div class="fw-bold">Historique des achats</div>
                <div class="small text-secondary">Suivi des achats de cartes privilèges, paiements sécurisés et cartes émises.</div>
            </div>
            <span class="badge-soft">{{ $purchases->total() }} achat{{ $purchases->total() > 1 ? 's' : '' }}</span>
        </div>

        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input class="form-control" name="purchase_search" value="{{ request('purchase_search') }}" placeholder="Usager, téléphone, numéro de paiement, carte">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Carte</label>
                    <select class="form-select" name="purchase_type_id">
                        <option value="">Toutes</option>
                        @foreach ($cardTypes as $cardType)
                            <option value="{{ $cardType->id }}" @selected((string) request('purchase_type_id') === (string) $cardType->id)>{{ $cardType->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-secondary">État du paiement</label>
                    <select class="form-select" name="purchase_status">
                        <option value="">Tous</option>
                        <option value="pending" @selected(request('purchase_status') === 'pending')>En attente</option>
                        <option value="paid" @selected(request('purchase_status') === 'paid')>Payé</option>
                        <option value="failed" @selected(request('purchase_status') === 'failed')>Échoué</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-dark w-100">OK</button>
                    <a href="{{ route('super-admin.privilege-card-types.purchases') }}" class="btn btn-outline-secondary">Réinitialiser</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Usager</th>
                        <th>Carte</th>
                        <th>Paiement</th>
                        <th>Carte émise</th>
                        <th>Dates</th>
                        <th>État</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($purchases as $purchase)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ trim(($purchase->publicUser?->first_name ?? '').' '.($purchase->publicUser?->last_name ?? '')) ?: '-' }}</div>
                                <div class="small text-secondary">{{ $purchase->publicUser?->phone ?: '-' }}</div>
                                <div class="small text-secondary">{{ $purchase->publicUser?->email ?: '-' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $purchase->type?->name ?: '-' }}</div>
                            </td>
                            <td>
                                <div>{{ number_format((float) $purchase->amount, 0, ',', ' ') }} {{ $purchase->currency }}</div>
                                <div class="small text-secondary">{{ $purchase->sync_ref }}</div>
                                <div class="small text-secondary">{{ $purchase->provider_reference ?: '-' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $purchase->card?->card_number ?: '-' }}</div>
                                <div class="small text-secondary">Code à scanner: <span class="font-monospace">{{ $purchase->card?->card_uuid ?: '-' }}</span></div>
                                <div class="small text-secondary">État de la carte: {{ $purchase->card ? ($cardStatusLabels[$purchase->card->status] ?? $purchase->card->status) : '-' }}</div>
                            </td>
                            <td>
                                <div class="small">Initié: {{ $purchase->initiated_at?->format('d/m/Y H:i') ?: '-' }}</div>
                                <div class="small">Payé: {{ $purchase->paid_at?->format('d/m/Y H:i') ?: ($purchase->status === 'paid' ? 'Date inconnue' : 'En attente') }}</div>
                                <div class="small">Activé: {{ $purchase->card?->activated_at?->format('d/m/Y H:i') ?: 'Carte non émise' }}</div>
                                <div class="small">Expire: {{ $purchase->card?->expires_at?->format('d/m/Y H:i') ?: 'Carte non émise' }}</div>
                            </td>
                            <td>
                                <span class="status-chip">{{ $paymentStatusLabels[$purchase->status] ?? $purchase->status }}</span>
                                @if ($purchase->card)
                                    <span class="status-chip">{{ $cardStatusLabels[$purchase->card->status] ?? $purchase->card->status }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-secondary">Aucun achat de carte privilège enregistré.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $purchases->currentPage() }} sur {{ $purchases->lastPage() }}</div>
            {{ $purchases->links() }}
        </div>
    </section>
@endsection
