<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} | Acces refuse</title>
    <style>
        :root {
            --ink: #0f172a;
            --muted: #64748b;
            --line: rgba(15, 23, 42, .1);
            --panel: rgba(255, 255, 255, .9);
            --brand: #0f5b8d;
            --gold: #b88a2a;
            --bg: #f7f9fc;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Inter, "Segoe UI", Arial, sans-serif;
            color: var(--ink);
            background:
                linear-gradient(135deg, rgba(15, 91, 141, .14), transparent 34%),
                linear-gradient(315deg, rgba(184, 138, 42, .16), transparent 30%),
                var(--bg);
        }

        .shell {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 32px;
        }

        .panel {
            width: min(960px, 100%);
            display: grid;
            grid-template-columns: .9fr 1.1fr;
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 28px;
            background: var(--panel);
            box-shadow: 0 30px 90px rgba(15, 23, 42, .16);
            backdrop-filter: blur(18px);
        }

        .visual {
            min-height: 420px;
            padding: 42px;
            color: white;
            background: linear-gradient(150deg, rgba(15, 23, 42, .96), rgba(15, 91, 141, .92));
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .mark {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .18);
            font-weight: 900;
        }

        .code {
            font-size: clamp(84px, 16vw, 150px);
            line-height: .82;
            font-weight: 900;
            letter-spacing: 0;
        }

        .copy {
            padding: 56px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 18px;
        }

        .eyebrow {
            width: fit-content;
            padding: 8px 12px;
            border-radius: 999px;
            color: #7a5512;
            background: rgba(184, 138, 42, .14);
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
        }

        h1 {
            margin: 0;
            font-size: clamp(30px, 5vw, 46px);
            line-height: 1.08;
            letter-spacing: 0;
        }

        p {
            margin: 0;
            color: var(--muted);
            line-height: 1.75;
            font-size: 16px;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 0 18px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 800;
            border: 1px solid var(--line);
        }

        .btn-primary {
            background: var(--ink);
            color: #fff;
        }

        .btn-secondary {
            color: var(--ink);
            background: #fff;
        }

        @media (max-width: 760px) {
            .shell { padding: 18px; }
            .panel { grid-template-columns: 1fr; border-radius: 22px; }
            .visual { min-height: 240px; padding: 28px; }
            .copy { padding: 32px 26px; }
        }
    </style>
</head>
<body>
    <main class="shell">
        <section class="panel">
            <div class="visual">
                <div class="mark">MS</div>
                <div>
                    <div class="code">403</div>
                    <div>Autorisation requise</div>
                </div>
            </div>
            <div class="copy">
                <div class="eyebrow">Acces refuse</div>
                <h1>Vous n avez pas les droits pour cette action.</h1>
                <p>Cette page ou cette operation est reservee a un profil disposant des permissions necessaires. Demandez au SA de mettre a jour le role si cet acces est attendu.</p>
                <div class="actions">
                    <a class="btn btn-primary" href="{{ url()->previous() }}">Retour</a>
                    <a class="btn btn-secondary" href="{{ route('super-admin.dashboard') }}">Tableau de bord</a>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
