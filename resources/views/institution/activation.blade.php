<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} | Activation institutionnelle</title>
    <style>
        :root { --primary: #ffa117; --rose: #ff0068; --blue: #6791ff; --green: #5bebaf; --ink: #101828; }
        body { margin: 0; min-height: 100vh; font-family: Arial, sans-serif; background: #f3f6fb; color: var(--ink); }
        .shell { max-width: 980px; margin: 0 auto; padding: 36px 18px; }
        .brand { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; font-weight: 800; font-size: 22px; }
        .platform-logo { width: 54px; height: 54px; border-radius: 14px; object-fit: contain; background: #fff; border: 1px solid #e6ebf2; padding: 6px; }
        .card { background: #fff; border: 1px solid #e6ebf2; border-radius: 18px; box-shadow: 0 24px 70px rgba(15, 23, 42, .08); overflow: hidden; }
        .hero { padding: 28px; border-bottom: 1px solid #edf1f6; display: grid; gap: 8px; }
        .hero h1 { margin: 0; font-size: clamp(28px, 4vw, 42px); }
        .hero p { margin: 0; color: #667085; line-height: 1.6; }
        .body { padding: 28px; }
        .grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
        label { display: block; font-size: 14px; color: #344054; margin-bottom: 7px; font-weight: 700; }
        input { width: 100%; box-sizing: border-box; border: 1px solid #d0d5dd; border-radius: 10px; padding: 12px 13px; font-size: 15px; outline-color: var(--blue); }
        .full { grid-column: 1 / -1; }
        .notice { padding: 14px 16px; border-radius: 12px; background: #ecfff6; border: 1px solid rgba(91, 235, 175, .5); margin-bottom: 18px; }
        .error { padding: 14px 16px; border-radius: 12px; background: #fff1f5; border: 1px solid rgba(255, 0, 104, .25); margin-bottom: 18px; }
        .actions { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-top: 22px; }
        .btn { border: 0; border-radius: 10px; padding: 12px 16px; font-weight: 800; cursor: pointer; }
        .btn-primary { background: var(--primary); color: #111827; }
        .btn-light { background: #eef2f7; color: #344054; }
        .institution { display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 999px; background: rgba(103, 145, 255, .13); color: #24426f; font-weight: 700; }
        .modal-backdrop { position: fixed; inset: 0; background: rgba(16, 24, 40, .58); display: none; align-items: center; justify-content: center; padding: 18px; }
        .modal-backdrop.open { display: flex; }
        .modal { max-width: 520px; width: 100%; background: #fff; border-radius: 18px; padding: 22px; box-shadow: 0 28px 80px rgba(0, 0, 0, .22); }
        .modal h2 { margin: 0 0 10px; }
        .summary { background: #f8fafc; border-radius: 12px; padding: 14px; line-height: 1.7; margin: 14px 0; }
        .location-note { grid-column: 1 / -1; padding: 12px 14px; border-radius: 12px; background: rgba(103, 145, 255, .1); color: #24426f; font-size: 14px; }
        @media (max-width: 720px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <main class="shell">
        <div class="brand">
            <img src="{{ asset('image/logo/logo-my-signal.png') }}" class="platform-logo" alt="Logo My-Signal">
            <div>My-Signal</div>
        </div>

        <section class="card">
            <div class="hero">
                <div class="institution">{{ $letter?->organization?->name ?: 'Activation institutionnelle' }}</div>
                <h1>Désignation du point focal</h1>
                <p>Renseignez les informations de la personne habilitée à gérer l’espace institutionnel My-Signal.</p>
            </div>
            <div class="body">
                @if (session('success'))
                    <div class="notice">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="error">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                @if ($isExpired)
                    <div class="error">Ce code d’activation a expiré. Veuillez contacter l’équipe My-Signal.</div>
                @elseif ($letter?->status === 'submitted' || $letter?->status === 'approved')
                    <div class="notice">Les informations du point focal ont déjà été transmises pour cette institution. Le formulaire est désormais verrouillé.</div>
                @else
                    <form method="POST" action="{{ route('institution.activation.store') }}" id="activationForm">
                        @csrf
                        <div class="grid">
                            <div class="full">
                                <label for="activation_code">Code d’activation <span style="color:#ff0068">*</span></label>
                                <input id="activation_code" name="activation_code" value="{{ old('activation_code', $code) }}" required>
                            </div>
                            <div>
                                <label for="focal_last_name">Nom <span style="color:#ff0068">*</span></label>
                                <input id="focal_last_name" name="focal_last_name" value="{{ old('focal_last_name') }}" required>
                            </div>
                            <div>
                                <label for="focal_first_names">Prénoms <span style="color:#ff0068">*</span></label>
                                <input id="focal_first_names" name="focal_first_names" value="{{ old('focal_first_names') }}" required>
                            </div>
                            <div>
                                <label for="focal_position">Fonction <span style="color:#ff0068">*</span></label>
                                <input id="focal_position" name="focal_position" value="{{ old('focal_position') }}" required>
                            </div>
                            <div>
                                <label for="focal_phone">Numéro de téléphone <span style="color:#ff0068">*</span></label>
                                <input id="focal_phone" name="focal_phone" value="{{ old('focal_phone') }}" required>
                            </div>
                            <div>
                                <label for="focal_email">Email <span style="color:#ff0068">*</span></label>
                                <input id="focal_email" name="focal_email" type="email" value="{{ old('focal_email') }}" required>
                            </div>
                            <div class="location-note">
                                La localisation demandée correspond aux coordonnées GPS du point focal. Utilisez le bouton de détection ou renseignez la latitude et la longitude.
                            </div>
                            <div>
                                <label for="focal_latitude">Latitude <span style="color:#ff0068">*</span></label>
                                <input id="focal_latitude" name="focal_latitude" type="text" inputmode="decimal" value="{{ old('focal_latitude') }}" placeholder="Ex. 5.345317" required>
                            </div>
                            <div>
                                <label for="focal_longitude">Longitude <span style="color:#ff0068">*</span></label>
                                <input id="focal_longitude" name="focal_longitude" type="text" inputmode="decimal" value="{{ old('focal_longitude') }}" placeholder="Ex. -4.024429" required>
                            </div>
                            <div class="full">
                                <label for="focal_location">Indication complémentaire</label>
                                <input id="focal_location" name="focal_location" value="{{ old('focal_location') }}" placeholder="Ville, commune, adresse ou repère">
                            </div>
                            <input type="hidden" name="location_accuracy" id="location_accuracy" value="{{ old('location_accuracy') }}">
                        </div>
                        <div class="actions">
                            <button class="btn btn-light" type="button" id="locateButton">Détecter ma position</button>
                            <button class="btn btn-primary" type="submit">Soumettre les informations</button>
                        </div>
                    </form>
                @endif
            </div>
        </section>
    </main>

    <div class="modal-backdrop" id="confirmModal" aria-hidden="true">
        <div class="modal">
            <h2>Confirmer la désignation</h2>
            <p>Merci de vérifier les informations avant l’envoi définitif.</p>
            <div class="summary" id="confirmSummary"></div>
            <div class="actions">
                <button class="btn btn-light" type="button" id="cancelConfirm">Corriger</button>
                <button class="btn btn-primary" type="button" id="confirmSubmit">Confirmer et envoyer</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('activationForm');
            const modal = document.getElementById('confirmModal');
            const summary = document.getElementById('confirmSummary');
            const cancelConfirm = document.getElementById('cancelConfirm');
            const confirmSubmit = document.getElementById('confirmSubmit');
            const locateButton = document.getElementById('locateButton');
            let confirmed = false;

            const escapeHtml = (value) => String(value || '').replace(/[&<>"']/g, function (char) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;',
                }[char];
            });

            if (locateButton && navigator.geolocation) {
                locateButton.addEventListener('click', function () {
                    locateButton.textContent = 'Localisation en cours...';
                    navigator.geolocation.getCurrentPosition(function (position) {
                        document.getElementById('focal_latitude').value = position.coords.latitude.toFixed(8);
                        document.getElementById('focal_longitude').value = position.coords.longitude.toFixed(8);
                        document.getElementById('location_accuracy').value = Math.round(position.coords.accuracy || 0);
                        locateButton.textContent = 'Position détectée';
                    }, function () {
                        locateButton.textContent = 'Détecter ma position';
                        alert('Impossible de détecter la position. Vous pouvez renseigner la localisation manuellement.');
                    }, { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 });
                });
            }

            if (form && modal) {
                form.addEventListener('submit', function (event) {
                    if (confirmed) {
                        return;
                    }

                    event.preventDefault();

                    if (!form.reportValidity()) {
                        return;
                    }

                    const firstNames = document.getElementById('focal_first_names').value;
                    const lastName = document.getElementById('focal_last_name').value;
                    const position = document.getElementById('focal_position').value;
                    const phone = document.getElementById('focal_phone').value;
                    const email = document.getElementById('focal_email').value;
                    const location = document.getElementById('focal_location').value;
                    const latitude = document.getElementById('focal_latitude').value;
                    const longitude = document.getElementById('focal_longitude').value;

                    summary.innerHTML = `
                        <strong>${escapeHtml(firstNames)} ${escapeHtml(lastName)}</strong><br>
                        Fonction : ${escapeHtml(position)}<br>
                        Téléphone : ${escapeHtml(phone)}<br>
                        Email : ${escapeHtml(email)}<br>
                        Latitude : ${escapeHtml(latitude)}<br>
                        Longitude : ${escapeHtml(longitude)}<br>
                        Indication : ${escapeHtml(location || '-')}
                    `;
                    modal.classList.add('open');
                    modal.setAttribute('aria-hidden', 'false');
                });

                cancelConfirm.addEventListener('click', function () {
                    modal.classList.remove('open');
                    modal.setAttribute('aria-hidden', 'true');
                });

                confirmSubmit.addEventListener('click', function () {
                    confirmed = true;
                    form.submit();
                });
            }
        });
    </script>
</body>
</html>
