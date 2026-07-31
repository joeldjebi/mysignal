<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} | Centre d’appels</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --mysignal-primary: #ffa117;
            --mysignal-pink: #ff0068;
            --mysignal-blue: #6791ff;
            --mysignal-green: #5bebaf;
            --mysignal-ink: #172033;
        }
        body {
            min-height: 100vh;
            background: #f6f8fc;
            color: var(--mysignal-ink);
        }
        .login-shell { min-height: 100vh; }
        .login-card {
            border: 1px solid rgba(23, 32, 51, .08);
            border-radius: 24px;
            background: #fff;
            box-shadow: 0 24px 70px rgba(23, 32, 51, .14);
        }
        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            border-radius: 999px;
            background: rgba(255, 161, 23, .14);
            color: #7a4a00;
            font-weight: 700;
            padding: .55rem .95rem;
        }
        .side-panel {
            border-left: 6px solid var(--mysignal-primary);
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 18px 50px rgba(23, 32, 51, .08);
        }
        .btn-premium {
            background: var(--mysignal-primary);
            border: none;
            color: #241500;
            font-weight: 700;
        }
        .btn-premium:hover { background: #f29300; color: #241500; }
        .accent-line {
            display: flex;
            gap: .45rem;
        }
        .accent-line span {
            height: 7px;
            border-radius: 99px;
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="container login-shell d-flex align-items-center py-5">
        <div class="row g-4 align-items-center w-100">
            <div class="col-lg-6">
                <div class="side-panel p-4 p-lg-5">
                    <span class="brand-badge mb-4">My-Signal · Centre d’appels</span>
                    <h1 class="display-6 fw-bold mb-3">Accès réservé aux agents du centre d’appels.</h1>
                    <p class="lead text-secondary mb-4">Connectez-vous avec les accès reçus par SMS pour accompagner les usagers publics et suivre leurs demandes.</p>
                    <div class="accent-line">
                        <span style="background: var(--mysignal-primary);"></span>
                        <span style="background: var(--mysignal-pink);"></span>
                        <span style="background: var(--mysignal-blue);"></span>
                        <span style="background: var(--mysignal-green);"></span>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 ms-lg-auto">
                <div class="login-card p-4 p-lg-5">
                    <div class="mb-4">
                        <h2 class="h3 fw-bold mb-2">Connexion centre d’appels</h2>
                        <p class="text-secondary mb-0">Utilisez le mot de passe temporaire envoyé par SMS.</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif

                    <form method="POST" action="{{ route('callcenter.login.store') }}" class="vstack gap-3">
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
                </div>
            </div>
        </div>
    </div>
</body>
</html>
