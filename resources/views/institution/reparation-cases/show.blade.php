@extends('institution.layouts.app')

@section('title', config('app.name').' | Detail dossier')
@section('page-title', 'Detail du dossier')
@section('page-description', 'Historique et avancement du dossier rattache a votre organisation.')

@section('content')
    @php
        $statusLabel = fn (?string $status) => [
            'submitted' => 'Soumis',
            'under_review' => 'Analyse',
            'awaiting_documents' => 'Pieces attendues',
            'sent_to_organization' => 'Transmis organisation',
            'organization_responded' => 'Reponse organisation',
            'approved' => 'Approuve',
            'rejected' => 'Rejete',
            'compensated' => 'Dedommage',
            'closed' => 'Clos',
        ][$status] ?? ($status ?: '-');
        $stepStatusLabel = fn (?string $status) => [
            'pending' => 'En attente',
            'in_progress' => 'En cours',
            'completed' => 'Terminee',
            'cancelled' => 'Annulee',
        ][$status] ?? ($status ?: '-');
    @endphp

    <div class="row g-4">
        <div class="col-xl-4">
            <section class="panel-card h-100">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <div class="fw-bold fs-5">{{ $reparationCase->reference }}</div>
                        <div class="text-secondary small">{{ $reparationCase->incidentReport?->reference }}</div>
                    </div>
                    <span class="status-chip">{{ $statusLabel($reparationCase->status) }}</span>
                </div>
                <div class="vstack gap-3">
                    <div>
                        <div class="small text-secondary">Application / Organisation</div>
                        <div class="fw-semibold">{{ $reparationCase->application?->name ?: '-' }} / {{ $reparationCase->organization?->name ?: '-' }}</div>
                    </div>
                    <div>
                        <div class="small text-secondary">Type</div>
                        <div class="fw-semibold">{{ $reparationCase->case_type }}</div>
                    </div>
                    <div>
                        <div class="small text-secondary">Priorite</div>
                        <div class="fw-semibold">{{ $reparationCase->priority }}</div>
                    </div>
                    <div>
                        <div class="small text-secondary">Ouvert le</div>
                        <div class="fw-semibold">{{ $reparationCase->opened_at?->format('d/m/Y H:i') ?: '-' }}</div>
                    </div>
                    <div>
                        <div class="small text-secondary">Montant reclame / valide</div>
                        <div class="fw-semibold">
                            {{ $reparationCase->damage_amount_claimed ? number_format((float) $reparationCase->damage_amount_claimed, 0, ',', ' ').' FCFA' : '-' }}
                            /
                            {{ $reparationCase->damage_amount_validated ? number_format((float) $reparationCase->damage_amount_validated, 0, ',', ' ').' FCFA' : '-' }}
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('institution.reports.show', $reparationCase->incidentReport) }}" class="btn btn-outline-dark w-100">Voir le signalement</a>
                    </div>
                </div>
            </section>
        </div>

        <div class="col-xl-8">
            <section class="panel-card mb-4">
                <div class="fw-bold mb-3">Intervenants</div>
                <div class="row g-3">
                    <div class="col-md-4"><div class="surface-soft"><div class="meta-subtitle">Responsable</div><div class="meta-title">{{ $reparationCase->assignedTo?->name ?: '-' }}</div></div></div>
                    <div class="col-md-4"><div class="surface-soft"><div class="meta-subtitle">Huissier</div><div class="meta-title">{{ $reparationCase->bailiff?->name ?: '-' }}</div></div></div>
                    <div class="col-md-4"><div class="surface-soft"><div class="meta-subtitle">Avocat</div><div class="meta-title">{{ $reparationCase->lawyer?->name ?: '-' }}</div></div></div>
                </div>
            </section>

            <section class="panel-card mb-4">
                <div class="fw-bold mb-3">Historique</div>
                <div class="vstack gap-3">
                    @forelse ($reparationCase->histories as $history)
                        <div class="surface-soft">
                            <div class="d-flex justify-content-between gap-3">
                                <div>
                                    <div class="meta-title">{{ $history->title }}</div>
                                    <div class="meta-subtitle">{{ $history->description ?: '-' }}</div>
                                </div>
                                <div class="meta-subtitle text-end">{{ $history->created_at?->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-secondary">Aucun historique disponible.</div>
                    @endforelse
                </div>
            </section>

            <section class="panel-card">
                <div class="fw-bold mb-3">Etapes d'avancement</div>
                <div class="vstack gap-3">
                    @forelse ($reparationCase->steps as $step)
                        <div class="surface-soft">
                            <div class="d-flex justify-content-between gap-3">
                                <div>
                                    <div class="meta-title">{{ $step->title }}</div>
                                    <div class="meta-subtitle">{{ $step->summary ?: '-' }}</div>
                                    <div class="meta-subtitle mt-1">Responsable: {{ $step->assignedTo?->name ?: '-' }}</div>
                                    @if (! empty($step->meta['attachments'] ?? []))
                                        <div class="d-flex flex-wrap gap-2 mt-3">
                                            @foreach ($step->meta['attachments'] as $attachment)
                                                @php
                                                    $attachmentUrl = app(\App\Services\WasabiService::class)->temporaryUrl($attachment['path'] ?? null);
                                                @endphp
                                                @if ($attachmentUrl)
                                                    <a href="{{ $attachmentUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-dark">
                                                        {{ $attachment['name'] ?? 'Piece jointe' }}
                                                    </a>
                                                @else
                                                    <span class="badge text-bg-light border">{{ $attachment['name'] ?? 'Piece jointe' }}</span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <div class="text-end">
                                    <span class="status-chip">{{ $stepStatusLabel($step->status) }}</span>
                                    <div class="meta-subtitle mt-2">{{ $step->created_at?->format('d/m/Y H:i') }}</div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-secondary">Aucune etape disponible.</div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
@endsection
