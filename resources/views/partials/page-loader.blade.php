@props([
    'title' => 'Chargement',
    'message' => 'Veuillez patienter pendant la préparation des données.',
])

@once
    <style>
        .page-loader-overlay {
            position: fixed;
            inset: 0;
            z-index: 1090;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: rgba(24, 52, 71, .42);
            backdrop-filter: blur(8px);
        }
        .page-loader-overlay.is-visible {
            display: flex;
        }
        .page-loader-card {
            width: min(360px, 100%);
            border: 1px solid rgba(24, 52, 71, .10);
            border-radius: 16px;
            background: rgba(255,255,255,.98);
            box-shadow: 0 24px 70px rgba(24, 52, 71, .24);
            padding: 1.25rem;
            text-align: center;
        }
        .page-loader-ring {
            width: 46px;
            height: 46px;
            margin: 0 auto .95rem;
            border-radius: 999px;
            border: 4px solid rgba(255, 161, 23, .20);
            border-top-color: #ffa117;
            animation: pageLoaderSpin .82s linear infinite;
        }
        .page-loader-dots {
            display: inline-flex;
            gap: .28rem;
            margin-top: .85rem;
        }
        .page-loader-dots span {
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: #6791ff;
            animation: pageLoaderPulse 1s ease-in-out infinite;
        }
        .page-loader-dots span:nth-child(2) {
            background: #ff0068;
            animation-delay: .14s;
        }
        .page-loader-dots span:nth-child(3) {
            background: #5bebaf;
            animation-delay: .28s;
        }
        @keyframes pageLoaderSpin {
            to { transform: rotate(360deg); }
        }
        @keyframes pageLoaderPulse {
            0%, 100% { transform: translateY(0); opacity: .45; }
            50% { transform: translateY(-4px); opacity: 1; }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const showLoader = () => {
                const overlay = document.querySelector('[data-page-loader]');
                if (!overlay) {
                    return;
                }

                overlay.classList.add('is-visible');
                overlay.setAttribute('aria-hidden', 'false');
            };

            document.addEventListener('submit', (event) => {
                if (event.defaultPrevented || event.target?.matches('[data-no-loader]')) {
                    return;
                }

                showLoader();
            });

            document.addEventListener('click', (event) => {
                const link = event.target.closest('a[href]');

                if (!link || event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                    return;
                }

                const href = link.getAttribute('href') || '';

                if (
                    href === '' ||
                    href.startsWith('#') ||
                    link.target === '_blank' ||
                    link.hasAttribute('download') ||
                    link.matches('[data-no-loader]')
                ) {
                    return;
                }

                showLoader();
            });
        });
    </script>
@endonce

<div class="page-loader-overlay" data-page-loader aria-hidden="true">
    <div class="page-loader-card" role="status" aria-live="polite">
        <div class="page-loader-ring" aria-hidden="true"></div>
        <div class="fw-bold mb-1">{{ $title }}</div>
        <div class="text-secondary small">{{ $message }}</div>
        <div class="page-loader-dots" aria-hidden="true">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
</div>
