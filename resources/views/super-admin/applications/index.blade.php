@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Applications')
@section('page-title', 'Applications')
@section('page-description', 'Parametrer les univers metier de la plateforme et leur identite.')

@section('header-badges')
    <span class="badge-soft">{{ $applications->total() }} applications</span>
    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#createApplicationModal">
        Nouvelle application
    </button>
@endsection

@section('content')
    <style>
        .app-admin-brand {
            display: flex;
            gap: .9rem;
            align-items: center;
        }
        .app-admin-logo {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            object-fit: contain;
            background: #fff;
            padding: .35rem;
            box-shadow: 0 12px 24px rgba(16,42,67,.08);
        }
        .app-admin-code {
            display: inline-flex;
            border-radius: 999px;
            background: rgba(25,75,112,.08);
            color: var(--acepen-blue);
            font-weight: 700;
            font-size: .74rem;
            padding: .3rem .6rem;
        }
        .app-admin-meta {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .75rem;
            margin: 1rem 0;
        }
        .app-admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1rem;
        }
        .app-admin-card {
            border: 1px solid rgba(16,42,67,.08);
            border-radius: 24px;
            background: linear-gradient(180deg, rgba(255,255,255,.96), rgba(244,248,252,.9));
            box-shadow: 0 18px 42px rgba(16,42,67,.08);
            padding: 1.1rem;
        }
        .app-admin-head {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        .app-admin-box {
            border-radius: 18px;
            background: rgba(255,255,255,.9);
            border: 1px solid rgba(16,42,67,.07);
            padding: .85rem;
        }
        .app-admin-label {
            color: var(--acepen-muted);
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .06em;
            font-weight: 700;
            margin-bottom: .35rem;
        }
        .app-feature-chip-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .5rem;
            margin-top: .75rem;
        }
        .app-feature-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: .4rem .7rem;
            background: rgba(196,155,72,.12);
            color: #7a5c1d;
            font-size: .75rem;
            font-weight: 700;
            text-align: center;
        }
        .feature-picker {
            display: grid;
            gap: 1rem;
            max-height: 52vh;
            overflow: auto;
            padding-right: .25rem;
        }
        .feature-picker-group {
            border: 1px solid rgba(16,42,67,.08);
            border-radius: 20px;
            background: rgba(248,250,252,.9);
            padding: .9rem;
        }
        .feature-picker-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .75rem;
        }
        .feature-option {
            display: block;
            border: 1px solid rgba(16,42,67,.08);
            border-radius: 18px;
            background: #fff;
            padding: .9rem;
            height: 100%;
            cursor: pointer;
        }
        .feature-option .form-check {
            display: flex;
            gap: .75rem;
            align-items: flex-start;
            margin: 0;
        }
        .feature-option-title {
            font-weight: 700;
            line-height: 1.3;
        }
        .feature-option-code {
            color: var(--acepen-blue);
            font-size: .73rem;
            font-weight: 800;
            letter-spacing: .03em;
            margin-top: .15rem;
        }
        @media (max-width: 767.98px) {
            .app-feature-chip-grid,
            .feature-picker-grid {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 1199.98px) {
            .app-feature-chip-grid,
            .feature-picker-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
    </style>

    <section class="panel-card mb-4">
        <div class="fw-bold mb-3">Catalogue des applications</div>
        <form method="GET" class="filter-bar">
            <div class="row g-2 align-items-end">
                <div class="col-md-7">
                    <label class="form-label small text-secondary">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nom, code, slug, slogan">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-secondary">Statut</label>
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="active" @selected(request('status') === 'active')>Actif</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactif</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-dark w-100">Filtrer</button>
                    <a href="{{ route('super-admin.applications.index') }}" class="btn btn-outline-secondary">RAZ</a>
                </div>
            </div>
        </form>

        @if ($applications->isEmpty())
            <div class="text-center text-secondary py-5">Aucune application enregistree.</div>
        @else
            <div class="app-admin-grid">
                @foreach ($applications as $application)
                    <article class="app-admin-card">
                        <div class="app-admin-head">
                            <div class="app-admin-brand">
                                <img src="{{ $application->logoUrl() ?: asset('image/logo/logo-my-signal.png') }}" alt="Logo {{ $application->name }}" class="app-admin-logo">
                                <div>
                                    <div class="app-admin-code">{{ $application->code }}</div>
                                    <div class="fw-bold mt-2">{{ $application->name }}</div>
                                    <div class="small text-secondary mt-1">{{ $application->slug }}</div>
                                </div>
                            </div>
                            <span class="status-chip">{{ $application->status }}</span>
                        </div>

                        <div class="fw-semibold">{{ $application->tagline ?: '-' }}</div>
                        <div class="small text-secondary mt-1">{{ $application->short_description ?: 'Aucune description courte renseignee.' }}</div>

                        <div class="app-admin-meta">
                            <div class="app-admin-box">
                                <div class="app-admin-label">Institutions</div>
                                <div class="fw-semibold">{{ $application->organizations_count }}</div>
                            </div>
                            <div class="app-admin-box">
                                <div class="app-admin-label">Types de signaux</div>
                                <div class="fw-semibold">{{ $application->signal_types_count }}</div>
                            </div>
                            <div class="app-admin-box">
                                <div class="app-admin-label">Ordre</div>
                                <div class="fw-semibold">{{ $application->sort_order }}</div>
                            </div>
                        </div>

                        <div class="app-admin-label">Fonctionnalites</div>
                        @if ($application->features->isEmpty())
                            <div class="small text-secondary">Aucune fonctionnalite</div>
                        @else
                            <div class="app-feature-chip-grid">
                                @foreach ($application->features->take(6) as $feature)
                                    <span class="app-feature-chip">{{ $feature->name }}</span>
                                @endforeach
                            </div>
                            @if ($application->features->count() > 6)
                                <div class="small text-secondary mt-2">+{{ $application->features->count() - 6 }} autre(s) fonctionnalite(s)</div>
                            @endif
                        @endif

                        <div class="actions-wrap mt-3">
                            <a href="{{ route('super-admin.organizations.index', ['application_id' => $application->id]) }}" class="btn btn-sm btn-outline-dark">Voir les organisations</a>
                            <a href="{{ route('super-admin.signal-types.index', ['application_id' => $application->id]) }}" class="btn btn-sm btn-outline-dark">Voir les types de signaux</a>
                            <a href="{{ route('super-admin.applications.edit', $application) }}" class="btn btn-sm btn-outline-dark">Modifier</a>
                            <form method="POST" action="{{ route('super-admin.applications.toggle-status', $application) }}">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-sm btn-outline-warning">{{ $application->status === 'active' ? 'Desactiver' : 'Activer' }}</button>
                            </form>
                            <form method="POST" action="{{ route('super-admin.applications.destroy', $application) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Supprimer</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="table-meta">Page {{ $applications->currentPage() }} sur {{ $applications->lastPage() }}</div>
                {{ $applications->links() }}
            </div>
        @endif
    </section>

    <div class="modal fade" id="createApplicationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content border-0" style="border-radius: 28px; overflow: hidden;">
                <div class="modal-header px-4 py-3 border-0" style="background: linear-gradient(145deg, #0f2738, #1b4867); color: white;">
                    <div>
                        <div class="small text-white-50 fw-semibold mb-1">Nouvelle application</div>
                        <div class="h5 fw-bold mb-0">Creer un univers metier</div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" action="{{ route('super-admin.applications.store') }}" class="row g-3" enctype="multipart/form-data">
                        @csrf
                        <div class="col-md-4">
                            <label class="form-label">Code</label>
                            <input type="text" name="code" class="form-control" placeholder="MON_NRJ" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nom</label>
                            <input type="text" name="name" class="form-control" placeholder="MON NRJ" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" class="form-control" placeholder="mon-nrj" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Slogan</label>
                            <input type="text" name="tagline" class="form-control" placeholder="Les griefs lies a l energie au meme endroit.">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ordre d'affichage</label>
                            <input type="number" min="1" max="999" name="sort_order" class="form-control" value="1">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description courte</label>
                            <input type="text" name="short_description" class="form-control" placeholder="Resume visible sur la landing et dans les listes SA.">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description longue</label>
                            <textarea name="long_description" class="form-control" rows="4"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Logo</label>
                            <input type="file" name="logo_file" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                            <div class="small text-secondary mt-2">Le fichier sera envoye sur Wasabi.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Image hero</label>
                            <input type="file" name="hero_image_file" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                            <div class="small text-secondary mt-2">Le fichier sera envoye sur Wasabi.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Couleur primaire</label>
                            <input type="text" name="primary_color" class="form-control" placeholder="#0c2435">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Couleur secondaire</label>
                            <input type="text" name="secondary_color" class="form-control" placeholder="#1e5877">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Couleur accent</label>
                            <input type="text" name="accent_color" class="form-control" placeholder="#cb6f2c">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Fonctionnalites par defaut de l'application</label>
                            <div class="small text-secondary mb-3">Ces fonctionnalites seront preactivees dans toutes les organisations rattachees a cette application. Le SA pourra ensuite en desactiver localement sur une organisation precise.</div>
                            <div class="feature-picker">
                                @foreach ($groupedFeatures as $groupLabel => $groupFeatures)
                                    <section class="feature-picker-group">
                                        <div class="small text-uppercase fw-bold text-secondary mb-3">{{ $groupLabel }}</div>
                                        <div class="feature-picker-grid">
                                            @foreach ($groupFeatures as $feature)
                                                <label for="application-feature-create-{{ $feature->id }}" class="feature-option">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" value="{{ $feature->id }}" name="feature_ids[]" id="application-feature-create-{{ $feature->id }}" @checked(in_array($feature->id, old('feature_ids', [])))>
                                                        <span class="form-check-label">
                                                            <span class="feature-option-title d-block">{{ $feature->name }}</span>
                                                            <span class="feature-option-code d-block">{{ $feature->code }}</span>
                                                            @if ($feature->description)
                                                                <span class="small text-secondary d-block mt-2">{{ $feature->description }}</span>
                                                            @endif
                                                        </span>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    </section>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-dark">Creer l'application</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('createApplicationModal')).show();
            });
        </script>
    @endif
@endsection
