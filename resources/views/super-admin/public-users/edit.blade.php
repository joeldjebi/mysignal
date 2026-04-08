@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Modifier un usager public')
@section('page-title', 'Modifier un usager public')
@section('page-description', 'Mettre a jour un compte public particulier ou entreprise.')

@section('content')
    @php
        $reports = $publicUser->incidentReports->sortByDesc('id')->values();
    @endphp
    <section class="panel-card">
        <div class="fw-bold mb-1">Edition de {{ $publicUser->first_name }} {{ $publicUser->last_name }}</div>
        <div class="small text-secondary mb-3">Le formulaire s ajuste selon le type d usager public et sa tarification associee.</div>
        <form method="POST" action="{{ route('super-admin.public-users.update', $publicUser) }}" class="row g-3">
            @csrf
            @method('PUT')
            @include('super-admin.public-users.partials.form-fields', ['publicUser' => $publicUser, 'publicUserTypes' => $publicUserTypes, 'mode' => 'edit'])
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-dark">Enregistrer</button>
                <a href="{{ route('super-admin.public-users.index') }}" class="btn btn-outline-secondary">Retour</a>
            </div>
        </form>
    </section>

    <section class="panel-card mt-4">
        <div class="fw-bold mb-1">Signalements et pieces jointes</div>
        <div class="small text-secondary mb-3">Consultez les signalements declarés par cet usager ainsi que les images et justificatifs envoyés.</div>

        @if ($reports->isEmpty())
            <div class="text-secondary">Aucun signalement n a encore ete enregistre pour cet usager.</div>
        @else
            <div class="vstack gap-3">
                @foreach ($reports as $report)
                    @php
                        $signalPayload = $report->resolvedSignalPayload();
                        $damageAttachment = $report->resolvedDamageAttachment();
                    @endphp
                    <div class="border rounded-4 p-3">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                            <div>
                                <div class="fw-bold">{{ $report->reference }}</div>
                                <div class="small text-secondary">{{ $report->signal_label ?: $report->signal_code }}</div>
                                <div class="small text-secondary">
                                    {{ $report->organization?->name ?: 'Organisation non definie' }}
                                    · {{ $report->application?->name ?: 'Application non definie' }}
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="status-chip">{{ $report->status }}</span>
                                <div class="small text-secondary mt-1">{{ $report->created_at?->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="small text-secondary fw-semibold mb-1">Description</div>
                            <div>{{ $report->description ?: 'Aucune description fournie.' }}</div>
                        </div>

                        @if (!empty($signalPayload))
                            <div class="mb-3">
                                <div class="small text-secondary fw-semibold mb-2">Pieces jointes du signalement</div>
                                <div class="row g-3">
                                    @foreach ($signalPayload as $key => $value)
                                        @if (is_array($value) && filled($value['temporary_url'] ?? null))
                                            <div class="col-md-6 col-xl-4">
                                                <div class="border rounded-4 p-3 h-100">
                                                    <div class="fw-semibold small mb-2">{{ $value['name'] ?? $key }}</div>
                                                    <div class="small text-secondary mb-2">{{ $key }}</div>
                                                    @if (str_starts_with((string) ($value['mime_type'] ?? ''), 'image/'))
                                                        <img src="{{ $value['temporary_url'] }}" alt="{{ $key }}" class="img-fluid rounded-4 border" style="max-height: 220px; object-fit: cover;">
                                                    @else
                                                        <a href="{{ $value['temporary_url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-dark btn-sm">Ouvrir le fichier</a>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if ($report->damage_summary || !empty($damageAttachment))
                            <div class="border-top pt-3">
                                <div class="small text-secondary fw-semibold mb-2">Dommage declare</div>
                                <div class="mb-2"><strong>Resume :</strong> {{ $report->damage_summary ?: 'Non renseigne' }}</div>
                                <div class="mb-2"><strong>Montant estime :</strong> {{ $report->damage_amount_estimated !== null ? number_format((float) $report->damage_amount_estimated, 0, ',', ' ').' FCFA' : 'Non renseigne' }}</div>
                                <div class="mb-3"><strong>Commentaires :</strong> {{ $report->damage_notes ?: 'Aucun detail complementaire fourni.' }}</div>

                                @if (!empty($damageAttachment) && filled($damageAttachment['temporary_url'] ?? null))
                                    <div class="border rounded-4 p-3">
                                        <div class="fw-semibold small mb-2">{{ $damageAttachment['name'] ?? 'Justificatif dommage' }}</div>
                                        <div class="small text-secondary mb-2">{{ $damageAttachment['mime_type'] ?? 'Fichier' }}</div>
                                        @if (str_starts_with((string) ($damageAttachment['mime_type'] ?? ''), 'image/'))
                                            <img src="{{ $damageAttachment['temporary_url'] }}" alt="Justificatif dommage" class="img-fluid rounded-4 border" style="max-height: 420px; width: 100%; object-fit: contain; background: #f7f9fc;">
                                        @else
                                            <a href="{{ $damageAttachment['temporary_url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-dark btn-sm">Ouvrir le justificatif</a>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </section>
@endsection

@section('scripts')
    @include('super-admin.public-users.partials.form-script', ['mode' => 'edit'])
@endsection
