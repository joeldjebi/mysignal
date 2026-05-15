@extends('super-admin.layouts.app')

@section('title', config('app.name').' | Landing page')
@section('page-title', 'Landing page')
@section('page-description', 'Mettre a jour chaque section de la landing publique avec des champs simples.')

@push('styles')
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    <style>
        .rich-editor-wrap .ql-toolbar {
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            border-color: rgba(16,42,67,.12);
            background: #fff;
        }
        .rich-editor-wrap .ql-container {
            min-height: 260px;
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
            border-color: rgba(16,42,67,.12);
            font-size: .95rem;
            background: #fff;
        }
        .rich-editor-wrap textarea.rich-editor-input {
            display: none;
        }
    </style>
@endpush

@section('header-badges')
    <span class="badge-soft">Sections structurees</span>
@endsection

@section('content')
    @php
        $settingsMeta = $settings->meta ?? [];
        $inputType = fn (string $field): string => in_array($field, ['url'], true) ? 'text' : 'text';
    @endphp

    <form id="landingPageForm" method="POST" action="{{ route('super-admin.landing-page.update') }}" class="row g-4" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="col-lg-8">
            <section class="panel-card">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <div class="fw-bold">Sections de la landing</div>
                        <div class="small text-secondary">
                            Chaque rubrique est organisee en champs et tableaux. Les lignes vides ne sont pas enregistrees.
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('super-admin.landing-page.contacts.index') }}" class="btn btn-outline-dark btn-sm">Messages contact</a>
                        <a href="{{ route('public.landing') }}" target="_blank" class="btn btn-outline-dark btn-sm">Previsualiser</a>
                    </div>
                </div>

                <div class="accordion" id="landingSectionsAccordion">
                    @foreach ($sections as $key => $section)
                        <div class="accordion-item border-0 rounded-4 overflow-hidden shadow-sm mb-3">
                            <h2 class="accordion-header">
                                <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#landing-section-{{ $key }}">
                                    <span class="fw-bold">{{ $section['label'] }}</span>
                                    <span class="small text-secondary ms-2">Section {{ $loop->iteration }}</span>
                                </button>
                            </h2>
                            <div id="landing-section-{{ $key }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#landingSectionsAccordion">
                                <div class="accordion-body">
                                    <div class="form-check form-switch mb-3">
                                        <input type="hidden" name="sections[{{ $key }}][is_active]" value="0">
                                        <input class="form-check-input" type="checkbox" name="sections[{{ $key }}][is_active]" value="1" id="section-active-{{ $key }}" @checked($section['is_active_value'])>
                                        <label class="form-check-label" for="section-active-{{ $key }}">Afficher cette section</label>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Titre</label>
                                            <input class="form-control" name="sections[{{ $key }}][title]" value="{{ $section['title_value'] }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Badge / sous-titre</label>
                                            <input class="form-control" name="sections[{{ $key }}][subtitle]" value="{{ $section['subtitle_value'] }}">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">Texte principal</label>
                                            @if (in_array($key, ['page_about', 'page_contact', 'page_tv', 'page_terms', 'page_privacy'], true))
                                                <div class="rich-editor-wrap">
                                                    <textarea class="rich-editor-input" name="sections[{{ $key }}][body]">{{ $section['body_value'] }}</textarea>
                                                    <div class="rich-editor">{!! $section['body_value'] !!}</div>
                                                </div>
                                                <div class="small text-secondary mt-1">
                                                    Utilisez la barre d'outils pour mettre en forme le contenu. Le HTML est enregistre automatiquement.
                                                </div>
                                            @else
                                                <textarea class="form-control" name="sections[{{ $key }}][body]" rows="3">{{ $section['body_value'] }}</textarea>
                                            @endif
                                        </div>

                                        @foreach ($section['meta_fields'] as $field => $definition)
                                            <div class="col-md-6">
                                                <label class="form-label">{{ $definition['label'] }}</label>
                                                <input class="form-control" name="sections[{{ $key }}][meta][{{ $field }}]" value="{{ old("sections.$key.meta.$field", $section['meta_value'][$field] ?? $definition['default'] ?? '') }}">
                                            </div>
                                        @endforeach
                                    </div>

                                    @foreach ($section['item_groups'] as $groupKey => $group)
                                        <div class="mt-4">
                                            <div class="d-flex justify-content-between align-items-center gap-3 mb-2">
                                                <div class="fw-semibold">{{ $group['label'] }}</div>
                                                <div class="small text-secondary">Decoche une ligne pour la masquer, vide-la pour la supprimer.</div>
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table align-middle bg-white rounded-4 overflow-hidden">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th style="width:80px">Afficher</th>
                                                            @foreach ($group['columns'] as $field => $label)
                                                                <th>{{ $label }}</th>
                                                            @endforeach
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($group['items'] as $index => $item)
                                                            <tr>
                                                                <td>
                                                                    <input type="hidden" name="items[{{ $key }}][{{ $groupKey }}][{{ $index }}][is_active]" value="0">
                                                                    <input class="form-check-input" type="checkbox" name="items[{{ $key }}][{{ $groupKey }}][{{ $index }}][is_active]" value="1" @checked(old("items.$key.$groupKey.$index.is_active", $item['is_active'] ?? true))>
                                                                </td>
                                                                @foreach ($group['columns'] as $field => $label)
                                                                    <td style="min-width: {{ $field === 'body' ? '260px' : '150px' }}">
                                                                        @php
                                                                            $value = old("items.$key.$groupKey.$index.$field", $item[$field] ?? '');
                                                                        @endphp
                                                                        @if ($field === 'body')
                                                                            <textarea class="form-control" rows="2" name="items[{{ $key }}][{{ $groupKey }}][{{ $index }}][{{ $field }}]">{{ $value }}</textarea>
                                                                        @elseif ($key === 'page_tv' && $groupKey === 'videos' && $field === 'url')
                                                                            <div class="d-grid gap-2">
                                                                                <input type="hidden" name="items[{{ $key }}][{{ $groupKey }}][{{ $index }}][existing_url]" value="{{ $value }}">
                                                                                @if ($value)
                                                                                    <div class="small text-secondary text-break">Fichier actuel: {{ $value }}</div>
                                                                                @endif
                                                                                <input type="file" class="form-control" name="items[{{ $key }}][{{ $groupKey }}][{{ $index }}][video_file]" accept=".mp4,.mov,.webm,.m4v,video/mp4,video/quicktime,video/webm">
                                                                                <div class="small text-secondary">Charge une video. Elle sera stockee sur Wasabi.</div>
                                                                            </div>
                                                                        @elseif ($key === 'partners' && $groupKey === 'items' && $field === 'url')
                                                                            @php
                                                                                $previewUrl = $value;
                                                                                if ($previewUrl && (str_starts_with($previewUrl, 'landing/') || str_starts_with($previewUrl, 'applications/'))) {
                                                                                    $previewUrl = app(\App\Services\WasabiService::class)->temporaryUrl($previewUrl);
                                                                                }
                                                                            @endphp
                                                                            <div class="d-grid gap-2">
                                                                                <input type="hidden" name="items[{{ $key }}][{{ $groupKey }}][{{ $index }}][existing_url]" value="{{ $value }}">
                                                                                @if ($value)
                                                                                    <div class="d-flex align-items-center gap-2">
                                                                                        <img src="{{ $previewUrl }}" alt="Logo partenaire" style="width:42px;height:42px;border-radius:12px;object-fit:contain;background:#fff;padding:.3rem;border:1px solid rgba(16,42,67,.08)">
                                                                                        <span class="small text-secondary text-break">{{ $value }}</span>
                                                                                    </div>
                                                                                @endif
                                                                                <input type="file" class="form-control" name="items[{{ $key }}][{{ $groupKey }}][{{ $index }}][url_file]" accept=".jpg,.jpeg,.png,.webp">
                                                                                <div class="small text-secondary">Charge un logo image. Si aucun fichier n est choisi, le logo actuel est conserve.</div>
                                                                            </div>
                                                                        @else
                                                                            <input type="{{ $inputType($field) }}" class="form-control" name="items[{{ $key }}][{{ $groupKey }}][{{ $index }}][{{ $field }}]" value="{{ $value }}">
                                                                        @endif
                                                                    </td>
                                                                @endforeach
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <button class="btn btn-dark">Enregistrer les sections</button>
                </div>
            </section>
        </div>

        <div class="col-lg-4">
            <section class="panel-card sticky-form-card">
                <div class="fw-bold mb-3">Parametres visuels</div>
                <div class="alert alert-info small">
                    Le SA modifie les contenus dans des champs lisibles. Le design de la landing reste protege.
                </div>

                @php
                    $colors = [
                        'primary_color' => ['Primaire', '#183447'],
                        'secondary_color' => ['Secondaire', '#256f8f'],
                        'accent_color' => ['Accent', '#ff0068'],
                    ];
                @endphp
                @foreach ($colors as $field => [$label, $default])
                    <div class="mb-3">
                        <label class="form-label">{{ $label }}</label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color" value="{{ old($field, $settingsMeta[$field] ?? $default) }}" onchange="this.nextElementSibling.value = this.value">
                            <input class="form-control" name="{{ $field }}" value="{{ old($field, $settingsMeta[$field] ?? $default) }}" pattern="#[0-9A-Fa-f]{6}">
                        </div>
                    </div>
                @endforeach

                <div class="fw-bold mb-2 mt-4">Logo</div>
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ $defaultLogoUrl }}" alt="My-Signal" style="width:64px;height:64px;object-fit:contain;border-radius:16px;background:white;padding:.35rem;border:1px solid rgba(16,42,67,.08)">
                    <div class="small text-secondary">
                        Le logo officiel reste affiche automatiquement sur la landing.
                    </div>
                </div>
            </section>
        </div>
    </form>
@endsection

@push('scripts')
    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('landingPageForm');
            const editors = [];

            document.querySelectorAll('.rich-editor-wrap').forEach((wrap) => {
                const editorNode = wrap.querySelector('.rich-editor');
                const input = wrap.querySelector('.rich-editor-input');
                const editor = new Quill(editorNode, {
                    theme: 'snow',
                    modules: {
                        toolbar: [
                            [{ header: [2, 3, false] }],
                            ['bold', 'italic', 'underline'],
                            [{ list: 'ordered' }, { list: 'bullet' }],
                            ['link', 'blockquote'],
                            ['clean']
                        ]
                    }
                });

                const syncEditor = () => {
                    input.value = editor.root.innerHTML;
                };

                editor.on('text-change', syncEditor);
                syncEditor();

                editors.push({ editor, input, syncEditor });
            });

            form?.addEventListener('submit', () => {
                editors.forEach(({ syncEditor }) => syncEditor());
            }, true);
        });
    </script>
@endpush
