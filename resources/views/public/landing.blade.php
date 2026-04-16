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
                --mysignal-navy: #183447;
                --mysignal-ocean: #256f8f;
                --mysignal-copper: #ff0068;
                --mysignal-amber: #ffa117;
                --mysignal-mint: #5bebaf;
                --mysignal-ink: #183447;
                --mysignal-muted: #667786;
                --mysignal-card: rgba(255, 255, 255, 0.92);
                --mysignal-soft: #f4f8fb;
            }
            body {
                font-family: "Manrope", sans-serif;
                color: var(--mysignal-ink);
                background:
                    radial-gradient(circle at top left, rgba(255, 0, 104, 0.10), transparent 24%),
                    radial-gradient(circle at 90% 0%, rgba(91, 235, 175, 0.14), transparent 28%),
                    linear-gradient(180deg, #f7fbff 0%, #eef6fa 52%, #ffffff 100%);
            }
            .shell { max-width: 1320px; }
            .glass-nav,.hero-panel,.feature-card,.auth-panel,.story-card,.proof-card,.impact-card,.mission-band,.timeline-card,.app-card { background: var(--mysignal-card); border: 1px solid rgba(24,52,71,.08); box-shadow: 0 28px 80px rgba(15,39,56,.08); backdrop-filter: blur(18px); }
            .glass-nav { border-radius: 22px; }
            .hero-panel,.feature-card,.auth-panel,.story-card,.proof-card,.impact-card,.mission-band,.timeline-card,.app-card { border-radius: 30px; }
            .brand-logo { width: 54px; height: 54px; object-fit: contain; border-radius: 18px; background: white; padding: .35rem; box-shadow: 0 14px 28px rgba(12,36,53,.08); }
            .eyebrow { display: inline-flex; align-items:center; gap:.55rem; padding:.55rem 1rem; border-radius:999px; background: rgba(30,88,119,.08); color: var(--mysignal-ocean); font-size:.82rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; }
            .hero-title { font-size: 4.7rem; font-weight: 800; line-height: .96; letter-spacing: 0; }
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
                color: #fff;
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
                background: radial-gradient(circle, rgba(255,0,104,.25), transparent 62%);
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
            .hero-phone-wrap {
                position: relative;
                display: grid;
                place-items: center;
                min-height: 430px;
                isolation: isolate;
            }
            .hero-phone-wrap::before,
            .hero-phone-wrap::after {
                content: "";
                position: absolute;
                border-radius: 999px;
                filter: blur(1px);
                z-index: -1;
            }
            .hero-phone-wrap::before {
                width: 230px;
                height: 230px;
                background: rgba(255, 161, 23, 0.22);
                top: 18px;
                right: 14px;
            }
            .hero-phone-wrap::after {
                width: 180px;
                height: 180px;
                background: rgba(91, 235, 175, 0.2);
                left: 10px;
                bottom: 32px;
            }
            .phone-shell {
                width: min(100%, 245px);
                border-radius: 34px;
                padding: .75rem;
                background: #102a3a;
                box-shadow: 0 34px 70px rgba(9, 30, 44, .32);
            }
            .phone-screen {
                min-height: 390px;
                border-radius: 26px;
                background: #fff;
                padding: 1rem;
                color: var(--mysignal-ink);
                display: flex;
                flex-direction: column;
                gap: .85rem;
            }
            .phone-top {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: .75rem;
            }
            .phone-logo {
                width: 38px;
                height: 38px;
                object-fit: contain;
                border-radius: 12px;
                background: #fff;
                box-shadow: 0 10px 22px rgba(24,52,71,.12);
            }
            .phone-status {
                border-radius: 999px;
                padding: .35rem .65rem;
                background: rgba(91,235,175,.22);
                color: #13744f;
                font-size: .72rem;
                font-weight: 800;
            }
            .phone-card {
                border-radius: 20px;
                padding: .9rem;
                background: linear-gradient(145deg, var(--mysignal-navy), var(--mysignal-ocean));
                color: #fff;
            }
            .phone-card small,
            .phone-item small {
                display: block;
                color: rgba(255,255,255,.7);
                font-weight: 700;
                margin-bottom: .25rem;
            }
            .phone-card strong {
                display: block;
                font-size: 1.05rem;
                line-height: 1.25;
            }
            .phone-item {
                border-radius: 18px;
                padding: .8rem;
                background: #f4f8fb;
                border: 1px solid rgba(24,52,71,.07);
            }
            .phone-item small {
                color: var(--mysignal-muted);
            }
            .phone-progress {
                height: 8px;
                border-radius: 999px;
                background: #dce9ef;
                overflow: hidden;
            }
            .phone-progress span {
                display: block;
                width: 68%;
                height: 100%;
                border-radius: inherit;
                background: linear-gradient(90deg, var(--mysignal-copper), var(--mysignal-amber));
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
                background: radial-gradient(circle, rgba(255,0,104,.12), transparent 68%);
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
            .btn-premium { border:none; min-height:3.25rem; border-radius:18px; background: linear-gradient(135deg, var(--mysignal-copper), var(--mysignal-amber)); color:white; font-weight:800; box-shadow: 0 18px 34px rgba(255,0,104,.18); }
            .btn-premium:hover { color: white; transform: translateY(-1px); }
            .btn-ghost-premium { min-height:3.25rem; border-radius:18px; background: rgba(12,36,53,.05); color: var(--mysignal-navy); border:1px solid rgba(12,36,53,.08); font-weight:800; }
            .auth-switch .nav-link,.register-switch .nav-link { border-radius:999px; color: var(--mysignal-muted); font-weight:800; padding:.9rem 1rem; }
            .auth-switch .nav-link.active,.register-switch .nav-link.active { background: linear-gradient(135deg, var(--mysignal-copper), var(--mysignal-amber)); color:white; box-shadow: 0 16px 28px rgba(255,0,104,.18); }
            .form-control,.form-select { border-radius:18px; border-color: rgba(24,52,71,.11); min-height:3.25rem; padding-inline:1rem; }
            .form-control:focus,.form-select:focus { border-color: rgba(255,0,104,.45); box-shadow: 0 0 0 .25rem rgba(255,0,104,.10); }
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
                    linear-gradient(145deg, rgba(24,52,71,.98), rgba(37,111,143,.94)),
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
                font-size: 2.45rem;
                font-weight: 800;
                letter-spacing: 0;
                margin-bottom: .65rem;
            }
            .mission-band {
                padding: 1.4rem;
                background:
                    radial-gradient(circle at top left, rgba(255,0,104,.08), transparent 34%),
                    linear-gradient(180deg, rgba(255,255,255,.95), rgba(244,248,251,.94));
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
                background: linear-gradient(135deg, var(--mysignal-copper), var(--mysignal-amber));
                color: white;
                font-weight: 800;
                flex-shrink: 0;
                box-shadow: 0 14px 28px rgba(255,0,104,.16);
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
                background: rgba(255,0,104,.10);
                color: var(--mysignal-copper);
                font-weight: 800;
                flex-shrink: 0;
            }
            .hidden { display:none !important; }
            .toast-container { z-index:1090; }
            .apdash-page {
                --app-purple: #6f2cff;
                --app-purple-dark: #4c16d8;
                --app-purple-soft: #f3edff;
                --app-pink: #ff4fa3;
                --app-blue: #20b8ff;
                --app-ink: #1e2240;
                --app-muted: #7b8094;
                color: var(--app-ink);
            }
            .apdash-page .glass-nav {
                border-radius: 0;
                box-shadow: none;
                background: linear-gradient(135deg, var(--app-purple-dark), var(--app-purple));
                border: 0;
                color: #fff;
            }
            .apdash-page .glass-nav .nav-link,
            .apdash-page .navbar-brand,
            .apdash-page .navbar-brand small {
                color: #fff !important;
            }
            .apdash-page .btn-premium {
                background: #fff;
                color: var(--app-purple);
                box-shadow: none;
                border-radius: 4px;
            }
            .apdash-page .btn-ghost-premium {
                background: rgba(255,255,255,.12);
                color: #fff;
                border: 1px solid rgba(255,255,255,.28);
                border-radius: 4px;
            }
            .app-hero {
                min-height: 650px;
                padding: 6rem 0 8rem;
                color: #fff;
                background:
                    radial-gradient(circle at 18% 20%, rgba(255,255,255,.16), transparent 18%),
                    linear-gradient(135deg, var(--app-purple-dark), var(--app-purple));
                position: relative;
                overflow: hidden;
            }
            .app-hero::after {
                content: "";
                position: absolute;
                left: -5%;
                right: -5%;
                bottom: -70px;
                height: 150px;
                background: #fff;
                transform: skewY(-4deg);
                transform-origin: left top;
            }
            .app-hero h1 {
                font-size: 3.35rem;
                font-weight: 800;
                line-height: 1.12;
                letter-spacing: 0;
            }
            .app-hero p {
                color: rgba(255,255,255,.78);
                line-height: 1.8;
            }
            .hero-mockup {
                position: relative;
                z-index: 2;
                display: grid;
                place-items: center;
            }
            .template-phone {
                width: 245px;
                border-radius: 34px;
                padding: .7rem;
                background: #16142a;
                box-shadow: 0 32px 70px rgba(20,11,70,.35);
            }
            .template-screen {
                min-height: 430px;
                border-radius: 27px;
                background: #fff;
                padding: 1rem;
                color: var(--app-ink);
            }
            .screen-bar {
                height: 9px;
                width: 54px;
                border-radius: 999px;
                background: #d9d6e8;
                margin: 0 auto 1rem;
            }
            .screen-card {
                border-radius: 16px;
                background: linear-gradient(135deg, var(--app-purple), var(--app-pink));
                color: #fff;
                padding: .9rem;
                margin-bottom: .8rem;
            }
            .screen-row {
                border-radius: 14px;
                background: #f6f4ff;
                padding: .75rem;
                margin-bottom: .65rem;
            }
            .screen-line {
                height: 9px;
                border-radius: 999px;
                background: #ddd8f4;
                margin-bottom: .45rem;
            }
            .screen-line.short { width: 62%; }
            .section-pad {
                padding: 5.5rem 0;
            }
            .section-kicker {
                color: var(--app-purple);
                font-weight: 800;
                text-transform: uppercase;
                font-size: .78rem;
                margin-bottom: .7rem;
            }
            .section-title {
                font-size: 2.35rem;
                line-height: 1.22;
                font-weight: 800;
                color: var(--app-ink);
                margin-bottom: 1rem;
            }
            .section-copy {
                color: var(--app-muted);
                line-height: 1.8;
            }
            .icon-card,
            .pricing-box,
            .review-box,
            .blog-box,
            .team-box {
                background: #fff;
                border: 1px solid #eeeafb;
                border-radius: 8px;
                box-shadow: 0 14px 40px rgba(42,26,92,.07);
                padding: 1.5rem;
            }
            .app-icon {
                width: 58px;
                height: 58px;
                border-radius: 50%;
                display: grid;
                place-items: center;
                background: var(--app-purple-soft);
                color: var(--app-purple);
                font-weight: 800;
                margin-bottom: 1rem;
            }
            .purple-band {
                color: #fff;
                background:
                    linear-gradient(135deg, var(--app-purple-dark), var(--app-purple));
                position: relative;
                overflow: hidden;
            }
            .purple-band .section-title,
            .purple-band .section-copy,
            .purple-band .section-kicker {
                color: #fff;
            }
            .app-shot {
                border-radius: 28px;
                padding: .7rem;
                background: #17152a;
                box-shadow: 0 22px 45px rgba(38,17,98,.2);
            }
            .app-shot-screen {
                min-height: 300px;
                border-radius: 22px;
                background: #fff;
                padding: 1rem;
            }
            .process-dot {
                width: 68px;
                height: 68px;
                border-radius: 50%;
                display: grid;
                place-items: center;
                background: linear-gradient(135deg, var(--app-purple), var(--app-pink));
                color: #fff;
                font-weight: 800;
                margin: 0 auto 1rem;
            }
            .price {
                font-size: 2.4rem;
                color: var(--app-purple);
                font-weight: 800;
            }
            .client-logo {
                min-height: 72px;
                border-radius: 8px;
                border: 1px solid #eeeafb;
                display: grid;
                place-items: center;
                color: var(--app-purple);
                font-weight: 800;
                background: #fff;
            }
            .footer-band {
                background: #201148;
                color: #d8d2ee;
                padding: 4rem 0 2rem;
            }
            .footer-band h5 {
                color: #fff;
            }
            .apdash-page .auth-panel .btn-premium,
            .apdash-page .pricing-box .btn-premium {
                background: linear-gradient(135deg, var(--app-purple), var(--app-pink));
                color: #fff;
            }
            .apdash-page .auth-panel .btn-ghost-premium,
            .apdash-page .pricing-box .btn-ghost-premium {
                background: var(--app-purple-soft);
                color: var(--app-purple);
                border-color: #e5dcff;
            }
            @media (max-width: 991.98px) {
                .hero-title {
                    font-size: 3rem;
                }
                .app-hero h1 {
                    font-size: 2.45rem;
                }
                .section-heading {
                    font-size: 2rem;
                }
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
    <body class="apdash-page">
        <div class="container shell py-3 py-lg-4">
            <nav class="navbar navbar-expand-lg glass-nav px-3 px-lg-4 py-3">
                <a class="navbar-brand d-flex align-items-center gap-3 fw-bold" href="{{ route('public.landing') }}">
                    <img src="{{ asset('image/logo/logo-my-signal.png') }}" alt="Logo My Signal" class="brand-logo">
                    <span>
                        <span class="d-block lh-1">MySignal</span>
                        <small class="fw-semibold">Consumer Protection App</small>
                    </span>
                </a>
                <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#publicNavbar">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="publicNavbar">
                    <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                        <li class="nav-item"><a class="nav-link" href="#landing">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                        <li class="nav-item"><a class="nav-link" href="#screens">Screens</a></li>
                        <li class="nav-item"><a class="nav-link" href="#pricing">Pricing</a></li>
                        <li class="nav-item"><a class="nav-link" href="#reviews">Reviews</a></li>
                        <li class="nav-item ms-lg-2"><button class="btn btn-ghost-premium px-4" data-auth-tab-target="login">Login</button></li>
                        <li class="nav-item"><button class="btn btn-premium px-4" data-auth-tab-target="register">Get Started</button></li>
                    </ul>
                </div>
            </nav>
        </div>

        <main class="apdash-page">
            <section id="landing" class="app-hero">
                <div class="container shell position-relative" style="z-index: 2;">
                    <div class="row align-items-center g-5">
                        <div class="col-lg-6">
                            <div class="section-kicker text-white">MySignal Mobile Platform</div>
                            <h1>Protect your consumer rights with a smarter signal.</h1>
                            <p class="mb-4">Declare incidents, follow every action, keep your evidence, manage your subscription and show your member card from one clean public dashboard.</p>
                            <div class="d-flex flex-wrap gap-3">
                                <button class="btn btn-premium px-4" data-auth-tab-target="register">Create Account</button>
                                <a class="btn btn-ghost-premium px-4" href="#features">Learn More</a>
                            </div>
                        </div>
                        <div class="col-lg-6 hero-mockup">
                            <div class="template-phone">
                                <div class="template-screen">
                                    <div class="screen-bar"></div>
                                    <div class="screen-card">
                                        <div class="small opacity-75 mb-1">Active subscription</div>
                                        <div class="fw-bold">Member card ready</div>
                                    </div>
                                    <div class="screen-row"><div class="screen-line"></div><div class="screen-line short"></div></div>
                                    <div class="screen-row"><div class="screen-line"></div><div class="screen-line short"></div></div>
                                    <div class="screen-card">
                                        <div class="small opacity-75 mb-1">Signalement</div>
                                        <div class="fw-bold">Resolution tracked</div>
                                    </div>
                                    <div class="screen-row"><div class="screen-line"></div><div class="screen-line short"></div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="section-pad bg-white">
                <div class="container shell">
                    <div class="row g-4 text-center">
                        <div class="col-md-3"><div class="icon-card h-100"><div class="app-icon mx-auto">UP</div><h5 class="fw-bold">Public Users</h5><p class="section-copy mb-0">A simple account for signals, payments and history.</p></div></div>
                        <div class="col-md-3"><div class="icon-card h-100"><div class="app-icon mx-auto">TCM</div><h5 class="fw-bold">Tracked Delays</h5><p class="section-copy mb-0">Clear treatment timing and visible progress.</p></div></div>
                        <div class="col-md-3"><div class="icon-card h-100"><div class="app-icon mx-auto">QR</div><h5 class="fw-bold">Wallet Card</h5><p class="section-copy mb-0">Virtual member card for active subscribers.</p></div></div>
                        <div class="col-md-3"><div class="icon-card h-100"><div class="app-icon mx-auto">REX</div><h5 class="fw-bold">Feedback</h5><p class="section-copy mb-0">Experience return after resolution or case closure.</p></div></div>
                    </div>
                </div>
            </section>

            <section id="features" class="section-pad">
                <div class="container shell">
                    <div class="text-center mb-5">
                        <div class="section-kicker">Awesome App Features</div>
                        <h2 class="section-title">Everything needed to turn a complaint into a useful case.</h2>
                        <p class="section-copy mx-auto" style="max-width: 680px;">MySignal keeps the same app-style experience as the reference: fast, visual, structured, and centered around the public user's journey.</p>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-4"><div class="icon-card h-100"><div class="app-icon">01</div><h5 class="fw-bold">Precise reports</h5><p class="section-copy mb-0">Network, meter, commune, location and evidence are captured in a guided flow.</p></div></div>
                        <div class="col-md-4"><div class="icon-card h-100"><div class="app-icon">02</div><h5 class="fw-bold">Subscription access</h5><p class="section-copy mb-0">Only active subscribers can access protected reporting workflows.</p></div></div>
                        <div class="col-md-4"><div class="icon-card h-100"><div class="app-icon">03</div><h5 class="fw-bold">Case history</h5><p class="section-copy mb-0">Payments, reports, REX and repair cases remain available in one dashboard.</p></div></div>
                    </div>
                </div>
            </section>

            <section class="section-pad bg-white">
                <div class="container shell">
                    <div class="row align-items-center g-5">
                        <div class="col-lg-6">
                            <div class="template-phone mx-auto">
                                <div class="template-screen">
                                    <div class="screen-bar"></div>
                                    <div class="screen-card"><div class="fw-bold">Create a signal</div><small>Meter, network, commune</small></div>
                                    <div class="screen-row"><div class="screen-line"></div><div class="screen-line short"></div></div>
                                    <div class="screen-row"><div class="screen-line"></div><div class="screen-line short"></div></div>
                                    <div class="screen-row"><div class="screen-line"></div><div class="screen-line short"></div></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="section-kicker">Interface Screenshots</div>
                            <h2 class="section-title">A dashboard built for clarity, not confusion.</h2>
                            <p class="section-copy">The public user sees reports, subscriptions, wallet card, receipts, damage declarations and feedback without switching tools.</p>
                            <div class="row g-3 mt-3">
                                <div class="col-sm-6"><div class="icon-card"><h6 class="fw-bold">Report status</h6><p class="section-copy mb-0">Follow submitted, processing and resolved cases.</p></div></div>
                                <div class="col-sm-6"><div class="icon-card"><h6 class="fw-bold">Member card</h6><p class="section-copy mb-0">Show QR benefits when subscription is active.</p></div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="purple-band section-pad">
                <div class="container shell text-center">
                    <div class="section-kicker">Download App</div>
                    <h2 class="section-title">Start using MySignal today.</h2>
                    <p class="section-copy mx-auto" style="max-width: 650px;">Create your public account, activate the annual subscription and keep every consumer protection action inside your dashboard.</p>
                    <div class="d-flex justify-content-center gap-3 flex-wrap mt-4">
                        <button class="btn btn-premium px-4" data-auth-tab-target="register">Create Account</button>
                        <button class="btn btn-ghost-premium px-4" data-auth-tab-target="login">Login</button>
                    </div>
                </div>
            </section>

            <section id="screens" class="section-pad bg-white">
                <div class="container shell">
                    <div class="text-center mb-5">
                        <div class="section-kicker">App Screenshots</div>
                        <h2 class="section-title">Core screens from the public journey.</h2>
                    </div>
                    <div class="row g-4 align-items-end">
                        @foreach(['Report', 'Wallet', 'History', 'Feedback'] as $screen)
                            <div class="col-md-3"><div class="app-shot"><div class="app-shot-screen"><div class="screen-card"><div class="fw-bold">{{ $screen }}</div></div><div class="screen-row"><div class="screen-line"></div><div class="screen-line short"></div></div><div class="screen-row"><div class="screen-line"></div><div class="screen-line short"></div></div></div></div></div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="parcours" class="section-pad">
                <div class="container shell">
                    <div class="text-center mb-5">
                        <div class="section-kicker">Our Work Process</div>
                        <h2 class="section-title">From signal to resolution.</h2>
                    </div>
                    <div class="row g-4 text-center">
                        <div class="col-md-4"><div class="process-dot">1</div><h5 class="fw-bold">Declare</h5><p class="section-copy">Send a structured incident with location and details.</p></div>
                        <div class="col-md-4"><div class="process-dot">2</div><h5 class="fw-bold">Track</h5><p class="section-copy">Follow institutional treatment and TCM visibility.</p></div>
                        <div class="col-md-4"><div class="process-dot">3</div><h5 class="fw-bold">Evaluate</h5><p class="section-copy">Confirm resolution, declare damage or submit REX.</p></div>
                    </div>
                </div>
            </section>

            <section id="pricing" class="section-pad bg-white">
                <div class="container shell">
                    <div class="text-center mb-5">
                        <div class="section-kicker">Our Pricing Plan</div>
                        <h2 class="section-title">Annual access for public users.</h2>
                    </div>
                    <div class="row g-4 justify-content-center">
                        <div class="col-md-4"><div class="pricing-box text-center h-100"><h5 class="fw-bold">Annual UP</h5><div class="price">1 Plan</div><p class="section-copy">Reporting access, subscription history and virtual member card.</p><button class="btn btn-premium px-4" data-auth-tab-target="register">Choose Plan</button></div></div>
                        <div class="col-md-4"><div class="pricing-box text-center h-100"><h5 class="fw-bold">Wallet Card</h5><div class="price">QR</div><p class="section-copy">Displayed in profile only when the subscription is active.</p><button class="btn btn-ghost-premium px-4" data-auth-tab-target="login">View Access</button></div></div>
                    </div>
                </div>
            </section>

            <section id="reviews" class="section-pad">
                <div class="container shell">
                    <div class="text-center mb-5">
                        <div class="section-kicker">Customer Reviews</div>
                        <h2 class="section-title">Built for public trust.</h2>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-4"><div class="review-box h-100"><p class="section-copy">“My report is no longer lost in conversations. I can see the treatment and keep proof.”</p><strong>Public user</strong></div></div>
                        <div class="col-md-4"><div class="review-box h-100"><p class="section-copy">“The TCM and status make it easier to understand what is happening.”</p><strong>Subscriber</strong></div></div>
                        <div class="col-md-4"><div class="review-box h-100"><p class="section-copy">“The feedback module gives a voice after resolution, not only before.”</p><strong>Consumer member</strong></div></div>
                    </div>
                </div>
            </section>

            <section class="purple-band section-pad">
                <div class="container shell">
                    <div class="row align-items-center g-4">
                        <div class="col-lg-6"><div class="section-kicker">Our Video</div><h2 class="section-title">See how a signal becomes a complete case.</h2><p class="section-copy">The experience is simple: account, subscription, report, follow-up, resolution, feedback.</p></div>
                        <div class="col-lg-6"><div class="ratio ratio-16x9 bg-white rounded-2 d-flex align-items-center justify-content-center"><div class="text-center text-dark fw-bold">MySignal Demo</div></div></div>
                    </div>
                </div>
            </section>

            <section class="section-pad bg-white">
                <div class="container shell">
                    <div class="text-center mb-5"><div class="section-kicker">Our Trusted Clients</div><h2 class="section-title">One platform, multiple service universes.</h2></div>
                    <div class="row g-3">
                        @foreach(($applications ?? collect())->take(6) as $application)
                            <div class="col-md-2 col-6"><div class="client-logo">{{ $application->name }}</div></div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="footer-band">
                <div class="container shell">
                    <div class="row g-4">
                        <div class="col-md-4"><h5>MySignal</h5><p>Consumer protection, reporting, subscription and feedback in one app-style platform.</p></div>
                        <div class="col-md-4"><h5>Rubriques</h5><p>Features<br>Screenshots<br>Pricing<br>Reviews</p></div>
                        <div class="col-md-4"><h5>Ready?</h5><button class="btn btn-premium px-4" data-auth-tab-target="register">Create Account</button></div>
                    </div>
                </div>
            </section>

            <div class="container shell py-5">
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
                                <circle cx="412" cy="156" r="26" fill="#ff0068"/>
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
        </main>

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
