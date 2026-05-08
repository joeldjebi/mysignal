@extends('super-admin.layouts.app')

@section('title', config('app.name').' | '.$case->reference)
@section('page-title', 'Dossier '.$case->reference)
@section('page-description', 'Constat huissier, attribution AODA et procedure judiciaire.')

@section('header-badges')
    <span class="badge-soft">{{ $case->status }}</span>
    @if ($case->bailiff_completed_at)
        <span class="badge-soft">Constat termine</span>
    @endif
    @if ($case->lawyer_assigned_at)
        <span class="badge-soft">Avocat attribue</span>
    @endif
@endsection

@push('styles')
    <style>
        .case-detail-grid { display: grid; grid-template-columns: minmax(260px, 340px) minmax(0, 1fr); gap: 1rem; align-items: start; }
        .case-summary-sticky { position: sticky; top: 1.1rem; }
        .case-kv { display: grid; gap: .85rem; }
        .case-kv-item { padding-bottom: .85rem; border-bottom: 1px solid rgba(15,41,64,.08); }
        .case-kv-item:last-child { border-bottom: 0; padding-bottom: 0; }
        .case-section-head { display: flex; align-items: center; justify-content: space-between; gap: .75rem; margin-bottom: .9rem; }
        .case-scroll-list { max-height: 620px; overflow: auto; padding-right: .25rem; }
        .case-feed-item { border: 1px solid rgba(15,41,64,.1); border-radius: 12px; padding: .9rem; background: rgba(255,255,255,.72); }
        .case-feed-item + .case-feed-item { margin-top: .75rem; }
        .case-actions-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
        .case-text-clamp { display: -webkit-box; -webkit-line-clamp: 4; -webkit-box-orient: vertical; overflow: hidden; }
        @media (max-width: 1199.98px) {
            .case-detail-grid { grid-template-columns: 1fr; }
            .case-summary-sticky { position: static; }
            .case-actions-grid { grid-template-columns: 1fr; }
            .case-scroll-list { max-height: none; overflow: visible; padding-right: 0; }
        }
    </style>
@endpush

@section('content')
    <div class="case-detail-grid">
        <aside class="case-summary-sticky">
            <section class="panel-card">
                <div class="small text-secondary fw-semibold mb-2">Dossier</div>
                <div class="h5 fw-bold mb-1">{{ $case->reference }}</div>
                <div class="small text-secondary mb-3">{{ $case->organization?->name ?: 'Organisation non definie' }}</div>

                <div class="d-flex flex-wrap gap-2 mb-4">
                    <span class="status-chip">{{ $case->status }}</span>
                    <span class="status-chip">{{ $case->case_type }}</span>
                    <span class="status-chip">Priorite {{ $case->priority }}</span>
                </div>

                <div class="case-kv">
                    <div class="case-kv-item">
                        <div class="small text-secondary">Victime</div>
                        <div class="fw-semibold">{{ trim(($case->publicUser?->first_name ?? '').' '.($case->publicUser?->last_name ?? '')) ?: '-' }}</div>
                        <div class="small text-secondary">{{ $case->publicUser?->phone ?: '-' }}</div>
                    </div>
                    <div class="case-kv-item">
                        <div class="small text-secondary">Signalement source</div>
                        <div class="fw-semibold">{{ $case->incidentReport?->reference ?: '-' }}</div>
                        <div class="small text-secondary">{{ $case->incidentReport?->signal_label ?: $case->incidentReport?->signal_code ?: '-' }}</div>
                    </div>
                    <div class="case-kv-item">
                        <div class="small text-secondary">Dommage</div>
                        <div class="fw-semibold case-text-clamp">{{ $case->damage_summary ?: 'Non renseigne' }}</div>
                        <div class="small text-secondary">{{ $case->damage_amount_claimed !== null ? number_format((float) $case->damage_amount_claimed, 0, ',', ' ').' FCFA' : 'Montant non renseigne' }}</div>
                    </div>
                    <div class="case-kv-item">
                        <div class="small text-secondary">Huissier</div>
                        <div class="fw-semibold">{{ $case->bailiff?->name ?: 'Non attribue' }}</div>
                        <div class="small text-secondary">{{ $case->bailiff_completed_at ? 'Termine le '.$case->bailiff_completed_at?->format('d/m/Y H:i') : 'Procedure en cours' }}</div>
                    </div>
                    <div class="case-kv-item">
                        <div class="small text-secondary">Avocat</div>
                        <div class="fw-semibold">{{ $case->lawyer?->name ?: 'Non attribue' }}</div>
                        <div class="small text-secondary">{{ $case->lawyer_assigned_at ? 'Attribue le '.$case->lawyer_assigned_at?->format('d/m/Y H:i') : 'En attente AODA' }}</div>
                    </div>
                </div>
            </section>
        </aside>

        <main>
            <div class="case-actions-grid mb-4">
                @if ($portal === 'huissier' && ! $case->bailiff_completed_at)
                <section class="panel-card mb-4">
                    <div class="fw-bold mb-3">Ajouter une etape de constat</div>
                    <form method="POST" action="{{ route('backoffice.legal-cases.bailiff-steps.store', $case) }}" enctype="multipart/form-data" class="row g-3">
                        @csrf
                        <div class="col-md-6">
                            <label class="form-label">Type d etape</label>
                            <select class="form-select" name="step_type" required>
                                @foreach ($bailiffStepTypes as $type => $label)
                                    <option value="{{ $type }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Titre</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes / rapport</label>
                            <textarea class="form-control" name="summary" rows="4" required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Photos, videos ou documents</label>
                            <input type="file" class="form-control" name="attachments[]" multiple accept="image/*,video/*,application/pdf">
                        </div>
                        <div class="col-12">
                            <button class="btn btn-dark">Enregistrer l etape</button>
                        </div>
                    </form>
                </section>

                <section class="panel-card mb-4">
                    <div class="fw-bold mb-3">Terminer la procedure huissier</div>
                    <form method="POST" action="{{ route('backoffice.legal-cases.complete-bailiff', $case) }}">
                        @csrf
                        @method('PATCH')
                        <label class="form-label">Synthese finale du constat</label>
                        <textarea class="form-control mb-3" name="summary" rows="4" required></textarea>
                        <button class="btn btn-dark">Marquer termine</button>
                    </form>
                </section>
                @endif

                @if ($portal === 'aoda')
                <section class="panel-card mb-4">
                    <div class="fw-bold mb-3">Attribution avocat</div>
                    <form method="POST" action="{{ route('backoffice.legal-cases.assign-lawyer', $case) }}" class="row g-3">
                        @csrf
                        @method('PATCH')
                        <div class="col-md-6">
                            <label class="form-label">Avocat</label>
                            <select class="form-select" name="lawyer_user_id" required>
                                <option value="">Selectionner un avocat</option>
                                @foreach ($lawyers as $lawyer)
                                    <option value="{{ $lawyer->id }}" @selected((int) $case->lawyer_user_id === (int) $lawyer->id)>{{ $lawyer->name }}{{ $lawyer->email ? ' - '.$lawyer->email : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Note d attribution</label>
                            <input type="text" class="form-control" name="summary" placeholder="Instruction transmise a l avocat">
                        </div>
                        <div class="col-12">
                            <button class="btn btn-dark">Attribuer a l avocat</button>
                        </div>
                    </form>
                </section>
                @endif

                @if ($portal === 'avocat')
                <section class="panel-card mb-4">
                    <div class="fw-bold mb-3">Ajouter une etape judiciaire</div>
                    <form method="POST" action="{{ route('backoffice.legal-cases.lawyer-steps.store', $case) }}" enctype="multipart/form-data" class="row g-3">
                        @csrf
                        <div class="col-md-6">
                            <label class="form-label">Type d etape</label>
                            <select class="form-select" name="step_type" required>
                                @foreach ($lawyerStepTypes as $type => $label)
                                    <option value="{{ $type }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Titre</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes / acte judiciaire</label>
                            <textarea class="form-control" name="summary" rows="4" required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Pieces jointes</label>
                            <input type="file" class="form-control" name="attachments[]" multiple accept="image/*,video/*,application/pdf">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" id="markCompleted" name="mark_completed">
                                <label class="form-check-label" for="markCompleted">Cette etape cloture la procedure judiciaire</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <button class="btn btn-dark">Enregistrer l etape</button>
                        </div>
                    </form>
                </section>
                @endif
            </div>

            <div class="row g-4">
                <div class="col-xl-7">
                    <section class="panel-card h-100">
                        <div class="case-section-head">
                            <div>
                                <div class="fw-bold">Etapes du dossier</div>
                                <div class="small text-secondary">{{ $case->steps->count() }} etape{{ $case->steps->count() > 1 ? 's' : '' }}</div>
                            </div>
                        </div>
                        <div class="case-scroll-list">
                    @forelse ($case->steps as $step)
                        <div class="case-feed-item">
                            <div class="d-flex justify-content-between gap-3 flex-wrap">
                                <div>
                                    <div class="fw-bold">{{ $step->title }}</div>
                                    <div class="small text-secondary">{{ $step->step_type }} - {{ $step->createdBy?->name ?: 'Systeme' }}</div>
                                </div>
                                <div class="small text-secondary">{{ $step->completed_at?->format('d/m/Y H:i') ?: $step->created_at?->format('d/m/Y H:i') }}</div>
                            </div>
                            <div class="mt-3">{{ $step->summary ?: 'Aucun resume.' }}</div>
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
                    @empty
                        <div class="text-secondary">Aucune etape enregistree.</div>
                    @endforelse
                        </div>
                    </section>
                </div>

                <div class="col-xl-5">
                    <section class="panel-card h-100">
                        <div class="case-section-head">
                            <div>
                                <div class="fw-bold">Historique victime</div>
                                <div class="small text-secondary">{{ $case->histories->count() }} entree{{ $case->histories->count() > 1 ? 's' : '' }}</div>
                            </div>
                        </div>
                        <div class="case-scroll-list">
                    @forelse ($case->histories as $history)
                        <div class="case-feed-item">
                            <div class="fw-bold">{{ $history->title }}</div>
                            <div class="small text-secondary mb-2">{{ $history->createdBy?->name ?: 'Systeme' }} - {{ $history->created_at?->format('d/m/Y H:i') }}</div>
                            <div>{{ $history->description ?: '-' }}</div>
                        </div>
                    @empty
                        <div class="text-secondary">Aucun historique.</div>
                    @endforelse
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </div>
@endsection
