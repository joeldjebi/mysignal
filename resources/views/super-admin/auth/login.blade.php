<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} | Super Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --acepen-navy: #102a43;
            --acepen-gold: #c49b48;
            --acepen-ink: #1f2933;
        }
        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(196,155,72,.18), transparent 32%),
                linear-gradient(140deg, #0b1f33 0%, #153a57 48%, #f4ede1 100%);
            color: var(--acepen-ink);
        }
        .login-shell { min-height: 100vh; }
        .login-card {
            border: 1px solid rgba(255,255,255,.18);
            border-radius: 28px;
            background: rgba(255,255,255,.94);
            box-shadow: 0 30px 80px rgba(7, 25, 40, .22);
        }
        .brand-badge {
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
            background: linear-gradient(135deg, var(--acepen-gold), #b6862f);
            border: none;
            color: #fff;
            font-weight: 700;
        }
        .btn-premium:hover { color: #fff; opacity: .96; }
        .hero-copy { color: rgba(255,255,255,.88); }
    </style>
</head>
<body>
    <div class="container login-shell d-flex align-items-center py-5">
        <div class="row g-4 align-items-center w-100">
            <div class="col-lg-6">
                <span class="brand-badge mb-4">SIGNAL ALERTE · Super Admin</span>
                <h1 class="display-5 fw-bold text-white mb-3">Un espace central pour piloter la plateforme.</h1>
                <p class="lead hero-copy mb-0">Cette première version accueille la connexion super admin et prépare le terrain pour le paramétrage global des localités, signaux, tarifs, organisations et accès.</p>
            </div>
            <div class="col-lg-5 ms-lg-auto">
                <div class="login-card p-4 p-lg-5">
                    <div class="mb-4">
                        <h2 class="h3 fw-bold mb-2">Connexion super admin</h2>
                        <p class="text-secondary mb-0">Accès réservé aux administrateurs système SIGNAL ALERTE.</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif

                    <form method="POST" action="{{ route('super-admin.login.store') }}" class="vstack gap-3">
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
                            <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Se souvenir de moi</label>
                        </div>
                        <button type="submit" class="btn btn-premium btn-lg w-100">Se connecter</button>
                    </form>

                    <div class="mt-4 pt-3 border-top text-secondary small">
                        Compte initial seedé : <strong>jo.djebi@gmail.com</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
