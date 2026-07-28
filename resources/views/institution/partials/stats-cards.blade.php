@props(['cards' => []])

@once
    <style>
        .institution-stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: .75rem;
            margin-bottom: 1rem;
        }
        .institution-stat-card {
            position: relative;
            overflow: hidden;
            min-height: 104px;
            border: 1px solid rgba(24,52,71,.08);
            border-radius: 16px;
            background: rgba(255,255,255,.95);
            box-shadow: 0 18px 42px rgba(24,52,71,.06);
            padding: .9rem;
            border-top: 4px solid #6791ff;
        }
        .institution-stat-card::after {
            content: "";
            position: absolute;
            width: 72px;
            height: 72px;
            top: -34px;
            right: -32px;
            border-radius: 999px;
            background: rgba(103,145,255,.12);
        }
        .institution-stat-card.tone-orange {
            border-top-color: #ffa117;
        }
        .institution-stat-card.tone-orange::after {
            background: rgba(255,161,23,.16);
        }
        .institution-stat-card.tone-pink {
            border-top-color: #ff0068;
        }
        .institution-stat-card.tone-pink::after {
            background: rgba(255,0,104,.10);
        }
        .institution-stat-card.tone-green {
            border-top-color: #5bebaf;
        }
        .institution-stat-card.tone-green::after {
            background: rgba(91,235,175,.18);
        }
        .institution-stat-card > * {
            position: relative;
            z-index: 1;
        }
        .institution-stat-label {
            color: #647887;
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
        }
        .institution-stat-value {
            color: #183447;
            font-size: 1.55rem;
            line-height: 1;
            font-weight: 800;
            margin-top: .55rem;
        }
        .institution-stat-help {
            color: #647887;
            font-size: .78rem;
            margin-top: .45rem;
        }
        @media (max-width: 991.98px) {
            .institution-stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 575.98px) {
            .institution-stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endonce

@if (! empty($cards))
    <div class="institution-stats-grid">
        @foreach ($cards as $card)
            <div class="institution-stat-card tone-{{ $card['tone'] ?? 'blue' }}">
                <div class="institution-stat-label">{{ $card['label'] ?? '-' }}</div>
                <div class="institution-stat-value">{{ $card['value'] ?? '-' }}</div>
                @if (filled($card['help'] ?? null))
                    <div class="institution-stat-help">{{ $card['help'] }}</div>
                @endif
            </div>
        @endforeach
    </div>
@endif
