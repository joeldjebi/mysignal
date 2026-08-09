@php
    $attachment = $attachment ?? null;
    $title = $title ?? 'Fichier joint';
    $url = is_array($attachment) ? ($attachment['temporary_url'] ?? null) : null;
    $name = is_array($attachment) ? ($attachment['name'] ?? 'Pièce jointe') : 'Pièce jointe';
    $mimeType = is_array($attachment) ? (string) ($attachment['mime_type'] ?? '') : '';
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
                src="{{ $url }}"
                controls
                preload="metadata"
                playsinline
                class="w-100 rounded-4 border"
                style="max-height: 460px; background: #111827;"
            ></video>
        @elseif (blank($url))
            <div class="text-secondary small">Le lien temporaire du fichier n’a pas pu être généré.</div>
        @else
            <div class="text-secondary small">Aperçu indisponible pour ce type de fichier.</div>
        @endif
    </div>
@endif
