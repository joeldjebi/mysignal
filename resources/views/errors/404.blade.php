<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} | Page introuvable</title>
    <style>
        :root {
            --acepen-navy: #0b2033;
            --acepen-blue: #17486b;
            --acepen-gold: #c49b48;
            --acepen-cream: #f6f1e7;
            --acepen-mist: #edf3f8;
            --acepen-card: rgba(255, 255, 255, 0.9);
            --acepen-ink: #162534;
            --acepen-muted: #61758b;
            --acepen-border: rgba(15, 41, 64, 0.1);
            --acepen-shadow: 0 32px 90px rgba(11, 32, 51, 0.18);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
            color: var(--acepen-ink);
            background:
                radial-gradient(circle at top left, rgba(196, 155, 72, 0.18), transparent 24%),
                radial-gradient(circle at bottom right, rgba(23, 72, 107, 0.22), transparent 26%),
                linear-gradient(180deg, var(--acepen-mist) 0%, var(--acepen-cream) 100%);
            position: relative;
            overflow-x: hidden;
        }

        body::before,
        body::after {
            content: "";
            position: fixed;
            inset: auto;
            border-radius: 999px;
            pointer-events: none;
            filter: blur(8px);
        }

        body::before {
            width: 420px;
            height: 420px;
            top: -140px;
            right: -120px;
            background: radial-gradient(circle, rgba(196, 155, 72, 0.18), transparent 64%);
        }

        body::after {
            width: 360px;
            height: 360px;
            left: -100px;
            bottom: -120px;
            background: radial-gradient(circle, rgba(23, 72, 107, 0.16), transparent 64%);
        }

        .shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px;
        }

        .panel {
            width: min(1120px, 100%);
            display: grid;
            grid-template-columns: 1.1fr .9fr;
            background: var(--acepen-card);
            border: 1px solid var(--acepen-border);
            border-radius: 32px;
            overflow: hidden;
            box-shadow: var(--acepen-shadow);
            backdrop-filter: blur(16px);
        }

        .hero {
            padding: 56px;
            background:
                radial-gradient(circle at top right, rgba(255, 255, 255, 0.18), transparent 28%),
                linear-gradient(145deg, var(--acepen-navy), var(--acepen-blue));
            color: #fff;
            position: relative;
        }

        .hero::after {
            content: "";
            position: absolute;
            width: 320px;
            height: 320px;
            right: -120px;
            bottom: -120px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.14), transparent 68%);
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.12);
            font-size: 12px;
            letter-spacing: .14em;
            text-transform: uppercase;
            font-weight: 700;
        }

        .code {
            margin: 28px 0 12px;
            font-size: clamp(88px, 16vw, 170px);
            line-height: .9;
            font-weight: 900;
            letter-spacing: -.06em;
        }

        .title {
            margin: 0 0 14px;
            font-size: clamp(28px, 4vw, 44px);
            line-height: 1.05;
            font-weight: 800;
            max-width: 560px;
        }

        .lead {
            margin: 0;
            max-width: 560px;
            color: rgba(255, 255, 255, 0.78);
            font-size: 16px;
            line-height: 1.7;
        }

        .meta-row {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 28px;
        }

        .meta-card {
            min-width: 160px;
            padding: 16px 18px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .meta-label {
            margin-bottom: 6px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .12em;
            color: rgba(255, 255, 255, 0.58);
            font-weight: 700;
        }

        .meta-value {
            font-size: 15px;
            font-weight: 700;
            color: #fff;
        }

        .aside {
            padding: 56px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 22px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            gap: 8px;
            border-radius: 999px;
            padding: 10px 14px;
            background: rgba(196, 155, 72, 0.12);
            color: #7b5b1d;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .aside h2 {
            margin: 0;
            font-size: 28px;
            line-height: 1.2;
            font-weight: 800;
        }

        .aside p {
            margin: 0;
            color: var(--acepen-muted);
            line-height: 1.8;
            font-size: 15px;
        }

        .tips {
            display: grid;
            gap: 12px;
        }

        .tip {
            padding: 16px 18px;
            border-radius: 20px;
            background: rgba(237, 243, 248, 0.8);
            border: 1px solid rgba(15, 41, 64, 0.06);
        }

        .tip strong {
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
        }

        .tip span {
            color: var(--acepen-muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 8px;
        }

        .btn {
            appearance: none;
            border: 0;
            text-decoration: none;
            cursor: pointer;
            border-radius: 16px;
            padding: 14px 18px;
            font-weight: 700;
            font-size: 14px;
            transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--acepen-navy), var(--acepen-blue));
            color: #fff;
            box-shadow: 0 18px 36px rgba(23, 72, 107, 0.18);
        }

        .btn-secondary {
            background: #fff;
            color: var(--acepen-navy);
            border: 1px solid rgba(15, 41, 64, 0.12);
        }

        .brand {
            margin-top: 12px;
            font-size: 13px;
            color: var(--acepen-muted);
        }

        @media (max-width: 920px) {
            .panel {
                grid-template-columns: 1fr;
            }

            .hero,
            .aside {
                padding: 34px 24px;
            }

            .meta-row {
                gap: 10px;
            }

            .meta-card {
                flex: 1 1 100%;
            }
        }
    </style>
</head>
<body>
    @php
        $homeUrl = route('public.landing');
        $isAuthenticated = auth()->check();
        $dashboardUrl = null;

        if ($isAuthenticated) {
            $dashboardUrl = auth()->user()?->is_super_admin
                ? route('super-admin.dashboard')
                : route('institution.dashboard');
        }
    @endphp

    <main class="shell">
        <section class="panel">
            <div class="hero">
                <div class="eyebrow">Navigation interrompue</div>
                <div class="code">404</div>
                <h1 class="title">La page demandee n’est plus disponible a cette adresse.</h1>
                <p class="lead">
                    Le lien peut etre incomplet, expire, ou vous essayez d’ouvrir une ressource qui n’existe pas dans cet espace.
                    Nous avons prepare des raccourcis pour vous remettre rapidement sur le bon parcours.
                </p>

                <div class="meta-row">
                    <div class="meta-card">
                        <div class="meta-label">Statut</div>
                        <div class="meta-value">Page introuvable</div>
                    </div>
                    <div class="meta-card">
                        <div class="meta-label">Application</div>
                        <div class="meta-value">{{ config('app.name') }}</div>
                    </div>
                    <div class="meta-card">
                        <div class="meta-label">Action conseillee</div>
                        <div class="meta-value">Revenir au bon espace</div>
                    </div>
                </div>
            </div>

            <div class="aside">
                <div class="badge">Experience premium</div>
                <h2>Reprenez la navigation sans friction.</h2>
                <p>
                    Si vous avez suivi un ancien lien ou si un menu a change, utilisez les acces rapides ci-dessous.
                    Ils vous renvoient vers une entree stable de la plateforme.
                </p>

                <div class="tips">
                    <div class="tip">
                        <strong>Verifier l’adresse</strong>
                        <span>Controlez l’URL, surtout si elle a ete saisie manuellement ou partagee depuis un ancien environnement.</span>
                    </div>
                    <div class="tip">
                        <strong>Revenir a l’espace adapte</strong>
                        <span>Selon votre profil, le tableau de bord institutionnel ou super admin reste le point de reprise le plus fiable.</span>
                    </div>
                </div>

                <div class="actions">
                    @if ($dashboardUrl)
                        <a href="{{ $dashboardUrl }}" class="btn btn-primary">Aller au dashboard</a>
                    @else
                        <a href="{{ $homeUrl }}" class="btn btn-primary">Retour a l’accueil</a>
                    @endif
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">Page precedente</button>
                    @unless ($dashboardUrl)
                        <a href="{{ route('institution.login') }}" class="btn btn-secondary">Connexion institution</a>
                        <a href="{{ route('super-admin.login') }}" class="btn btn-secondary">Connexion SA</a>
                    @endunless
                </div>

                <div class="brand">SIGNAL ALERTE • Interface de navigation securisee</div>
            </div>
        </section>
    </main>
</body>
</html>
