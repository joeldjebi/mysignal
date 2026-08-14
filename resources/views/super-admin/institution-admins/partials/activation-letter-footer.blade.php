<footer class="{{ $footerClass ?? 'footer-preview' }}">
    <div>
        @if ($letter->footerLogoUrl())
            <img src="{{ $letter->footerLogoUrl() }}" style="width: {{ (int) ($footer['logo']['size'] ?? 72) }}px; height: auto; object-fit: contain;" alt="Logo pied de page">
        @endif
    </div>
    @foreach ([
        'address' => $footer['address'],
        'phone' => $footer['phone'],
        'email' => $footer['email'],
        'website' => $footer['website'],
    ] as $column)
        <div style="{{ $textStyle($column) }}">
            <div class="fw-semibold mb-1 footer-label">{{ $column['label'] }}</div>
            <div style="white-space: pre-line;">
                @if (($column['label'] ?? '') === 'Téléphone' && filled($column['text']))
                    @foreach (preg_split('/\r\n|\r|\n/', $column['text']) as $phone)
                        <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}">{{ $phone }}</a><br>
                    @endforeach
                @elseif (($column['label'] ?? '') === 'Email' && filled($column['text']))
                    @foreach (preg_split('/\r\n|\r|\n/', $column['text']) as $email)
                        <a href="mailto:{{ trim($email) }}">{{ $email }}</a><br>
                    @endforeach
                @elseif (($column['label'] ?? '') === 'Site web' && filled($column['text']))
                    @foreach (preg_split('/\r\n|\r|\n/', $column['text']) as $url)
                        <a href="{{ str_starts_with(trim($url), 'http') ? trim($url) : 'https://'.trim($url) }}" target="_blank" rel="noopener">{{ $url }}</a><br>
                    @endforeach
                @else
                    {{ $column['text'] ?: '-' }}
                @endif
            </div>
        </div>
    @endforeach
</footer>
