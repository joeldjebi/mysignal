@php
    $attachment = $attachment ?? null;
    $title = $title ?? 'Fichier joint';
    $url = is_array($attachment) ? ($attachment['temporary_url'] ?? null) : null;
    $name = is_array($attachment) ? ($attachment['name'] ?? 'Pièce jointe') : 'Pièce jointe';
    $mimeType = is_array($attachment) ? (string) ($attachment['mime_type'] ?? '') : '';
    $sourceMimeType = $mimeType !== '' ? $mimeType : 'video/mp4';
    $type = is_array($attachment) ? ($attachment['type'] ?? null) : null;
    $isImage = $type === 'image' || str_starts_with($mimeType, 'image/');
    $isVideo = $type === 'video' || str_starts_with($mimeType, 'video/');
@endphp

@if (! empty($attachment))
    <div class="surface-soft mb-3">
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-2">
            <div>
                <div class="meta-subtitle">{{ $title }}</div>
                <div class="small text-secondary">{{ $name }}</div>
            </div>
            @if (filled($url))
                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-dark">
                    Ouvrir
                </a>
            @endif
        </div>

        @if ($isImage && filled($url))
            <img
                src="{{ $url }}"
                alt="Photo jointe au signalement"
                class="img-fluid rounded-4 border"
                style="max-height: 420px; width: 100%; object-fit: contain; background: #f7f9fc;"
            >
        @elseif ($isVideo && filled($url))
            <video
                controls
                preload="metadata"
                playsinline
                data-report-video
                class="w-100 rounded-4 border"
                style="max-height: 460px; background: #111827;"
            >
                <source src="{{ $url }}" type="{{ $sourceMimeType }}">
                Votre navigateur ne peut pas lire cette vidéo directement.
            </video>
            <div class="small text-secondary mt-2">
                Si la lecture ne démarre pas, ouvrez la vidéo dans un nouvel onglet.
            </div>
        @elseif (blank($url))
            <div class="text-secondary small">Le lien temporaire du fichier n’a pas pu être généré.</div>
        @else
            <div class="text-secondary small">Aperçu indisponible pour ce type de fichier.</div>
        @endif
    </div>
@endif

@once
    @push('scripts')
        <script>
            document.addEventListener('shown.bs.modal', function (event) {
                event.target.querySelectorAll('video[data-report-video]').forEach(function (video) {
                    try {
                        video.load();
                    } catch (error) {}
                });
            });

            document.addEventListener('hidden.bs.modal', function (event) {
                event.target.querySelectorAll('video[data-report-video]').forEach(function (video) {
                    try {
                        video.pause();
                        video.currentTime = 0;
                    } catch (error) {}
                });
            });
        </script>
    @endpush
@endonce
