<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name') }} | Authentification</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            :root {
                --mysignal-navy: #183447;
                --mysignal-ocean: #256f8f;
                --mysignal-pink: #ff0068;
                --mysignal-amber: #ffa117;
                --mysignal-mint: #5bebaf;
                --mysignal-muted: #667786;
                --mysignal-soft: #f4f9fb;
            }

            * { box-sizing: border-box; }

            body {
                min-height: 100vh;
                font-family: "Manrope", sans-serif;
                color: var(--mysignal-navy);
                background:
                    radial-gradient(circle at top left, rgba(255, 0, 104, .10), transparent 28%),
                    radial-gradient(circle at bottom right, rgba(91, 235, 175, .18), transparent 32%),
                    linear-gradient(145deg, #f7fbff 0%, #eef6fa 55%, #ffffff 100%);
            }

            .auth-shell {
                min-height: 100vh;
                display: grid;
                align-items: center;
                padding: 32px 0;
            }

            .brand-logo {
                width: 58px;
                height: 58px;
                object-fit: contain;
                border-radius: 16px;
                background: #fff;
                padding: 6px;
                box-shadow: 0 16px 32px rgba(24, 52, 71, .10);
            }

            .hero-panel,
            .auth-card {
                border: 1px solid rgba(24, 52, 71, .08);
                background: rgba(255, 255, 255, .92);
                box-shadow: 0 28px 80px rgba(15, 39, 56, .10);
            }

            .hero-panel {
                border-radius: 24px;
                padding: 32px;
                height: 100%;
                background:
                    linear-gradient(145deg, rgba(24, 52, 71, .96), rgba(37, 111, 143, .94)),
                    var(--mysignal-navy);
                color: #fff;
                overflow: hidden;
                position: relative;
            }

            .hero-panel::after {
                content: "";
                position: absolute;
                width: 280px;
                height: 280px;
                right: -90px;
                bottom: -90px;
                border-radius: 50%;
                background: radial-gradient(circle, rgba(255, 161, 23, .22), transparent 64%);
            }

            .auth-card {
                border-radius: 24px;
                padding: 28px;
            }

            .auth-tabs {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 8px;
                padding: 6px;
                border-radius: 14px;
                background: var(--mysignal-soft);
                margin-bottom: 24px;
            }

            .auth-tab {
                border: 0;
                border-radius: 8px;
                padding: 12px;
                background: transparent;
                color: var(--mysignal-muted);
                font-weight: 800;
            }

            .auth-tab.active {
                background: #fff;
                color: var(--mysignal-navy);
                box-shadow: 0 10px 24px rgba(24, 52, 71, .08);
            }

            .auth-pane { display: none; }
            .auth-pane.active { display: block; }

            .form-control,
            .form-select {
                min-height: 48px;
                border-radius: 8px;
                border-color: rgba(24, 52, 71, .12);
            }

            .form-control:focus,
            .form-select:focus {
                border-color: rgba(255, 0, 104, .42);
                box-shadow: 0 0 0 .22rem rgba(255, 0, 104, .10);
            }

            .btn-main {
                min-height: 48px;
                border-radius: 8px;
                border: 0;
                background: linear-gradient(135deg, var(--mysignal-pink), var(--mysignal-amber));
                color: #fff;
                font-weight: 800;
            }

            .btn-main:hover { color: #fff; opacity: .92; }

            .btn-soft {
                min-height: 48px;
                border-radius: 8px;
                border: 1px solid rgba(24, 52, 71, .12);
                background: #fff;
                color: var(--mysignal-navy);
                font-weight: 800;
            }

            .status-box {
                border-radius: 12px;
                padding: 12px 14px;
                background: rgba(24, 52, 71, .06);
                color: var(--mysignal-muted);
                font-size: .88rem;
            }

            .hidden { display: none !important; }

            @media (max-width: 991.98px) {
                .auth-shell { padding: 18px 0; }
                .hero-panel,
                .auth-card { border-radius: 18px; }
            }
        </style>
    </head>
    <body>
        <main class="auth-shell">
            <div class="container">
                <div class="row g-4 align-items-stretch">
                    <div class="col-lg-5">
                        <section class="hero-panel">
                            <div class="position-relative" style="z-index:1">
                                <a href="{{ route('public.landing') }}" class="d-inline-flex align-items-center gap-3 text-white text-decoration-none mb-5">
                                    <img class="brand-logo" src="{{ asset('image/logo/logo-my-signal.png') }}" alt="MySignal">
                                    <span class="fw-bold fs-4">MySignal</span>
                                </a>
                                <div class="small text-white-50 fw-bold text-uppercase mb-3">Signalement consommateur</div>
                                <h1 class="display-6 fw-bold mb-3">Connectez-vous pour signaler maintenant.</h1>
                                <p class="text-white text-opacity-75 mb-4">
                                    Authentifiez-vous ou creez votre compte pour acceder a votre espace, declarer un signalement et suivre son traitement.
                                </p>
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="status-box text-white" style="background:rgba(255,255,255,.10)">Compte securise</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="status-box text-white" style="background:rgba(255,255,255,.10)">Suivi des dossiers</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="status-box text-white" style="background:rgba(255,255,255,.10)">Abonnement UP</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="status-box text-white" style="background:rgba(255,255,255,.10)">REX apres traitement</div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>

                    <div class="col-lg-7">
                        <section class="auth-card">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                                <div>
                                    <div class="small text-secondary fw-bold text-uppercase mb-1">Acces public</div>
                                    <h2 class="h3 fw-bold mb-1">Authentification</h2>
                                    <div class="text-secondary">Choisissez une option pour continuer vers le signalement.</div>
                                </div>
                                <a href="{{ route('public.landing') }}" class="btn btn-soft px-3">Retour</a>
                            </div>

                            <div class="auth-tabs" role="tablist">
                                <button class="auth-tab active" type="button" data-auth-tab="login">Connexion</button>
                                <button class="auth-tab" type="button" data-auth-tab="register">Creer un compte</button>
                            </div>

                            <div class="alert d-none" id="authAlert"></div>

                            <section class="auth-pane active" data-auth-pane="login">
                                <form id="loginForm" class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Numero de telephone</label>
                                        <div class="input-group">
                                            <select class="form-select flex-grow-0" name="phone_dial_code" data-dial-code-select style="width: 140px"></select>
                                            <input class="form-control" name="phone_local" inputmode="numeric" required placeholder="0700000000">
                                        </div>
                                        <input type="hidden" name="phone">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Mot de passe</label>
                                        <input class="form-control" type="password" name="password" required>
                                    </div>
                                    <div class="col-12">
                                        <button class="btn btn-main w-100" type="submit">Se connecter et signaler maintenant</button>
                                    </div>
                                </form>
                            </section>

                            <section class="auth-pane" data-auth-pane="register">
                                <form id="registerForm" class="row g-3">
                                    <div class="col-12">
                                        <div class="status-box">
                                            1. Renseignez votre numero, demandez le code OTP, puis verifiez-le avant de finaliser le compte.
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Type d usager</label>
                                        <select class="form-select" name="public_user_type_id" id="registerPublicUserTypeId" required>
                                            @foreach ($publicUserTypes as $publicUserType)
                                                <option value="{{ $publicUserType->id }}" data-type-code="{{ $publicUserType->code }}">{{ $publicUserType->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Numero WhatsApp</label>
                                        <select class="form-select" name="is_whatsapp_number">
                                            <option value="1">Oui</option>
                                            <option value="0">Non</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Numero de telephone</label>
                                        <div class="input-group">
                                            <select class="form-select flex-grow-0" name="phone_dial_code" data-dial-code-select style="width: 140px"></select>
                                            <input class="form-control" name="phone_local" inputmode="numeric" required placeholder="0700000000">
                                        </div>
                                        <input type="hidden" name="phone">
                                    </div>
                                    <div class="col-md-6">
                                        <button class="btn btn-soft w-100" type="button" id="requestOtpButton">Recevoir le code OTP</button>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <input class="form-control" name="otp_code" placeholder="Code OTP">
                                            <button class="btn btn-soft" type="button" id="verifyOtpButton">Verifier</button>
                                        </div>
                                    </div>
                                    <input type="hidden" name="verification_token" id="verificationToken">

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Prenom</label>
                                        <input class="form-control" name="first_name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Nom</label>
                                        <input class="form-control" name="last_name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Email</label>
                                        <input class="form-control" type="email" name="email">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Commune</label>
                                        <select class="form-select" name="commune" required>
                                            <option value="">Selectionner une commune</option>
                                            @foreach ($communes as $commune)
                                                <option value="{{ $commune->name }}">{{ $commune->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-12 hidden" id="sectorFields">
                                        <label class="form-label fw-semibold">Secteur d activite</label>
                                        <select class="form-select" name="business_sector">
                                            <option value="">Selectionner un secteur</option>
                                            @foreach ($businessSectors as $businessSector)
                                                <option value="{{ $businessSector->name }}">{{ $businessSector->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-12 hidden" id="businessFields">
                                        <div class="row g-3">
                                            <div class="col-md-6"><label class="form-label fw-semibold">Raison sociale</label><input class="form-control" name="company_name"></div>
                                            <div class="col-md-6"><label class="form-label fw-semibold">RCCM / Immatriculation</label><input class="form-control" name="company_registration_number"></div>
                                            <div class="col-md-6"><label class="form-label fw-semibold">Identifiant fiscal</label><input class="form-control" name="tax_identifier"></div>
                                            <div class="col-md-6"><label class="form-label fw-semibold">Adresse entreprise</label><input class="form-control" name="company_address"></div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Mot de passe</label>
                                        <input class="form-control" type="password" name="password" minlength="8" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Confirmer le mot de passe</label>
                                        <input class="form-control" type="password" name="password_confirmation" minlength="8" required>
                                    </div>
                                    <div class="col-12">
                                        <button class="btn btn-main w-100" type="submit" id="registerSubmitButton" disabled>Verifier le numero pour continuer</button>
                                    </div>
                                </form>
                            </section>
                        </section>
                    </div>
                </div>
            </div>
        </main>

        <script>
            (() => {
                const apiBase = '/api/v1/public';
                const dashboardUrl = '{{ route('public.dashboard') }}';
                const tokenKey = 'acepen_public_token';
                const dashboardPanelStorageKey = 'acepen_public_dashboard_panel';
                const dialCodeOptions = @json($dialCodeOptions);

                const authAlert = document.getElementById('authAlert');
                const verificationToken = document.getElementById('verificationToken');
                const registerSubmitButton = document.getElementById('registerSubmitButton');
                let verifiedPhone = '';

                function showAlert(message, type = 'danger') {
                    authAlert.className = `alert alert-${type}`;
                    authAlert.textContent = message;
                }

                function clearAlert() {
                    authAlert.className = 'alert d-none';
                    authAlert.textContent = '';
                }

                function setLoading(button, isLoading) {
                    button.disabled = isLoading;
                    button.dataset.originalText = button.dataset.originalText || button.textContent;
                    button.textContent = isLoading ? 'Traitement...' : button.dataset.originalText;
                }

                function populateDialCodeSelects() {
                    document.querySelectorAll('[data-dial-code-select]').forEach((select) => {
                        select.innerHTML = dialCodeOptions.map((option) => `<option value="${option.value}">${option.label}</option>`).join('');
                        select.value = select.value || dialCodeOptions[0]?.value || '225';
                    });
                }

                function composePhoneNumber(form) {
                    const local = String(form.querySelector('[name="phone_local"]')?.value || '').replace(/\D+/g, '');
                    const dialCode = form.querySelector('[name="phone_dial_code"]')?.value || '';
                    const phone = form.querySelector('[name="phone"]');

                    if (phone) {
                        phone.value = local ? `${dialCode}${local}` : '';
                    }

                    return phone?.value || '';
                }

                function setRegistrationVerified(phone = '') {
                    verifiedPhone = phone;
                    verificationToken.value = '';
                    registerSubmitButton.disabled = true;
                    registerSubmitButton.textContent = 'Verifier le numero pour continuer';
                }

                function enableRegistrationSubmit(token, phone) {
                    verifiedPhone = phone;
                    verificationToken.value = token;
                    registerSubmitButton.disabled = false;
                    registerSubmitButton.textContent = 'Creer mon compte et signaler maintenant';
                }

                async function publicApi(path, payload) {
                    const response = await fetch(`${apiBase}${path}`, {
                        method: 'POST',
                        headers: {
                            Accept: 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    });
                    const data = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        const firstError = data.errors ? Object.values(data.errors).flat()[0] : null;
                        throw new Error(firstError || data.message || 'Une erreur est survenue.');
                    }

                    return data;
                }

                function continueToReport(data) {
                    const token = data?.data?.access_token;

                    if (!token) {
                        throw new Error('Token de session introuvable.');
                    }

                    localStorage.setItem(tokenKey, token);
                    sessionStorage.setItem(dashboardPanelStorageKey, 'reports');
                    window.location.href = dashboardUrl;
                }

                function syncUserTypeFields() {
                    const select = document.getElementById('registerPublicUserTypeId');
                    const selected = select.options[select.selectedIndex];
                    const typeCode = String(selected?.dataset.typeCode || '').toUpperCase();
                    const showBusinessFields = typeCode === 'UPE';
                    const showSectorFields = typeCode === 'UPE' || typeCode === 'UPTI';

                    document.getElementById('businessFields').classList.toggle('hidden', !showBusinessFields);
                    document.querySelectorAll('#businessFields input').forEach((input) => {
                        input.disabled = !showBusinessFields;
                        input.required = showBusinessFields;
                    });

                    document.getElementById('sectorFields').classList.toggle('hidden', !showSectorFields);
                    document.querySelectorAll('#sectorFields select').forEach((input) => {
                        input.disabled = !showSectorFields;
                        input.required = showSectorFields;
                    });
                }

                document.querySelectorAll('[data-auth-tab]').forEach((tab) => {
                    tab.addEventListener('click', () => {
                        clearAlert();
                        document.querySelectorAll('[data-auth-tab]').forEach((candidate) => candidate.classList.toggle('active', candidate === tab));
                        document.querySelectorAll('[data-auth-pane]').forEach((pane) => pane.classList.toggle('active', pane.dataset.authPane === tab.dataset.authTab));
                    });
                });

                document.getElementById('loginForm').addEventListener('submit', async (event) => {
                    event.preventDefault();
                    clearAlert();
                    const form = event.currentTarget;
                    const button = form.querySelector('button[type="submit"]');
                    setLoading(button, true);

                    try {
                        const payload = Object.fromEntries(new FormData(form).entries());
                        payload.phone = composePhoneNumber(form);
                        continueToReport(await publicApi('/auth/login', payload));
                    } catch (error) {
                        showAlert(error.message);
                    } finally {
                        setLoading(button, false);
                    }
                });

                document.getElementById('requestOtpButton').addEventListener('click', async (event) => {
                    clearAlert();
                    const button = event.currentTarget;
                    const form = document.getElementById('registerForm');
                    setLoading(button, true);

                    try {
                        const phone = composePhoneNumber(form);
                        setRegistrationVerified(phone);
                        const response = await publicApi('/auth/request-otp', { phone });
                        const testingCode = response?.data?.otp_code_for_testing ? ` Code local: ${response.data.otp_code_for_testing}` : '';
                        showAlert(`Code OTP envoye.${testingCode}`, 'success');
                    } catch (error) {
                        showAlert(error.message);
                    } finally {
                        setLoading(button, false);
                        if (!verificationToken.value) {
                            setRegistrationVerified(composePhoneNumber(form));
                        }
                    }
                });

                document.getElementById('verifyOtpButton').addEventListener('click', async (event) => {
                    clearAlert();
                    const button = event.currentTarget;
                    const form = document.getElementById('registerForm');
                    setLoading(button, true);

                    try {
                        const phone = composePhoneNumber(form);
                        const code = form.querySelector('[name="otp_code"]').value;
                        const response = await publicApi('/auth/verify-otp', { phone, code });
                        enableRegistrationSubmit(response.data.verification_token, phone);
                        showAlert('Numero verifie. Vous pouvez creer le compte.', 'success');
                    } catch (error) {
                        showAlert(error.message);
                    } finally {
                        setLoading(button, false);
                    }
                });

                document.getElementById('registerForm').addEventListener('submit', async (event) => {
                    event.preventDefault();
                    clearAlert();
                    const form = event.currentTarget;
                    const button = form.querySelector('button[type="submit"]');
                    setLoading(button, true);

                    try {
                        const payload = Object.fromEntries(new FormData(form).entries());
                        payload.phone = composePhoneNumber(form);

                        if (!payload.verification_token || payload.phone !== verifiedPhone) {
                            setRegistrationVerified(payload.phone);
                            showAlert('Veuillez verifier votre numero avec le code OTP avant de creer le compte.');
                            return;
                        }

                        payload.is_whatsapp_number = payload.is_whatsapp_number === '1';
                        delete payload.phone_dial_code;
                        delete payload.phone_local;
                        delete payload.otp_code;
                        continueToReport(await publicApi('/auth/register', payload));
                    } catch (error) {
                        showAlert(error.message);
                    } finally {
                        setLoading(button, false);
                    }
                });

                document.getElementById('registerPublicUserTypeId').addEventListener('change', syncUserTypeFields);
                document.querySelectorAll('#registerForm [name="phone_dial_code"], #registerForm [name="phone_local"]').forEach((input) => {
                    input.addEventListener('input', () => setRegistrationVerified(composePhoneNumber(document.getElementById('registerForm'))));
                    input.addEventListener('change', () => setRegistrationVerified(composePhoneNumber(document.getElementById('registerForm'))));
                });

                if (localStorage.getItem(tokenKey)) {
                    sessionStorage.setItem(dashboardPanelStorageKey, 'reports');
                    window.location.href = dashboardUrl;
                    return;
                }

                populateDialCodeSelects();
                setRegistrationVerified();
                syncUserTypeFields();
            })();
        </script>
    </body>
</html>
