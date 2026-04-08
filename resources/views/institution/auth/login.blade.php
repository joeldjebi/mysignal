<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} | Portail institutionnel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --acepen-navy: #102a43;
            --acepen-blue: #184b70;
            --acepen-gold: #c49b48;
        }
        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top right, rgba(196,155,72,.16), transparent 28%),
                linear-gradient(135deg, #0b2034 0%, #184b70 52%, #f6f1e8 100%);
        }
        .login-shell { min-height: 100vh; }
        .login-card {
            border-radius: 28px;
            background: rgba(255,255,255,.94);
            border: 1px solid rgba(255,255,255,.18);
            box-shadow: 0 28px 80px rgba(7,25,40,.24);
        }
        .pill {
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            border-radius: 999px;
            background: rgba(196,155,72,.12);
            color: var(--acepen-navy);
            font-weight: 700;
            padding: .55rem .95rem;
        }
        .btn-premium {
            background: linear-gradient(135deg, var(--acepen-gold), #af7e28);
            border: none;
            color: #fff;
            font-weight: 700;
        }
        .btn-premium:hover { color: #fff; opacity: .96; }
    </style>
</head>
<body>
    <div class="container login-shell d-flex align-items-center py-5">
        <div class="row g-4 align-items-center w-100">
            <div class="col-lg-6">
                <span class="pill mb-4">SIGNAL ALERTE · Portail institutionnel</span>
                <h1 class="display-5 fw-bold text-white mb-3">Connexion commune pour les institutions partenaires.</h1>
                <p class="lead text-white-50 mb-0">Une fois connecté, le bon portail s'affiche selon l'organisation du compte : CIE, SODECI ou une autre institution autorisée.</p>
            </div>
            <div class="col-lg-5 ms-lg-auto">
                <div class="login-card p-4 p-lg-5">
                    <div class="mb-4">
                        <h2 class="h3 fw-bold mb-2">Se connecter</h2>
                        <p class="text-secondary mb-0">Accès réservé aux admins institutionnels.</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif

                    <form method="POST" action="{{ route('institution.login.store') }}" class="vstack gap-3">
                        @csrf
                        <div>
                            <label for="email" class="form-label fw-semibold">Email</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control form-control-lg" required autofocus>
                        </div>
                        <div>
                            <label for="password" class="form-label fw-semibold">Mot de passe</label>
                            <input id="password" type="password" name="password" class="form-control form-control-lg" required>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" name="remember" id="remember">
                            <label class="form-check-label" for="remember">Se souvenir de moi</label>
                        </div>
                        <button type="submit" class="btn btn-premium btn-lg w-100">Se connecter</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
