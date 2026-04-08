@extends('institution.layouts.app')

@section('title', config('app.name').' | Mon profil')
@section('page-title', 'Mon profil')
@section('page-description', 'Mettre a jour vos informations de connexion et visualiser votre perimetre d acces institutionnel.')

@section('content')
    <style>
        .profile-shell {
            display: grid;
            gap: 1.15rem;
        }
        .profile-hero {
            border: 1px solid rgba(16,42,67,.08);
            border-radius: 24px;
            background: linear-gradient(145deg, rgba(15,41,64,.98), rgba(25,75,112,.96));
            color: white;
            padding: 1.15rem;
            box-shadow: 0 20px 48px rgba(15,41,64,.24);
        }
        .profile-avatar {
            width: 58px;
            height: 58px;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #c49b48, #a97824);
            color: white;
            font-weight: 800;
            font-size: 1.05rem;
            box-shadow: 0 14px 28px rgba(196,155,72,.24);
            flex-shrink: 0;
        }
        .summary-strip {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: .8rem;
            margin-top: 1rem;
        }
        .summary-card {
            border-radius: 18px;
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.1);
            padding: .85rem;
        }
        .summary-label {
            color: rgba(255,255,255,.62);
            text-transform: uppercase;
            letter-spacing: .06em;
            font-size: .7rem;
            font-weight: 700;
            margin-bottom: .3rem;
        }
        .summary-value {
            font-size: 1.05rem;
            font-weight: 800;
            line-height: 1.15;
        }
        .profile-form-card,
        .profile-side-card {
            border: 1px solid rgba(16,42,67,.08);
            border-radius: 22px;
            background: rgba(255,255,255,.92);
            box-shadow: 0 18px 42px rgba(16,42,67,.06);
            padding: 1rem;
        }
        .section-kicker {
            color: var(--acepen-muted);
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            font-weight: 800;
            margin-bottom: .3rem;
        }
        .section-copy {
            color: var(--acepen-muted);
            font-size: .83rem;
            margin-bottom: 1rem;
        }
        .quick-note {
            border-radius: 16px;
            background: rgba(196,155,72,.1);
            border: 1px solid rgba(196,155,72,.18);
            padding: .85rem .95rem;
            color: #7a5c1d;
            font-size: .82rem;
        }
        .meaning-grid {
            display: grid;
            gap: .75rem;
        }
        .meaning-grid-landscape {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: .75rem;
        }
        .meaning-card {
            border: 1px solid rgba(16,42,67,.08);
            border-radius: 18px;
            background: rgba(255,255,255,.88);
            padding: .9rem;
        }
        .meaning-title {
            font-weight: 700;
            color: var(--acepen-navy);
        }
        .meaning-code {
            color: var(--acepen-blue);
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .04em;
            margin-top: .2rem;
        }
        .meaning-copy {
            color: var(--acepen-muted);
            font-size: .82rem;
            margin-top: .45rem;
            line-height: 1.55;
        }
        @media (max-width: 1199.98px) {
            .meaning-grid-landscape {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 767.98px) {
            .meaning-grid-landscape {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="profile-shell">
        <section class="profile-hero">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="profile-avatar">{{ strtoupper(substr((string) $profileUser->name, 0, 2)) }}</div>
                    <div>
                        <div class="small text-white-50 fw-semibold mb-1">{{ $application?->name ?? 'Portail institutionnel' }}</div>
                        <div class="h4 fw-bold mb-1">{{ $profileUser->name }}</div>
                        <div class="text-white-50 small">{{ $profileUser->email }}{{ $profileUser->phone ? ' · '.$profileUser->phone : '' }}</div>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge-soft">{{ $organization?->name }}</span>
                    <span class="badge-soft">{{ $profileUser->status }}</span>
                </div>
            </div>

            <div class="summary-strip">
                <div class="summary-card">
                    <div class="summary-label">Application</div>
                    <div class="summary-value">{{ $application?->name ?: '-' }}</div>
                </div>
                <div class="summary-card">
                    <div class="summary-label">Organisation</div>
                    <div class="summary-value">{{ $organization?->name ?: '-' }}</div>
                </div>
                <div class="summary-card">
                    <div class="summary-label">Portail</div>
                    <div class="summary-value">{{ $organization?->portal_key ?: '-' }}</div>
                </div>
                <div class="summary-card">
                    <div class="summary-label">Roles internes</div>
                    <div class="summary-value">{{ $roleItems->count() }}</div>
                </div>
            </div>
        </section>

        <div class="row g-4">
            <div class="col-12">
                <section class="profile-form-card">
                    <div class="section-kicker">Informations personnelles</div>
                    <div class="fw-bold fs-5 mb-2">Mettre a jour mon profil</div>
                    <div class="section-copy">Ces informations sont utilisees pour votre connexion et votre identification dans le portail institutionnel.</div>

                    <form method="POST" action="{{ route('institution.profile.update') }}" class="row g-3">
                        @csrf
                        @method('PUT')
                        <div class="col-md-6">
                            <label class="form-label">Nom complet</label>
                            <input type="text" name="name" value="{{ old('name', $profileUser->name) }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" value="{{ old('email', $profileUser->email) }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            @include('partials.phone-field', ['value' => old('phone', $profileUser->phone), 'placeholder' => '0700000000'])
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Statut du compte</label>
                            <input type="text" value="{{ $profileUser->status }}" class="form-control" readonly>
                        </div>
                        <div class="col-12">
                            <div class="quick-note">
                                Le changement de mot de passe est facultatif. Laissez les deux champs vides si vous souhaitez conserver votre mot de passe actuel.
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nouveau mot de passe</label>
                            <input type="password" name="password" class="form-control" placeholder="Laisser vide pour conserver l actuel">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirmation du mot de passe</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Confirmer le nouveau mot de passe">
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-dark">Enregistrer les modifications</button>
                            <a href="{{ route('institution.dashboard') }}" class="btn btn-outline-secondary">Retour au dashboard</a>
                        </div>
                    </form>
                </section>
            </div>
            <div class="col-12">
                <section class="profile-side-card mb-4">
                    <div class="section-kicker">Perimetre du compte</div>
                    <div class="fw-bold fs-5 mb-2">Roles internes affectes</div>
                    <div class="section-copy">Chaque role interne regroupe un ensemble d’autorisations donnees par votre institution.</div>

                    @if ($roleItems->isEmpty())
                        <div class="text-secondary small">Aucun role interne affecte.</div>
                    @else
                        <div class="meaning-grid-landscape">
                            @foreach ($roleItems as $role)
                                <div class="meaning-card">
                                    <div class="meaning-title">{{ $role->name }}</div>
                                    <div class="meaning-code">{{ $role->code }}</div>
                                    <div class="meaning-copy">{{ $role->description ?: 'Role interne defini par votre institution pour regrouper plusieurs autorisations.' }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section class="profile-side-card mb-4">
                    <div class="section-kicker">Fonctionnalites visibles</div>
                    <div class="fw-bold fs-5 mb-2">Ce que vous voyez dans le portail</div>
                    <div class="section-copy">Ces fonctionnalites ont ete activees par le super admin puis rendues visibles pour votre compte.</div>

                    @if ($featureDetails->isEmpty())
                        <div class="text-secondary small">Aucune fonctionnalite active visible pour ce compte.</div>
                    @else
                        <div class="meaning-grid-landscape">
                            @foreach ($featureDetails as $feature)
                                <div class="meaning-card">
                                    <div class="meaning-title">{{ $feature->name }}</div>
                                    <div class="meaning-copy">{{ $feature->description ?: 'Fonctionnalite rendue visible par le super admin puis autorisee pour votre compte.' }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section class="profile-side-card">
                    <div class="section-kicker">Autorisations</div>
                    <div class="fw-bold fs-5 mb-2">Droits concrets de ce compte</div>
                    <div class="section-copy">Voici les actions precises que votre compte peut utiliser dans ce portail institutionnel.</div>

                    @if ($permissionDetails->isEmpty())
                        <div class="text-secondary small">Aucune autorisation detaillee detectee pour ce compte.</div>
                    @else
                        <div class="meaning-grid-landscape">
                            @foreach ($permissionDetails as $permission)
                                <div class="meaning-card">
                                    <div class="meaning-title">{{ $permission->name }}</div>
                                    <div class="meaning-copy">{{ $permission->description ?: 'Autorisation precise accordee a votre compte ou heritee via un role interne.' }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>
        </div>
    </div>
@endsection
