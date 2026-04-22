<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} | Portail partenaire</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --partner-forest: #173f35;
            --partner-sage: #2f6c5b;
            --partner-sand: #d8b36a;
        }
        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(216,179,106,.18), transparent 28%),
                linear-gradient(135deg, #102a25 0%, #2f6c5b 52%, #eef2eb 100%);
        }
        .login-shell { min-height: 100vh; }
        .login-card {
            border-radius: 28px;
            background: rgba(255,255,255,.95);
            border: 1px solid rgba(255,255,255,.18);
            box-shadow: 0 28px 80px rgba(10,31,26,.24);
        }
        .pill {
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            border-radius: 999px;
            background: rgba(216,179,106,.12);
            color: white;
            font-weight: 700;
            padding: .55rem .95rem;
        }
        .btn-partner {
            background: linear-gradient(135deg, var(--partner-sand), #b98934);
            border: none;
            color: #fff;
            font-weight: 700;
        }
        .btn-partner:hover { color: #fff; opacity: .96; }
    </style>
</head>
<body>
    <div class="container login-shell d-flex align-items-center py-5">
        <div class="row g-4 align-items-center w-100">
            <div class="col-lg-6">
                <span class="pill mb-4">SIGNAL ALERTE · Portail partenaire</span>
                <h1 class="display-5 fw-bold text-white mb-3">Suivez vos reductions et pilotez vos equipes mobiles.</h1>
                <p class="lead text-white-50 mb-0">L espace partenaire permet de gerer les offres, suivre les reductions appliquees et creer les comptes des agents mobiles qui utilisent l application de scan.</p>
            </div>
            <div class="col-lg-5 ms-lg-auto">
                <div class="login-card p-4 p-lg-5">
                    <div class="mb-4">
                        <h2 class="h3 fw-bold mb-2">Connexion partenaire</h2>
                        <p class="text-secondary mb-0">Acces reserve aux etablissements partenaires autorises.</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif

                    <form method="POST" action="{{ route('partner.login.store') }}" class="vstack gap-3">
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
                        <button type="submit" class="btn btn-partner btn-lg w-100">Se connecter</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
