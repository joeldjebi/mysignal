@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Notifications UP')
@section('page-title', 'Envoyer une notification aux UP')
@section('page-description', 'Envoyer des notifications aux usagers publics ayant un appareil actif et suivre l’historique des envois.')

@section('header-badges')
    <span class="badge-soft">{{ $eligibleUsersCount }} UP éligibles</span>
    <a href="{{ route('super-admin.public-users.index') }}" class="btn btn-outline-secondary">Retour aux UP</a>
@endsection

@push('styles')
    <style>
        .select-search-input {
            border-radius: 12px;
            font-size: .86rem;
            margin-bottom: .45rem;
        }
        .smart-multiselect {
            position: relative;
        }
        .smart-multiselect-toggle {
            min-height: 46px;
            border-radius: 14px;
            border: 1px solid #d8e1ec;
            background: #fff;
            color: #1f2933;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .75rem;
            width: 100%;
            padding: .62rem .85rem;
            text-align: left;
            box-shadow: 0 10px 24px rgba(16, 42, 67, .06);
        }
        .smart-multiselect-toggle .summary {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .smart-multiselect-menu {
            display: none;
            position: absolute;
            z-index: 30;
            inset: calc(100% + .45rem) 0 auto 0;
            border: 1px solid rgba(103, 145, 255, .22);
            border-radius: 18px;
            background: #fff;
            box-shadow: 0 24px 70px rgba(24, 52, 71, .18);
            padding: .85rem;
        }
        .smart-multiselect.open .smart-multiselect-menu {
            display: block;
        }
        .smart-options {
            max-height: 310px;
            overflow: auto;
            display: grid;
            gap: .35rem;
            padding-right: .2rem;
        }
        .smart-option {
            display: flex;
            gap: .65rem;
            align-items: flex-start;
            padding: .62rem .7rem;
            border: 1px solid #edf2f7;
            border-radius: 14px;
            cursor: pointer;
            transition: .15s ease;
        }
        .smart-option:hover {
            border-color: rgba(103, 145, 255, .45);
            background: #f7faff;
        }
        .smart-option input {
            margin-top: .2rem;
        }
        .smart-option-title {
            font-weight: 600;
            color: #1f2933;
        }
        .smart-option-meta {
            font-size: .78rem;
            color: #6b7c93;
        }
    </style>
@endpush

@section('content')
    <section class="panel-card">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
            <div>
                <div class="fw-bold">Nouvel envoi</div>
                <div class="small text-secondary">Filtrez les UP, sélectionnez une ou plusieurs personnes, ou envoyez à toute l’audience avec appareil actif.</div>
            </div>
            <span class="badge-soft">Firebase Push</span>
        </div>

        <form method="POST" action="{{ route('super-admin.public-users.push-notifications.store') }}" id="saPushNotificationForm">
            @csrf
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Destinataires</label>
                    <input type="search" class="form-control select-search-input" data-select-filter-target="pushTargetScope" placeholder="Filtrer...">
                    <select name="target_scope" class="form-select" id="pushTargetScope">
                        <option value="selected" @selected(old('target_scope') === 'selected' || ! old('target_scope'))>Sélection manuelle</option>
                        <option value="filtered" @selected(old('target_scope') === 'filtered')>Audience filtrée</option>
                        <option value="all" @selected(old('target_scope') === 'all')>Tous les UP avec appareil actif</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-secondary">Titre</label>
                    <input type="text" name="title" value="{{ old('title') }}" class="form-control" maxlength="120" placeholder="Titre de la notification" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label small text-secondary">Message</label>
                    <input type="text" name="body" value="{{ old('body') }}" class="form-control" maxlength="500" placeholder="Message envoyé aux UP" required>
                </div>
            </div>

            <div class="row g-3 mt-1" id="filteredAudienceFields">
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Ville</label>
                    <input type="search" class="form-control select-search-input" data-select-filter-target="pushCityFilter" placeholder="Filtrer les villes...">
                    <select name="city_id" class="form-select" id="pushCityFilter">
                        <option value="">Toutes les villes</option>
                        @foreach ($cities as $city)
                            <option value="{{ $city->id }}" @selected((string) old('city_id') === (string) $city->id)>{{ $city->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Commune</label>
                    <input type="search" class="form-control select-search-input" data-select-filter-target="pushCommuneFilter" placeholder="Filtrer les communes...">
                    <select name="commune_id" class="form-select" id="pushCommuneFilter">
                        <option value="">Toutes les communes</option>
                        @foreach ($communes as $commune)
                            <option value="{{ $commune->id }}" data-city-id="{{ $commune->city_id }}" @selected((string) old('commune_id') === (string) $commune->id)>{{ $commune->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Quartier</label>
                    <input type="search" class="form-control select-search-input" data-select-filter-target="pushNeighborhoodFilter" placeholder="Filtrer les quartiers...">
                    <select name="neighborhood_id" class="form-select" id="pushNeighborhoodFilter">
                        <option value="">Tous les quartiers</option>
                        @foreach ($neighborhoods as $neighborhood)
                            <option value="{{ $neighborhood->id }}" data-commune-id="{{ $neighborhood->commune_id }}" @selected((string) old('neighborhood_id') === (string) $neighborhood->id)>{{ $neighborhood->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Type de signalement</label>
                    <input type="search" class="form-control select-search-input" data-select-filter-target="pushSignalTypeFilter" placeholder="Filtrer les types...">
                    <select name="signal_type_id" class="form-select" id="pushSignalTypeFilter">
                        <option value="">Tous les types</option>
                        @foreach ($signalTypes as $signalType)
                            <option value="{{ $signalType->id }}" @selected((string) old('signal_type_id') === (string) $signalType->id)>{{ $signalType->label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-secondary">État des signalements</label>
                    <input type="search" class="form-control select-search-input" data-select-filter-target="pushResolutionStatusFilter" placeholder="Filtrer les états...">
                    <select name="report_resolution_status" class="form-select" id="pushResolutionStatusFilter">
                        <option value="">Tous les états</option>
                        <option value="resolved" @selected(old('report_resolution_status') === 'resolved')>Résolu</option>
                        <option value="unresolved" @selected(old('report_resolution_status') === 'unresolved')>Non résolu</option>
                    </select>
                </div>
                <div class="col-md-9 d-flex align-items-end">
                    <div class="small text-secondary border rounded-4 p-3 w-100">
                        Les filtres s’appliquent uniquement aux UP ayant un appareil actif. Les filtres “type” et “état” ciblent les UP ayant au moins un signalement correspondant.
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-1" id="manualAudienceFields">
                <div class="col-md-8">
                    @php
                        $oldSelectedPushUsers = array_map('intval', old('public_user_ids', []));
                    @endphp
                    <label class="form-label small text-secondary">UP avec appareil actif</label>
                    <div class="smart-multiselect" id="pushUserPicker">
                        <button class="smart-multiselect-toggle" type="button" id="pushUserPickerButton">
                            <span class="summary" id="pushUserPickerSummary">Aucun UP sélectionné</span>
                            <span class="badge-soft" id="pushUserPickerCount">0</span>
                        </button>
                        <div class="smart-multiselect-menu" id="pushUserPickerMenu">
                            <input type="search" class="form-control select-search-input" id="pushUserFilter" placeholder="Filtrer par nom, téléphone, email ou appareil...">
                            <div class="d-flex gap-2 flex-wrap mb-2">
                                <button class="btn btn-outline-dark btn-sm" type="button" id="selectVisiblePushUsers">Sélectionner visibles</button>
                                <button class="btn btn-outline-secondary btn-sm" type="button" id="clearPushUsers">Vider</button>
                            </div>
                            <div class="smart-options" id="pushUserOptions">
                                @foreach ($eligibleUsers as $publicUser)
                                    @php
                                        $platforms = $publicUser->activeDeviceTokens
                                            ->pluck('platform')
                                            ->filter()
                                            ->map(fn ($platform) => strtoupper($platform))
                                            ->unique()
                                            ->values()
                                            ->join(', ');
                                        $fullName = trim($publicUser->first_name.' '.$publicUser->last_name) ?: 'UP sans nom';
                                        $label = $fullName.' | '.$publicUser->phone.($publicUser->email ? ' | '.$publicUser->email : '').($platforms ? ' | '.$platforms : '');
                                    @endphp
                                    <label class="smart-option" data-search="{{ strtolower($label) }}">
                                        <input type="checkbox" value="{{ $publicUser->id }}" data-push-user-checkbox @checked(in_array($publicUser->id, $oldSelectedPushUsers, true))>
                                        <span>
                                            <span class="smart-option-title">{{ $fullName }}</span>
                                            <span class="smart-option-meta d-block">{{ $publicUser->phone }}{{ $publicUser->email ? ' · '.$publicUser->email : '' }}{{ $platforms ? ' · '.$platforms : '' }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <select name="public_user_ids[]" class="d-none" id="pushUserSelect" multiple>
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
                    <div class="small text-secondary mt-2">Utilisez la recherche puis sélectionnez un ou plusieurs UP. Le bouton sélectionne uniquement les résultats visibles.</div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded-4 p-3 h-100">
                        <div class="small text-secondary">Audience disponible</div>
                        <div class="h4 fw-bold mb-1">{{ $eligibleUsersCount }}</div>
                        <div class="small text-secondary">UP avec appareil actif. Le mode global enverra uniquement à cette audience.</div>
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
                <div class="small text-secondary">Suivi des campagnes envoyées depuis le super admin.</div>
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
                        <th>Résultat</th>
                        <th>Statut</th>
                        <th>Envoyé par</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($history as $item)
                        @php
                            $statusLabels = [
                                'pending' => 'En cours',
                                'sent' => 'Envoyé',
                                'partial' => 'Partiel',
                                'failed' => 'Échoué',
                            ];
                            $targetLabels = [
                                'selected' => 'Sélection',
                                'filtered' => 'Audience filtrée',
                                'all' => 'Tous les UP éligibles',
                            ];
                        @endphp
                        <tr>
                            <td>{{ $item->sent_at?->format('d/m/Y H:i') ?: $item->created_at?->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="fw-semibold">{{ $item->title }}</div>
                                <div class="small text-secondary">{{ $item->body }}</div>
                            </td>
                            <td>
                                <div>{{ $targetLabels[$item->target_scope] ?? $item->target_scope }}</div>
                                @if (filled($item->target_filters))
                                    @php
                                        $filterLabels = collect($item->target_filters)->map(function ($value, $key) use ($cities, $communes, $neighborhoods, $signalTypes) {
                                            return match ($key) {
                                                'city_id' => 'Ville : '.($cities->firstWhere('id', (int) $value)?->name ?? 'sélectionnée'),
                                                'commune_id' => 'Commune : '.($communes->firstWhere('id', (int) $value)?->name ?? 'sélectionnée'),
                                                'neighborhood_id' => 'Quartier : '.($neighborhoods->firstWhere('id', (int) $value)?->name ?? 'sélectionné'),
                                                'signal_type_id' => 'Type : '.($signalTypes->firstWhere('id', (int) $value)?->label ?? 'sélectionné'),
                                                'report_resolution_status' => 'État : '.($value === 'resolved' ? 'résolu' : 'non résolu'),
                                                default => null,
                                            };
                                        })->filter()->values();
                                    @endphp
                                    <div class="small text-secondary">
                                        {{ $filterLabels->join(' · ') }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $item->sent_count }} / {{ $item->requested_count }} envoyé{{ $item->sent_count > 1 ? 's' : '' }}</div>
                                <div class="small text-secondary">{{ $item->failed_count }} échec{{ $item->failed_count > 1 ? 's' : '' }}</div>
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
                        <tr><td colspan="6" class="text-center text-secondary">Aucune notification envoyée pour le moment.</td></tr>
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
            const picker = document.getElementById('pushUserPicker');
            const pickerButton = document.getElementById('pushUserPickerButton');
            const pickerSummary = document.getElementById('pushUserPickerSummary');
            const pickerCount = document.getElementById('pushUserPickerCount');
            const userCheckboxes = Array.from(document.querySelectorAll('[data-push-user-checkbox]'));
            const scopeSelect = document.getElementById('pushTargetScope');
            const filteredFields = document.getElementById('filteredAudienceFields');
            const manualFields = document.getElementById('manualAudienceFields');
            const citySelect = document.getElementById('pushCityFilter');
            const communeSelect = document.getElementById('pushCommuneFilter');
            const neighborhoodSelect = document.getElementById('pushNeighborhoodFilter');

            if (!filterInput || !userSelect) {
                return;
            }

            const options = Array.from(userSelect.options);
            const communeOptions = communeSelect ? Array.from(communeSelect.options) : [];
            const neighborhoodOptions = neighborhoodSelect ? Array.from(neighborhoodSelect.options) : [];

            const normalize = (value) => String(value || '')
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '');

            const optionMatchesSearch = (option, search) => {
                if (option.value === '') {
                    return true;
                }

                return normalize(option.textContent).includes(search);
            };

            const applySelectFilter = (select, predicate = () => true) => {
                if (!select) {
                    return;
                }

                const input = document.querySelector(`[data-select-filter-target="${select.id}"]`);
                const search = normalize(input?.value || '');

                Array.from(select.options).forEach((option) => {
                    option.hidden = !predicate(option) || !optionMatchesSearch(option, search);
                });

                if (select.selectedOptions[0]?.hidden) {
                    select.value = '';
                }
            };

            const syncScope = () => {
                const scope = scopeSelect?.value || 'selected';
                if (manualFields) manualFields.style.display = scope === 'selected' ? '' : 'none';
                if (filteredFields) filteredFields.style.display = scope === 'filtered' ? '' : 'none';
            };

            const syncCommunes = () => {
                const cityId = citySelect?.value || '';
                applySelectFilter(communeSelect, (option) => option.value === '' || cityId === '' || option.dataset.cityId === cityId);
                syncNeighborhoods();
            };

            const syncNeighborhoods = () => {
                const communeId = communeSelect?.value || '';
                applySelectFilter(neighborhoodSelect, (option) => option.value === '' || communeId === '' || option.dataset.communeId === communeId);
            };

            const syncUserSelect = () => {
                const selectedValues = userCheckboxes
                    .filter((checkbox) => checkbox.checked)
                    .map((checkbox) => checkbox.value);

                options.forEach((option) => {
                    option.selected = selectedValues.includes(option.value);
                });

                const selectedLabels = options
                    .filter((option) => option.selected)
                    .map((option) => option.textContent.split('|')[0].trim());

                if (pickerCount) {
                    pickerCount.textContent = String(selectedValues.length);
                }

                if (pickerSummary) {
                    pickerSummary.textContent = selectedLabels.length
                        ? selectedLabels.slice(0, 3).join(', ') + (selectedLabels.length > 3 ? ` +${selectedLabels.length - 3}` : '')
                        : 'Aucun UP sélectionné';
                }
            };

            scopeSelect?.addEventListener('change', syncScope);
            citySelect?.addEventListener('change', () => {
                syncCommunes();
            });
            communeSelect?.addEventListener('change', () => {
                syncNeighborhoods();
            });

            document.querySelectorAll('[data-select-filter-target]').forEach((input) => {
                input.addEventListener('input', () => {
                    const select = document.getElementById(input.dataset.selectFilterTarget);

                    if (select === communeSelect) {
                        syncCommunes();
                        return;
                    }

                    if (select === neighborhoodSelect) {
                        syncNeighborhoods();
                        return;
                    }

                    applySelectFilter(select);
                });
            });

            pickerButton?.addEventListener('click', () => {
                picker?.classList.toggle('open');
                filterInput?.focus();
            });

            document.addEventListener('click', (event) => {
                if (picker && !picker.contains(event.target)) {
                    picker.classList.remove('open');
                }
            });

            filterInput.addEventListener('input', () => {
                const value = normalize(filterInput.value.trim());

                userCheckboxes.forEach((checkbox) => {
                    const row = checkbox.closest('.smart-option');
                    row.hidden = value && !normalize(row.dataset.search || '').includes(value);
                });
            });

            selectVisibleButton?.addEventListener('click', () => {
                userCheckboxes.forEach((checkbox) => {
                    const row = checkbox.closest('.smart-option');

                    if (!row.hidden) {
                        checkbox.checked = true;
                    }
                });

                syncUserSelect();
            });

            clearButton?.addEventListener('click', () => {
                userCheckboxes.forEach((checkbox) => {
                    checkbox.checked = false;
                });

                syncUserSelect();
            });

            userCheckboxes.forEach((checkbox) => {
                checkbox.addEventListener('change', syncUserSelect);
            });

            syncScope();
            applySelectFilter(scopeSelect);
            applySelectFilter(citySelect);
            syncCommunes();
            applySelectFilter(document.getElementById('pushSignalTypeFilter'));
            applySelectFilter(document.getElementById('pushResolutionStatusFilter'));
            syncUserSelect();
        })();
    </script>
@endpush
