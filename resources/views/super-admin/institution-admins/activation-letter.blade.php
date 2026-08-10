@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Courrier point focal')
@section('page-title', 'Courrier du point focal')
@section('page-description', 'Préparer le courrier officiel transmis à l’institution pour la désignation du point focal.')

@section('header-badges')
    <a href="{{ route('super-admin.institution-admins.index') }}" class="btn btn-outline-secondary">Retour</a>
    <a href="{{ route('super-admin.institution-admins.activation-letter.print', $institutionAdmin) }}" target="_blank" rel="noopener" class="btn btn-outline-dark">Aperçu imprimable</a>
    <a href="{{ route('super-admin.institution-admins.activation-letter.pdf', $institutionAdmin) }}" class="btn btn-dark">Télécharger le PDF</a>
@endsection

@section('content')
    <style>
        .letter-grid {
            display: grid;
            grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr);
            gap: 1.25rem;
        }
        .letter-preview {
            background: #fff;
            border: 1px solid rgba(15, 23, 42, .08);
            box-shadow: 0 28px 70px rgba(15, 23, 42, .10);
            padding: 2.2rem;
            min-height: 820px;
            color: #101828;
        }
        .letter-logo {
            max-width: 130px;
            max-height: 95px;
            object-fit: contain;
        }
        .letter-logo-row.logo-left { text-align: left; }
        .letter-logo-row.logo-center { text-align: center; }
        .letter-logo-row.logo-right { text-align: right; }
        .activation-link-box {
            border: 1px solid rgba(255, 161, 23, .35);
            background: rgba(255, 161, 23, .08);
            border-radius: 12px;
            padding: 1rem;
        }
        .official-header {
            border-bottom: 3px solid #ffa117;
            padding-bottom: 1rem;
            margin-bottom: 1.8rem;
        }
        .letter-reference {
            display: inline-block;
            border: 1px solid rgba(103, 145, 255, .28);
            background: rgba(103, 145, 255, .08);
            border-radius: 999px;
            padding: .35rem .75rem;
            font-size: .78rem;
            color: #24426f;
        }
        .rich-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: .4rem;
            padding: .55rem;
            border: 1px solid #d0d5dd;
            border-bottom: 0;
            border-radius: .5rem .5rem 0 0;
            background: #f8fafc;
        }
        .rich-toolbar button {
            min-width: 34px;
            height: 34px;
            border: 1px solid #d0d5dd;
            border-radius: .45rem;
            background: #fff;
            font-weight: 700;
        }
        .rich-editor {
            min-height: 360px;
            border: 1px solid #d0d5dd;
            border-radius: 0 0 .5rem .5rem;
            padding: 1rem;
            line-height: 1.65;
            background: #fff;
            outline-color: #6791ff;
            overflow: auto;
        }
        .rich-editor:focus {
            border-color: #6791ff;
            box-shadow: 0 0 0 .2rem rgba(103, 145, 255, .12);
        }
        .letter-body {
            line-height: 1.72;
            font-size: .98rem;
        }
        .letter-body p {
            margin-bottom: .9rem;
        }
        .security-box {
            border: 1px solid rgba(255, 161, 23, .45);
            background: #fffaf0;
            border-radius: 14px;
            padding: 1rem;
        }
        @media (max-width: 991.98px) {
            .letter-grid { grid-template-columns: 1fr; }
        }
    </style>

    <div class="letter-grid">
        <section class="panel-card">
            <div class="fw-bold mb-3">Paramètres du courrier</div>
            <div class="mb-3 p-3 rounded-3 bg-light">
                <div class="small text-secondary">Institution</div>
                <div class="fw-semibold">{{ $institutionAdmin->organization?->name ?: '-' }}</div>
                <div class="small text-secondary mt-2">Admin institutionnel</div>
                <div>{{ $institutionAdmin->name }} · {{ $institutionAdmin->email }}</div>
            </div>

            <div class="activation-link-box mb-3">
                <div class="small text-secondary">Code d’activation</div>
                <div class="h5 mb-2">{{ $letter->activation_code }}</div>
                <div class="small text-secondary">Lien du formulaire</div>
                <a href="{{ $letter->activation_url }}" target="_blank" rel="noopener">{{ $letter->activation_url }}</a>
            </div>

            @if ($letter->submitted_at)
                <div class="alert alert-success">
                    <div class="fw-semibold">Point focal transmis</div>
                    <div>{{ $letter->focal_first_names }} {{ $letter->focal_last_name }} · {{ $letter->focal_position }}</div>
                    <div class="small">{{ $letter->focal_phone }} · {{ $letter->focal_email }}</div>
                    <div class="small">Latitude : {{ $letter->focal_latitude ?: '-' }} · Longitude : {{ $letter->focal_longitude ?: '-' }}</div>
                    <div class="small">Indication : {{ $letter->focal_location ?: '-' }}</div>
                    @if ($letter->status === 'submitted')
                        <form method="POST" action="{{ route('super-admin.institution-admins.activation-letter.send-access', $institutionAdmin) }}" class="mt-3" onsubmit="return confirm('Confirmer la validation du point focal et envoyer les accès par SMS ?')">
                            @csrf
                            <button class="btn btn-success">Valider et envoyer les accès</button>
                        </form>
                    @elseif ($letter->status === 'approved')
                        <div class="badge text-bg-success mt-3">Point focal validé</div>
                    @endif
                </div>
            @endif

            <form method="POST" action="{{ route('super-admin.institution-admins.activation-letter.update', $institutionAdmin) }}" enctype="multipart/form-data" id="letterForm">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Objet <span class="text-danger">*</span></label>
                    <input type="text" name="letter_subject" value="{{ old('letter_subject', $letter->letter_subject) }}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contenu du courrier <span class="text-danger">*</span></label>
                    <div class="rich-toolbar" aria-label="Outils de mise en forme">
                        <button type="button" data-command="bold" title="Gras">B</button>
                        <button type="button" data-command="italic" title="Italique"><em>I</em></button>
                        <button type="button" data-command="underline" title="Souligné"><u>U</u></button>
                        <button type="button" data-command="insertUnorderedList" title="Liste à puces">•</button>
                        <button type="button" data-command="insertOrderedList" title="Liste numérotée">1.</button>
                        <button type="button" data-command="justifyLeft" title="Aligner à gauche">↤</button>
                        <button type="button" data-command="justifyCenter" title="Centrer">↔</button>
                        <button type="button" data-command="justifyRight" title="Aligner à droite">↦</button>
                    </div>
                    <div class="rich-editor" id="letterEditor" contenteditable="true">{!! old('letter_content', $letter->letter_content) !!}</div>
                    <textarea name="letter_content" id="letterContentInput" class="d-none" required>{{ old('letter_content', $letter->letter_content) }}</textarea>
                    <div class="form-text">Le lien, le code et le QR code restent affichés dans le courrier. Vous pouvez enrichir le texte avec du gras, de l’italique, des listes et des alignements.</div>
                </div>
                <div class="border rounded-3 p-3 mb-3">
                    <div class="fw-semibold mb-3">Signature du courrier</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nom du signataire</label>
                            <input type="text" name="signature_name" value="{{ old('signature_name', $letter->signature_name) }}" class="form-control" placeholder="Nom et prénoms">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fonction du signataire</label>
                            <input type="text" name="signature_title" value="{{ old('signature_title', $letter->signature_title) }}" class="form-control" placeholder="Fonction ou qualité">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Texte de signature</label>
                            <textarea name="signature_content" rows="3" class="form-control">{{ old('signature_content', $letter->signature_content) }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Position du logo</label>
                        <select name="logo_position" class="form-select">
                            @foreach ($logoPositions as $value => $label)
                                <option value="{{ $value }}" @selected(old('logo_position', $letter->logo_position) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date d’expiration du code</label>
                        <input type="datetime-local" name="expires_at" value="{{ old('expires_at', $letter->expires_at?->format('Y-m-d\\TH:i')) }}" class="form-control">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Logo du courrier</label>
                        <input type="file" name="logo" class="form-control" accept="image/png,image/jpeg,image/webp">
                        <div class="form-text">PNG, JPG ou WebP. Taille maximale : 5 Mo.</div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <label class="form-check mb-2">
                            <input type="checkbox" name="remove_logo" value="1" class="form-check-input">
                            <span class="form-check-label">Retirer le logo personnalisé</span>
                        </label>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button class="btn btn-dark">Enregistrer</button>
                </div>
            </form>
        </section>

        <section class="panel-card">
            <div class="fw-bold mb-3">Aperçu du courrier</div>
            <div class="letter-preview">
                @php
                    $logoUrl = $letter->logoUrl();
                    $logoPosition = old('logo_position', $letter->logo_position);
                @endphp
                <div class="official-header">
                    @if ($logoUrl && $logoPosition !== 'none')
                        <div class="letter-logo-row logo-{{ $logoPosition }} mb-3">
                            <img src="{{ $logoUrl }}" class="letter-logo" alt="Logo">
                        </div>
                    @endif
                    <div class="d-flex justify-content-between flex-wrap gap-2 align-items-end">
                        <div>
                            <div class="fw-bold h5 mb-1">Union Fédérale des Consommateurs</div>
                            <div class="text-secondary">Programme My-Signal</div>
                        </div>
                        <div class="letter-reference">Réf. {{ $letter->activation_code }}</div>
                    </div>
                </div>
                <div class="text-end small text-secondary mb-4">Abidjan, le {{ now()->format('d/m/Y') }}</div>
                <div class="mb-4">
                    <div class="fw-semibold">À l’attention de {{ $institutionAdmin->organization?->name ?: 'l’institution' }}</div>
                    <div class="small text-secondary">{{ $institutionAdmin->organization?->address ?: '' }}</div>
                </div>
                <div class="fw-bold mb-4">Objet : {{ old('letter_subject', $letter->letter_subject) }}</div>
                <div class="letter-body mb-4" id="letterPreviewContent">{!! old('letter_content', $letter->letter_content) !!}</div>
                <div class="mt-5 mb-4 text-end">
                    <div class="letter-body mb-3">{!! $letter->signatureHtml() !!}</div>
                    <div class="fw-bold">{{ old('signature_name', $letter->signature_name) ?: 'Le Coordonnateur du programme My-Signal' }}</div>
                    <div class="small text-secondary">{{ old('signature_title', $letter->signature_title) ?: 'Union Fédérale des Consommateurs' }}</div>
                </div>
                <div class="row g-3 align-items-center security-box">
                    <div class="col-sm-8">
                        <div class="small text-secondary">Code d’activation</div>
                        <div class="fw-bold">{{ $letter->activation_code }}</div>
                        <div class="small text-secondary mt-2">Lien</div>
                        <div class="small">{{ $letter->activation_url }}</div>
                    </div>
                    <div class="col-sm-4 text-sm-end">
                        <div id="activationQr" class="d-inline-block bg-white p-2 rounded-3"></div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" referrerpolicy="no-referrer"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('activationQr');
            const form = document.getElementById('letterForm');
            const editor = document.getElementById('letterEditor');
            const input = document.getElementById('letterContentInput');
            const preview = document.getElementById('letterPreviewContent');

            if (container && window.QRCode) {
                new QRCode(container, {
                    text: @json($letter->activation_url),
                    width: 124,
                    height: 124,
                    correctLevel: QRCode.CorrectLevel.M,
                });
            }

            document.querySelectorAll('[data-command]').forEach((button) => {
                button.addEventListener('click', function () {
                    editor?.focus();
                    document.execCommand(this.dataset.command, false, null);
                    if (input && editor) {
                        input.value = editor.innerHTML;
                    }
                    if (preview && editor) {
                        preview.innerHTML = editor.innerHTML;
                    }
                });
            });

            editor?.addEventListener('input', function () {
                if (input) {
                    input.value = editor.innerHTML;
                }
                if (preview) {
                    preview.innerHTML = editor.innerHTML;
                }
            });

            form?.addEventListener('submit', function () {
                if (input && editor) {
                    input.value = editor.innerHTML;
                }
            });
        });
    </script>
@endsection
