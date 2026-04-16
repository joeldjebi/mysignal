<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name') }} | Accueil</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <style>
            :root {
                --mysignal-navy: #0c2435;
                --mysignal-ocean: #1e5877;
                --mysignal-copper: #cb6f2c;
                --mysignal-sand: #f7f1e8;
                --mysignal-ink: #173447;
                --mysignal-muted: #667786;
                --mysignal-card: rgba(255, 255, 255, 0.92);
                --mysignal-soft: #f6f8fa;
            }
            body {
                font-family: "Manrope", sans-serif;
                color: var(--mysignal-ink);
                background:
                    radial-gradient(circle at top left, rgba(203, 111, 44, 0.14), transparent 24%),
                    radial-gradient(circle at 90% 0%, rgba(30, 88, 119, 0.16), transparent 28%),
                    linear-gradient(180deg, #faf7f2 0%, #eef3f6 52%, #faf7f3 100%);
            }
            .shell { max-width: 1320px; }
            .glass-nav,.hero-panel,.feature-card,.auth-panel,.story-card,.proof-card,.impact-card,.mission-band,.timeline-card,.app-card { background: var(--mysignal-card); border: 1px solid rgba(24,52,71,.08); box-shadow: 0 28px 80px rgba(15,39,56,.08); backdrop-filter: blur(18px); }
            .glass-nav { border-radius: 22px; }
            .hero-panel,.feature-card,.auth-panel,.story-card,.proof-card,.impact-card,.mission-band,.timeline-card,.app-card { border-radius: 30px; }
            .brand-logo { width: 54px; height: 54px; object-fit: contain; border-radius: 18px; background: white; padding: .35rem; box-shadow: 0 14px 28px rgba(12,36,53,.08); }
            .eyebrow { display: inline-flex; align-items:center; gap:.55rem; padding:.55rem 1rem; border-radius:999px; background: rgba(30,88,119,.08); color: var(--mysignal-ocean); font-size:.82rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; }
            .hero-title { font-size: clamp(2.85rem, 5vw, 5.65rem); font-weight: 800; line-height: .94; letter-spacing: -.06em; }
            .hero-copy,.muted-copy { color: var(--mysignal-muted); line-height: 1.75; }
            .hero-lead-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: .9rem;
                margin-top: 1.5rem;
            }
            .hero-lead-card {
                border-radius: 24px;
                background: rgba(12,36,53,.03);
                border: 1px solid rgba(24,52,71,.07);
                padding: 1rem 1.05rem;
            }
            .hero-lead-label {
                font-size: .78rem;
                text-transform: uppercase;
                letter-spacing: .08em;
                color: var(--mysignal-muted);
                font-weight: 800;
                margin-bottom: .4rem;
            }
            .hero-lead-value {
                font-size: 1.05rem;
                font-weight: 800;
                color: var(--mysignal-navy);
                line-height: 1.2;
            }
            .hero-stage {
                position: relative;
                overflow: hidden;
                border-radius: 28px;
                min-height: 100%;
                background:
                    radial-gradient(circle at top right, rgba(255,255,255,.18), transparent 36%),
                    linear-gradient(145deg, var(--mysignal-navy), var(--mysignal-ocean));
                color: #f9f3ec;
                padding: 1.4rem;
            }
            .hero-stage::after {
                content: "";
                position: absolute;
                right: -72px;
                bottom: -72px;
                width: 220px;
                height: 220px;
                border-radius: 50%;
                background: radial-gradient(circle, rgba(203,111,44,.34), transparent 62%);
            }
            .hero-stage-card {
                position: relative;
                border-radius: 22px;
                background: rgba(255,255,255,.08);
                border: 1px solid rgba(255,255,255,.12);
                padding: 1rem;
                margin-bottom: .9rem;
            }
            .hero-stage-card:last-child { margin-bottom: 0; }
            .hero-stage-kicker {
                font-size: .74rem;
                text-transform: uppercase;
                letter-spacing: .08em;
                color: rgba(255,255,255,.64);
                font-weight: 800;
                margin-bottom: .35rem;
            }
            .hero-stage strong {
                display: block;
                font-size: 1.1rem;
                margin-bottom: .25rem;
            }
            .flat-illustration {
                position: relative;
                border-radius: 28px;
                overflow: hidden;
                border: 1px solid rgba(24,52,71,.08);
                background: linear-gradient(180deg, rgba(255,255,255,.95), rgba(244,247,249,.92));
                min-height: 280px;
                box-shadow: inset 0 1px 0 rgba(255,255,255,.6);
            }
            .flat-illustration svg,
            .section-visual svg,
            .feature-art svg {
                display: block;
                width: 100%;
                height: 100%;
            }
            .section-visual {
                border-radius: 26px;
                overflow: hidden;
                border: 1px solid rgba(24,52,71,.08);
                background: linear-gradient(180deg, rgba(255,255,255,.96), rgba(246,248,250,.92));
                min-height: 100%;
                box-shadow: inset 0 1px 0 rgba(255,255,255,.7);
            }
            .feature-art {
                border-radius: 22px;
                overflow: hidden;
                border: 1px solid rgba(24,52,71,.08);
                background: linear-gradient(180deg, rgba(246,248,250,.95), rgba(255,255,255,.95));
                margin-bottom: 1rem;
            }
            .feature-art svg {
                height: 180px;
            }
            .access-showcase {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: .9rem;
                margin-top: 1rem;
            }
            .access-tile {
                border-radius: 22px;
                border: 1px solid rgba(24,52,71,.08);
                background: rgba(255,255,255,.84);
                padding: .95rem;
            }
            .app-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 1rem;
            }
            .app-card {
                padding: 1.25rem;
                position: relative;
                overflow: hidden;
            }
            .app-card::after {
                content: "";
                position: absolute;
                inset: auto -36px -36px auto;
                width: 120px;
                height: 120px;
                border-radius: 50%;
                background: radial-gradient(circle, rgba(203,111,44,.16), transparent 68%);
            }
            .app-chip {
                display: inline-flex;
                align-items: center;
                gap: .45rem;
                border-radius: 999px;
                padding: .45rem .8rem;
                background: rgba(12,36,53,.06);
                color: var(--mysignal-navy);
                font-size: .76rem;
                font-weight: 800;
                letter-spacing: .04em;
                text-transform: uppercase;
            }
            .app-logo {
                width: 44px;
                height: 44px;
                border-radius: 14px;
                object-fit: contain;
                background: white;
                padding: .3rem;
                box-shadow: 0 12px 24px rgba(12,36,53,.08);
            }
            .btn-premium { border:none; min-height:3.25rem; border-radius:18px; background: linear-gradient(135deg, var(--mysignal-copper), #dd8d4d); color:white; font-weight:800; box-shadow: 0 18px 34px rgba(196,106,43,.24); }
            .btn-premium:hover { color: white; transform: translateY(-1px); }
            .btn-ghost-premium { min-height:3.25rem; border-radius:18px; background: rgba(12,36,53,.05); color: var(--mysignal-navy); border:1px solid rgba(12,36,53,.08); font-weight:800; }
            .auth-switch .nav-link,.register-switch .nav-link { border-radius:999px; color: var(--mysignal-muted); font-weight:800; padding:.9rem 1rem; }
            .auth-switch .nav-link.active,.register-switch .nav-link.active { background: linear-gradient(135deg, var(--mysignal-copper), #dd8d4d); color:white; box-shadow: 0 16px 28px rgba(196,106,43,.22); }
            .form-control,.form-select { border-radius:18px; border-color: rgba(24,52,71,.11); min-height:3.25rem; padding-inline:1rem; }
            .form-control:focus,.form-select:focus { border-color: rgba(196,106,43,.55); box-shadow: 0 0 0 .25rem rgba(196,106,43,.12); }
            .required-star { color:#d6005a; font-weight:800; margin-left:.15rem; }
            .public-select-shell { position: relative; }
            .public-select-input { display:block; width:100%; min-height:3.25rem; border-radius:18px; border-color: rgba(24,52,71,.11); padding-inline:1rem; padding-right:3.4rem; background:white; cursor:pointer; margin-bottom:.55rem; }
            .public-select-toggle { position:absolute; top:0; right:0; width:3rem; height:3.25rem; border:0; background:transparent; color:var(--mysignal-muted); border-radius:0 18px 18px 0; }
            .public-select-toggle::before,.public-select-toggle::after { content:""; position:absolute; top:50%; width:7px; height:2px; background:currentColor; }
            .public-select-toggle::before { right:18px; transform: translateY(-50%) rotate(45deg); }
            .public-select-toggle::after { right:13px; transform: translateY(-50%) rotate(-45deg); }
            .public-select-help { margin-top:-.2rem; margin-bottom:.55rem; color:var(--mysignal-muted); font-size:.76rem; }
            .public-select-results { display:none; margin-top:-.2rem; margin-bottom:.55rem; background:#fff; border:1px solid rgba(24,52,71,.12); border-radius:18px; box-shadow:0 18px 34px rgba(12,36,53,.08); max-height:220px; overflow:auto; padding:.35rem; }
            .public-select-results.is-open { display:block; }
            .public-select-option { width:100%; text-align:left; border:0; background:transparent; border-radius:12px; padding:.65rem .8rem; color:var(--mysignal-navy); }
            .public-select-option:hover { background: rgba(12,36,53,.05); }
            .feature-icon { width:52px; height:52px; border-radius:18px; display:grid; place-items:center; background: rgba(30,88,119,.08); color: var(--mysignal-ocean); font-weight:800; }
            .mini-card { background: white; border: 1px solid rgba(24,52,71,.08); border-radius: 24px; padding: 1.15rem; }
            .story-card,.proof-card,.impact-card { padding: 1.35rem; }
            .impact-card {
                background:
                    linear-gradient(145deg, rgba(12,36,53,.96), rgba(30,88,119,.94)),
                    var(--mysignal-navy);
                color: white;
            }
            .impact-card .muted-copy,
            .hero-stage .muted-copy {
                color: rgba(255,255,255,.74);
            }
            .fact-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: .9rem;
            }
            .fact-tile {
                border-radius: 22px;
                background: white;
                border: 1px solid rgba(24,52,71,.08);
                padding: 1rem;
            }
            .fact-kicker {
                font-size: .74rem;
                letter-spacing: .08em;
                text-transform: uppercase;
                color: var(--mysignal-muted);
                font-weight: 800;
                margin-bottom: .35rem;
            }
            .fact-value {
                font-size: 1.65rem;
                font-weight: 800;
                line-height: 1;
                margin-bottom: .4rem;
            }
            .section-heading {
                font-size: clamp(1.7rem, 3vw, 2.7rem);
                font-weight: 800;
                letter-spacing: -.04em;
                margin-bottom: .65rem;
            }
            .mission-band {
                padding: 1.4rem;
                background:
                    radial-gradient(circle at top left, rgba(203,111,44,.12), transparent 34%),
                    linear-gradient(180deg, rgba(255,255,255,.92), rgba(247,241,232,.9));
            }
            .mission-point {
                padding: 1rem 0;
                border-top: 1px solid rgba(24,52,71,.08);
            }
            .mission-point:first-child {
                border-top: 0;
                padding-top: 0;
            }
            .timeline-card {
                padding: 1.3rem;
                background: linear-gradient(180deg, rgba(255,255,255,.95), rgba(246,248,250,.92));
            }
            .timeline-step {
                display: flex;
                gap: .9rem;
                align-items: flex-start;
                padding: .9rem 0;
                border-top: 1px solid rgba(24,52,71,.08);
            }
            .timeline-step:first-child {
                padding-top: 0;
                border-top: 0;
            }
            .timeline-badge {
                width: 40px;
                height: 40px;
                border-radius: 14px;
                display: grid;
                place-items: center;
                background: linear-gradient(135deg, var(--mysignal-copper), #dd8d4d);
                color: white;
                font-weight: 800;
                flex-shrink: 0;
                box-shadow: 0 14px 28px rgba(196,106,43,.2);
            }
            .bullet-check {
                display: flex;
                gap: .75rem;
                align-items: flex-start;
            }
            .bullet-check strong {
                display: block;
                margin-bottom: .15rem;
            }
            .bullet-mark {
                width: 34px;
                height: 34px;
                border-radius: 12px;
                display: grid;
                place-items: center;
                background: rgba(203,111,44,.14);
                color: var(--mysignal-copper);
                font-weight: 800;
                flex-shrink: 0;
            }
            .hidden { display:none !important; }
            .toast-container { z-index:1090; }
            @media (max-width: 991.98px) {
                .fact-grid {
                    grid-template-columns: 1fr;
                }
                .hero-lead-grid {
                    grid-template-columns: 1fr;
                }
                .access-showcase {
                    grid-template-columns: 1fr;
                }
                .app-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    </head>
    <body>
        <div class="container shell py-3 py-lg-4">
            <nav class="navbar navbar-expand-lg glass-nav px-3 px-lg-4 py-3 mb-4 mb-lg-5">
                <a class="navbar-brand d-flex align-items-center gap-3 fw-bold text-dark" href="{{ route('public.landing') }}">
                    <img src="{{ asset('image/logo/logo-my-signal.png') }}" alt="Logo My Signal" class="brand-logo">
                    <span>
                        <span class="d-block lh-1">My Signal</span>
                        <small class="text-secondary fw-semibold">Plateforme citoyenne de signalement</small>
                    </span>
                </a>
                <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#publicNavbar">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="publicNavbar">
                    <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                        <li class="nav-item"><a class="nav-link" href="#landing">Accueil</a></li>
                        <li class="nav-item"><a class="nav-link" href="#importance">Importance</a></li>
                        <li class="nav-item"><a class="nav-link" href="#features">Avantages</a></li>
                        <li class="nav-item"><a class="nav-link" href="#parcours">Parcours</a></li>
                        <li class="nav-item"><a class="nav-link" href="#access">Accès</a></li>
                        <li class="nav-item ms-lg-2"><button class="btn btn-ghost-premium px-4" data-auth-tab-target="login">Se connecter</button></li>
                        <li class="nav-item"><button class="btn btn-premium px-4" data-auth-tab-target="register">Créer un compte</button></li>
                    </ul>
                </div>
            </nav>

            <section id="landing" class="hero-panel p-4 p-lg-5 mb-4 mb-lg-5">
                <div class="row align-items-center g-4 g-lg-5">
                    <div class="col-lg-7">
                        <span class="eyebrow mb-3">Signalement citoyen structuré</span>
                        <h1 class="hero-title mb-3">Transformer une alerte en dossier utile, clair et suivi jusqu’au bout.</h1>
                        <p class="hero-copy mb-4">
                            My Signal aide les usagers à signaler un incident de façon sérieuse, compréhensible et exploitable par les institutions. L’objectif est simple : mieux décrire le problème, mieux orienter l’intervention, puis suivre la résolution avec un niveau de preuve et de transparence que les appels informels ne donnent pas.
                        </p>
                        <div class="d-flex flex-wrap gap-3">
                            <button class="btn btn-premium px-4" data-auth-tab-target="register">Créer mon compte</button>
                            <a class="btn btn-ghost-premium px-4" href="#importance">Pourquoi signaler ?</a>
                        </div>
                        <div class="hero-lead-grid">
                            <div class="hero-lead-card">
                                <div class="hero-lead-label">Importance</div>
                                <div class="hero-lead-value">Des incidents mieux localisés et mieux compris.</div>
                            </div>
                            <div class="hero-lead-card">
                                <div class="hero-lead-label">Avantage</div>
                                <div class="hero-lead-value">Un suivi visible à chaque étape du traitement.</div>
                            </div>
                            <div class="hero-lead-card">
                                <div class="hero-lead-label">Objectif</div>
                                <div class="hero-lead-value">Renforcer la qualité de service et la confiance.</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="hero-stage">
                            <div class="flat-illustration mb-3">
                                <svg viewBox="0 0 520 320" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <rect width="520" height="320" fill="#10283b"/>
                                    <circle cx="414" cy="64" r="74" fill="#1d5a78" opacity=".48"/>
                                    <circle cx="84" cy="276" r="92" fill="#cb6f2c" opacity=".22"/>
                                    <rect x="52" y="70" width="190" height="168" rx="26" fill="#f8fafb"/>
                                    <rect x="72" y="95" width="150" height="18" rx="9" fill="#dbe5eb"/>
                                    <rect x="72" y="126" width="86" height="72" rx="18" fill="#1e5877"/>
                                    <path d="M96 170l18-20 20 24 14-10 22 24H96z" fill="#d9edf7"/>
                                    <circle cx="148" cy="142" r="9" fill="#cb6f2c"/>
                                    <rect x="170" y="128" width="52" height="10" rx="5" fill="#b7c8d3"/>
                                    <rect x="170" y="146" width="38" height="10" rx="5" fill="#b7c8d3"/>
                                    <rect x="170" y="174" width="44" height="10" rx="5" fill="#b7c8d3"/>
                                    <rect x="274" y="52" width="190" height="214" rx="30" fill="#15364d"/>
                                    <rect x="298" y="80" width="142" height="20" rx="10" fill="#335f79"/>
                                    <rect x="298" y="118" width="76" height="76" rx="22" fill="#f6f1e8"/>
                                    <path d="M336 132a21 21 0 100 42 21 21 0 000-42zm0 9a12 12 0 110 24 12 12 0 010-24z" fill="#cb6f2c"/>
                                    <path d="M334 148h5v10h10v5h-10v10h-5v-10h-10v-5h10z" fill="#cb6f2c"/>
                                    <rect x="388" y="126" width="52" height="12" rx="6" fill="#8eacbe"/>
                                    <rect x="388" y="148" width="38" height="12" rx="6" fill="#8eacbe"/>
                                    <rect x="298" y="214" width="142" height="18" rx="9" fill="#335f79"/>
                                    <rect x="298" y="242" width="92" height="12" rx="6" fill="#335f79"/>
                                </svg>
                            </div>
                            <div class="hero-stage-card">
                                <div class="hero-stage-kicker">Etape 1</div>
                                <strong>Déclarer le problème</strong>
                                <div class="muted-copy mb-0">Choisissez le réseau, le compteur concerné, la localisation et le type de signal le plus juste pour créer un dossier fiable.</div>
                            </div>
                            <div class="hero-stage-card">
                                <div class="hero-stage-kicker">Etape 2</div>
                                <strong>Suivre le traitement</strong>
                                <div class="muted-copy mb-0">Consultez l’état du signalement, la réponse institutionnelle, le respect du TCM et l’évolution concrète du dossier.</div>
                            </div>
                            <div class="hero-stage-card">
                                <div class="hero-stage-kicker">Etape 3</div>
                                <strong>Documenter les suites</strong>
                                <div class="muted-copy mb-0">Confirmez la résolution, déclarez un dommage si nécessaire et gardez l’historique de vos preuves et de vos actions.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="importance" class="mb-4 mb-lg-5">
                <div class="row g-4 align-items-stretch">
                    <div class="col-lg-7">
                        <div class="story-card h-100">
                            <span class="eyebrow mb-3">Pourquoi signaler</span>
                            <h2 class="section-heading">Chaque signalement bien structuré améliore la réactivité, la preuve et la protection de l’usager.</h2>
                            <p class="muted-copy mb-4">
                                Un bon signalement ne sert pas seulement à alerter. Il aide à orienter les équipes, prioriser les incidents, localiser précisément le problème et documenter les faits. Plus les informations sont claires, plus la prise en charge devient crédible et efficace.
                            </p>
                            <div class="vstack gap-3">
                                <div class="bullet-check">
                                    <div class="bullet-mark">01</div>
                                    <div>
                                        <strong>Réduire les délais de prise en charge</strong>
                                        <div class="muted-copy mb-0">Le compteur, l’adresse et le type de signal guident immédiatement l’institution vers la bonne intervention.</div>
                                    </div>
                                </div>
                                <div class="bullet-check">
                                    <div class="bullet-mark">02</div>
                                    <div>
                                        <strong>Améliorer la qualité des décisions</strong>
                                        <div class="muted-copy mb-0">Les données saisies, les photos et la localisation donnent un dossier plus fiable et plus exploitable.</div>
                                    </div>
                                </div>
                                <div class="bullet-check">
                                    <div class="bullet-mark">03</div>
                                    <div>
                                        <strong>Créer une vraie transparence</strong>
                                        <div class="muted-copy mb-0">L’usager suit le statut du dossier, la réponse apportée, le TCM appliqué et les suites éventuelles.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="impact-card h-100">
                            <span class="eyebrow mb-3" style="background: rgba(255,255,255,.08); color: #fff;">Valeur pour l’usager</span>
                            <h2 class="section-heading">Une plateforme utile pour agir, suivre et garder des preuves.</h2>
                            <p class="muted-copy mb-4">
                                My Signal rassemble dans un seul espace les compteurs, le Gonhi familial, les signalements, les justificatifs utiles et le suivi institutionnel du dossier.
                            </p>
                            <div class="vstack gap-3">
                                <div class="hero-stage-card">
                                    <strong>Un historique centralisé</strong>
                                    <div class="muted-copy mb-0">Retrouvez vos signalements, vos reçus, vos réponses institutionnelles et vos dommages déclarés au même endroit.</div>
                                </div>
                                <div class="hero-stage-card">
                                    <strong>Une relation plus claire avec l’institution</strong>
                                    <div class="muted-copy mb-0">Vous voyez ce qui a été résolu, ce qui reste en cours et les réponses enregistrées sur votre dossier.</div>
                                </div>
                                <div class="hero-stage-card">
                                    <strong>Une meilleure qualité d’information</strong>
                                    <div class="muted-copy mb-0">Compteurs, photos, GPS et type de signal améliorent la compréhension du problème dès le départ.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="mb-4 mb-lg-5">
                <div class="mission-band p-4 p-lg-5">
                    <div class="row g-4 align-items-center">
                        <div class="col-lg-5">
                            <span class="eyebrow mb-3">Le but</span>
                            <h2 class="section-heading mb-3">Donner à chaque usager un moyen fiable de faire remonter un incident et d’en suivre l’issue.</h2>
                            <p class="muted-copy mb-0">
                                La plateforme ne met pas l’accent sur un paiement. Elle met l’accent sur la valeur du signalement lui-même : sa qualité, sa traçabilité, sa capacité à déclencher une meilleure intervention et à créer une mémoire utile du dossier.
                            </p>
                        </div>
                        <div class="col-lg-7">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="mini-card h-100">
                                        <div class="fw-bold mb-2">Mieux orienter</div>
                                        <div class="muted-copy mb-0">Le bon réseau, le bon compteur et la bonne zone réduisent les ambiguïtés.</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mini-card h-100">
                                        <div class="fw-bold mb-2">Mieux suivre</div>
                                        <div class="muted-copy mb-0">Le statut, le TCM et les réponses forment une lecture claire de l’avancement.</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mini-card h-100">
                                        <div class="fw-bold mb-2">Mieux documenter</div>
                                        <div class="muted-copy mb-0">La résolution, les dommages et les preuves restent attachés au bon dossier.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            @if(($applications ?? collect())->isNotEmpty())
                <section class="mb-4 mb-lg-5">
                    <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
                        <div>
                            <span class="eyebrow mb-2">{{ $landingBlocks->get('applications_heading')?->subtitle ?? 'Applications disponibles' }}</span>
                            <h2 class="section-heading mb-0">{{ $landingBlocks->get('applications_heading')?->title ?? 'Six univers metier, un meme socle de protection des consommateurs.' }}</h2>
                        </div>
                    </div>
                    <p class="muted-copy mb-4">{{ $landingBlocks->get('applications_heading')?->body ?? 'Chaque application specialisee reprend le meme principe : regrouper les griefs, documenter les torts subis, suivre les resolutions et soutenir, lorsque necessaire, les actions de reparation et de dedommagement.' }}</p>
                    <div class="app-grid">
                        @foreach($applications as $application)
                            <article class="app-card">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <img src="{{ asset($application->logo_path ?: 'image/logo/logo-my-signal.png') }}" alt="Logo {{ $application->name }}" class="app-logo">
                                    <div>
                                        <div class="fw-bold fs-5">{{ $application->name }}</div>
                                        <div class="text-secondary small">{{ $application->tagline }}</div>
                                    </div>
                                </div>
                                <div class="app-chip mb-3">{{ str_replace('_', ' ', $application->code) }}</div>
                                <p class="muted-copy mb-3">{{ $application->short_description }}</p>
                                @if(filled($application->long_description))
                                    <div class="small text-secondary">{{ $application->long_description }}</div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            <section id="features" class="mb-4 mb-lg-5">
                <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
                    <div>
                        <span class="eyebrow mb-2">Avantages</span>
                        <h2 class="section-heading mb-0">Ce que la plateforme apporte concrètement.</h2>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-lg-4"><div class="feature-card p-4 h-100"><div class="feature-art"><svg viewBox="0 0 320 180" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect width="320" height="180" fill="#f3f6f8"/><rect x="28" y="26" width="120" height="128" rx="22" fill="#ffffff"/><rect x="172" y="44" width="120" height="100" rx="24" fill="#1e5877"/><circle cx="108" cy="88" r="22" fill="#cb6f2c"/><path d="M95 88h26M108 75v26" stroke="#fff2e8" stroke-width="8" stroke-linecap="round"/><rect x="62" y="58" width="28" height="8" rx="4" fill="#c8d7e0"/><rect x="62" y="76" width="22" height="8" rx="4" fill="#c8d7e0"/><rect x="62" y="112" width="54" height="10" rx="5" fill="#c8d7e0"/><path d="M195 111l19-23 19 18 16-10 25 27h-79z" fill="#dff0f8"/><circle cx="232" cy="74" r="12" fill="#f7c27f"/></svg></div><div class="feature-icon mb-3">01</div><h3 class="h5 fw-bold">Signalement mieux qualifié</h3><p class="muted-copy mb-0">Le parcours guide l’usager sur le bon réseau, le bon compteur, la bonne localisation et les bonnes informations complémentaires.</p></div></div>
                    <div class="col-lg-4"><div class="feature-card p-4 h-100"><div class="feature-art"><svg viewBox="0 0 320 180" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect width="320" height="180" fill="#f4f7f9"/><rect x="40" y="34" width="240" height="112" rx="28" fill="#ffffff"/><rect x="60" y="58" width="92" height="16" rx="8" fill="#d2dfe7"/><rect x="60" y="90" width="200" height="12" rx="6" fill="#e3ebf0"/><rect x="60" y="114" width="126" height="12" rx="6" fill="#e3ebf0"/><circle cx="232" cy="66" r="22" fill="#cb6f2c"/><path d="M232 54v13l9 6" stroke="#fff3eb" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/><rect x="60" y="146" width="60" height="12" rx="6" fill="#1e5877" opacity=".18"/><rect x="128" y="146" width="90" height="12" rx="6" fill="#1e5877" opacity=".28"/></svg></div><div class="feature-icon mb-3">02</div><h3 class="h5 fw-bold">Suivi clair du dossier</h3><p class="muted-copy mb-0">Chaque signalement garde son statut, son temps de traitement, sa réponse institutionnelle, le TCM appliqué et, si besoin, la suite liée aux dommages.</p></div></div>
                    <div class="col-lg-4"><div class="feature-card p-4 h-100"><div class="feature-art"><svg viewBox="0 0 320 180" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect width="320" height="180" fill="#f3f7f9"/><rect x="34" y="56" width="252" height="90" rx="28" fill="#ffffff"/><circle cx="102" cy="98" r="28" fill="#1e5877"/><circle cx="160" cy="84" r="24" fill="#cb6f2c"/><circle cx="216" cy="102" r="26" fill="#dfeaf0"/><rect x="78" y="128" width="154" height="12" rx="6" fill="#d2dfe7"/><path d="M94 96h16M102 88v16" stroke="#eaf6fb" stroke-width="7" stroke-linecap="round"/><path d="M152 82h16" stroke="#fff5ec" stroke-width="7" stroke-linecap="round"/></svg></div><div class="feature-icon mb-3">03</div><h3 class="h5 fw-bold">Usage familial et terrain</h3><p class="muted-copy mb-0">Le Gonhi permet de partager un compteur, d’inviter des proches et d’organiser les usages au sein d’un même cadre familial.</p></div></div>
                    <div class="col-lg-4"><div class="feature-card p-4 h-100"><div class="feature-art"><svg viewBox="0 0 320 180" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect width="320" height="180" fill="#f5f8fa"/><rect x="52" y="28" width="92" height="124" rx="24" fill="#15364d"/><rect x="68" y="48" width="60" height="72" rx="14" fill="#eff6f9"/><circle cx="98" cy="136" r="7" fill="#7ea0b5"/><rect x="168" y="44" width="102" height="92" rx="24" fill="#ffffff"/><path d="M210 66a26 26 0 100 52 26 26 0 000-52zm0 10a16 16 0 110 32 16 16 0 010-32z" fill="#cb6f2c"/><circle cx="242" cy="112" r="12" fill="#1e5877"/><path d="M242 106v12M236 112h12" stroke="#e6f4fb" stroke-width="6" stroke-linecap="round"/></svg></div><div class="feature-icon mb-3">04</div><h3 class="h5 fw-bold">Preuves plus exploitables</h3><p class="muted-copy mb-0">La géolocalisation, la photo et les champs demandés à l’usager rendent le dossier plus solide pour les équipes.</p></div></div>
                    <div class="col-lg-4"><div class="feature-card p-4 h-100"><div class="feature-art"><svg viewBox="0 0 320 180" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect width="320" height="180" fill="#f4f7fa"/><rect x="44" y="40" width="232" height="102" rx="28" fill="#ffffff"/><path d="M82 92l28 28 38-48" stroke="#1f7a4f" stroke-width="12" stroke-linecap="round" stroke-linejoin="round"/><rect x="150" y="64" width="92" height="12" rx="6" fill="#c9d7df"/><rect x="150" y="88" width="74" height="12" rx="6" fill="#d8e4ea"/><rect x="150" y="112" width="48" height="12" rx="6" fill="#d8e4ea"/><circle cx="252" cy="62" r="18" fill="#cb6f2c"/><path d="M252 54v16M244 62h16" stroke="#fff3eb" stroke-width="6" stroke-linecap="round"/></svg></div><div class="feature-icon mb-3">05</div><h3 class="h5 fw-bold">Résolution vérifiable</h3><p class="muted-copy mb-0">L’usager confirme la résolution, voit si le TCM a été respecté et peut déclarer un dommage si nécessaire.</p></div></div>
                    <div class="col-lg-4"><div class="feature-card p-4 h-100"><div class="feature-art"><svg viewBox="0 0 320 180" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect width="320" height="180" fill="#f5f8fa"/><rect x="56" y="34" width="208" height="114" rx="28" fill="#ffffff"/><rect x="82" y="58" width="156" height="16" rx="8" fill="#d7e3ea"/><rect x="82" y="88" width="120" height="12" rx="6" fill="#e5edf1"/><rect x="82" y="112" width="98" height="12" rx="6" fill="#e5edf1"/><circle cx="220" cy="108" r="24" fill="#1e5877"/><path d="M210 108l7 7 13-16" stroke="#e9f6fc" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/><circle cx="98" cy="130" r="8" fill="#cb6f2c"/></svg></div><div class="feature-icon mb-3">06</div><h3 class="h5 fw-bold">Mémoire de service</h3><p class="muted-copy mb-0">Les reçus, historiques et interactions restent accessibles pour mieux comprendre ce qui s’est passé sur chaque dossier.</p></div></div>
                </div>
            </section>

            <section class="mb-4 mb-lg-5">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="proof-card h-100">
                            <span class="eyebrow mb-3">Importance métier</span>
                            <h2 class="section-heading">Pourquoi une bonne trace change la suite du traitement.</h2>
                            <p class="muted-copy mb-4">
                                Une alerte vague ralentit. Un signalement structuré permet d’intervenir plus vite, d’éviter les confusions et de mieux documenter le contexte si le dossier doit être revu ou approfondi.
                            </p>
                            <div class="fact-grid">
                                <div class="fact-tile">
                                    <div class="fact-kicker">Clarté</div>
                                    <div class="fact-value">+ lisible</div>
                                    <div class="muted-copy mb-0">Le signalement est compréhensible dès la première lecture.</div>
                                </div>
                                <div class="fact-tile">
                                    <div class="fact-kicker">Traçabilité</div>
                                    <div class="fact-value">+ suivie</div>
                                    <div class="muted-copy mb-0">Chaque étape du cycle de vie du dossier reste consultable.</div>
                                </div>
                                <div class="fact-tile">
                                    <div class="fact-kicker">Coordination</div>
                                    <div class="fact-value">+ rapide</div>
                                    <div class="muted-copy mb-0">Les équipes terrain partent avec de meilleures informations.</div>
                                </div>
                                <div class="fact-tile">
                                    <div class="fact-kicker">Confiance</div>
                                    <div class="fact-value">+ forte</div>
                                    <div class="muted-copy mb-0">L’usager voit ce qui bouge réellement sur son signalement.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="proof-card h-100">
                            <div class="section-visual mb-4" style="min-height: 260px;">
                                <svg viewBox="0 0 560 320" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <rect width="560" height="320" fill="#f6f8fa"/>
                                    <circle cx="448" cy="86" r="68" fill="#dfeaf0"/>
                                    <rect x="72" y="58" width="416" height="220" rx="34" fill="#ffffff"/>
                                    <rect x="104" y="96" width="146" height="20" rx="10" fill="#d6e2e9"/>
                                    <rect x="104" y="134" width="112" height="12" rx="6" fill="#e5edf1"/>
                                    <rect x="104" y="160" width="198" height="12" rx="6" fill="#e5edf1"/>
                                    <rect x="104" y="212" width="340" height="18" rx="9" fill="#edf3f6"/>
                                    <rect x="104" y="246" width="288" height="18" rx="9" fill="#edf3f6"/>
                                    <rect x="332" y="110" width="116" height="110" rx="24" fill="#15364d"/>
                                    <path d="M365 168l20 20 32-40" stroke="#dff4ff" stroke-width="14" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="388" cy="148" r="20" fill="#cb6f2c"/>
                                    <path d="M388 138v20M378 148h20" stroke="#fff4eb" stroke-width="7" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <span class="eyebrow mb-3">Ce que vous pouvez faire</span>
                            <h2 class="section-heading">Un espace public pensé pour l’action, pas seulement pour l’alerte.</h2>
                            <div class="vstack gap-3">
                                <div class="mini-card">
                                    <div class="fw-bold mb-2">Gérer vos compteurs</div>
                                    <div class="muted-copy mb-0">Ajoutez vos compteurs, définissez le principal et reliez-les à la bonne commune et au bon réseau.</div>
                                </div>
                                <div class="mini-card">
                                    <div class="fw-bold mb-2">Déclarer et suivre vos signalements</div>
                                    <div class="muted-copy mb-0">Voyez l’état d’avancement, la réponse institutionnelle, la résolution et le respect du TCM.</div>
                                </div>
                                <div class="mini-card">
                                    <div class="fw-bold mb-2">Retrouver votre historique</div>
                                    <div class="muted-copy mb-0">Consultez vos paiements, téléchargez vos reçus et gardez une mémoire claire de vos dossiers.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="parcours" class="mb-4 mb-lg-5">
                <div class="row g-4 align-items-stretch">
                    <div class="col-lg-6">
                        <div class="timeline-card h-100">
                            <span class="eyebrow mb-3">Parcours usager</span>
                            <h2 class="section-heading">Un chemin pensé pour rester simple, même quand le dossier est sensible.</h2>
                            <div class="timeline-step">
                                <div class="timeline-badge">1</div>
                                <div>
                                    <div class="fw-bold mb-1">Créer son espace</div>
                                    <div class="muted-copy mb-0">Le compte public permet de centraliser identité, compteurs, Gonhi et historique personnel.</div>
                                </div>
                            </div>
                            <div class="timeline-step">
                                <div class="timeline-badge">2</div>
                                <div>
                                    <div class="fw-bold mb-1">Déclarer avec précision</div>
                                    <div class="muted-copy mb-0">Le parcours vous guide sur le réseau, le compteur, le type de signal, l’adresse et les preuves utiles.</div>
                                </div>
                            </div>
                            <div class="timeline-step">
                                <div class="timeline-badge">3</div>
                                <div>
                                    <div class="fw-bold mb-1">Suivre jusqu’à l’issue</div>
                                    <div class="muted-copy mb-0">Vous consultez la réponse, la résolution, le respect du TCM et, si besoin, la gestion du dommage.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="story-card h-100">
                            <div class="section-visual mb-4" style="min-height: 220px;">
                                <svg viewBox="0 0 560 240" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <rect width="560" height="240" fill="#f5f8fa"/>
                                    <rect x="54" y="74" width="118" height="92" rx="24" fill="#ffffff"/>
                                    <rect x="220" y="52" width="118" height="114" rx="24" fill="#ffffff"/>
                                    <rect x="386" y="86" width="118" height="80" rx="24" fill="#ffffff"/>
                                    <circle cx="112" cy="120" r="24" fill="#cb6f2c"/>
                                    <path d="M100 120h24M112 108v24" stroke="#fff3eb" stroke-width="7" stroke-linecap="round"/>
                                    <circle cx="278" cy="108" r="24" fill="#1e5877"/>
                                    <path d="M265 108h11l8 8 15-19" stroke="#e8f6fc" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/>
                                    <circle cx="444" cy="126" r="24" fill="#15364d"/>
                                    <path d="M434 126l7 7 13-15" stroke="#def2fb" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M172 120h48M338 120h48" stroke="#c9d7df" stroke-width="8" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <span class="eyebrow mb-3">Pourquoi cela compte</span>
                            <h2 class="section-heading">Un bon signalement ne vaut pas par son coût, mais par la qualité de service qu’il déclenche.</h2>
                            <div class="mission-point">
                                <div class="fw-bold mb-2">Pour l’usager</div>
                                <div class="muted-copy mb-0">Moins d’incertitude, plus de visibilité et une meilleure capacité à prouver ce qui a été signalé.</div>
                            </div>
                            <div class="mission-point">
                                <div class="fw-bold mb-2">Pour l’institution</div>
                                <div class="muted-copy mb-0">Des dossiers plus propres, mieux qualifiés et plus faciles à prioriser sur le terrain.</div>
                            </div>
                            <div class="mission-point">
                                <div class="fw-bold mb-2">Pour la qualité de service</div>
                                <div class="muted-copy mb-0">Davantage de traçabilité, de discipline dans le traitement et de confiance dans la relation avec le public.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="access" class="auth-panel p-4 p-lg-5">
                <div class="row g-4 align-items-start">
                    <div class="col-lg-5">
                        <span class="eyebrow mb-3">Accès public</span>
                        <h2 class="display-6 fw-bold mb-3">Un seul compte pour déclarer, suivre et documenter vos dossiers.</h2>
                        <p class="muted-copy mb-4">Créez votre compte, vérifiez votre numéro puis accédez à votre espace personnel pour gérer vos compteurs, votre Gonhi, vos signalements et leurs suites.</p>
                        <div class="section-visual mb-4" style="min-height: 250px;">
                            <svg viewBox="0 0 520 280" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <rect width="520" height="280" fill="#f5f8fa"/>
                                <rect x="48" y="40" width="170" height="200" rx="34" fill="#15364d"/>
                                <rect x="68" y="64" width="130" height="132" rx="24" fill="#eef6f9"/>
                                <rect x="88" y="86" width="90" height="14" rx="7" fill="#d2dee6"/>
                                <rect x="88" y="114" width="68" height="12" rx="6" fill="#d2dee6"/>
                                <rect x="88" y="142" width="84" height="12" rx="6" fill="#d2dee6"/>
                                <circle cx="133" cy="214" r="9" fill="#7f9eb1"/>
                                <rect x="258" y="58" width="214" height="164" rx="30" fill="#ffffff"/>
                                <rect x="286" y="88" width="124" height="16" rx="8" fill="#d5e2e9"/>
                                <rect x="286" y="120" width="96" height="12" rx="6" fill="#e4edf1"/>
                                <rect x="286" y="146" width="146" height="12" rx="6" fill="#e4edf1"/>
                                <circle cx="412" cy="156" r="26" fill="#cb6f2c"/>
                                <path d="M400 156h24M412 144v24" stroke="#fff3eb" stroke-width="7" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div class="mini-card">
                            <div class="fw-bold mb-2">Votre prochaine étape</div>
                            <div class="muted-copy mb-0">Créer votre compte puis accéder au dashboard public pour gérer compteurs, Gonhi familial, signalements, paiements et dommages.</div>
                        </div>
                        <div class="access-showcase">
                            <div class="access-tile">
                                <div class="fw-bold mb-2">Compte personnel</div>
                                <div class="muted-copy mb-0">Connexion sécurisée, profil à jour, indicatif et contact WhatsApp si besoin.</div>
                            </div>
                            <div class="access-tile">
                                <div class="fw-bold mb-2">Espace vivant</div>
                                <div class="muted-copy mb-0">Compteurs, Gonhi, signalements, historique et reçus dans une seule interface.</div>
                            </div>
                        </div>
                        <div class="text-secondary small mt-3">En environnement local, le code OTP de test reste <strong>1234</strong>.</div>
                    </div>
                    <div class="col-lg-7">
                        <ul class="nav auth-switch nav-pills gap-2 mb-4">
                            <li class="nav-item"><button class="nav-link active" type="button" data-auth-tab="login">Se connecter</button></li>
                            <li class="nav-item"><button class="nav-link" type="button" data-auth-tab="register">Créer un compte</button></li>
                        </ul>

                        <div id="loginTab" class="auth-tab-pane">
                            <form id="loginForm" class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Numéro</label>
                                    <div class="input-group">
                                        <select class="form-select flex-grow-0" name="phone_dial_code" data-dial-code-select style="width: 132px; max-width: 132px; min-width: 132px;"></select>
                                        <input class="form-control" name="phone_local" placeholder="0700000000" required>
                                    </div>
                                    <input type="hidden" name="phone">
                                </div>
                                <div class="col-md-6"><label class="form-label fw-semibold">Mot de passe</label><input class="form-control" type="password" name="password" required></div>
                                <div class="col-12"><button class="btn btn-premium px-4" type="submit">Se connecter</button></div>
                            </form>
                        </div>

                        <div id="registerTab" class="auth-tab-pane hidden">
                            <div class="row g-4">
                                <div class="col-12">
                                    <ul class="nav register-switch nav-pills gap-2" id="registerSteps">
                                        <li class="nav-item"><button class="nav-link active" type="button" data-step="otp-request">1. Numéro</button></li>
                                        <li class="nav-item"><button class="nav-link" type="button" data-step="otp-verify">2. OTP</button></li>
                                        <li class="nav-item"><button class="nav-link" type="button" data-step="register">3. Compte</button></li>
                                    </ul>
                                </div>
                                <div class="col-12" id="otpRequestStep">
                                    <form id="otpRequestForm" class="row g-3">
                                        <div class="col-md-8">
                                            <label class="form-label fw-semibold">Numéro</label>
                                            <div class="input-group">
                                                <select class="form-select flex-grow-0" name="phone_dial_code" data-dial-code-select style="width: 132px; max-width: 132px; min-width: 132px;"></select>
                                                <input class="form-control" name="phone_local" placeholder="0700000000" required>
                                            </div>
                                            <input type="hidden" name="phone">
                                        </div>
                                        <div class="col-md-4 d-flex align-items-end"><button class="btn btn-premium w-100" type="submit">Recevoir OTP</button></div>
                                    </form>
                                </div>
                                <div class="col-12 hidden" id="otpVerifyStep">
                                    <form id="otpVerifyForm" class="row g-3">
                                        <div class="col-md-6"><label class="form-label fw-semibold">Numéro</label><input class="form-control" name="phone" required readonly></div>
                                        <div class="col-md-3"><label class="form-label fw-semibold">Code OTP</label><input class="form-control" name="code" placeholder="1234" required></div>
                                        <div class="col-md-3 d-flex align-items-end"><button class="btn btn-premium w-100" type="submit">Valider</button></div>
                                        <div class="col-12"><div class="text-secondary small" id="otpTestingHint"></div></div>
                                    </form>
                                </div>
                                <div class="col-12 hidden" id="registerAccountStep">
                                    <form id="registerForm" class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Personne physique</label>
                                            <select class="form-select" name="public_user_type_id" id="registerPublicUserTypeSelect" required>
                                                @foreach ($publicUserTypes as $publicUserType)
                                                    <option value="{{ $publicUserType->id }}" data-profile-kind="{{ $publicUserType->profile_kind }}" data-type-code="{{ $publicUserType->code }}">{{ $publicUserType->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6 d-flex align-items-end">
                                            <div class="w-100 mini-card py-3">
                                                <div class="fact-kicker mb-1">Tarification associee</div>
                                                <div class="fw-bold" id="registerPricingHint">{{ $publicUserTypes->first()?->pricingRule?->label ?: '-' }}</div>
                                                <div class="small text-secondary" id="registerPricingAmount">{{ $publicUserTypes->first()?->pricingRule ? number_format($publicUserTypes->first()->pricingRule->amount, 0, ',', ' ').' '.$publicUserTypes->first()->pricingRule->currency : '-' }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Prénom</label><input class="form-control" name="first_name" required></div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Nom</label><input class="form-control" name="last_name" required></div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Numéro</label><input class="form-control" name="phone" required readonly></div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Commune</label><select class="form-select" name="commune" id="registerCommuneSelect" required></select></div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Ce numéro est sur WhatsApp ?</label>
                                            <select class="form-select" name="is_whatsapp_number">
                                                <option value="0" selected>Non</option>
                                                <option value="1">Oui</option>
                                            </select>
                                        </div>
                                        <div class="col-12"><label class="form-label fw-semibold">Email</label><input class="form-control" type="email" name="email"></div>
                                        <div class="col-12 hidden" id="registerSectorFields">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Domaine d'activite</label>
                                                    <select class="form-select" name="business_sector">
                                                        <option value="">Selectionner un secteur</option>
                                                        @foreach ($businessSectors as $businessSector)
                                                            <option value="{{ $businessSector->name }}">{{ $businessSector->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 hidden" id="registerBusinessFields">
                                            <div class="row g-3">
                                                <div class="col-md-6"><label class="form-label fw-semibold">Raison sociale</label><input class="form-control" name="company_name"></div>
                                                <div class="col-md-6"><label class="form-label fw-semibold">RCCM / Immatriculation</label><input class="form-control" name="company_registration_number"></div>
                                                <div class="col-md-6"><label class="form-label fw-semibold">Identifiant fiscal</label><input class="form-control" name="tax_identifier"></div>
                                                <div class="col-12"><label class="form-label fw-semibold">Adresse de l entreprise</label><input class="form-control" name="company_address"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Mot de passe</label><input class="form-control" type="password" name="password" required></div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Confirmation</label><input class="form-control" type="password" name="password_confirmation" required></div>
                                        <div class="col-12 hidden"><input class="form-control" name="verification_token"></div>
                                        <div class="col-12"><button class="btn btn-premium w-100" type="submit">Créer mon compte</button></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div class="toast-container position-fixed top-0 end-0 p-3">
            <div id="appToast" class="toast align-items-center text-bg-dark border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body" id="appToastMessage">Action exécutée.</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        </div>

        @php
            $publicUserTypesPayload = $publicUserTypes->map(function ($type) {
                return [
                    'id' => $type->id,
                    'code' => $type->code,
                    'name' => $type->name,
                    'profile_kind' => $type->profile_kind,
                    'pricing_rule' => $type->pricingRule ? [
                        'label' => $type->pricingRule->label,
                        'amount' => $type->pricingRule->amount,
                        'currency' => $type->pricingRule->currency,
                    ] : null,
                ];
            })->values();
        @endphp
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <script>
            (() => {
                const apiBase = '/api/v1/public';
                const dashboardUrl = '{{ route('public.dashboard') }}';
                const tokenKey = 'acepen_public_token';
                const publicUserTypes = @json($publicUserTypesPayload);
                const dialCodeOptions = @json($dialCodeOptions);
                const state = { verificationToken: null, countries: [], communes: [] };
                const toast = new bootstrap.Toast(document.getElementById('appToast'));

                function showToast(message, isError = false) {
                    const toastEl = document.getElementById('appToast');
                    toastEl.classList.remove('text-bg-dark', 'text-bg-success', 'text-bg-danger');
                    toastEl.classList.add(isError ? 'text-bg-danger' : 'text-bg-success');
                    document.getElementById('appToastMessage').textContent = message;
                    toast.show();
                }

                function normalizeText(value) {
                    return String(value || '')
                        .normalize('NFD')
                        .replace(/[\u0300-\u036f]/g, '')
                        .trim()
                        .toLowerCase();
                }

                function ensurePublicSelectId(select) {
                    if (select.id) {
                        return select.id;
                    }

                    const baseId = String(select.name || 'public-select')
                        .replace(/[^a-zA-Z0-9_-]+/g, '-')
                        .replace(/^-+|-+$/g, '');

                    select.id = `${baseId || 'public-select'}-${Math.random().toString(36).slice(2, 8)}`;
                    return select.id;
                }

                function annotateRequiredFields(root = document) {
                    root.querySelectorAll('form input[required], form select[required], form textarea[required]').forEach((field) => {
                        if (field.type === 'hidden' || field.classList.contains('d-none')) {
                            return;
                        }

                        const group = field.closest('.col-12, .col-md-3, .col-md-4, .col-md-6, .col-md-8');
                        const label = group?.querySelector('label.form-label');

                        if (!label || label.querySelector('.required-star')) {
                            return;
                        }

                        const star = document.createElement('span');
                        star.className = 'required-star';
                        star.textContent = '*';
                        label.appendChild(star);
                    });
                }

                function syncPublicEnhancedSelect(select) {
                    if (select.dataset.publicEnhanced !== '1') {
                        return;
                    }

                    const input = document.getElementById(`${select.id}PublicInput`);
                    const results = document.getElementById(`${select.id}PublicResults`);

                    if (!input || !results) {
                        return;
                    }

                    const options = Array.from(select.options).map((option) => ({
                        value: option.value,
                        label: option.textContent,
                    }));

                    select.dataset.publicEnhancedOptions = JSON.stringify(options);
                    if (document.activeElement !== input) {
                        input.value = select.options[select.selectedIndex]?.textContent || '';
                    }
                    results.classList.remove('is-open');
                }

                function renderPublicEnhancedSelectOptions(select, query = '', forceOpen = false) {
                    if (select.dataset.publicEnhanced !== '1') {
                        return;
                    }

                    const results = document.getElementById(`${select.id}PublicResults`);
                    const options = JSON.parse(select.dataset.publicEnhancedOptions || '[]');
                    const normalizedQuery = normalizeText(query);
                    const selectedLabel = normalizeText(select.options[select.selectedIndex]?.textContent || '');
                    const matches = normalizedQuery
                        ? options.filter((option) => normalizeText(option.label).includes(normalizedQuery))
                        : options;
                    const hasExactMatch = options.some((option) => normalizeText(option.label) === normalizedQuery);

                    if (!results) {
                        return;
                    }

                    results.innerHTML = matches.length
                        ? matches.map((option) => `<button class="public-select-option" type="button" data-public-select-value="${option.value}" data-public-select-label="${option.label}">${option.label}</button>`).join('')
                        : '<div class="public-select-help">Aucun resultat</div>';
                    results.classList.toggle('is-open', forceOpen || normalizedQuery === '' || (!hasExactMatch && normalizedQuery !== selectedLabel));
                }

                function enhancePublicFormSelects(root = document) {
                    root.querySelectorAll('form select.form-select:not([data-dial-code-select])').forEach((select) => {
                        if (select.dataset.publicEnhanced === '1') {
                            return;
                        }

                        const selectId = ensurePublicSelectId(select);
                        const shell = document.createElement('div');
                        shell.className = 'public-select-shell';
                        shell.innerHTML = `
                            <input class="form-control public-select-input" id="${selectId}PublicInput" type="search" autocomplete="off" placeholder="Rechercher ou selectionner">
                            <button class="public-select-toggle" id="${selectId}PublicToggle" type="button" aria-label="Afficher les options"></button>
                        `;
                        const help = document.createElement('div');
                        help.className = 'public-select-help';
                        help.textContent = 'Champ de selection avec recherche.';
                        const results = document.createElement('div');
                        results.className = 'public-select-results';
                        results.id = `${selectId}PublicResults`;

                        select.parentNode.insertBefore(shell, select);
                        select.parentNode.insertBefore(help, select);
                        select.parentNode.insertBefore(results, select);
                        select.classList.add('d-none');
                        select.dataset.publicEnhanced = '1';

                        const input = document.getElementById(`${selectId}PublicInput`);
                        const toggle = document.getElementById(`${selectId}PublicToggle`);
                        const observer = new MutationObserver(() => syncPublicEnhancedSelect(select));
                        observer.observe(select, { childList: true, subtree: true });

                        input.addEventListener('focus', () => renderPublicEnhancedSelectOptions(select, input.value));
                        input.addEventListener('input', () => renderPublicEnhancedSelectOptions(select, input.value));
                        input.addEventListener('change', () => {
                            const options = JSON.parse(select.dataset.publicEnhancedOptions || '[]');
                            const exactMatch = options.find((option) => normalizeText(option.label) === normalizeText(input.value));

                            if (!exactMatch) {
                                input.value = select.options[select.selectedIndex]?.textContent || '';
                                return;
                            }

                            const previousValue = select.value;
                            select.value = exactMatch.value;
                            input.value = exactMatch.label;

                            if (String(previousValue) !== String(select.value)) {
                                select.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        });
                        input.addEventListener('blur', () => {
                            window.setTimeout(() => {
                                results.classList.remove('is-open');
                                input.value = select.options[select.selectedIndex]?.textContent || '';
                            }, 150);
                        });
                        toggle.addEventListener('mousedown', (event) => event.preventDefault());
                        toggle.addEventListener('click', () => {
                            const shouldOpen = !results.classList.contains('is-open');
                            renderPublicEnhancedSelectOptions(select, '', true);
                            results.classList.toggle('is-open', shouldOpen);
                            input.focus();
                        });
                        results.addEventListener('click', (event) => {
                            const option = event.target.closest('[data-public-select-value]');

                            if (!option) {
                                return;
                            }

                            const previousValue = select.value;
                            select.value = option.dataset.publicSelectValue;
                            input.value = option.dataset.publicSelectLabel || '';
                            results.classList.remove('is-open');

                            if (String(previousValue) !== String(select.value)) {
                                select.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        });

                        syncPublicEnhancedSelect(select);
                    });
                }

                function setLoading(form, isLoading) {
                    const button = form.querySelector('button[type="submit"]');
                    if (!button) return;
                    button.disabled = isLoading;
                    button.dataset.originalText = button.dataset.originalText || button.textContent;
                    button.textContent = isLoading ? 'Traitement...' : button.dataset.originalText;
                }

                async function apiFetch(path, options = {}) {
                    const headers = {
                        Accept: 'application/json',
                        ...(options.body ? { 'Content-Type': 'application/json' } : {}),
                    };
                    const response = await fetch(`${apiBase}${path}`, { ...options, headers });
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        const error = new Error(data.message || 'Une erreur est survenue.');
                        error.payload = data;
                        throw error;
                    }
                    return data;
                }

                function setAuthTab(mode) {
                    document.querySelectorAll('[data-auth-tab]').forEach((button) => button.classList.toggle('active', button.dataset.authTab === mode));
                    document.getElementById('loginTab').classList.toggle('hidden', mode !== 'login');
                    document.getElementById('registerTab').classList.toggle('hidden', mode !== 'register');
                    document.getElementById('access')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }

                function setRegisterStep(step) {
                    document.querySelectorAll('[data-step]').forEach((button) => button.classList.toggle('active', button.dataset.step === step));
                    document.getElementById('otpRequestStep').classList.toggle('hidden', step !== 'otp-request');
                    document.getElementById('otpVerifyStep').classList.toggle('hidden', step !== 'otp-verify');
                    document.getElementById('registerAccountStep').classList.toggle('hidden', step !== 'register');
                }

                function populateCommunes() {
                    const options = state.communes.length
                        ? state.communes.map((commune) => `<option value="${commune.name}">${commune.name}</option>`).join('')
                        : '<option value="">Aucune commune disponible</option>';
                    document.getElementById('registerCommuneSelect').innerHTML = options;
                }

                function populateDialCodeSelects() {
                    document.querySelectorAll('[data-dial-code-select]').forEach((select) => {
                        select.innerHTML = dialCodeOptions.map((option) => `<option value="${option.value}">${option.label}</option>`).join('');
                        if (!select.value) {
                            select.value = dialCodeOptions[0]?.value || '225';
                        }
                    });
                }

                function syncPublicUserTypeFields(selectId, businessFieldsContainerId, pricingLabelId = null, pricingAmountId = null, sectorFieldsContainerId = null) {
                    const select = document.getElementById(selectId);
                    const businessFieldsContainer = document.getElementById(businessFieldsContainerId);
                    const sectorFieldsContainer = sectorFieldsContainerId ? document.getElementById(sectorFieldsContainerId) : null;

                    if (!select || !businessFieldsContainer) {
                        return;
                    }

                    const selectedType = publicUserTypes.find((type) => String(type.id) === String(select.value));
                    const typeCode = String(selectedType?.code || '').toUpperCase();
                    const showBusinessFields = typeCode === 'UPE';
                    const showSectorFields = typeCode === 'UPE' || typeCode === 'UPTI';

                    businessFieldsContainer.classList.toggle('hidden', !showBusinessFields);
                    businessFieldsContainer.querySelectorAll('input, select, textarea').forEach((field) => {
                        field.disabled = !showBusinessFields;
                        field.required = showBusinessFields;
                    });

                    if (sectorFieldsContainer) {
                        sectorFieldsContainer.classList.toggle('hidden', !showSectorFields);
                        sectorFieldsContainer.querySelectorAll('input, select, textarea').forEach((field) => {
                            field.disabled = !showSectorFields;
                            field.required = showSectorFields;
                        });
                    }

                    if (pricingLabelId) {
                        document.getElementById(pricingLabelId).textContent = selectedType?.pricing_rule?.label || '-';
                    }

                    if (pricingAmountId) {
                        document.getElementById(pricingAmountId).textContent = selectedType?.pricing_rule
                            ? `${Number(selectedType.pricing_rule.amount || 0).toLocaleString('fr-FR')} ${selectedType.pricing_rule.currency || ''}`.trim()
                            : '-';
                    }
                }

                function composePhoneNumber(form) {
                    const localInput = form.querySelector('[name="phone_local"]');
                    const dialCodeSelect = form.querySelector('[name="phone_dial_code"]');
                    const hiddenPhoneInput = form.querySelector('[name="phone"]');

                    if (!localInput || !dialCodeSelect || !hiddenPhoneInput) {
                        return;
                    }

                    const local = String(localInput.value || '').replace(/\D+/g, '');
                    hiddenPhoneInput.value = local ? `${dialCodeSelect.value}${local}` : '';
                }

                async function loadReferences() {
                    populateDialCodeSelects();
                    const response = await apiFetch('/locations');
                    state.countries = response.data.countries || [];
                    state.communes = state.countries.flatMap((country) => (country.cities || []).flatMap((city) => city.communes || []));
                    populateCommunes();
                }

                document.querySelectorAll('[data-auth-tab-target]').forEach((button) => {
                    button.addEventListener('click', () => setAuthTab(button.dataset.authTabTarget));
                });

                document.querySelectorAll('[data-auth-tab]').forEach((button) => {
                    button.addEventListener('click', () => setAuthTab(button.dataset.authTab));
                });

                document.querySelectorAll('[data-step]').forEach((button) => {
                    button.addEventListener('click', () => setRegisterStep(button.dataset.step));
                });

                document.getElementById('registerPublicUserTypeSelect')?.addEventListener('change', () => {
                    syncPublicUserTypeFields('registerPublicUserTypeSelect', 'registerBusinessFields', 'registerPricingHint', 'registerPricingAmount', 'registerSectorFields');
                });

                document.getElementById('otpRequestForm').addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const form = event.currentTarget;
                    setLoading(form, true);
                    try {
                        composePhoneNumber(form);
                        const payload = Object.fromEntries(new FormData(form).entries());
                        const response = await apiFetch('/auth/request-otp', { method: 'POST', body: JSON.stringify(payload) });
                        document.querySelector('#otpVerifyForm [name="phone"]').value = payload.phone;
                        document.querySelector('#registerForm [name="phone"]').value = payload.phone;
                        document.getElementById('otpTestingHint').textContent = response.data.otp_code_for_testing ? `Code de test : ${response.data.otp_code_for_testing}` : response.message;
                        setRegisterStep('otp-verify');
                        showToast(response.message);
                    } catch (error) {
                        showToast(error.message, true);
                    } finally {
                        setLoading(form, false);
                    }
                });

                document.getElementById('otpVerifyForm').addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const form = event.currentTarget;
                    setLoading(form, true);
                    try {
                        const payload = Object.fromEntries(new FormData(form).entries());
                        const response = await apiFetch('/auth/verify-otp', { method: 'POST', body: JSON.stringify(payload) });
                        state.verificationToken = response.data.verification_token;
                        document.querySelector('#registerForm [name="verification_token"]').value = response.data.verification_token;
                        setRegisterStep('register');
                        showToast(response.message);
                    } catch (error) {
                        showToast(error.message, true);
                    } finally {
                        setLoading(form, false);
                    }
                });

                document.getElementById('registerForm').addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const form = event.currentTarget;
                    setLoading(form, true);
                    try {
                        composePhoneNumber(form);
                        const payload = Object.fromEntries(new FormData(form).entries());
                        payload.verification_token = state.verificationToken || payload.verification_token;
                        const response = await apiFetch('/auth/register', { method: 'POST', body: JSON.stringify(payload) });
                        localStorage.setItem(tokenKey, response.data.access_token);
                        window.location.href = dashboardUrl;
                    } catch (error) {
                        showToast(error.message, true);
                    } finally {
                        setLoading(form, false);
                    }
                });

                document.getElementById('loginForm').addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const form = event.currentTarget;
                    setLoading(form, true);
                    try {
                        composePhoneNumber(form);
                        const payload = Object.fromEntries(new FormData(form).entries());
                        const response = await apiFetch('/auth/login', { method: 'POST', body: JSON.stringify(payload) });
                        localStorage.setItem(tokenKey, response.data.access_token);
                        window.location.href = dashboardUrl;
                    } catch (error) {
                        showToast(error.message, true);
                    } finally {
                        setLoading(form, false);
                    }
                });

                populateDialCodeSelects();
                enhancePublicFormSelects();
                annotateRequiredFields();
                syncPublicUserTypeFields('registerPublicUserTypeSelect', 'registerBusinessFields', 'registerPricingHint', 'registerPricingAmount', 'registerSectorFields');
                loadReferences().catch(() => {});
            })();
        </script>
    </body>
</html>
