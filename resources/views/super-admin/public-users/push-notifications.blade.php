@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Notifications UP')
@section('page-title', 'Notifications UP')
@section('page-description', 'Envoyer des notifications aux usagers publics ayant un token actif et suivre l historique des envois.')

@section('header-badges')
    <span class="badge-soft">{{ $eligibleUsersCount }} UP eligibles</span>
    <a href="{{ route('super-admin.public-users.index') }}" class="btn btn-outline-secondary">Retour aux UP</a>
@endsection

@section('content')
    <section class="panel-card">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
            <div>
                <div class="fw-bold">Nouvel envoi</div>
                <div class="small text-secondary">Filtrez les UP, selectionnez une ou plusieurs personnes, ou envoyez a toute l audience avec token actif.</div>
            </div>
            <span class="badge-soft">Firebase Push</span>
        </div>

        <form method="POST" action="{{ route('super-admin.public-users.push-notifications.store') }}" id="saPushNotificationForm">
            @csrf
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Destinataires</label>
                    <select name="target_scope" class="form-select" id="pushTargetScope">
                        <option value="selected" @selected(old('target_scope') !== 'all')>Selection manuelle</option>
                        <option value="all" @selected(old('target_scope') === 'all')>Tous les UP avec token actif</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-secondary">Titre</label>
                    <input type="text" name="title" value="{{ old('title') }}" class="form-control" maxlength="120" placeholder="Titre de la notification" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label small text-secondary">Message</label>
                    <input type="text" name="body" value="{{ old('body') }}" class="form-control" maxlength="500" placeholder="Message envoye aux UP" required>
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-md-8">
                    @php
                        $oldSelectedPushUsers = array_map('intval', old('public_user_ids', []));
                    @endphp
                    <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-2">
                        <div class="flex-grow-1">
                            <label class="form-label small text-secondary">Filtrer les UP</label>
                            <input type="text" class="form-control" id="pushUserFilter" placeholder="Nom, telephone, email...">
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-dark" type="button" id="selectVisiblePushUsers">Selectionner visibles</button>
                            <button class="btn btn-outline-secondary" type="button" id="clearPushUsers">Vider</button>
                        </div>
                    </div>
                    <label class="form-label small text-secondary">UP avec token actif</label>
                    <select name="public_user_ids[]" class="form-select" id="pushUserSelect" multiple size="10">
                        @foreach ($eligibleUsers as $publicUser)
                            @php
                                $platforms = $publicUser->activeDeviceTokens
                                    ->pluck('platform')
                                    ->filter()
                                    ->map(fn ($platform) => strtoupper($platform))
                                    ->unique()
                                    ->values()
                                    ->join(', ');
                                $label = trim($publicUser->first_name.' '.$publicUser->last_name).' | '.$publicUser->phone.($publicUser->email ? ' | '.$publicUser->email : '').($platforms ? ' | '.$platforms : '');
                            @endphp
                            <option value="{{ $publicUser->id }}" data-search="{{ strtolower($label) }}" @selected(in_array($publicUser->id, $oldSelectedPushUsers, true))>{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="small text-secondary mt-1">Maintenez Ctrl ou Cmd pour choisir plusieurs UP. Le bouton selectionne uniquement les resultats visibles apres filtre.</div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded-4 p-3 h-100">
                        <div class="small text-secondary">Audience disponible</div>
                        <div class="h4 fw-bold mb-1">{{ $eligibleUsersCount }}</div>
                        <div class="small text-secondary">UP avec token actif. Le mode global enverra uniquement a cette audience.</div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-3">
                <button class="btn btn-dark px-4" type="submit">Envoyer la notification</button>
            </div>
        </form>
    </section>

    <section class="panel-card mt-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <div>
                <div class="fw-bold">Historique des envois</div>
                <div class="small text-secondary">Suivi des campagnes envoyees depuis le super admin.</div>
            </div>
            <span class="badge-soft">{{ $history->total() }} envoi{{ $history->total() > 1 ? 's' : '' }}</span>
        </div>

        <div class="table-responsive">
            <table class="table table-modern align-middle">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Notification</th>
                        <th>Cible</th>
                        <th>Resultat</th>
                        <th>Statut</th>
                        <th>Envoye par</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($history as $item)
                        @php
                            $statusLabels = [
                                'pending' => 'En cours',
                                'sent' => 'Envoye',
                                'partial' => 'Partiel',
                                'failed' => 'Echoue',
                            ];
                            $targetLabels = [
                                'selected' => 'Selection',
                                'all' => 'Tous les UP eligibles',
                            ];
                        @endphp
                        <tr>
                            <td>{{ $item->sent_at?->format('d/m/Y H:i') ?: $item->created_at?->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="fw-semibold">{{ $item->title }}</div>
                                <div class="small text-secondary">{{ $item->body }}</div>
                            </td>
                            <td>{{ $targetLabels[$item->target_scope] ?? $item->target_scope }}</td>
                            <td>
                                <div class="fw-semibold">{{ $item->sent_count }} / {{ $item->requested_count }} envoye{{ $item->sent_count > 1 ? 's' : '' }}</div>
                                <div class="small text-secondary">{{ $item->failed_count }} echec{{ $item->failed_count > 1 ? 's' : '' }}</div>
                                @if ($item->failed_count > 0 && filled($item->failure_details))
                                    @php
                                        $firstFailure = collect($item->failure_details)->first();
                                        $firstError = collect($firstFailure['errors'] ?? [])->first();
                                    @endphp
                                    <div class="small text-danger">{{ $firstError['firebase_status'] ?? 'Erreur' }}{{ filled($firstError['message'] ?? null) ? ': '.$firstError['message'] : '' }}</div>
                                @endif
                            </td>
                            <td><span class="status-chip">{{ $statusLabels[$item->status] ?? $item->status }}</span></td>
                            <td>{{ $item->sender?->name ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-secondary">Aucune notification envoyee pour le moment.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="table-meta">Page {{ $history->currentPage() }} sur {{ $history->lastPage() }}</div>
            {{ $history->links() }}
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        (() => {
            const filterInput = document.getElementById('pushUserFilter');
            const userSelect = document.getElementById('pushUserSelect');
            const selectVisibleButton = document.getElementById('selectVisiblePushUsers');
            const clearButton = document.getElementById('clearPushUsers');

            if (!filterInput || !userSelect) {
                return;
            }

            const options = Array.from(userSelect.options);

            filterInput.addEventListener('input', () => {
                const value = filterInput.value.trim().toLowerCase();

                options.forEach((option) => {
                    option.hidden = value && !String(option.dataset.search || '').includes(value);
                });
            });

            selectVisibleButton?.addEventListener('click', () => {
                options.forEach((option) => {
                    if (!option.hidden) {
                        option.selected = true;
                    }
                });
            });

            clearButton?.addEventListener('click', () => {
                options.forEach((option) => {
                    option.selected = false;
                });
            });
        })();
    </script>
@endpush
