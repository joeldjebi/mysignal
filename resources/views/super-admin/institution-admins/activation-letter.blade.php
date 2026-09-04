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
    @php
        $header = $letter->headerSettings();
        $footer = $letter->footerSettings();
        $textStyle = function (array $settings): string {
            return collect([
                'font-size: '.(int) ($settings['size'] ?? 10).'px',
                'color: '.($settings['color'] ?? '#475467'),
                ! empty($settings['bold']) ? 'font-weight: 800' : 'font-weight: 400',
                ! empty($settings['italic']) ? 'font-style: italic' : 'font-style: normal',
            ])->implode('; ');
        };
        $splitForTwoPages = function (string $html): array {
            $blocks = preg_split('/(?=<p\b|<div\b|<h[2-4]\b|<ul\b|<ol\b)/i', trim($html), -1, PREG_SPLIT_NO_EMPTY) ?: [];

            if ($blocks === []) {
                return [$html, ''];
            }

            $first = '';
            $second = '';
            $firstLineBudget = 22;
            $usedLines = 0;
            $lineWidth = 92;

            foreach ($blocks as $block) {
                $blockText = trim(html_entity_decode(strip_tags($block), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
                $blockLength = mb_strlen($blockText);
                $blockLines = max(1, (int) ceil($blockLength / $lineWidth)) + 1;

                if ($first !== '' && ($usedLines + $blockLines) > $firstLineBudget) {
                    $remainingLines = max(0, $firstLineBudget - $usedLines);

                    if ($remainingLines > 1) {
                        $words = preg_split('/\s+/u', $blockText, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                        $firstWords = [];
                        $secondWords = [];
                        $lineCount = 1;
                        $lineLength = 0;

                        foreach ($words as $word) {
                            $wordLength = mb_strlen($word);
                            $nextLength = $lineLength + $wordLength + ($lineLength > 0 ? 1 : 0);

                            if ($nextLength > $lineWidth) {
                                $lineCount++;
                                $lineLength = $wordLength;
                            } else {
                                $lineLength = $nextLength;
                            }

                            if ($lineCount <= $remainingLines) {
                                $firstWords[] = $word;
                            } else {
                                $secondWords[] = $word;
                            }
                        }

                        $first .= '<p>'.e(implode(' ', $firstWords)).'</p>';
                        $second .= '<p>'.e(implode(' ', $secondWords)).'</p>';
                        $usedLines = $firstLineBudget;
                    } else {
                        $second .= $block;
                    }
                } elseif ($first === '' && $blockLines > $firstLineBudget) {
                    $words = preg_split('/\s+/u', $blockText, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                    $firstWords = [];
                    $secondWords = [];
                    $lineCount = 1;
                    $lineLength = 0;

                    foreach ($words as $word) {
                        $wordLength = mb_strlen($word);
                        $nextLength = $lineLength + $wordLength + ($lineLength > 0 ? 1 : 0);

                        if ($nextLength > $lineWidth) {
                            $lineCount++;
                            $lineLength = $wordLength;
                        } else {
                            $lineLength = $nextLength;
                        }

                        if ($lineCount <= $firstLineBudget) {
                            $firstWords[] = $word;
                        } else {
                            $secondWords[] = $word;
                        }
                    }

                    $first .= '<p>'.e(implode(' ', $firstWords)).'</p>';
                    $second .= '<p>'.e(implode(' ', $secondWords)).'</p>';
                    $usedLines = $firstLineBudget;
                } else {
                    $first .= $block;
                    $usedLines += $blockLines;
                }
            }

            return [trim($first), trim($second)];
        };
        [$mainLetterContent, $secondPageLetterContent] = $splitForTwoPages((string) old('letter_content', $letter->letter_content));
    @endphp

    <style>
        .letter-grid { display: grid; grid-template-columns: minmax(0, .95fr) minmax(0, 1.05fr); gap: 1.25rem; }
        .letter-preview { background: #f8fafc; border: 1px solid rgba(15, 23, 42, .08); box-shadow: 0 28px 70px rgba(15, 23, 42, .10); color: #101828; }
        .letter-preview-page { display: flex; flex-direction: column; background: #fff; height: 860px; padding: 2.2rem; overflow: hidden; }
        .letter-preview-page + .letter-preview-page { margin-top: 1rem; }
        .letter-preview-page.second-page { display: flex; flex-direction: column; }
        .page-content { flex: 1 1 auto; min-height: 0; overflow: hidden; }
        .second-page-content { flex: 0 0 auto; }
        .official-header { border-bottom: 3px solid #ffa117; padding-bottom: 1rem; margin-bottom: 1.8rem; }
        .header-brand-row { display: flex; align-items: center; justify-content: space-between; gap: .75rem; }
        .header-main { display: flex; align-items: center; gap: .55rem; min-width: 0; }
        .letter-logo { height: auto; object-fit: contain; }
        .letter-reference { display: inline-block; border: 1px solid rgba(103, 145, 255, .28); background: rgba(103, 145, 255, .08); border-radius: 999px; padding: .35rem .75rem; font-size: .78rem; color: #24426f; }
        .activation-link-box { border: 1px solid rgba(255, 161, 23, .35); background: rgba(255, 161, 23, .08); border-radius: 12px; padding: 1rem; }
        .rich-toolbar { display: flex; flex-wrap: wrap; gap: .4rem; padding: .55rem; border: 1px solid #d0d5dd; border-bottom: 0; border-radius: .5rem .5rem 0 0; background: #f8fafc; }
        .rich-toolbar button { min-width: 34px; height: 34px; border: 1px solid #d0d5dd; border-radius: .45rem; background: #fff; font-weight: 700; }
        .rich-editor { min-height: 330px; border: 1px solid #d0d5dd; border-radius: 0 0 .5rem .5rem; padding: 1rem; line-height: 1.65; background: #fff; outline-color: #6791ff; overflow: auto; }
        .letter-body { line-height: 1.72; font-size: .98rem; }
        .letter-preview-page:first-child .letter-body { max-height: 430px; overflow: hidden; }
        .second-page-content .letter-body { max-height: 240px; overflow: hidden; }
        .letter-body p { margin-bottom: .9rem; }
        .security-box { border: 1px solid rgba(255, 161, 23, .38); background: #fffaf0; border-radius: 10px; padding: .45rem .6rem; font-size: .82rem; }
        .security-box a, .footer-preview a { color: inherit; text-decoration: none; }
        .signature-image { max-width: 180px; max-height: 78px; object-fit: contain; }
        .signature-space { height: 58px; border-bottom: 1px solid #98a2b3; margin-left: auto; max-width: 220px; }
        .footer-preview { display: grid; grid-template-columns: .72fr repeat(4, minmax(0, 1fr)); gap: .85rem; align-items: start; border-top: 1px solid #e4e7ec; margin-top: auto; padding-top: 1rem; }
        .footer-label { min-height: 18px; line-height: 18px; }
        .style-controls { display: grid; grid-template-columns: 92px 82px repeat(2, minmax(0, 1fr)); gap: .5rem; align-items: end; }
        @media (max-width: 991.98px) { .letter-grid { grid-template-columns: 1fr; } }
        @media (max-width: 767.98px) { .footer-preview { grid-template-columns: repeat(2, minmax(0, 1fr)); } .style-controls { grid-template-columns: 1fr 1fr; } }
    </style>

    <div class="letter-grid">
        <section class="panel-card">
            <div class="fw-bold mb-3">Paramètres du courrier</div>
            <div class="alert alert-info small">
                Une fois enregistrés, le logo, la signature, les couleurs et les éléments du pied de page seront repris par défaut sur les prochains courriers.
            </div>
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

                <div class="border rounded-3 p-3 mb-3">
                    <div class="fw-semibold mb-3">En-tête officiel</div>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Logo du courrier</label>
                            <input type="file" name="logo" class="form-control" accept="image/png,image/jpeg,image/webp">
                            <div class="form-text">PNG, JPG ou WebP. Taille maximale : 5 Mo.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Taille du logo</label>
                            <input type="number" name="logo_width" value="{{ old('logo_width', $header['logo_width']) }}" min="60" max="260" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Position du logo</label>
                            <select name="logo_position" class="form-select">
                                @foreach ($logoPositions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('logo_position', $letter->logo_position) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <label class="form-check mb-2">
                                <input type="checkbox" name="remove_logo" value="1" class="form-check-input">
                                <span class="form-check-label">Retirer le logo personnalisé</span>
                            </label>
                        </div>
                    </div>

                    @foreach ([
                        'header_title' => ['label' => 'Texte principal', 'settings' => $header['title']],
                        'header_subtitle' => ['label' => 'Texte secondaire', 'settings' => $header['subtitle']],
                        'header_description' => ['label' => 'Description', 'settings' => $header['description']],
                    ] as $prefix => $field)
                        <div class="mt-3">
                            <label class="form-label">{{ $field['label'] }}</label>
                            <input type="text" name="{{ $prefix }}_text" value="{{ old($prefix.'_text', $field['settings']['text']) }}" class="form-control mb-2">
                            <div class="style-controls">
                                <div>
                                    <label class="form-label small text-secondary">Taille</label>
                                    <input type="number" name="{{ $prefix }}_size" value="{{ old($prefix.'_size', $field['settings']['size']) }}" min="8" max="28" class="form-control">
                                </div>
                                <div>
                                    <label class="form-label small text-secondary">Couleur</label>
                                    <input type="color" name="{{ $prefix }}_color" value="{{ old($prefix.'_color', $field['settings']['color']) }}" class="form-control form-control-color w-100">
                                </div>
                                <label class="form-check mb-2">
                                    <input type="checkbox" name="{{ $prefix }}_bold" value="1" class="form-check-input" @checked(old($prefix.'_bold', $field['settings']['bold']))>
                                    <span class="form-check-label">Gras</span>
                                </label>
                                <label class="form-check mb-2">
                                    <input type="checkbox" name="{{ $prefix }}_italic" value="1" class="form-check-input" @checked(old($prefix.'_italic', $field['settings']['italic']))>
                                    <span class="form-check-label">Italique</span>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mb-3">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Numéro du courrier</label>
                            <input type="text" name="letter_number" value="{{ old('letter_number', $letter->letter_number) }}" class="form-control" placeholder="UFC/MS/2026/000001">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Lieu</label>
                            <input type="text" name="issue_place" value="{{ old('issue_place', $letter->issue_place ?: 'Abidjan') }}" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date du courrier</label>
                            <input type="date" name="issue_date" value="{{ old('issue_date', $letter->issue_date?->format('Y-m-d') ?: now()->toDateString()) }}" class="form-control">
                        </div>
                    </div>
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
                </div>

                <div class="border rounded-3 p-3 mb-3">
                    <div class="fw-semibold mb-3">Signature du courrier</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nom du signataire</label>
                            <input type="text" name="signature_name" value="{{ old('signature_name', $letter->signature_name) }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fonction du signataire</label>
                            <input type="text" name="signature_title" value="{{ old('signature_title', $letter->signature_title) }}" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Texte de signature</label>
                            <textarea name="signature_content" rows="3" class="form-control">{{ old('signature_content', $letter->signature_content) }}</textarea>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Signature scannée</label>
                            <input type="file" name="signature_image" class="form-control" accept="image/png,image/jpeg,image/webp">
                            <div class="form-text">Si aucune signature n’est chargée, un espace sera laissé pour signer manuellement.</div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <label class="form-check mb-2">
                                <input type="checkbox" name="remove_signature_image" value="1" class="form-check-input">
                                <span class="form-check-label">Retirer la signature</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="border rounded-3 p-3">
                    <div class="fw-semibold mb-3">Pied de page</div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label class="form-label">Logo du pied de page</label>
                            <input type="file" name="footer_logo" class="form-control" accept="image/png,image/jpeg,image/webp">
                            <div class="form-text">Ce logo est indépendant du logo d’en-tête.</div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <label class="form-check mb-2">
                                <input type="checkbox" name="remove_footer_logo" value="1" class="form-check-input">
                                <span class="form-check-label">Retirer ce logo</span>
                            </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Taille du logo dans le pied de page</label>
                        <input type="number" name="footer_logo_size" value="{{ old('footer_logo_size', $footer['logo']['size'] ?? 72) }}" min="32" max="120" class="form-control">
                    </div>
                    @foreach ([
                        'footer_address' => ['label' => 'Adresse', 'settings' => $footer['address']],
                        'footer_phone' => ['label' => 'Téléphone', 'settings' => $footer['phone']],
                        'footer_email' => ['label' => 'Email', 'settings' => $footer['email']],
                        'footer_website' => ['label' => 'Site web', 'settings' => $footer['website']],
                    ] as $prefix => $field)
                        <div class="mb-3">
                            <label class="form-label">{{ $field['label'] }}</label>
                            <textarea name="{{ $prefix }}_text" rows="2" class="form-control mb-2">{{ old($prefix.'_text', $field['settings']['text']) }}</textarea>
                            <div class="style-controls">
                                <div>
                                    <label class="form-label small text-secondary">Taille</label>
                                    <input type="number" name="{{ $prefix }}_size" value="{{ old($prefix.'_size', $field['settings']['size']) }}" min="8" max="16" class="form-control">
                                </div>
                                <div>
                                    <label class="form-label small text-secondary">Couleur</label>
                                    <input type="color" name="{{ $prefix }}_color" value="{{ old($prefix.'_color', $field['settings']['color']) }}" class="form-control form-control-color w-100">
                                </div>
                                <label class="form-check mb-2">
                                    <input type="checkbox" name="{{ $prefix }}_bold" value="1" class="form-check-input" @checked(old($prefix.'_bold', $field['settings']['bold']))>
                                    <span class="form-check-label">Gras</span>
                                </label>
                                <label class="form-check mb-2">
                                    <input type="checkbox" name="{{ $prefix }}_italic" value="1" class="form-check-input" @checked(old($prefix.'_italic', $field['settings']['italic']))>
                                    <span class="form-check-label">Italique</span>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3">
                    <label class="form-label">Date d’expiration du code</label>
                    <input type="datetime-local" name="expires_at" value="{{ old('expires_at', $letter->expires_at?->format('Y-m-d\\TH:i')) }}" class="form-control">
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
                <div class="letter-preview-page">
                    <div class="page-content">
                        <div class="official-header">
                            <div class="header-brand-row">
                                <div class="header-main">
                                    @if ($logoUrl && $logoPosition !== 'none')
                                        <img src="{{ $logoUrl }}" class="letter-logo" id="previewLetterLogo" style="width: {{ (int) old('logo_width', $header['logo_width']) }}px;" alt="Logo">
                                    @endif
                                    <div>
                                        <div style="{{ $textStyle($header['title']) }}">{{ $header['title']['text'] }}</div>
                                        <div style="{{ $textStyle($header['subtitle']) }}">{{ $header['subtitle']['text'] }}</div>
                                        <div style="{{ $textStyle($header['description']) }}">{{ $header['description']['text'] }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <div class="letter-reference">N° {{ $letter->letter_number ?: 'UFC/MS/'.now()->format('Y').'/000001' }}</div>
                                <div class="letter-reference">Code {{ $letter->activation_code }}</div>
                            </div>
                        </div>
                        <div class="text-end small text-secondary mb-4">{{ $letter->issue_place ?: 'Abidjan' }}, le {{ ($letter->issue_date ?: now())->format('d/m/Y') }}</div>
                        <div class="mb-4">
                            <div class="fw-semibold">À l’attention de {{ $institutionAdmin->organization?->name ?: 'l’institution' }}</div>
                            <div class="small text-secondary">{{ $institutionAdmin->organization?->address ?: '' }}</div>
                        </div>
                        <div class="fw-bold mb-4">Objet : {{ old('letter_subject', $letter->letter_subject) }}</div>
                        <div class="letter-body mb-4" id="letterPreviewContent">{!! $mainLetterContent !!}</div>
                    </div>
                    @include('super-admin.institution-admins.partials.activation-letter-footer')
                </div>
                <div class="letter-preview-page second-page">
                    <div class="second-page-content">
                        @if (filled($secondPageLetterContent))
                            <div class="letter-body mb-4" id="letterPreviewSecondContent">{!! $secondPageLetterContent !!}</div>
                        @endif
                        <div class="mt-5 mb-4 text-end">
                            <div class="letter-body mb-3">{!! $letter->signatureHtml() !!}</div>
                            @if ($letter->signatureUrl())
                                <img src="{{ $letter->signatureUrl() }}" class="signature-image mb-2" alt="Signature">
                            @else
                                <div class="signature-space mb-2"></div>
                            @endif
                            <div class="fw-bold">{{ old('signature_name', $letter->signature_name) ?: 'Le Coordonnateur du programme My-Signal' }}</div>
                            <div class="small text-secondary">{{ old('signature_title', $letter->signature_title) ?: 'Union Fédérale des Consommateurs' }}</div>
                        </div>
                        <div class="row g-2 align-items-center security-box">
                            <div class="col-sm-9">
                                <div class="small text-secondary">Code d’activation</div>
                                <div class="fw-bold">{{ $letter->activation_code }}</div>
                                <div class="small text-secondary mt-2">Lien</div>
                                <a href="{{ $letter->activation_url }}" class="small" target="_blank" rel="noopener">{{ $letter->activation_url }}</a>
                            </div>
                            <div class="col-sm-3 text-sm-end">
                                <div id="activationQr" class="d-inline-block bg-white p-2 rounded-3"></div>
                            </div>
                        </div>
                    </div>
                    @include('super-admin.institution-admins.partials.activation-letter-footer')
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
            const logoWidthInput = document.querySelector('input[name="logo_width"]');
            const previewLetterLogo = document.getElementById('previewLetterLogo');

            if (container && window.QRCode) {
                new QRCode(container, {
                    text: @json($letter->activation_url),
                    width: 82,
                    height: 82,
                    correctLevel: QRCode.CorrectLevel.M,
                });
            }

            document.querySelectorAll('[data-command]').forEach((button) => {
                button.addEventListener('click', function () {
                    editor?.focus();
                    document.execCommand(this.dataset.command, false, null);
                    if (input && editor) input.value = editor.innerHTML;
                    if (preview && editor) preview.innerHTML = editor.innerHTML;
                });
            });

            logoWidthInput?.addEventListener('input', function () {
                if (previewLetterLogo) previewLetterLogo.style.width = `${this.value || 145}px`;
            });

            editor?.addEventListener('input', function () {
                if (input) input.value = editor.innerHTML;
                if (preview) preview.innerHTML = editor.innerHTML;
            });

            form?.addEventListener('submit', function () {
                if (input && editor) input.value = editor.innerHTML;
            });
        });
    </script>
@endsection
