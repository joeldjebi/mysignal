<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name') }} | Dashboard</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <style>
            :root {
                --acepen-navy: #183447;
                --acepen-ocean: #ff0068;
                --acepen-copper: #ffa117;
                --acepen-mint: #5bebaf;
                --acepen-ink: #183447;
                --acepen-muted: #667786;
                --acepen-card: rgba(255, 255, 255, 0.92);
                --acepen-card-solid: #ffffff;
                --acepen-soft: #f7fafc;
            }
            body {
                font-family: "Manrope", sans-serif;
                color: var(--acepen-ink);
                background:
                    radial-gradient(circle at top left, rgba(255, 161, 23, 0.12), transparent 24%),
                    radial-gradient(circle at top right, rgba(103, 145, 255, 0.10), transparent 25%),
                    linear-gradient(180deg, #fff7ea 0%, #eef4ff 100%);
            }
            .shell {
                max-width: 1460px;
            }
            .app-grid {
                display: grid;
                grid-template-columns: 286px minmax(0, 1fr);
                gap: 0.85rem;
                align-items: start;
            }
            .sidebar-backdrop {
                display: none;
            }
            .sidebar,
            .topbar,
            .dashboard-card,
            .hero-card,
            .mini-card {
                background: var(--acepen-card);
                border: 1px solid rgba(24, 52, 71, 0.08);
                box-shadow: 0 28px 80px rgba(15, 39, 56, 0.08);
                backdrop-filter: blur(18px);
            }
            .sidebar {
                border-radius: 28px;
                padding: 0.85rem;
                position: sticky;
                top: 1rem;
                align-self: start;
                height: calc(100vh - 2rem);
                max-height: calc(100vh - 2rem);
                overflow: hidden;
                display: flex;
                flex-direction: column;
                background: var(--acepen-navy);
                color: white;
            }
            .sidebar-brand {
                padding: 0.15rem 0.15rem 0.8rem;
                border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            }
            .brand-mark {
                width: 42px;
                height: 42px;
                border-radius: 15px;
                display: grid;
                place-items: center;
                background: var(--acepen-copper);
                color: white;
                font-weight: 800;
            }
            .sidebar-label {
                color: rgba(255, 255, 255, 0.52);
                text-transform: uppercase;
                letter-spacing: 0.08em;
                font-size: 0.67rem;
                font-weight: 800;
                margin: 0.8rem 0 0.55rem;
                padding-inline: 0.35rem;
            }
            .sidebar-menu {
                flex: 1;
                overflow: auto;
                padding-right: 0.25rem;
                display: flex;
                flex-direction: column;
            }
            .sidebar-bottom-links {
                margin-top: auto;
                padding-top: 1rem;
            }
            .nav-pill {
                width: 100%;
                border: 0;
                background: transparent;
                color: white;
                display: flex;
                align-items: center;
                gap: 0.7rem;
                border-radius: 16px;
                padding: 0.65rem 0.72rem;
                text-align: left;
                transition: 0.2s ease;
                margin-bottom: 0.22rem;
            }
            .nav-pill:hover {
                background: rgba(255, 255, 255, 0.08);
            }
            .nav-pill.active {
                background: #ffa117;
                box-shadow: 0 18px 34px rgba(255, 161, 23, 0.22);
            }
            .nav-icon {
                width: 30px;
                height: 30px;
                border-radius: 10px;
                display: grid;
                place-items: center;
                background: rgba(255, 255, 255, 0.12);
                font-size: 0.66rem;
                font-weight: 800;
                letter-spacing: 0.04em;
                flex-shrink: 0;
            }
            .sidebar-footer {
                border-top: 1px solid rgba(255, 255, 255, 0.08);
                padding-top: 0.8rem;
                margin-top: 0.8rem;
            }
            .sidebar-card {
                border-radius: 16px;
                background: rgba(255, 255, 255, 0.08);
                border: 1px solid rgba(255, 255, 255, 0.08);
                padding: 0.7rem 0.8rem;
            }
            .btn-sidebar {
                min-height: 2.65rem;
                border-radius: 18px;
                border: 1px solid rgba(255, 255, 255, 0.12);
                color: white;
                background: rgba(255, 255, 255, 0.08);
                font-weight: 700;
            }
            .content {
                min-width: 0;
            }
            .topbar-menu-button {
                display: none;
            }
            .topbar {
                border-radius: 24px;
                padding: 0.85rem 1rem;
                margin-bottom: 1rem;
                border-top: 4px solid var(--acepen-blue, #6791ff);
            }
            .topbar-session {
                min-width: min(100%, 380px);
                border-radius: 22px;
                padding: 0.85rem 0.95rem;
                background: linear-gradient(145deg, rgba(24, 52, 71, 0.98), rgba(40, 83, 112, 0.94));
                color: white;
                box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.06);
            }
            .topbar-session-meta {
                color: rgba(255, 255, 255, 0.72);
                font-size: 0.78rem;
            }
            .topbar-session-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 0.55rem;
                margin-top: 0.8rem;
            }
            .btn-topbar-session {
                min-height: 2.5rem;
                border-radius: 16px;
                border: 1px solid rgba(255, 255, 255, 0.12);
                color: white;
                background: rgba(255, 255, 255, 0.08);
                font-weight: 700;
                padding-inline: 0.95rem;
            }
            .btn-topbar-session:hover {
                background: rgba(255, 255, 255, 0.14);
                color: white;
            }
            .notification-button {
                position: relative;
            }
            .notification-badge {
                min-width: 1.35rem;
                height: 1.35rem;
                border-radius: 999px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 0 .35rem;
                background: #d9480f;
                color: #fff;
                font-size: .72rem;
                font-weight: 800;
            }
            .nav-pill .notification-badge {
                margin-left: auto;
            }
            .notification-item {
                border: 1px solid rgba(15, 41, 64, .08);
                border-left: 4px solid transparent;
                border-radius: 18px;
                padding: 1rem;
                background: rgba(255,255,255,.82);
            }
            .notification-item.unread {
                border-left-color: var(--acepen-blue);
                background: rgba(237, 243, 248, .96);
            }
            .notification-title {
                color: var(--acepen-navy);
                font-weight: 800;
            }
            .notification-category {
                display: inline-flex;
                align-items: center;
                width: fit-content;
                border-radius: 999px;
                padding: .25rem .65rem;
                background: rgba(15, 41, 64, .08);
                color: var(--acepen-navy);
                font-size: .74rem;
                font-weight: 800;
            }
            .hero-card,
            .dashboard-card,
            .mini-card {
                border-radius: 28px;
            }
            .hero-card {
                padding: 1.15rem;
                background: var(--acepen-navy);
                color: white;
                position: relative;
                overflow: hidden;
            }
            .hero-card::after {
                content: "";
                position: absolute;
                width: 360px;
                height: 360px;
                right: -120px;
                top: -120px;
                border-radius: 50%;
                background: radial-gradient(circle, rgba(255, 255, 255, 0.15), transparent 60%);
            }
            .metric-tile {
                border-radius: 22px;
                padding: 0.8rem;
                background: rgba(255, 255, 255, 0.1);
                border: 1px solid rgba(255, 255, 255, 0.12);
                border-top: 3px solid var(--acepen-copper);
                color: white;
                min-height: 96px;
            }
            .hero-card .row .col-4:nth-child(2) .metric-tile {
                border-top-color: var(--acepen-ocean);
            }
            .hero-card .row .col-4:nth-child(3) .metric-tile {
                border-top-color: var(--acepen-mint);
            }
            .dashboard-card {
                padding: 1rem;
                position: relative;
                overflow: hidden;
            }
            .dashboard-card::before {
                content: "";
                position: absolute;
                inset: 0 0 auto 0;
                height: 4px;
                background: var(--acepen-blue, #6791ff);
            }
            .section-title {
                font-size: 1rem;
                font-weight: 800;
                letter-spacing: -0.03em;
                color: var(--acepen-navy);
            }
            .muted-label {
                color: var(--acepen-muted);
                font-size: 0.8rem;
            }
            .hidden {
                display: none !important;
            }
            .status-pill {
                display: inline-flex;
                align-items: center;
                gap: 0.45rem;
                padding: 0.35rem 0.65rem;
                border-radius: 999px;
                font-size: 0.72rem;
                font-weight: 800;
                background: rgba(91, 235, 175, 0.18);
                color: #159c6b;
            }
            .status-pill.status-report-submitted,
            .status-pill.status-payment-pending,
            .status-pill.status-resolution-pending {
                background: rgba(255, 161, 23, 0.16);
                color: #c87800;
            }
            .status-pill.status-report-in-progress,
            .status-pill.status-resolution-waiting {
                background: rgba(103, 145, 255, 0.16);
                color: #4c73df;
            }
            .status-pill.status-report-resolved,
            .status-pill.status-payment-paid,
            .status-pill.status-resolution-confirmed {
                background: rgba(91, 235, 175, 0.18);
                color: #159c6b;
            }
            .status-pill.status-report-rejected,
            .status-pill.status-payment-failed,
            .status-pill.status-resolution-expired {
                background: rgba(255, 0, 104, 0.14);
                color: #d6005a;
            }
            .btn-premium {
                border: none;
                min-height: 2.85rem;
                border-radius: 18px;
                background: var(--acepen-copper);
                color: #102a43;
                font-weight: 800;
                box-shadow: 0 18px 34px rgba(255, 161, 23, 0.24);
            }
            .btn-premium:hover,
            .btn-premium:focus,
            .btn-premium:focus-visible,
            .btn-premium:active {
                background: #ffb540;
                color: #102a43;
                transform: translateY(-1px);
                box-shadow: 0 18px 34px rgba(255, 161, 23, 0.28);
            }
            .btn-ghost-premium {
                min-height: 2.85rem;
                border-radius: 18px;
                background: rgba(103, 145, 255, 0.08);
                color: var(--acepen-ocean);
                border: 1px solid rgba(103, 145, 255, 0.14);
                font-weight: 800;
            }
            .form-control,
            .form-select {
                border-radius: 18px;
                border-color: rgba(24, 52, 71, 0.11);
                min-height: 2.85rem;
                padding-inline: 0.85rem;
            }
            .form-control:focus,
            .form-select:focus {
                border-color: rgba(255, 161, 23, 0.55);
                box-shadow: 0 0 0 0.25rem rgba(255, 161, 23, 0.12);
            }
            .mini-card {
                background: var(--acepen-card-solid);
                padding: 0.9rem;
                border-top: 3px solid var(--acepen-mint);
            }
            .soft-panel {
                background: var(--acepen-soft);
                border: 1px solid rgba(24, 52, 71, 0.06);
                border-left: 4px solid var(--acepen-ocean);
                border-radius: 20px;
                padding: 0.85rem;
            }
            .member-wallet-card {
                position: relative;
                overflow: hidden;
                border-radius: 18px;
                padding: 1.05rem;
                width: min(100%, 420px);
                aspect-ratio: 1.586 / 1;
                min-height: 250px;
                color: white;
                background:
                    linear-gradient(135deg, rgba(24, 52, 71, 0.98), rgba(255, 0, 104, 0.9) 56%, rgba(255, 161, 23, 0.94));
                box-shadow: 0 26px 60px rgba(24, 52, 71, 0.24);
            }
            .member-wallet-card::before {
                content: "";
                position: absolute;
                inset: 0;
                background:
                    linear-gradient(110deg, transparent 0 48%, rgba(255, 255, 255, 0.13) 48% 49%, transparent 49%),
                    linear-gradient(180deg, rgba(255, 255, 255, 0.16), transparent 40%);
                pointer-events: none;
            }
            .member-wallet-content {
                position: relative;
                z-index: 1;
                height: 100%;
                display: flex;
                flex-direction: column;
                gap: 1rem;
            }
            .member-wallet-chip {
                width: 42px;
                height: 30px;
                border-radius: 8px;
                background: linear-gradient(135deg, #ffe1a3, #ffa117);
                box-shadow: inset 0 0 0 1px rgba(24, 52, 71, 0.18);
            }
            .member-wallet-brand {
                font-size: 0.72rem;
                font-weight: 800;
                letter-spacing: 0.12em;
                text-transform: uppercase;
            }
            .member-wallet-number {
                font-size: 1.08rem;
                font-weight: 800;
                letter-spacing: 0.1em;
                white-space: nowrap;
            }
            .member-wallet-meta {
                color: rgba(255, 255, 255, 0.72);
                font-size: 0.62rem;
                text-transform: uppercase;
                font-weight: 800;
            }
            .member-wallet-value {
                font-weight: 800;
                font-size: 0.82rem;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .member-qr-box {
                width: 82px;
                min-width: 82px;
                height: 82px;
                border-radius: 12px;
                background: white;
                display: grid;
                place-items: center;
                padding: 0.35rem;
                box-shadow: 0 16px 32px rgba(24, 52, 71, 0.18);
                position: relative;
                bottom:50px;
            }
            .member-qr-box img,
            .member-qr-box canvas {
                width: 70px !important;
                height: 70px !important;
            }
            .member-wallet-footer {
                position: absolute;
                right: 0;
                bottom: 0;
                left: 0;
                z-index: 2;
                display: grid;
                grid-template-columns: minmax(0, 1fr) auto auto;
                gap: 0.75rem;
                /* align-items: end; */
            }
            .member-wallet-qr-caption {
                color: rgba(255, 255, 255, 0.84);
                font-size: 0.62rem;
                font-weight: 800;
                text-transform: uppercase;
            }
            .member-wallet-locked {
                border-radius: 18px;
                padding: 1rem;
                background: linear-gradient(135deg, rgba(24, 52, 71, 0.08), rgba(255, 161, 23, 0.12));
                border: 1px dashed rgba(24, 52, 71, 0.18);
            }
            .signal-field-card {
                background: #f8fbfd;
                border: 1px dashed rgba(24, 52, 71, 0.14);
                border-left: 4px solid var(--acepen-copper);
                border-radius: 20px;
                padding: 0.85rem;
            }
            .location-search-hint {
                color: var(--acepen-muted);
                font-size: 0.82rem;
                margin-top: 0.45rem;
            }
            .select-search-input {
                display: block;
                width: 100%;
                margin-bottom: 0.55rem;
                min-height: 2.85rem;
                border-radius: 16px;
                background: #fff;
                padding-right: 3.4rem;
                cursor: pointer;
            }
            .select-search-shell {
                position: relative;
            }
            .select-search-toggle {
                position: absolute;
                top: 0;
                right: 0;
                width: 3rem;
                height: 2.85rem;
                border: 0;
                background: transparent;
                color: var(--acepen-muted);
                border-radius: 0 16px 16px 0;
            }
            .select-search-toggle::before,
            .select-search-toggle::after {
                content: "";
                position: absolute;
                top: 50%;
                width: 7px;
                height: 2px;
                background: currentColor;
            }
            .select-search-toggle::before {
                right: 18px;
                transform: translateY(-50%) rotate(45deg);
            }
            .select-search-toggle::after {
                right: 13px;
                transform: translateY(-50%) rotate(-45deg);
            }
            .required-star {
                color: #d6005a;
                font-weight: 800;
                margin-left: 0.15rem;
            }
            .select-search-help {
                margin-top: -0.2rem;
                margin-bottom: 0.55rem;
                color: var(--acepen-muted);
                font-size: 0.76rem;
            }
            .select-search-results {
                display: none;
                margin-top: -0.2rem;
                margin-bottom: 0.55rem;
                background: #fff;
                border: 1px solid rgba(24, 52, 71, 0.12);
                border-radius: 16px;
                box-shadow: 0 18px 34px rgba(15, 39, 56, 0.08);
                max-height: 220px;
                overflow: auto;
                padding: 0.35rem;
            }
            .select-search-results.is-open {
                display: block;
            }
            .select-search-option {
                width: 100%;
                text-align: left;
                border: 0;
                background: transparent;
                border-radius: 12px;
                padding: 0.65rem 0.8rem;
                color: var(--acepen-ink);
            }
            .select-search-option:hover,
            .select-search-option.is-active {
                background: rgba(103, 145, 255, 0.08);
            }
            .select-search-empty {
                padding: 0.65rem 0.8rem;
                color: var(--acepen-muted);
                font-size: 0.86rem;
            }
            .public-select-shell {
                position: relative;
            }
            .public-select-input {
                display: block;
                width: 100%;
                margin-bottom: 0.55rem;
                min-height: 2.85rem;
                border-radius: 16px;
                background: #fff;
                padding-right: 3.4rem;
                cursor: pointer;
            }
            .public-select-toggle {
                position: absolute;
                top: 0;
                right: 0;
                width: 3rem;
                height: 2.85rem;
                border: 0;
                background: transparent;
                color: var(--acepen-muted);
                border-radius: 0 16px 16px 0;
            }
            .public-select-toggle::before,
            .public-select-toggle::after {
                content: "";
                position: absolute;
                top: 50%;
                width: 7px;
                height: 2px;
                background: currentColor;
            }
            .public-select-toggle::before {
                right: 18px;
                transform: translateY(-50%) rotate(45deg);
            }
            .public-select-toggle::after {
                right: 13px;
                transform: translateY(-50%) rotate(-45deg);
            }
            .public-select-help {
                margin-top: -0.2rem;
                margin-bottom: 0.55rem;
                color: var(--acepen-muted);
                font-size: 0.76rem;
            }
            .public-select-results {
                display: none;
                margin-top: -0.2rem;
                margin-bottom: 0.55rem;
                background: #fff;
                border: 1px solid rgba(24, 52, 71, 0.12);
                border-radius: 16px;
                box-shadow: 0 18px 34px rgba(15, 39, 56, 0.08);
                max-height: 220px;
                overflow: auto;
                padding: 0.35rem;
            }
            .public-select-results.is-open {
                display: block;
            }
            .public-select-option {
                width: 100%;
                text-align: left;
                border: 0;
                background: transparent;
                border-radius: 12px;
                padding: 0.65rem 0.8rem;
                color: var(--acepen-ink);
            }
            .public-select-option:hover {
                background: rgba(103, 145, 255, 0.08);
            }
            .report-table-shell {
                overflow: hidden;
                border-radius: 24px;
                border: 1px solid rgba(24, 52, 71, 0.08);
                background: white;
            }
            .report-table-wrap {
                overflow-x: auto;
            }
            .report-table {
                width: 100%;
                min-width: 980px;
                margin-bottom: 0;
            }
            .report-table thead th {
                background: rgba(103, 145, 255, 0.08);
                color: var(--acepen-muted);
                font-size: 0.78rem;
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                border-bottom: 1px solid rgba(24, 52, 71, 0.08);
                padding: 1rem 1.1rem;
                white-space: nowrap;
            }
            .report-table tbody td {
                padding: 1rem 1.1rem;
                border-bottom: 1px solid rgba(24, 52, 71, 0.06);
                vertical-align: top;
            }
            .report-table tbody tr:last-child td {
                border-bottom: 0;
            }
            .report-ref {
                font-weight: 800;
                letter-spacing: -0.02em;
                color: var(--acepen-ocean);
            }
            .report-main {
                font-weight: 700;
                color: var(--acepen-navy);
            }
            .report-sub {
                color: var(--acepen-muted);
                font-size: 0.84rem;
                line-height: 1.55;
            }
            .report-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 0.55rem;
                align-items: center;
                justify-content: flex-end;
            }
            .pagination-shell {
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 1rem;
                padding: 1rem 1.1rem;
                border-top: 1px solid rgba(24, 52, 71, 0.06);
                background: #fbfdff;
            }
            .pagination-shell .btn-outline-secondary {
                border-color: rgba(103, 145, 255, 0.22);
                color: var(--acepen-navy);
            }
            .sidebar-card .fw-semibold,
            .sidebar-card .fw-bold {
                color: #ffffff;
            }
            #topbarMetersBadge {
                background: rgba(103, 145, 255, 0.16);
                color: #4c73df;
            }
            #topbarReportsBadge {
                background: rgba(255, 161, 23, 0.16);
                color: #c87800;
            }
            #topbarPaymentsBadge {
                background: rgba(91, 235, 175, 0.18);
                color: #159c6b;
            }
            #userStatus {
                background: rgba(255, 0, 104, 0.14);
                color: #d6005a;
            }
            .pagination-info {
                color: var(--acepen-muted);
                font-size: 0.88rem;
                font-weight: 600;
            }
            .pagination-actions {
                display: flex;
                gap: 0.65rem;
                align-items: center;
            }
            .pagination-chip {
                min-width: 2.4rem;
                height: 2.4rem;
                border-radius: 999px;
                border: 1px solid rgba(24, 52, 71, 0.08);
                background: white;
                font-weight: 700;
                color: var(--acepen-navy);
            }
            .pagination-chip[disabled] {
                opacity: 0.45;
            }
            .geo-box {
                border: 1px dashed rgba(24, 52, 71, 0.14);
                border-radius: 20px;
                padding: 1rem;
                background: #f8fbfd;
            }
            .geo-help {
                font-size: 0.82rem;
                color: var(--acepen-muted);
            }
            .panel-grid {
                display: grid;
                gap: 1.5rem;
            }
            .overview-grid {
                display: grid;
                gap: 1.5rem;
            }
            .quick-action {
                width: 100%;
                border: 1px solid rgba(24, 52, 71, 0.08);
                border-radius: 26px;
                padding: 1.15rem;
                background:
                    radial-gradient(circle at top right, rgba(255, 161, 23, 0.14), transparent 34%),
                    linear-gradient(145deg, #ffffff 0%, #f8fbff 100%);
                text-align: left;
                transition: 0.2s ease;
                min-height: 158px;
                position: relative;
                overflow: hidden;
                box-shadow: 0 18px 38px rgba(15, 39, 56, 0.05);
            }
            .quick-action:hover {
                transform: translateY(-3px);
                box-shadow: 0 24px 44px rgba(15, 39, 56, 0.1);
            }
            .quick-action::after {
                content: "";
                position: absolute;
                width: 92px;
                height: 92px;
                right: -34px;
                bottom: -34px;
                border-radius: 999px;
                background: rgba(255, 0, 104, 0.08);
            }
            .quick-action-icon {
                width: 46px;
                height: 46px;
                border-radius: 17px;
                display: grid;
                place-items: center;
                font-weight: 900;
                color: #102a43;
                background: var(--acepen-copper);
                box-shadow: 0 18px 30px rgba(255, 161, 23, 0.2);
            }
            .quick-action-title {
                font-size: 1.02rem;
                font-weight: 900;
                color: var(--acepen-navy);
                letter-spacing: 0;
            }
            .quick-action-arrow {
                width: 34px;
                height: 34px;
                border-radius: 999px;
                display: grid;
                place-items: center;
                background: rgba(24, 52, 71, 0.06);
                color: var(--acepen-navy);
                font-weight: 900;
            }
            .overview-report-table {
                width: 100%;
                min-width: 760px;
                margin-bottom: 0;
            }
            .overview-report-table th {
                padding: 0.85rem 1rem;
                background: #f8fbff;
                color: var(--acepen-muted);
                font-size: 0.73rem;
                font-weight: 900;
                letter-spacing: 0.07em;
                text-transform: uppercase;
                border-bottom: 1px solid rgba(24, 52, 71, 0.07);
                white-space: nowrap;
            }
            .overview-report-table td {
                padding: 0.9rem 1rem;
                border-bottom: 1px solid rgba(24, 52, 71, 0.06);
                vertical-align: middle;
            }
            .overview-report-table tr:last-child td {
                border-bottom: 0;
            }
            .overview-report-empty {
                border: 1px dashed rgba(24, 52, 71, 0.14);
                border-radius: 24px;
                padding: 1.2rem;
                background: #f8fbff;
            }
            .overview-status-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 0.9rem;
            }
            .overview-status-tile {
                border: 1px solid rgba(24, 52, 71, 0.08);
                border-radius: 18px;
                padding: 1rem;
                background: #f8fbff;
            }
            .overview-status-number {
                font-size: 2rem;
                line-height: 1;
                font-weight: 900;
                color: var(--acepen-navy);
            }
            .overview-action-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 1rem;
            }
            .overview-report-list {
                display: grid;
                gap: 0.85rem;
            }
            .overview-report-card {
                display: flex;
                justify-content: space-between;
                gap: 1rem;
                align-items: center;
                border: 1px solid rgba(24, 52, 71, 0.08);
                border-radius: 18px;
                padding: 1rem;
                background: #fff;
                box-shadow: 0 16px 30px rgba(15, 39, 56, 0.04);
            }
            .overview-report-title {
                font-weight: 900;
                color: var(--acepen-navy);
            }
            .overview-report-meta {
                color: var(--acepen-muted);
                font-size: 0.86rem;
            }
            .simple-home {
                min-height: 64vh;
                display: grid;
                align-items: center;
            }
            .simple-home-card {
                border-radius: 28px;
                padding: clamp(1.4rem, 4vw, 3rem);
                background:
                    linear-gradient(135deg, rgba(16, 42, 67, 0.96), rgba(24, 52, 71, 0.92)),
                    url('{{ asset('image/logo/logo-my-signal.png') }}');
                background-size: cover;
                background-position: center;
                color: #fff;
                box-shadow: 0 28px 60px rgba(15, 39, 56, 0.18);
                overflow: hidden;
                position: relative;
            }
            .simple-home-card::after {
                content: "";
                position: absolute;
                inset: 0;
                background: rgba(16, 42, 67, 0.74);
            }
            .simple-home-content {
                position: relative;
                z-index: 1;
                max-width: 760px;
            }
            .simple-home-title {
                font-size: clamp(2rem, 4vw, 3.6rem);
                line-height: 1.02;
                font-weight: 900;
                margin-bottom: 1rem;
            }
            .simple-home-copy {
                font-size: 1.08rem;
                color: rgba(255, 255, 255, 0.78);
                max-width: 620px;
                margin-bottom: 2rem;
            }
            .simple-home-actions {
                display: flex;
                flex-wrap: wrap;
                gap: 0.9rem;
            }
            .simple-home-main-action {
                min-height: 3.25rem;
                border-radius: 999px;
                padding-inline: 1.35rem;
                font-weight: 900;
                font-size: 1rem;
            }
            .simple-home-secondary {
                min-height: 3.25rem;
                border-radius: 999px;
                border: 1px solid rgba(255, 255, 255, 0.24);
                background: rgba(255, 255, 255, 0.1);
                color: #fff;
                padding-inline: 1.2rem;
                font-weight: 800;
            }
            .simple-home-secondary:hover {
                color: #fff;
                background: rgba(255, 255, 255, 0.18);
            }
            .simple-home-note {
                margin-top: 1.8rem;
                display: inline-flex;
                align-items: center;
                gap: 0.55rem;
                border-radius: 999px;
                padding: 0.65rem 0.9rem;
                background: rgba(255, 255, 255, 0.12);
                color: rgba(255, 255, 255, 0.82);
                font-size: 0.92rem;
            }
            @media (max-width: 991.98px) {
                .overview-status-grid,
                .overview-action-grid {
                    grid-template-columns: 1fr;
                }
            }
            .payment-history-grid {
                gap: 1rem;
            }
            .payment-table-shell {
                overflow: hidden;
                border-radius: 26px;
                border: 1px solid rgba(24, 52, 71, 0.08);
                background: #fff;
                box-shadow: 0 22px 40px rgba(15, 39, 56, 0.05);
            }
            .payment-table {
                width: 100%;
                min-width: 980px;
                border-collapse: collapse;
            }
            .payment-table thead th {
                font-size: .74rem;
                letter-spacing: .08em;
                text-transform: uppercase;
                color: var(--acepen-muted);
                background: linear-gradient(180deg, #fbfdff 0%, #f4f8fb 100%);
                padding: 1rem 1.1rem;
                border-bottom: 1px solid rgba(24, 52, 71, 0.08);
            }
            .payment-table tbody td {
                padding: 1rem 1.1rem;
                border-bottom: 1px solid rgba(24, 52, 71, 0.06);
                vertical-align: top;
            }
            .payment-table tbody tr:hover {
                background: rgba(247, 250, 252, 0.8);
            }
            .payment-ref {
                font-weight: 800;
                color: var(--acepen-navy);
                letter-spacing: -.02em;
            }
            .payment-sub {
                margin-top: .18rem;
                color: var(--acepen-muted);
                font-size: .87rem;
                line-height: 1.5;
            }
            .payment-amount {
                font-size: 1.15rem;
                font-weight: 800;
                color: var(--acepen-navy);
            }
            .privilege-premium-shell {
                border-radius: 30px;
                padding: clamp(1rem, 2vw, 1.35rem);
                background:
                    linear-gradient(135deg, rgba(24, 52, 71, 0.98), rgba(35, 72, 95, 0.96) 48%, rgba(19, 31, 43, 0.98)),
                    linear-gradient(90deg, rgba(255, 161, 23, 0.18), rgba(255, 255, 255, 0));
                color: #fff;
                box-shadow: 0 30px 70px rgba(15, 39, 56, 0.18);
                overflow: hidden;
                position: relative;
            }
            .privilege-premium-shell::before {
                content: "";
                position: absolute;
                inset: 0;
                background:
                    linear-gradient(110deg, transparent 0 52%, rgba(255, 255, 255, 0.08) 52% 53%, transparent 53%),
                    linear-gradient(180deg, rgba(255, 255, 255, 0.1), transparent 46%);
                pointer-events: none;
            }
            .privilege-premium-content {
                position: relative;
                z-index: 1;
            }
            .privilege-eyebrow {
                color: rgba(255, 255, 255, 0.68);
                font-size: 0.76rem;
                font-weight: 900;
                letter-spacing: 0.1em;
                text-transform: uppercase;
            }
            .privilege-title {
                font-size: clamp(1.7rem, 3vw, 2.6rem);
                line-height: 1.05;
                font-weight: 900;
                letter-spacing: 0;
            }
            .privilege-copy {
                color: rgba(255, 255, 255, 0.72);
                max-width: 680px;
            }
            .privilege-active-card {
                border: 1px solid rgba(255, 255, 255, 0.14);
                border-radius: 24px;
                padding: 1rem;
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(16px);
            }
            .privilege-active-number {
                font-size: 1.08rem;
                font-weight: 900;
                letter-spacing: 0.08em;
                overflow-wrap: anywhere;
            }
            .privilege-offer-card {
                border: 1px solid rgba(24, 52, 71, 0.08);
                border-radius: 26px;
                padding: 1rem;
                background:
                    linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
                box-shadow: 0 22px 44px rgba(15, 39, 56, 0.07);
                height: 100%;
                position: relative;
                overflow: hidden;
            }
            .privilege-offer-card::before {
                content: "";
                position: absolute;
                inset: 0 0 auto 0;
                height: 5px;
                background: linear-gradient(90deg, var(--acepen-copper), var(--acepen-ocean));
            }
            .privilege-price {
                font-size: 1.75rem;
                font-weight: 900;
                color: var(--acepen-navy);
                letter-spacing: 0;
            }
            .privilege-benefit {
                display: flex;
                gap: 0.55rem;
                color: var(--acepen-muted);
                font-size: 0.88rem;
                line-height: 1.45;
            }
            .privilege-benefit::before {
                content: "";
                width: 0.42rem;
                height: 0.42rem;
                border-radius: 999px;
                background: var(--acepen-copper);
                margin-top: 0.48rem;
                flex-shrink: 0;
            }
            .privilege-wallet-disabled {
                border-radius: 18px;
                padding: 0.75rem 0.9rem;
                background: rgba(255, 161, 23, 0.12);
                color: #ffe2a3;
                font-size: 0.84rem;
                font-weight: 700;
            }
            .summary-value {
                font-size: 1.55rem;
                font-weight: 800;
                letter-spacing: -0.05em;
            }
            .public-panel {
                display: none;
            }
            .public-panel.active {
                display: block;
            }
            .toast-container {
                z-index: 1090;
            }
            @media (max-width: 1199.98px) {
                .app-grid {
                    grid-template-columns: 1fr;
                }
                .sidebar {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: min(88vw, 340px);
                    min-height: 100vh;
                    border-radius: 0 28px 28px 0;
                    z-index: 1080;
                    transform: translateX(-100%);
                    transition: transform 0.25s ease;
                }
                .sidebar.is-open {
                    transform: translateX(0);
                }
                .sidebar-backdrop {
                    position: fixed;
                    inset: 0;
                    background: rgba(9, 19, 29, 0.42);
                    backdrop-filter: blur(3px);
                    z-index: 1070;
                }
                .sidebar-backdrop.is-visible {
                    display: block;
                }
                .topbar-menu-button {
                    display: inline-flex;
                }
            }
            @media (max-width: 767.98px) {
                .topbar {
                    padding: 1rem;
                }
                .dashboard-card,
                .hero-card,
                .mini-card {
                    border-radius: 24px;
                }
                .metric-tile {
                    min-height: 96px;
                    padding: 0.9rem;
                }
                .pagination-shell {
                    flex-direction: column;
                    align-items: stretch;
                }
                .pagination-actions {
                    justify-content: space-between;
                }
            }
        </style>
    </head>
    <body>
        <div class="container shell py-3 py-lg-4">
            <div class="sidebar-backdrop" id="publicSidebarBackdrop"></div>
            <div class="app-grid">
                <aside class="sidebar" id="publicSidebar">
                    <div class="sidebar-brand">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="brand-mark">AA</div>
                            <div>
                                <div class="small text-white-50 fw-semibold">SIGNAL ALERTE</div>
                                <div class="fw-bold fs-5">Espace public</div>
                            </div>
                        </div>
                    </div>

                    <div class="sidebar-menu">
                        <div class="sidebar-label">Navigation</div>
                        <button class="nav-pill active" type="button" data-panel-target="overview">
                            <span class="nav-icon">DB</span>
                            <span>
                                <span class="d-block fw-semibold">Vue d'ensemble</span>
                                <span class="small text-white-50">Synthèse et raccourcis</span>
                            </span>
                        </button>
                        <button class="nav-pill notification-button" type="button" data-panel-target="notifications">
                            <span class="nav-icon">NT</span>
                            <span>
                                <span class="d-block fw-semibold">Notifications</span>
                                <span class="small text-white-50">Messages et alertes</span>
                            </span>
                            <span class="notification-badge d-none" id="sidebarNotificationBadge">0</span>
                        </button>
                        <button class="nav-pill" type="button" data-panel-target="profile">
                            <span class="nav-icon">PR</span>
                            <span>
                                <span class="d-block fw-semibold">Mon profil</span>
                                <span class="small text-white-50">Infos personnelles</span>
                            </span>
                        </button>
                        <button class="nav-pill" type="button" data-panel-target="meters">
                            <span class="nav-icon">CM</span>
                            <span>
                                <span class="d-block fw-semibold">Mes identifiants</span>
                                <span class="small text-white-50">CIE et SODECI, ...</span>
                            </span>
                        </button>
                        <button class="nav-pill" type="button" data-panel-target="household">
                            <span class="nav-icon">FY</span>
                            <span>
                                <span class="d-block fw-semibold">Mon Gbonhi</span>
                                <span class="small text-white-50">Famille et invitations</span>
                            </span>
                        </button>
                        <button class="nav-pill" type="button" data-panel-target="reports">
                            <span class="nav-icon">SG</span>
                            <span>
                                <span class="d-block fw-semibold">Mes signalements</span>
                                <span class="small text-white-50">Déclaration et suivi</span>
                            </span>
                        </button>
                        <button class="nav-pill" type="button" data-panel-target="rex">
                            <span class="nav-icon">RX</span>
                            <span>
                                <span class="d-block fw-semibold">Mes avis</span>
                                <span class="small text-white-50">Retours d’expérience</span>
                            </span>
                        </button>
                        <button class="nav-pill" type="button" data-panel-target="damages">
                            <span class="nav-icon">DG</span>
                            <span>
                                <span class="d-block fw-semibold">Mes dommages</span>
                                <span class="small text-white-50">Historique et suivi</span>
                            </span>
                        </button>
                        <button class="nav-pill" type="button" data-panel-target="receipts">
                            <span class="nav-icon">RC</span>
                            <span>
                                <span class="d-block fw-semibold">Mes reçus</span>
                                <span class="small text-white-50">Achats de matériel</span>
                            </span>
                        </button>
                        <button class="nav-pill" type="button" data-panel-target="cases">
                            <span class="nav-icon">DC</span>
                            <span>
                                <span class="d-block fw-semibold">Mes dossiers</span>
                                <span class="small text-white-50">Avancement et historique</span>
                            </span>
                        </button>
                        <div class="sidebar-bottom-links">
                            <button class="nav-pill d-none" type="button" data-panel-target="subscriptions">
                                <span class="nav-icon">AB</span>
                                <span>
                                    <span class="d-block fw-semibold">Mes abonnements</span>
                                    <span class="small text-white-50">Offre et état</span>
                                </span>
                            </button>
                            <button class="nav-pill" type="button" data-panel-target="payments">
                                <span class="nav-icon">PM</span>
                                <span>
                                    <span class="d-block fw-semibold">Mes paiements</span>
                                    <span class="small text-white-50">Historique et reçus</span>
                                </span>
                            </button>
                            <button class="nav-pill" type="button" data-panel-target="privilege-cards">
                                <span class="nav-icon">CP</span>
                                <span>
                                    <span class="d-block fw-semibold">Cartes privilèges</span>
                                    <span class="small text-white-50">Achat et ajout au téléphone</span>
                                </span>
                            </button>
                        </div>
                    </div>

                </aside>

                <main class="content">
                    <header class="topbar d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                        <div>
                            <button class="btn btn-ghost-premium topbar-menu-button mb-3 px-3" id="openPublicSidebarButton" type="button">Menu</button>
                            <div class="small text-secondary fw-semibold mb-1">Dashboard utilisateur</div>
                            <div class="h4 mb-1 fw-bold" id="dashboardGreeting">Bienvenue</div>
                            <div class="text-secondary">Choisissez une action, nous vous guidons ensuite.</div>
                        </div>
                        <div class="d-none">
                            <button class="btn btn-premium btn-sm px-3" id="openSubscriptionModalButton" type="button">Prendre un abonnement</button>
                            <span class="status-pill" id="userStatus">active</span>
                            <span class="status-pill" id="topbarSubscriptionBadge">Abonnement non actif</span>
                            <span class="status-pill" id="topbarMetersBadge">0 identifiant</span>
                            <span class="status-pill" id="topbarReportsBadge">0 signalement</span>
                            <span class="status-pill" id="topbarPaymentsBadge">0 paiement</span>
                        </div>
                        <div class="topbar-session">
                            <div class="d-none" id="sidebarUserLocation">Localisation non renseignée</div>
                            <div class="d-none" id="sidebarUserGps">Position non renseignée</div>
                            <button type="button" class="d-none" id="sidebarRequestGpsButton">Renseigner ma position</button>
                            <button id="topbarNotificationsButton" class="btn btn-sm btn-topbar-session notification-button" type="button" data-panel-target="notifications">
                                Notifications <span class="notification-badge d-none ms-2" id="topbarNotificationBadge">0</span>
                            </button>
                            <button id="logoutButton" class="btn btn-sm btn-topbar-session" type="button">Se déconnecter</button>
                        </div>
                    </header>

                    <section class="public-panel active" data-panel="overview">
                        <div class="simple-home">
                            <section class="simple-home-card" id="subscriptionOverviewCard">
                                <div class="simple-home-content">
                                    <div class="text-uppercase small fw-bold opacity-75 mb-3">Vue d'ensemble</div>
                                    <h1 class="simple-home-title">Que voulez-vous faire maintenant ?</h1>
                                    <p class="simple-home-copy">Cet espace sert surtout à signaler un problème et suivre vos demandes. Commencez par l'action dont vous avez besoin.</p>

                                    <div class="simple-home-actions">
                                        <button class="btn btn-premium simple-home-main-action" type="button" data-panel-target="reports">Faire un signalement</button>
                                        <button class="simple-home-secondary" type="button" data-panel-target="meters">Ajouter un identifiant</button>
                                        <button class="simple-home-secondary" type="button" data-panel-target="privilege-cards">Carte privilège</button>
                                        <button class="simple-home-secondary" type="button" data-panel-target="payments">Voir mes paiements</button>
                                    </div>

                                    <div class="simple-home-note" id="subscriptionOverviewText">Accès libre aux signalements</div>
                                    <button class="d-none" id="subscriptionOverviewButton" type="button">Prendre un abonnement</button>
                                    <span class="d-none" id="meterCount">0</span>
                                    <span class="d-none" id="reportCount">0</span>
                                    <span class="d-none" id="householdMemberCount">0</span>
                                    <div class="d-none" id="overviewReportsList"></div>
                                </div>
                            </section>
                        </div>
                    </section>

                    <section class="public-panel" data-panel="notifications">
                        <div class="dashboard-card">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                <div>
                                    <div class="section-title">Mes notifications</div>
                                    <div class="muted-label">Retrouvez les messages envoyés par My-Signal et les alertes liées à votre compte.</div>
                                </div>
                                <button class="btn btn-premium px-4" type="button" id="markAllNotificationsReadButton">Tout marquer comme lu</button>
                            </div>
                            <div class="mini-card mb-4">
                                <div class="row g-3 align-items-end">
                                    <div class="col-12 col-lg-4">
                                        <label class="form-label fw-semibold">Recherche</label>
                                        <input class="form-control" id="notificationSearchFilter" placeholder="Titre, message...">
                                    </div>
                                    <div class="col-12 col-md-4 col-lg-2">
                                        <label class="form-label fw-semibold">État</label>
                                        <select class="form-select" id="notificationReadFilter">
                                            <option value="">Tous</option>
                                            <option value="unread">Non lus</option>
                                            <option value="read">Lus</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-4 col-lg-3">
                                        <label class="form-label fw-semibold">Catégorie</label>
                                        <select class="form-select" id="notificationCategoryFilter">
                                            <option value="">Toutes</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-4 col-lg-3">
                                        <button class="btn btn-ghost-premium w-100" type="button" id="resetNotificationFiltersButton">Effacer</button>
                                    </div>
                                </div>
                            </div>
                            <div id="notificationsList"></div>
                        </div>
                    </section>

                    <section class="public-panel" data-panel="profile">
                        <div class="dashboard-card">
                            <div class="d-flex justify-content-between align-items-start mb-4">
                                <div>
                                    <div class="section-title">Mon profil public</div>
                                    <div class="muted-label">Votre identité, votre adresse et votre position de référence pour accélérer les futures déclarations.</div>
                                </div>
                                <span class="status-pill" id="profileStatusPill">active</span>
                            </div>
                            <div class="row g-4">
                                <div class="col-xl-7">
                                    <form id="profileForm" class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Type d’usager public</label>
                                            <select class="form-select" id="profilePublicUserTypeSelect" required disabled>
                                                @foreach ($publicUserTypes as $publicUserType)
                                                    <option value="{{ $publicUserType->id }}" data-profile-kind="{{ $publicUserType->profile_kind }}" data-type-code="{{ $publicUserType->code }}">{{ $publicUserType->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="location-search-hint">Ce type est défini à la création du compte et ne peut pas être modifié ici.</div>
                                        </div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Prénom</label><input class="form-control" name="first_name" required></div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Nom</label><input class="form-control" name="last_name" required></div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Numéro WhatsApp</label>
                                            <select class="form-select" name="is_whatsapp_number">
                                                <option value="0">Non</option>
                                                <option value="1">Oui</option>
                                            </select>
                                        </div>
                                        <div class="col-12"><label class="form-label fw-semibold">Email</label><input class="form-control" type="email" name="email"></div>
                                        <div class="col-12 hidden" id="profileSectorFields">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Secteur d’activité</label>
                                                    <select class="form-select" name="business_sector">
                                                        <option value="">Sélectionner un secteur</option>
                                                        @foreach ($businessSectors as $businessSector)
                                                            <option value="{{ $businessSector->name }}">{{ $businessSector->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 hidden" id="profileBusinessFields">
                                            <div class="row g-3">
                                                <div class="col-md-6"><label class="form-label fw-semibold">Raison sociale</label><input class="form-control" name="company_name"></div>
                                                <div class="col-md-6"><label class="form-label fw-semibold">RCCM / Immatriculation</label><input class="form-control" name="company_registration_number"></div>
                                                <div class="col-md-6"><label class="form-label fw-semibold">Numéro fiscal</label><input class="form-control" name="tax_identifier"></div>
                                                <div class="col-12"><label class="form-label fw-semibold">Adresse de l entreprise</label><input class="form-control" name="company_address"></div>
                                            </div>
                                        </div>
                                        <div class="col-12"><label class="form-label fw-semibold">Commune</label><select class="form-select" name="commune" id="profileCommuneSelect" required></select></div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Adresse</label>
                                            <input class="form-control" name="address" id="profileAddressSearch" placeholder="Rechercher une adresse ou laisser la position automatique">
                                            <div class="location-search-hint">Si le service d’adresse est disponible, ce champ propose des adresses et place automatiquement la position.</div>
                                        </div>
                                        <div class="col-12">
                                            <div class="geo-box">
                                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                                                    <div>
                                                        <div class="fw-bold">Position du compte</div>
                                                        <div class="muted-label">Votre position est récupérée automatiquement quand vous ouvrez ce profil.</div>
                                                    </div>
                                                    <div class="d-flex gap-2 flex-wrap">
                                                        <button class="btn btn-ghost-premium px-4" type="button" id="captureProfileLocationButton">Récupérer ma position</button>
                                                        <button class="btn btn-ghost-premium px-4" type="button" id="toggleProfileManualLocationButton">Saisie manuelle</button>
                                                    </div>
                                                </div>
                                                <div class="row g-3">
                                                    <div class="col-md-4"><label class="form-label fw-semibold">Position nord-sud</label><input class="form-control" name="latitude" id="profileLatitude" readonly></div>
                                                    <div class="col-md-4"><label class="form-label fw-semibold">Position est-ouest</label><input class="form-control" name="longitude" id="profileLongitude" readonly></div>
                                                    <div class="col-md-4"><label class="form-label fw-semibold">Qualité de position</label><input class="form-control" name="location_accuracy" id="profileAccuracy" readonly></div>
                                                </div>
                                                <div class="geo-help mt-2">Si le navigateur refuse la géolocalisation, vous pouvez choisir une adresse ou activer la saisie manuelle.</div>
                                                <input type="hidden" name="location_source" id="profileLocationSource" value="">
                                            </div>
                                        </div>
                                        <div class="col-12"><button class="btn btn-premium w-100" type="submit">Mettre à jour mon profil</button></div>
                                    </form>
                                </div>
                                <div class="col-xl-5">
                                    <div class="mini-card h-100">
                                        <div class="small text-secondary fw-semibold mb-2">Compte public</div>
                                        <div class="fw-bold fs-4 mb-1" id="profileFullNameCard">-</div>
                                        <div class="muted-label mb-4" id="profilePhoneCard">-</div>
                                        <div class="soft-panel mb-3">
                                            <div class="small text-secondary fw-semibold mb-1">Commune actuelle</div>
                                            <div class="fw-semibold" id="profileCommuneCard">-</div>
                                        </div>
                                        <div class="soft-panel mb-3">
                                            <div class="small text-secondary fw-semibold mb-1">Adresse actuelle</div>
                                            <div class="fw-semibold" id="profileAddressCard">-</div>
                                            <div class="muted-label" id="profileGpsCard">Position non renseignée</div>
                                        </div>
                                        <div class="soft-panel mb-3">
                                            <div class="small text-secondary fw-semibold mb-1">Type d’usager</div>
                                            <div class="fw-semibold" id="profileUserTypeCard">-</div>
                                        </div>
                                        <div class="soft-panel mb-3">
                                            <div class="small text-secondary fw-semibold mb-1">Numéro WhatsApp</div>
                                            <div class="fw-semibold" id="profileWhatsappCard">Non</div>
                                        </div>
                                        <div class="soft-panel">
                                            <div class="small text-secondary fw-semibold mb-1">État du compte</div>
                                            <div class="fw-semibold" id="profileStatusCard">-</div>
                                        </div>
                                        <div class="mt-3" id="memberWalletCardWrap"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="public-panel" data-panel="meters">
                        <div class="dashboard-card">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                <div>
                                    <div class="section-title">Mes identifiants</div>
                                    <div class="muted-label">Ajoutez vos identifiants CIE et SODECI, ... avec la localisation et la commune associées.</div>
                                </div>
                                <button class="btn btn-ghost-premium px-4" type="button" data-bs-toggle="collapse" data-bs-target="#meterFormWrap">Ajouter ou modifier</button>
                            </div>
                            <div id="meterFormWrap" class="collapse show mb-4">
                                <form id="meterForm" class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Catégorie</label>
                                        <select class="form-select" id="meterApplicationId" required></select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Institution</label>
                                        <select class="form-select" id="meterOrganizationId" required></select>
                                    </div>
                                    <input type="hidden" name="network_type" id="meterNetworkType" required>
                                    <div class="col-md-4"><label class="form-label fw-semibold">Mon identifiant</label><input class="form-control" name="meter_number" required></div>
                                    <div class="col-md-4"><label class="form-label fw-semibold">Libellé</label><input class="form-control" name="label"></div>
                                    <div class="col-md-4"><label class="form-label fw-semibold">Ville</label><select class="form-select" name="city" id="meterCitySelect"></select></div>
                                    <div class="col-md-4"><label class="form-label fw-semibold">Commune</label><select class="form-select" name="commune" id="meterCommuneSelect"></select></div>
                                    <div class="col-md-4"><label class="form-label fw-semibold">Quartier</label><select class="form-select" name="neighborhood" id="meterNeighborhoodSelect"></select></div>
                                    <div class="col-md-4"><label class="form-label fw-semibold">Sous-quartier</label><select class="form-select" name="sub_neighborhood" id="meterSubNeighborhoodSelect"></select></div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Situation géographique</label>
                                        <input class="form-control" name="address" id="meterAddressSearch" placeholder="Ex: Abatta carrefour Ab Center">
                                        <div class="location-search-hint">Une adresse peut remplir automatiquement la position et aider à retrouver la bonne commune.</div>
                                    </div>
                                    <div class="col-12">
                                        <div class="soft-panel">
                                            <div class="fw-bold mb-1">Position de l’identifiant</div>
                                            <div class="muted-label">La position de l'identifiant est récupérée automatiquement en arrière-plan pour vous. Si vous choisissez une adresse, la localisation est mise à jour automatiquement.</div>
                                        </div>
                                        <input type="hidden" name="latitude" id="meterLatitude">
                                        <input type="hidden" name="longitude" id="meterLongitude">
                                        <input type="hidden" name="location_accuracy" id="meterAccuracy">
                                        <input type="hidden" name="location_source" id="meterLocationSource" value="">
                                    </div>
                                    <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" value="1" name="is_primary" id="isPrimaryMeter"><label class="form-check-label fw-semibold" for="isPrimaryMeter">Définir comme identifiant principal</label></div></div>
                                    <div class="col-12"><button class="btn btn-premium" type="submit">Enregistrer</button></div>
                                </form>
                            </div>
                            <div id="metersList" class="row g-3"></div>
                        </div>
                    </section>

                    <section class="public-panel" data-panel="household">
                        <div class="dashboard-card">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                <div>
                                    <div class="section-title">Mon Gbonhi et ma famille</div>
                                    <div class="muted-label">Créez plusieurs Gbonhi, puis choisissez celui sur lequel inviter vos proches.</div>
                                </div>
                                <button class="btn btn-premium px-4" type="button" id="showHouseholdFormButton">Créer un autre Gbonhi</button>
                            </div>
                            <div id="householdEmptyState" class="mini-card mb-4">
                                <div class="fw-bold mb-2">Aucun Gbonhi enregistré</div>
                                <p class="muted-label mb-3">Créez votre Gbonhi principal pour inviter vos proches et partager un identifiant commun.</p>
                                <form id="householdForm" class="row g-3">
                                    <div class="col-12"><label class="form-label fw-semibold">Nom du Gbonhi</label><input class="form-control" name="name"></div>
                                    <div class="col-12 d-flex gap-2 flex-wrap">
                                        <button class="btn btn-premium" type="submit">Créer le Gbonhi</button>
                                        <button class="btn btn-ghost-premium d-none" type="button" id="cancelHouseholdFormButton">Annuler</button>
                                    </div>
                                </form>
                            </div>
                            <div id="householdPanel" class="d-none">
                                <div class="mini-card mb-4">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                                        <div>
                                            <div class="fw-bold">Mes Gbonhi</div>
                                            <div class="muted-label">Sélectionnez un Gbonhi pour voir ses membres et envoyer des invitations.</div>
                                        </div>
                                    </div>
                                    <div id="householdsList" class="vstack gap-2"></div>
                                </div>
                                <div class="mini-card mb-4">
                                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                                        <div><div class="fw-bold fs-5" id="householdName">-</div><div class="muted-label" id="householdAddress">-</div></div>
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <span class="status-pill" id="householdStatus">active</span>
                                            <button class="btn btn-sm btn-outline-danger d-none" type="button" id="deleteHouseholdButton">Supprimer</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row g-4">
                                    <div class="col-lg-5">
                                        <div class="mini-card h-100">
                                            <div class="fw-bold mb-3">Inviter un membre</div>
                                            <form id="householdInvitationForm" class="row g-3">
                                                <div class="col-12">
                                                    <label class="form-label fw-semibold">Numéro</label>
                                                    <div class="input-group">
                                                        <select class="form-select flex-grow-0" name="phone_dial_code" data-dial-code-select style="width: 132px; max-width: 132px; min-width: 132px;"></select>
                                                        <input class="form-control" name="phone_local" required>
                                                    </div>
                                                    <input type="hidden" name="phone">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label fw-semibold">identifiant commun du Gbonhi</label>
                                                    <select class="form-select" name="meter_id" id="householdSharedMeterId" required></select>
                                                </div>
                                                <div class="col-12"><button class="btn btn-premium w-100" type="submit">Envoyer l invitation</button></div>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="col-lg-7">
                                        <div class="mini-card mb-3"><div class="fw-bold mb-3">Membres</div><div id="householdMembersList" class="vstack gap-2"></div></div>
                                        <div class="mini-card"><div class="fw-bold mb-3">Invitations en attente</div><div id="householdInvitationsList" class="vstack gap-2"></div></div>
                                    </div>
                                </div>
                            </div>
                            <div class="mini-card mt-4">
                                <div class="fw-bold mb-3">Invitations Gbonhi reçues</div>
                                <div id="incomingHouseholdInvitationsList" class="vstack gap-2"></div>
                            </div>
                        </div>
                    </section>

                    <section class="public-panel" data-panel="reports">
                        <div class="dashboard-card">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                <div>
                                    <div class="section-title">Mes signalements</div>
                                    <div class="muted-label">Un parcours de déclaration moderne, ancré sur les identifiants et les références géographiques.</div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 justify-content-end">
                                    <button class="btn btn-ghost-premium px-4 d-none" type="button" id="openDamageDeclarationButton">Enregistrer un dommage</button>
                                    <button class="btn btn-premium px-4" type="button" data-bs-toggle="modal" data-bs-target="#reportFormModal">Signaler un problème</button>
                                </div>
                            </div>
                            <div class="mini-card mb-4">
                                <div class="row g-3">
                                    <div class="col-lg-4">
                                        <label class="form-label fw-semibold">Recherche</label>
                                        <input class="form-control" id="reportSearchFilter" placeholder="Numéro de suivi, type, commune...">
                                    </div>
                                    <div class="col-md-4 col-lg-2">
                                        <label class="form-label fw-semibold">État</label>
                                        <select class="form-select" id="reportStatusFilter">
                                            <option value="">Tous</option>
                                            <option value="submitted">Soumis</option>
                                            <option value="in_progress">En cours de traitement</option>
                                            <option value="resolved">Résolu par l'institution</option>
                                            <option value="rejected">Non retenu</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-lg-2">
                                        <label class="form-label fw-semibold">Paiement</label>
                                        <select class="form-select" id="reportPaymentFilter">
                                            <option value="">Tous</option>
                                            <option value="paid">Payé</option>
                                            <option value="pending">En attente</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-lg-2">
                                        <label class="form-label fw-semibold">Institution</label>
                                        <select class="form-select" id="reportOrganizationFilter">
                                            <option value="">Toutes</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-lg-3">
                                        <label class="form-label fw-semibold">Résolution</label>
                                        <select class="form-select" id="reportResolutionFilter">
                                            <option value="">Toutes</option>
                                            <option value="awaiting_institution">En attente de traitement</option>
                                            <option value="institution_resolved">Résolu, en attente de votre confirmation</option>
                                            <option value="confirmed">Résolution confirmée</option>
                                            <option value="rejected">Signalement non retenu</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-1 d-flex align-items-end">
                                        <button class="btn btn-ghost-premium w-100" type="button" id="resetReportFiltersButton">Effacer</button>
                                    </div>
                                </div>
                            </div>
                            <div id="reportsList"></div>
                        </div>
                    </section>

                    <section class="public-panel" data-panel="report-payment">
                        <div class="dashboard-card">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                                <div>
                                    <div class="section-title">Paiement du signalement</div>
                                    <div class="muted-label" id="reportPaymentWaitingSubtitle">Gardez cette page ouverte pendant que vous terminez le paiement dans l’autre onglet.</div>
                                </div>
                                <span class="status-pill" id="reportPaymentWaitingStatus">En attente</span>
                            </div>

                            <div class="mini-card mb-4">
                                <div class="row g-4 align-items-center">
                                    <div class="col-lg-7">
                                        <div class="small text-secondary fw-semibold mb-1">Numéro de suivi</div>
                                        <div class="fw-bold fs-4 mb-2" id="reportPaymentWaitingReference">-</div>
                                        <div class="muted-label" id="reportPaymentWaitingMessage">Nous vérifions automatiquement la confirmation du paiement.</div>
                                    </div>
                                    <div class="col-lg-5">
                                        <div class="soft-panel">
                                            <div class="small text-secondary fw-semibold mb-1">Montant</div>
                                            <div class="fw-bold fs-4" id="reportPaymentWaitingAmount">-</div>
                                            <div class="muted-label" id="reportPaymentWaitingProvider">Paiement sécurisé</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mini-card mb-4" id="reportPaymentWaitingLoader">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
                                    <div>
                                        <div class="fw-bold" id="reportPaymentWaitingLoaderTitle">Vérification du paiement en cours</div>
                                        <div class="muted-label" id="reportPaymentWaitingLoaderText">Le signalement sera créé automatiquement dès que le paiement est confirmé.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2" id="reportPaymentWaitingActions">
                                <button class="btn btn-premium px-4" type="button" id="refreshReportPaymentStatusButton">J’ai terminé le paiement</button>
                                <button class="btn btn-ghost-premium px-4" type="button" id="reopenReportPaymentButton">Rouvrir le paiement</button>
                                <button class="btn btn-ghost-premium px-4" type="button" id="cancelReportPaymentWaitingButton">Annuler</button>
                            </div>
                        </div>
                    </section>

                    <section class="public-panel" data-panel="payments">
                        <div class="dashboard-card">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                <div>
                                    <div class="section-title">Historique des paiements</div>
                                    <div class="muted-label">Retrouve tous tes paiements confirmés ou en attente, puis télécharge ton reçu quand il est disponible.</div>
                                </div>
                                <button class="btn btn-ghost-premium px-4" type="button" data-panel-target="reports">Voir les signalements</button>
                            </div>
                            <div class="mini-card mb-4">
                                <div class="row g-3">
                                    <div class="col-lg-5">
                                        <label class="form-label fw-semibold">Recherche</label>
                                        <input class="form-control" id="paymentSearchFilter" placeholder="Numéro de paiement, signalement, mode...">
                                    </div>
                                    <div class="col-md-4 col-lg-3">
                                        <label class="form-label fw-semibold">État</label>
                                        <select class="form-select" id="paymentStatusFilter">
                                            <option value="">Tous</option>
                                            <option value="paid">Confirmés</option>
                                            <option value="pending">En attente</option>
                                            <option value="failed">Échoués</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-lg-3">
                                        <label class="form-label fw-semibold">Reçu</label>
                                        <select class="form-select" id="paymentReceiptFilter">
                                            <option value="">Tous</option>
                                            <option value="available">Disponible</option>
                                            <option value="unavailable">Indisponible</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-lg-1 d-flex align-items-end">
                                        <button class="btn btn-ghost-premium w-100" type="button" id="resetPaymentFiltersButton">Effacer</button>
                                    </div>
                                </div>
                            </div>
                            <div id="paymentsList"></div>
                        </div>
                    </section>

                    <section class="public-panel" data-panel="privilege-cards">
                        <div class="dashboard-card">
                            <div class="privilege-premium-shell mb-4">
                                <div class="privilege-premium-content">
                                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                                        <div>
                                            <div class="privilege-eyebrow mb-2">Cartes privilèges</div>
                                            <div class="privilege-title mb-2">Vos avantages, prêts à scanner.</div>
                                            <div class="privilege-copy">Achetez une carte, suivez le paiement sécurisé et ajoutez la carte active au portefeuille de votre téléphone après confirmation.</div>
                                        </div>
                                        <button class="btn btn-premium px-4" type="button" id="refreshPrivilegeCardsButton">Actualiser</button>
                                    </div>
                                    <div class="privilege-active-card" id="activePrivilegeCardBox">
                                        <div class="fw-bold mb-1">Carte active</div>
                                        <div class="text-white-50">Votre carte active apparaîtra ici dès que le paiement est confirmé.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                                <div>
                                    <div class="section-title" style="font-size: 1rem;">Acheter une carte</div>
                                    <div class="muted-label">Choisissez le niveau qui correspond à vos besoins.</div>
                                </div>
                            </div>
                            <div class="row g-3 mb-4" id="privilegeCardTypesList"></div>

                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                                <div>
                                    <div class="section-title" style="font-size: 1rem;">Historique d'achat</div>
                                    <div class="muted-label">Suivez vos paiements et les cartes émises après confirmation.</div>
                                </div>
                            </div>
                            <div id="privilegeCardPaymentsList"></div>
                        </div>
                    </section>

                    <section class="public-panel d-none" data-panel="subscriptions">
                        <div class="dashboard-card">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                <div>
                                    <div class="section-title">Historique des abonnements</div>
                                    <div class="muted-label">Retrouve tes souscriptions annuelles, leurs états, leurs périodes et les paiements associés.</div>
                                </div>
                                <button class="btn btn-premium px-4" type="button" id="openSubscriptionFromHistoryButton">Prendre un abonnement</button>
                            </div>
                            <div class="mini-card mb-4">
                                <div class="row g-3">
                                    <div class="col-lg-5">
                                        <label class="form-label fw-semibold">Recherche</label>
                                        <input class="form-control" id="subscriptionSearchFilter" placeholder="Plan, référence paiement, montant...">
                                    </div>
                                    <div class="col-md-4 col-lg-3">
                                        <label class="form-label fw-semibold">État</label>
                                        <select class="form-select" id="subscriptionStatusFilter">
                                            <option value="">Tous</option>
                                            <option value="active">Actifs</option>
                                            <option value="pending">En attente</option>
                                            <option value="expired">Expirés</option>
                                            <option value="cancelled">Annulés</option>
                                            <option value="suspended">Suspendus</option>
                                            <option value="payment_failed">Paiement échoué</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-lg-3">
                                        <label class="form-label fw-semibold">Paiement</label>
                                        <select class="form-select" id="subscriptionPaymentStatusFilter">
                                            <option value="">Tous</option>
                                            <option value="paid">Confirmés</option>
                                            <option value="pending">En attente</option>
                                            <option value="failed">Échoués</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-lg-1 d-flex align-items-end">
                                        <button class="btn btn-ghost-premium w-100" type="button" id="resetSubscriptionFiltersButton">Effacer</button>
                                    </div>
                                </div>
                            </div>
                            <div id="subscriptionsList"></div>
                        </div>
                    </section>

                    <section class="public-panel" data-panel="rex">
                        <div class="dashboard-card">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                <div>
                                    <div class="section-title">Mes retours d’expérience</div>
                                    <div class="muted-label">Historique global de tes retours sur les signalements, dommages et dossiers traités.</div>
                                </div>
                            </div>
                            <div class="mini-card mb-4">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-4">
                                        <label class="form-label small text-secondary">Recherche</label>
                                        <input class="form-control" id="rexSearchFilter" placeholder="Numéro de suivi, institution, commentaire...">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small text-secondary">Type</label>
                                        <select class="form-select" id="rexContextFilter">
                                            <option value="">Tous</option>
                                            <option value="incident_report">Signalements</option>
                                            <option value="damage_declaration">Dommages</option>
                                            <option value="reparation_case">Dossiers</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small text-secondary">Note globale</label>
                                        <select class="form-select" id="rexRatingFilter">
                                            <option value="">Toutes</option>
                                            <option value="5">5/5</option>
                                            <option value="4">4/5</option>
                                            <option value="3">3/5</option>
                                            <option value="2">2/5</option>
                                            <option value="1">1/5</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button class="btn btn-ghost-premium w-100" type="button" id="resetRexFiltersButton">Effacer</button>
                                    </div>
                                </div>
                            </div>
                            <div id="rexFeedbacksList"></div>
                        </div>
                    </section>

                    <section class="public-panel" data-panel="damages">
                        <div class="dashboard-card">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                <div>
                                    <div class="section-title">Historique des dommages</div>
                                    <div class="muted-label">Retrouvez les dommages déclarés après résolution, leur état de traitement et leurs justificatifs.</div>
                                </div>
                                <button class="btn btn-ghost-premium px-4" type="button" data-panel-target="reports">Voir les signalements</button>
                            </div>
                            <div class="mini-card mb-4">
                                <div class="row g-3">
                                    <div class="col-lg-4">
                                        <label class="form-label fw-semibold">Recherche</label>
                                        <input class="form-control" id="damageSearchFilter" placeholder="Numéro de suivi, résumé, institution...">
                                    </div>
                                    <div class="col-md-4 col-lg-3">
                                        <label class="form-label fw-semibold">Institution</label>
                                        <select class="form-select" id="damageOrganizationFilter">
                                            <option value="">Toutes</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-lg-2">
                                        <label class="form-label fw-semibold">Traitement</label>
                                        <select class="form-select" id="damageResolutionFilter">
                                            <option value="">Tous</option>
                                            <option value="submitted">Soumis</option>
                                            <option value="in_progress">En cours</option>
                                            <option value="resolved">Résolu</option>
                                            <option value="rejected">Rejeté</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-lg-2">
                                        <label class="form-label fw-semibold">Justificatif</label>
                                        <select class="form-select" id="damageAttachmentFilter">
                                            <option value="">Tous</option>
                                            <option value="available">Disponible</option>
                                            <option value="unavailable">Indisponible</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-1 d-flex align-items-end">
                                        <button class="btn btn-ghost-premium w-100" type="button" id="resetDamageFiltersButton">Effacer</button>
                                    </div>
                                </div>
                            </div>
                            <div id="damagesList"></div>
                        </div>
                    </section>

                    <section class="public-panel" data-panel="receipts">
                        <div class="dashboard-card">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                <div>
                                    <div class="section-title">Mes reçus d’achat</div>
                                    <div class="muted-label">Conservez les informations utiles sur vos achats de matériel.</div>
                                </div>
                            </div>
                            <div class="mini-card mb-4">
                                <form id="purchaseReceiptForm" class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Nom du matériel</label>
                                        <input class="form-control" name="material_name" maxlength="160" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Date d’achat</label>
                                        <input class="form-control" type="date" name="purchase_date" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Montant (FCFA)</label>
                                        <input class="form-control" type="number" min="0" step="0.01" name="amount" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Fichier du reçu</label>
                                        <input class="form-control" type="file" name="receipt_file" accept="image/*,application/pdf">
                                        <div class="location-search-hint">Image ou PDF stocké sur Wasabi. Optionnel lors de la saisie, remplaçable à la modification.</div>
                                    </div>
                                    <div class="col-12 d-flex justify-content-end gap-2">
                                        <button class="btn btn-ghost-premium px-4 d-none" type="button" id="cancelPurchaseReceiptEditButton">Annuler</button>
                                        <button class="btn btn-premium px-4" type="submit">Enregistrer le reçu</button>
                                    </div>
                                </form>
                            </div>
                            <div id="purchaseReceiptsList" class="row g-3"></div>
                        </div>
                    </section>

                    <section class="public-panel" data-panel="cases">
                        <div class="dashboard-card">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                <div>
                                    <div class="section-title">Historique de mes dossiers</div>
                                    <div class="muted-label">Consulte l’avancement des dossiers ouverts à partir de tes signalements et les mises à jour enregistrées par le traitement du dossier.</div>
                                </div>
                                <button class="btn btn-ghost-premium px-4" type="button" data-panel-target="reports">Voir les signalements</button>
                            </div>
                            <div id="reparationCasesList"></div>
                        </div>
                    </section>
                </main>
            </div>
        </div>

        <div class="modal fade" id="reportFormModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content border-0" style="border-radius: 28px; overflow: hidden;">
                    <div class="modal-header px-4 py-3 border-0" style="background: var(--acepen-navy); color: white;">
                        <div>
                            <div class="small text-white-50 fw-semibold mb-1">Nouveau signalement</div>
                            <div class="h5 fw-bold mb-0">Déclarer un problème</div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                            <div class="modal-body p-4 p-lg-4">
                        <form id="reportForm" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Catégorie concernée<span class="required-star">*</span></label>
                                <div class="select-search-shell">
                                    <input class="form-control select-search-input" style="display:block;width:100%;" type="search" data-search-select-target="reportApplicationId" autocomplete="off" placeholder="Rechercher une catégorie">
                                    <button class="select-search-toggle" type="button" data-search-toggle-target="reportApplicationId" aria-label="Afficher les options"></button>
                                </div>
                                <div class="select-search-help">Champ de sélection avec recherche.</div>
                                <select class="form-select d-none" id="reportApplicationId" required></select>
                                <div class="select-search-results" id="reportApplicationIdResults"></div>
                            </div>
                            <div class="col-md-4" id="reportOrganizationTypeFieldWrap">
                                <label class="form-label fw-semibold">Sous Catégorie<span class="required-star">*</span></label>
                                <div class="select-search-shell">
                                    <input class="form-control select-search-input" style="display:block;width:100%;" type="search" data-search-select-target="reportOrganizationTypeId" autocomplete="off" placeholder="Rechercher une sous catégorie">
                                    <button class="select-search-toggle" type="button" data-search-toggle-target="reportOrganizationTypeId" aria-label="Afficher les options"></button>
                                </div>
                                <div class="select-search-help">Sous catégorie requise selon la catégorie.</div>
                                <select class="form-select d-none" id="reportOrganizationTypeId"></select>
                                <div class="select-search-results" id="reportOrganizationTypeIdResults"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Institution concernée<span class="required-star">*</span></label>
                                <div class="select-search-shell">
                                    <input class="form-control select-search-input" style="display:block;width:100%;" type="search" data-search-select-target="reportOrganizationType" autocomplete="off" placeholder="Rechercher une institution">
                                    <button class="select-search-toggle" type="button" data-search-toggle-target="reportOrganizationType" aria-label="Afficher les options"></button>
                                </div>
                                <div class="select-search-help">Champ de sélection avec recherche.</div>
                                <select class="form-select d-none" id="reportOrganizationType" required></select>
                                <div class="select-search-results" id="reportOrganizationTypeResults"></div>
                                <div class="location-search-hint">Choisissez d’abord la catégorie, puis l’institution concernée, pour afficher uniquement les identifiants et types de signal compatibles.</div>
                            </div>
                            <div class="col-md-4" id="reportMeterFieldWrap">
                                <label class="form-label fw-semibold"><span id="reportMeterFieldLabel">Identifiant</span><span class="required-star">*</span></label>
                                <div class="select-search-shell">
                                    <input class="form-control select-search-input" style="display:block;width:100%;" type="search" data-search-select-target="reportMeterId" autocomplete="off" placeholder="Rechercher un identifiant" id="reportMeterSearchInput">
                                    <button class="select-search-toggle" type="button" data-search-toggle-target="reportMeterId" aria-label="Afficher les options"></button>
                                </div>
                                <div class="select-search-help">Champ de sélection avec recherche.</div>
                                <select class="form-select d-none" name="meter_id" id="reportMeterId" required></select>
                                <div class="select-search-results" id="reportMeterIdResults"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Type de signal<span class="required-star">*</span></label>
                                <div class="select-search-shell">
                                    <input class="form-control select-search-input" style="display:block;width:100%;" type="search" data-search-select-target="reportSignalCode" autocomplete="off" placeholder="Rechercher un type de signal">
                                    <button class="select-search-toggle" type="button" data-search-toggle-target="reportSignalCode" aria-label="Afficher les options"></button>
                                </div>
                                <div class="select-search-help">Champ de sélection avec recherche.</div>
                                <select class="form-select d-none" name="signal_code" id="reportSignalCode" required></select>
                                <div class="select-search-results" id="reportSignalCodeResults"></div>
                                <div class="location-search-hint mt-2" id="reportSignalInlineDescription">Sélectionnez un type de signal pour afficher sa description et son délai de résolution.</div>
                            </div>
                            <div class="col-md-4 d-none" id="reportSignalSubTypeWrap">
                                <label class="form-label fw-semibold">Sous-type de signal<span class="required-star">*</span></label>
                                <div class="select-search-shell">
                                    <input class="form-control select-search-input" style="display:block;width:100%;" type="search" data-search-select-target="reportSignalSubTypeCode" autocomplete="off" placeholder="Rechercher un sous-type">
                                    <button class="select-search-toggle" type="button" data-search-toggle-target="reportSignalSubTypeCode" aria-label="Afficher les options"></button>
                                </div>
                                <div class="select-search-help">Champ de sélection avec recherche.</div>
                                <select class="form-select d-none" name="signal_sub_type_code" id="reportSignalSubTypeCode"></select>
                                <div class="select-search-results" id="reportSignalSubTypeCodeResults"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Date et heure<span class="required-star">*</span></label>
                                <input class="form-control" type="datetime-local" name="occurred_at" id="reportOccurredAt" readonly>
                                <div class="location-search-hint">La date et l’heure actuelles sont appliquées automatiquement au signalement.</div>
                            </div>
                            <div class="col-12">
                                <div class="soft-panel">
                                    <div class="fw-bold mb-1">Localisation automatique</div>
                                    <div class="muted-label" id="reportLocationHelp">Le pays, la ville, la commune et l’adresse sont récupérés automatiquement à partir de l’identifiant sélectionné.</div>
                                </div>
                                <input type="hidden" name="latitude" id="reportLatitude">
                                <input type="hidden" name="longitude" id="reportLongitude">
                                <input type="hidden" name="location_accuracy" id="reportAccuracy">
                                <input type="hidden" name="location_source" id="reportLocationSource" value="">
                            </div>
                            <div class="col-12">
                                <div class="soft-panel d-none" id="reportNoMeterHint">
                                    <div class="fw-bold mb-1">Aucun identifiant disponible pour ce réseau</div>
                                    <div class="muted-label">Ajoutez d’abord un identifiant sur ce réseau dans la section <strong>Mes identifiants</strong>, puis revenez déclarer votre signalement.</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="signal-field-card">
                                    <div class="fw-bold mb-2" id="reportSignalMetaTitle">Signalement sélectionné</div>
                                    <div class="muted-label mb-3" id="reportSignalMetaDescription">Sélectionnez un type de signal pour afficher les détails.</div>
                                    <div id="signalPayloadFields" class="row g-3"></div>
                                </div>
                            </div>
                            <div class="col-12"><label class="form-label fw-semibold">Description</label><textarea class="form-control" name="description" rows="4"></textarea></div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Fichier photo ou vidéo</label>
                                <input class="form-control" type="file" name="signal_attachment" accept="image/*,video/*">
                                <div class="location-search-hint">Optionnel.</div>
                            </div>
                            <div class="col-12"><button class="btn btn-premium" type="submit">Enregistrer le signalement</button></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="reportDetailModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content border-0" style="border-radius: 28px; overflow: hidden;">
                    <div class="modal-header px-4 py-3 border-0" style="background: var(--acepen-navy); color: white;">
                        <div>
                            <div class="small text-white-50 fw-semibold mb-1">Détail du signalement</div>
                            <div class="h5 fw-bold mb-0" id="reportDetailTitle">Signalement</div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div id="reportDetailContent"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="reparationCaseDetailModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content border-0" style="border-radius: 28px; overflow: hidden;">
                    <div class="modal-header px-4 py-3 border-0" style="background: var(--acepen-navy); color: white;">
                        <div>
                            <div class="small text-white-50 fw-semibold mb-1">Détail du dossier</div>
                            <div class="h5 fw-bold mb-0" id="reparationCaseDetailTitle">Dossier</div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div id="reparationCaseDetailContent"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="damageDeclarationModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content border-0" style="border-radius: 28px; overflow: hidden;">
                    <div class="modal-header px-4 py-3 border-0" style="background: var(--acepen-copper); color: white;">
                        <div>
                            <div class="small text-white-50 fw-semibold mb-1">Déclaration de dommage</div>
                            <div class="h5 fw-bold mb-0" id="damageDeclarationTitle">Signaler un dommage après résolution</div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <form id="damageDeclarationForm" class="row g-3">
                            <input type="hidden" name="report_id" id="damageDeclarationReportId">
                            <div class="col-12">
                                <div class="soft-panel">
                                    <div class="fw-bold mb-1">Quand utiliser ce bouton ?</div>
                                    <div class="muted-label">Utilisez cette déclaration si le problème a bien été traité mais qu’un dommage matériel, financier ou d’usage reste à signaler après la résolution du sinistre.</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Resume du dommage</label>
                                <input class="form-control" type="text" name="damage_summary" maxlength="255" placeholder="Ex: appareils endommagés, denrées perdues, installation interne touchée">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Montant estimé (FCFA)</label>
                                <input class="form-control" type="number" min="0" step="0.01" name="damage_amount_estimated" placeholder="15000">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Justificatif</label>
                                <input class="form-control" type="file" id="damageAttachmentInput" name="damage_attachment" accept="image/*,application/pdf" required>
                                <div class="geo-help mt-2">Chargez une image ou un PDF. Sur mobile, l’appareil peut proposer directement la caméra si disponible.</div>
                            </div>
                            <div class="col-12 d-none" id="damageAttachmentPreviewWrap">
                                <div class="soft-panel">
                                    <div class="small text-secondary fw-semibold mb-2">Aperçu du justificatif</div>
                                    <img id="damageAttachmentPreviewImage" alt="Aperçu du justificatif" class="img-fluid rounded-4 d-none" style="max-height: 220px; object-fit: cover;">
                                    <div id="damageAttachmentPreviewFile" class="fw-semibold d-none"></div>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Détails complémentaires</label>
                                <textarea class="form-control" name="damage_notes" rows="4" placeholder="Expliquez l’impact du sinistre, ce qui reste endommagé et les informations utiles pour l’analyse."></textarea>
                            </div>
                            <div class="col-12">
                                <div class="soft-panel">
                                    <div class="fw-bold mb-1">Reçu d’achat lié au dommage</div>
                                    <div class="muted-label">Optionnel. Sélectionnez un reçu déjà enregistré ou saisissez les trois informations du reçu pendant cette déclaration.</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Reçu enregistré</label>
                                <select class="form-select" name="purchase_receipt_id" id="damagePurchaseReceiptId">
                                    <option value="">Aucun reçu sélectionné</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Nom du matériel</label>
                                <input class="form-control" type="text" name="receipt_material_name" maxlength="160" placeholder="Ex: Téléviseur">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Date d’achat</label>
                                <input class="form-control" type="date" name="receipt_purchase_date">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Montant (FCFA)</label>
                                <input class="form-control" type="number" min="0" step="0.01" name="receipt_amount" placeholder="150000">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Fichier du reçu</label>
                                <input class="form-control" type="file" name="receipt_attachment" accept="image/*,application/pdf">
                                <div class="geo-help mt-2">Optionnel. Si vous créez un reçu ici, le fichier sera stocké sur Wasabi.</div>
                            </div>
                            <div class="col-12 d-flex justify-content-end gap-2">
                                <button class="btn btn-ghost-premium px-4" type="button" data-bs-dismiss="modal">Annuler</button>
                                <button class="btn btn-premium px-4" type="submit">Enregistrer le dommage</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="paymentReceiptPreviewModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content border-0" style="border-radius: 28px; overflow: hidden;">
                    <div class="modal-header px-4 py-3 border-0" style="background: var(--acepen-navy); color: white;">
                        <div>
                            <div class="small text-white-50 fw-semibold mb-1">Aperçu du reçu</div>
                            <div class="h5 fw-bold mb-0" id="paymentReceiptPreviewTitle">Reçu de paiement</div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4" style="background: #f4f7fb;">
                        <div id="paymentReceiptPreviewContent"></div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4 pt-0" style="background: #f4f7fb;">
                        <button class="btn btn-ghost-premium px-4" type="button" data-bs-dismiss="modal">Fermer</button>
                        <button class="btn btn-premium px-4" type="button" id="paymentReceiptPreviewDownloadButton">Télécharger le PDF</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="subscriptionPromptModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0" style="border-radius: 28px; overflow: hidden;">
                    <div class="modal-header px-4 py-3 border-0" style="background: var(--acepen-navy); color: white;">
                        <div>
                            <div class="small text-white-50 fw-semibold mb-1">Abonnement annuel UP</div>
                            <div class="h5 fw-bold mb-0">Active ton accès aux signalements</div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mini-card mb-3">
                            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                <div>
                                    <div class="small text-secondary fw-semibold mb-1">État actuel</div>
                                    <div class="fw-bold fs-5" id="subscriptionPromptStatus">Abonnement non actif</div>
                                    <div class="muted-label" id="subscriptionPromptDetails">Souscris maintenant pour effectuer tes signalements.</div>
                                </div>
                                <span class="status-pill" id="subscriptionPromptBadge">non actif</span>
                            </div>
                        </div>
                        <div class="soft-panel mb-3">
                            <div class="fw-bold mb-1">Ce que l’abonnement débloque</div>
                            <div class="muted-label">La création de signalements est réservée aux UP avec un abonnement annuel actif ou en période de grâce.</div>
                        </div>
                        <div class="soft-panel d-none" id="subscriptionPaymentPanel">
                            <div class="small text-secondary fw-semibold mb-1">Paiement en attente</div>
                            <div class="fw-bold" id="subscriptionPaymentReference">-</div>
                            <div class="muted-label" id="subscriptionPaymentAmount">-</div>
                        </div>
                        <div class="mt-4">
                            <div class="fw-bold mb-3">Historique des abonnements</div>
                            <div id="subscriptionHistoryList" class="vstack gap-3">
                                <div class="muted-label">Aucun historique disponible.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4 pt-0">
                        <button class="btn btn-ghost-premium px-4" type="button" data-bs-dismiss="modal">Plus tard</button>
                        <button class="btn btn-premium px-4" type="button" id="startSubscriptionPaymentButton">Prendre un abonnement</button>
                        <button class="btn btn-premium px-4 d-none" type="button" id="confirmSubscriptionPaymentButton">Confirmer le paiement</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="rexFeedbackModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0" style="border-radius: 28px; overflow: hidden;">
                    <div class="modal-header px-4 py-3 border-0" style="background: var(--acepen-navy); color: white;">
                        <div>
                            <div class="small text-white-50 fw-semibold mb-1">Retour d’expérience</div>
                            <div class="h5 fw-bold mb-0" id="rexFeedbackTitle">Donner mon retour</div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <form id="rexFeedbackForm" class="row g-3">
                            <input type="hidden" name="context_type" id="rexContextType">
                            <input type="hidden" name="context_id" id="rexContextId">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Note globale</label>
                                <select class="form-select" name="rating" required>
                                    <option value="">Sélectionner</option>
                                    <option value="5">5 - Excellent</option>
                                    <option value="4">4 - Bon</option>
                                    <option value="3">3 - Moyen</option>
                                    <option value="2">2 - Insatisfaisant</option>
                                    <option value="1">1 - Très insatisfaisant</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Le besoin est-il résolu ?</label>
                                <select class="form-select" name="is_resolved">
                                    <option value="">Non applicable</option>
                                    <option value="1">Oui</option>
                                    <option value="0">Non</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Le traitement a-t-il été assez rapide ?</label>
                                <select class="form-select" name="response_time_rating">
                                    <option value="">Non note</option>
                                    <option value="5">5 - Très satisfait</option>
                                    <option value="4">4 - Satisfait</option>
                                    <option value="3">3 - Moyen</option>
                                    <option value="2">2 - Peu satisfait</option>
                                    <option value="1">1 - Pas satisfait</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Les informations reçues étaient-elles claires ?</label>
                                <select class="form-select" name="communication_rating">
                                    <option value="">Non note</option>
                                    <option value="5">5 - Très satisfait</option>
                                    <option value="4">4 - Satisfait</option>
                                    <option value="3">3 - Moyen</option>
                                    <option value="2">2 - Peu satisfait</option>
                                    <option value="1">1 - Pas satisfait</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">La solution apportée répond-elle au besoin ?</label>
                                <select class="form-select" name="quality_rating">
                                    <option value="">Non note</option>
                                    <option value="5">5 - Très satisfait</option>
                                    <option value="4">4 - Satisfait</option>
                                    <option value="3">3 - Moyen</option>
                                    <option value="2">2 - Peu satisfait</option>
                                    <option value="1">1 - Pas satisfait</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Le traitement vous a-t-il semble juste ?</label>
                                <select class="form-select" name="fairness_rating">
                                    <option value="">Non note</option>
                                    <option value="5">5 - Très satisfait</option>
                                    <option value="4">4 - Satisfait</option>
                                    <option value="3">3 - Moyen</option>
                                    <option value="2">2 - Peu satisfait</option>
                                    <option value="1">1 - Pas satisfait</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Commentaire</label>
                                <textarea class="form-control" name="comment" rows="4" maxlength="3000" placeholder="Expliquez votre expérience, ce qui a bien fonctionné ou ce qui doit être amélioré."></textarea>
                            </div>
                            <div class="col-12 d-flex justify-content-end gap-2">
                                <button class="btn btn-ghost-premium px-4" type="button" data-bs-dismiss="modal">Annuler</button>
                                <button class="btn btn-premium px-4" type="submit">Envoyer mon avis</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="toast-container position-fixed top-0 end-0 p-3">
            <div id="appToast" class="toast align-items-center text-bg-dark border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body" id="appToastMessage">Action executee.</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" referrerpolicy="no-referrer"></script>
        @php
            $publicUserTypesPayload = $publicUserTypes->map(fn ($type) => [
                'id' => $type->id,
                'code' => $type->code,
                'name' => $type->name,
                'profile_kind' => $type->profile_kind,
                'pricing_rule' => $type->pricingRule ? [
                    'label' => $type->pricingRule->label,
                    'amount' => $type->pricingRule->amount,
                    'currency' => $type->pricingRule->currency,
                ] : null,
            ])->values();
            $serviceApplicationsPayload = collect($serviceApplications ?? [])->values()->all();
        @endphp
        <script>
            (() => {
                const apiBase = '/api/v1/public';
                const landingUrl = '{{ route('public.landing') }}';
                const googleMapsApiKey = @json(config('services.google_maps.key'));
                const tokenKey = 'acepen_public_token';
                const dashboardPanelStorageKey = 'acepen_public_dashboard_panel';
                const pendingReportPaymentStorageKey = 'acepen_public_pending_report_payment';
                const dialCodeOptions = @json($dialCodeOptions);
                const publicUserTypes = @json($publicUserTypesPayload);
                const serviceApplications = @json($serviceApplicationsPayload);
                const firebaseWebConfig = @json(array_filter(config('services.firebase.web.config', []), fn ($value) => filled($value)));
                const firebaseWebVapidKey = @json(config('services.firebase.web.vapid_key'));
                const firebasePushEnabled = @json((bool) config('services.firebase.enabled'));
                const state = {
                    token: localStorage.getItem(tokenKey),
                    currentUser: null,
                    household: null,
                    pendingHouseholdInvitations: [],
                    meters: [],
                    payments: [],
                    purchaseReceipts: [],
                    pendingReportPayment: null,
                    pendingReportPaymentPoller: null,
                    pendingReportPaymentAttempts: 0,
                    subscription: null,
                    discountCard: null,
                    privilegeCardTypes: [],
                    privilegeCard: null,
                    privilegeCardPaymentSessions: [],
                    subscriptionHistory: [],
                    subscriptionPayments: [],
                    subscriptionHistoryPage: 1,
                    subscriptionHistoryPageSize: 5,
                    household: null,
                    households: [],
                    selectedHouseholdId: null,
                    rexFeedbacks: [],
                    rexFeedbacksPage: 1,
                    rexFeedbacksPageSize: 5,
                    reparationCases: [],
                    countries: [],
                    cities: [],
                    communes: [],
                    reports: [],
                    notifications: [],
                    unreadNotificationsCount: 0,
                    signalTypes: [],
                    reportsPage: 1,
                    reportsPageSize: 5,
                    overviewReportsPage: 1,
                    overviewReportsPageSize: 5,
                    damagesPage: 1,
                    damagesPageSize: 4,
                    overviewReportFilters: {
                        search: '',
                        status: '',
                    },
                    reportFilters: {
                        search: '',
                        status: '',
                        payment: '',
                        organization: '',
                        resolution: '',
                    },
                    paymentFilters: {
                        search: '',
                        status: '',
                        receipt: '',
                    },
                    subscriptionFilters: {
                        search: '',
                        status: '',
                        payment: '',
                    },
                    rexFilters: {
                        search: '',
                        context: '',
                        rating: '',
                    },
                    damageFilters: {
                        search: '',
                        organization: '',
                        resolution: '',
                        attachment: '',
                    },
                    notificationFilters: {
                        search: '',
                        status: '',
                        category: '',
                    },
                    autoGeoAttempts: {
                        profile: false,
                        meter: false,
                        report: false,
                    },
                    pushMessageHandlerRegistered: false,
                };
                const toast = new bootstrap.Toast(document.getElementById('appToast'));
                const reportFormModalElement = document.getElementById('reportFormModal');
                const meterFormWrapElement = document.getElementById('meterFormWrap');
                const reportFormModal = reportFormModalElement ? bootstrap.Modal.getOrCreateInstance(reportFormModalElement) : null;
                const reportDetailModal = document.getElementById('reportDetailModal') ? bootstrap.Modal.getOrCreateInstance(document.getElementById('reportDetailModal')) : null;
                const reparationCaseDetailModal = document.getElementById('reparationCaseDetailModal') ? bootstrap.Modal.getOrCreateInstance(document.getElementById('reparationCaseDetailModal')) : null;
                const damageDeclarationModalElement = document.getElementById('damageDeclarationModal');
                const damageDeclarationModal = damageDeclarationModalElement ? bootstrap.Modal.getOrCreateInstance(damageDeclarationModalElement) : null;
                const paymentReceiptPreviewModalElement = document.getElementById('paymentReceiptPreviewModal');
                const paymentReceiptPreviewModal = paymentReceiptPreviewModalElement ? bootstrap.Modal.getOrCreateInstance(paymentReceiptPreviewModalElement) : null;
                const subscriptionPromptModalElement = document.getElementById('subscriptionPromptModal');
                const subscriptionPromptModal = subscriptionPromptModalElement ? bootstrap.Modal.getOrCreateInstance(subscriptionPromptModalElement) : null;
                const rexFeedbackModalElement = document.getElementById('rexFeedbackModal');
                const rexFeedbackModal = rexFeedbackModalElement ? bootstrap.Modal.getOrCreateInstance(rexFeedbackModalElement) : null;

                if (!state.token) {
                    window.location.href = landingUrl;
                }

                function showToast(message, isError = false) {
                    const toastEl = document.getElementById('appToast');
                    toastEl.classList.remove('text-bg-dark', 'text-bg-success', 'text-bg-danger');
                    toastEl.classList.add(isError ? 'text-bg-danger' : 'text-bg-success');
                    document.getElementById('appToastMessage').textContent = message;
                    toast.show();
                }

                function normalizeText(value) {
                    return String(value || '')
                        .normalize('NFD')
                        .replace(/[\u0300-\u036f]/g, '')
                        .trim()
                        .toLowerCase();
                }

                function ensurePublicSelectId(select) {
                    if (select.id) {
                        return select.id;
                    }

                    const baseId = String(select.name || 'public-select')
                        .replace(/[^a-zA-Z0-9_-]+/g, '-')
                        .replace(/^-+|-+$/g, '');

                    select.id = `${baseId || 'public-select'}-${Math.random().toString(36).slice(2, 8)}`;
                    return select.id;
                }

                function annotateRequiredFields(root = document) {
                    root.querySelectorAll('form input[required], form select[required], form textarea[required]').forEach((field) => {
                        if (field.type === 'hidden' || field.classList.contains('d-none') || field.closest('#reportFormModal')) {
                            return;
                        }

                        const group = field.closest('.col-12, .col-md-3, .col-md-4, .col-md-6, .col-md-8, .col-lg-1, .col-lg-2, .col-lg-3, .col-lg-4, .col-lg-5, .col-lg-7, .col-lg-8, .col-xl-5, .col-xl-7');
                        const label = group?.querySelector('label.form-label');

                        if (!label || label.querySelector('.required-star')) {
                            return;
                        }

                        const star = document.createElement('span');
                        star.className = 'required-star';
                        star.textContent = '*';
                        label.appendChild(star);
                    });
                }

                function syncPublicEnhancedSelect(select) {
                    if (select.dataset.publicEnhanced !== '1') {
                        return;
                    }

                    const input = document.getElementById(`${select.id}PublicInput`);
                    const results = document.getElementById(`${select.id}PublicResults`);

                    if (!input || !results) {
                        return;
                    }

                    const options = Array.from(select.options).map((option) => ({
                        value: option.value,
                        label: option.textContent,
                    }));
                    const selectedLabel = select.options[select.selectedIndex]?.textContent || '';

                    select.dataset.publicEnhancedOptions = JSON.stringify(options);
                    if (document.activeElement !== input) {
                        input.value = selectedLabel;
                    }
                    results.classList.remove('is-open');
                }

                function renderPublicEnhancedSelectOptions(select, query = '', forceOpen = false) {
                    if (select.dataset.publicEnhanced !== '1') {
                        return;
                    }

                    const results = document.getElementById(`${select.id}PublicResults`);
                    const options = JSON.parse(select.dataset.publicEnhancedOptions || '[]');
                    const normalizedQuery = normalizeText(query);
                    const selectedLabel = normalizeText(select.options[select.selectedIndex]?.textContent || '');
                    const matches = normalizedQuery
                        ? options.filter((option) => normalizeText(option.label).includes(normalizedQuery))
                        : options;
                    const hasExactMatch = options.some((option) => normalizeText(option.label) === normalizedQuery);

                    if (!results) {
                        return;
                    }

                    results.innerHTML = matches.length
                        ? matches.map((option) => `<button class="public-select-option" type="button" data-public-select-value="${option.value}" data-public-select-label="${option.label}">${option.label}</button>`).join('')
                        : '<div class="select-search-empty">Aucun résultat</div>';

                    const shouldOpen = forceOpen || normalizedQuery === '' || (!hasExactMatch && normalizedQuery !== selectedLabel);
                    results.classList.toggle('is-open', shouldOpen);
                }

                function enhancePublicFormSelects(root = document) {
                    root.querySelectorAll('form select.form-select:not([data-dial-code-select]):not(.d-none)').forEach((select) => {
                        if (select.closest('#reportForm') || select.dataset.publicEnhanced === '1') {
                            return;
                        }

                        const selectId = ensurePublicSelectId(select);
                        const shell = document.createElement('div');
                        shell.className = 'public-select-shell';
                        shell.innerHTML = `
                            <input class="form-control public-select-input" id="${selectId}PublicInput" type="search" autocomplete="off" placeholder="Rechercher ou sélectionner">
                            <button class="public-select-toggle" id="${selectId}PublicToggle" type="button" aria-label="Afficher les options"></button>
                        `;
                        const help = document.createElement('div');
                        help.className = 'public-select-help';
                        help.textContent = 'Champ de sélection avec recherche.';
                        const results = document.createElement('div');
                        results.className = 'public-select-results';
                        results.id = `${selectId}PublicResults`;

                        select.parentNode.insertBefore(shell, select);
                        select.parentNode.insertBefore(help, select);
                        select.parentNode.insertBefore(results, select);
                        select.classList.add('d-none');
                        select.dataset.publicEnhanced = '1';

                        const input = document.getElementById(`${selectId}PublicInput`);
                        const toggle = document.getElementById(`${selectId}PublicToggle`);
                        const observer = new MutationObserver(() => syncPublicEnhancedSelect(select));
                        observer.observe(select, { childList: true, subtree: true });

                        input.addEventListener('focus', () => renderPublicEnhancedSelectOptions(select, input.value));
                        input.addEventListener('input', () => renderPublicEnhancedSelectOptions(select, input.value));
                        input.addEventListener('change', () => {
                            const options = JSON.parse(select.dataset.publicEnhancedOptions || '[]');
                            const exactMatch = options.find((option) => normalizeText(option.label) === normalizeText(input.value));

                            if (!exactMatch) {
                                input.value = select.options[select.selectedIndex]?.textContent || '';
                                return;
                            }

                            const previousValue = select.value;
                            select.value = exactMatch.value;
                            input.value = exactMatch.label;

                            if (String(previousValue) !== String(select.value)) {
                                select.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        });
                        input.addEventListener('blur', () => {
                            const resultsPanel = document.getElementById(`${selectId}PublicResults`);
                            window.setTimeout(() => {
                                resultsPanel?.classList.remove('is-open');
                                input.value = select.options[select.selectedIndex]?.textContent || '';
                            }, 150);
                        });
                        toggle.addEventListener('mousedown', (event) => event.preventDefault());
                        toggle.addEventListener('click', () => {
                            const resultsPanel = document.getElementById(`${selectId}PublicResults`);
                            const shouldOpen = !resultsPanel?.classList.contains('is-open');
                            renderPublicEnhancedSelectOptions(select, '', true);
                            resultsPanel?.classList.toggle('is-open', shouldOpen);
                            input.focus();
                        });
                        results.addEventListener('click', (event) => {
                            const option = event.target.closest('[data-public-select-value]');

                            if (!option) {
                                return;
                            }

                            const previousValue = select.value;
                            select.value = option.dataset.publicSelectValue;
                            input.value = option.dataset.publicSelectLabel || '';
                            results.classList.remove('is-open');

                            if (String(previousValue) !== String(select.value)) {
                                select.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        });

                        syncPublicEnhancedSelect(select);
                    });
                }

                function cacheSearchableSelectOptions(select) {
                    if (!select) {
                        return;
                    }

                    select.dataset.searchOptions = JSON.stringify(
                        Array.from(select.options).map((option) => ({
                            value: option.value,
                            label: option.textContent,
                        }))
                    );
                }

                function getSearchableSelectInput(selectId) {
                    return document.querySelector(`[data-search-select-target="${selectId}"]`);
                }

                function getSearchableSelectResults(selectId) {
                    return document.getElementById(`${selectId}Results`);
                }

                function applySearchableSelectFilter(selectId, forceOpenAll = false) {
                    const select = document.getElementById(selectId);
                    const searchInput = getSearchableSelectInput(selectId);
                    const results = getSearchableSelectResults(selectId);

                    if (!select || !searchInput || !results) {
                        return;
                    }

                    const cachedOptions = JSON.parse(select.dataset.searchOptions || '[]');
                    const query = normalizeText(searchInput.value);
                    const selectedLabel = normalizeText(select.options[select.selectedIndex]?.textContent || '');
                    const filteredOptions = forceOpenAll
                        ? cachedOptions
                        : query
                        ? cachedOptions.filter((option) => normalizeText(option.label).includes(query))
                        : cachedOptions;
                    const hasExactMatch = cachedOptions.some((option) => normalizeText(option.label) === query);

                    results.innerHTML = filteredOptions.length
                        ? filteredOptions.map((option) => `<button class="select-search-option" type="button" data-search-select-value="${option.value}" data-search-select-label="${option.label}">${option.label}</button>`).join('')
                        : '<div class="select-search-empty">Aucun résultat</div>';

                    const shouldOpen = forceOpenAll || query === '' || (!hasExactMatch && query !== selectedLabel);
                    results.classList.toggle('is-open', shouldOpen);
                }

                function refreshSearchableSelect(selectId) {
                    const select = document.getElementById(selectId);
                    const searchInput = getSearchableSelectInput(selectId);

                    if (!select || !searchInput) {
                        return;
                    }

                    cacheSearchableSelectOptions(select);
                    searchInput.value = select.options[select.selectedIndex]?.textContent || '';
                    applySearchableSelectFilter(selectId);
                }

                function bindSearchableSelects() {
                    document.querySelectorAll('[data-search-select-target]').forEach((input) => {
                        if (input.dataset.searchBound === '1') {
                            return;
                        }

                        const targetId = input.dataset.searchSelectTarget;
                        input.addEventListener('input', () => applySearchableSelectFilter(targetId));
                        input.addEventListener('change', () => {
                            const select = document.getElementById(targetId);
                            const cachedOptions = JSON.parse(select?.dataset.searchOptions || '[]');
                            const exactMatch = cachedOptions.find((option) => normalizeText(option.label) === normalizeText(input.value));

                            if (!select || !exactMatch) {
                                return;
                            }

                            const previousValue = select.value;
                            select.value = exactMatch.value;
                            input.value = exactMatch.label;

                            if (String(previousValue) !== String(select.value)) {
                                select.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        });
                        input.addEventListener('blur', () => {
                            const select = document.getElementById(targetId);
                            const results = getSearchableSelectResults(targetId);

                            if (!select) {
                                return;
                            }

                            window.setTimeout(() => results?.classList.remove('is-open'), 150);
                            input.value = select.options[select.selectedIndex]?.textContent || '';
                        });
                        input.dataset.searchBound = '1';
                        refreshSearchableSelect(targetId);
                    });

                    document.querySelectorAll('[data-search-toggle-target]').forEach((button) => {
                        if (button.dataset.searchBound === '1') {
                            return;
                        }

                        button.addEventListener('mousedown', (event) => {
                            event.preventDefault();
                        });

                        button.addEventListener('click', () => {
                            const targetId = button.dataset.searchToggleTarget;
                            const input = getSearchableSelectInput(targetId);
                            const results = getSearchableSelectResults(targetId);

                            if (!input || !results) {
                                return;
                            }

                            const shouldOpen = !results.classList.contains('is-open');
                            applySearchableSelectFilter(targetId, true);
                            results.classList.toggle('is-open', shouldOpen);
                            input.focus();
                        });

                        button.dataset.searchBound = '1';
                    });

                    document.querySelectorAll('.select-search-results').forEach((results) => {
                        if (results.dataset.searchBound === '1') {
                            return;
                        }

                        results.addEventListener('click', (event) => {
                            const option = event.target.closest('[data-search-select-value]');

                            if (!option) {
                                return;
                            }

                            const selectId = results.id.replace(/Results$/, '');
                            const select = document.getElementById(selectId);
                            const input = getSearchableSelectInput(selectId);
                            const previousValue = select?.value;

                            if (!select || !input) {
                                return;
                            }

                            select.value = option.dataset.searchSelectValue;
                            input.value = option.dataset.searchSelectLabel || '';
                            results.classList.remove('is-open');

                            if (String(previousValue) !== String(select.value)) {
                                select.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        });

                        results.dataset.searchBound = '1';
                    });
                }

                function populateDialCodeSelects() {
                    document.querySelectorAll('[data-dial-code-select]').forEach((select) => {
                        select.innerHTML = dialCodeOptions.map((option) => `<option value="${option.value}">${option.label}</option>`).join('');
                        if (!select.value) {
                            select.value = dialCodeOptions[0]?.value || '225';
                        }
                    });
                }

                function syncPublicUserTypeFields(selectId, businessFieldsContainerId, sectorFieldsContainerId = null) {
                    const select = document.getElementById(selectId);
                    const businessFieldsContainer = document.getElementById(businessFieldsContainerId);
                    const sectorFieldsContainer = sectorFieldsContainerId ? document.getElementById(sectorFieldsContainerId) : null;

                    if (!select || !businessFieldsContainer) {
                        return;
                    }

                    const selectedType = publicUserTypes.find((type) => String(type.id) === String(select.value));
                    const typeCode = String(selectedType?.code || '').toUpperCase();
                    const showBusinessFields = typeCode === 'UPE';
                    const showSectorFields = typeCode === 'UPE' || typeCode === 'UPTI';

                    businessFieldsContainer.classList.toggle('hidden', !showBusinessFields);
                    businessFieldsContainer.querySelectorAll('input, select, textarea').forEach((field) => {
                        field.disabled = !showBusinessFields;
                        field.required = showBusinessFields && ['company_name', 'company_registration_number'].includes(field.name);
                    });

                    if (sectorFieldsContainer) {
                        sectorFieldsContainer.classList.toggle('hidden', !showSectorFields);
                        sectorFieldsContainer.querySelectorAll('input, select, textarea').forEach((field) => {
                            field.disabled = !showSectorFields;
                            field.required = false;
                        });
                    }
                }

                function composePhoneNumber(form) {
                    const localInput = form.querySelector('[name="phone_local"]');
                    const dialCodeSelect = form.querySelector('[name="phone_dial_code"]');
                    const hiddenPhoneInput = form.querySelector('[name="phone"]');

                    if (!localInput || !dialCodeSelect || !hiddenPhoneInput) {
                        return;
                    }

                    const local = String(localInput.value || '').replace(/\D+/g, '');
                    hiddenPhoneInput.value = local ? `${dialCodeSelect.value}${local}` : '';
                }

                function currentLocalDateTimeValue() {
                    const now = new Date();
                    const pad = (value) => String(value).padStart(2, '0');

                    return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;
                }

                function clearProfileGeoFields() {
                    document.getElementById('profileLatitude').value = '';
                    document.getElementById('profileLongitude').value = '';
                    document.getElementById('profileAccuracy').value = '';
                    document.getElementById('profileLocationSource').value = '';
                }

                function clearMeterGeoFields() {
                    document.getElementById('meterLatitude').value = '';
                    document.getElementById('meterLongitude').value = '';
                    document.getElementById('meterAccuracy').value = '';
                    document.getElementById('meterLocationSource').value = '';
                }

                function clearReportGeoFields() {
                    document.getElementById('reportLatitude').value = '';
                    document.getElementById('reportLongitude').value = '';
                    document.getElementById('reportAccuracy').value = '';
                    document.getElementById('reportLocationSource').value = '';
                }

                function applyReportMeterLocationIfAvailable(silent = true) {
                    if (hasGeoCoordinates('report')) {
                        return;
                    }

                    const meter = state.meters.find((item) => String(item.id) === String(document.getElementById('reportMeterId').value));

                    if (!meter || !meter.latitude || !meter.longitude) {
                        return;
                    }

                    setGeoManualMode('report', false);
                    fillGeoFields('report', {
                        latitude: meter.latitude,
                        longitude: meter.longitude,
                        accuracy: meter.location_accuracy || '',
                    }, 'meter_location');

                    if (!silent) {
                        showToast('Position de l’identifiant appliquée au signalement.');
                    }
                }

                function fillGeoFields(prefix, coords, source = 'device_gps') {
                    document.getElementById(`${prefix}Latitude`).value = Number(coords.latitude).toFixed(7);
                    document.getElementById(`${prefix}Longitude`).value = Number(coords.longitude).toFixed(7);
                    document.getElementById(`${prefix}Accuracy`).value = coords.accuracy ? Math.round(coords.accuracy) : '';
                    document.getElementById(`${prefix}LocationSource`).value = source;

                }

                function hasGeoCoordinates(prefix) {
                    return Boolean(document.getElementById(`${prefix}Latitude`)?.value && document.getElementById(`${prefix}Longitude`)?.value);
                }

                function setGeoManualMode(prefix, enabled) {
                    ['Latitude', 'Longitude', 'Accuracy'].forEach((suffix) => {
                        const input = document.getElementById(`${prefix}${suffix}`);
                        input.readOnly = !enabled;
                    });

                    if (enabled) {
                        document.getElementById(`${prefix}LocationSource`).value = 'manual';
                    }
                }

                function translateGeoError(error) {
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            return 'Accès à la position refusé. Active la localisation dans les paramètres puis réessaie.';
                        case error.POSITION_UNAVAILABLE:
                            return 'Position indisponible. Active les services de localisation du téléphone ou du navigateur, ou utilise la saisie manuelle.';
                        case error.TIMEOUT:
                            return 'Le délai de récupération de la position a expiré. Réessaie ou utilise la saisie manuelle.';
                        default:
                            return error.message || 'Impossible de récupérer la position.';
                    }
                }

                function openLocationSettings() {
                    const userAgent = navigator.userAgent || '';
                    const isAppleDevice = /iPhone|iPad|iPod/i.test(userAgent);
                    const isAndroidDevice = /Android/i.test(userAgent);

                    if (isAppleDevice) {
                        window.location.href = 'app-settings:';
                        return true;
                    }

                    if (isAndroidDevice) {
                        window.location.href = 'intent:#Intent;action=android.settings.LOCATION_SOURCE_SETTINGS;end';
                        return true;
                    }

                    return false;
                }

                function captureCurrentPosition(prefix, options = {}) {
                    const { silent = false, force = false } = options;

                    if (!force && hasGeoCoordinates(prefix)) {
                        return;
                    }

                    if (!navigator.geolocation) {
                        if (!silent) {
                            showToast('La géolocalisation n’est pas disponible sur cet appareil. Utilise la saisie manuelle.', true);
                        }
                        return;
                    }

                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            setGeoManualMode(prefix, false);
                            fillGeoFields(prefix, position.coords);
                            if (!silent) {
                                showToast('Position géographique récupérée.');
                            }
                        },
                        (error) => {
                            if (!silent) {
                                showToast(translateGeoError(error), true);

                                if (error.code === error.PERMISSION_DENIED && force) {
                                    setTimeout(() => {
                                        if (!openLocationSettings()) {
                                            showToast('Ouvre les paramètres de ton navigateur et active la localisation pour ce site.', true);
                                        }
                                    }, 350);
                                }
                            }
                        },
                        { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                    );
                }

                function maybeCaptureCurrentPosition(prefix, options = {}) {
                    if (state.autoGeoAttempts[prefix]) {
                        return;
                    }

                    state.autoGeoAttempts[prefix] = true;
                    captureCurrentPosition(prefix, { silent: true, ...options });
                }

                function setLoading(form, isLoading) {
                    const button = form.querySelector('button[type="submit"]');
                    if (!button) return;
                    button.disabled = isLoading;
                    button.dataset.originalText = button.dataset.originalText || button.textContent;
                    button.textContent = isLoading ? 'Traitement...' : button.dataset.originalText;
                }

                async function apiFetch(path, options = {}) {
                    const hasFormDataBody = options.body instanceof FormData;
                    const headers = {
                        Accept: 'application/json',
                        ...(options.body && !hasFormDataBody ? { 'Content-Type': 'application/json' } : {}),
                        Authorization: `Bearer ${state.token}`,
                    };
                    const response = await fetch(`${apiBase}${path}`, { ...options, headers });
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        const error = new Error(data.message || 'Une erreur est survenue.');
                        error.status = response.status;
                        error.payload = data;

                        if (response.status === 401) {
                            logout(false);
                        }

                        throw error;
                    }
                    return data;
                }

                function hasFirebaseWebConfig() {
                    return firebasePushEnabled
                        && firebaseWebVapidKey
                        && firebaseWebConfig.apiKey
                        && firebaseWebConfig.messagingSenderId
                        && firebaseWebConfig.appId;
                }

                function isPushSupportedInThisContext() {
                    return 'Notification' in window
                        && 'serviceWorker' in navigator
                        && window.isSecureContext;
                }

                function getWebPushDeviceName() {
                    const browserData = navigator.userAgentData?.brands
                        ?.map((brand) => `${brand.brand} ${brand.version}`)
                        .join(', ');
                    const browserName = browserData || navigator.userAgent || 'Navigateur';

                    return `${navigator.platform || 'Web'} - ${browserName}`.slice(0, 120);
                }

                async function registerPublicWebPushToken() {
                    if (!state.token) {
                        return;
                    }

                    if (!hasFirebaseWebConfig()) {
                        return;
                    }

                    if (!isPushSupportedInThisContext()) {
                        return;
                    }

                    try {
                        const permission = await Notification.requestPermission();

                        if (permission !== 'granted') {
                            return;
                        }

                        const [{ initializeApp, getApp, getApps }, { getMessaging, getToken, isSupported, onMessage }] = await Promise.all([
                            import('https://www.gstatic.com/firebasejs/10.12.5/firebase-app.js'),
                            import('https://www.gstatic.com/firebasejs/10.12.5/firebase-messaging.js'),
                        ]);

                        if (!(await isSupported())) {
                            return;
                        }

                        const firebaseApp = getApps().length ? getApp() : initializeApp(firebaseWebConfig);
                        const messaging = getMessaging(firebaseApp);
                        if (!state.pushMessageHandlerRegistered) {
                            state.pushMessageHandlerRegistered = true;
                            onMessage(messaging, (messagePayload) => {
                                const notification = messagePayload.notification || {};
                                const data = messagePayload.data || {};
                                const title = notification.title || data.title || 'MYSIGNAL';
                                const body = notification.body || data.body || '';

                                if (Notification.permission === 'granted') {
                                    new Notification(title, {
                                        body,
                                        icon: '/favicon.ico',
                                        badge: '/favicon.ico',
                                        data,
                                    });
                                }

                                showToast(title);
                                void refreshNotifications();
                            });
                        }

                        const serviceWorkerRegistration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');
                        const token = await getToken(messaging, {
                            vapidKey: firebaseWebVapidKey,
                            serviceWorkerRegistration,
                        });

                        if (!token) {
                            return;
                        }

                        const payload = {
                            token,
                            platform: 'web',
                            device_name: getWebPushDeviceName(),
                            app_version: 'web-dashboard',
                        };

                        await apiFetch('/push-tokens', {
                            method: 'POST',
                            body: JSON.stringify(payload),
                        });
                    } catch (error) {
                        return;
                    }
                }

                function logout(showMessage = true) {
                    localStorage.removeItem(tokenKey);
                    if (showMessage) {
                        showToast('Vous etes deconnecte.');
                        setTimeout(() => { window.location.href = landingUrl; }, 500);
                        return;
                    }
                    window.location.href = landingUrl;
                }

                function activatePanel(panelName) {
                    const targetPanel = document.querySelector(`.public-panel[data-panel="${panelName}"]`) ? panelName : 'overview';

                    document.querySelectorAll('[data-panel-target]').forEach((button) => {
                        button.classList.toggle('active', button.dataset.panelTarget === targetPanel);
                    });
                    document.querySelectorAll('.public-panel').forEach((panel) => {
                        panel.classList.toggle('active', panel.dataset.panel === targetPanel);
                    });

                    sessionStorage.setItem(dashboardPanelStorageKey, targetPanel);

                    if (targetPanel === 'profile') {
                        maybeCaptureCurrentPosition('profile');
                    }

                    if (targetPanel === 'meters' && !document.getElementById('meterForm')?.dataset.editId) {
                        maybeCaptureCurrentPosition('meter');
                    }

                    closeSidebar();
                }

                function restoreActivePanel() {
                    const requestedPanel = window.location.hash.replace('#', '');
                    sessionStorage.removeItem(dashboardPanelStorageKey);
                    activatePanel(requestedPanel || 'overview');
                }

                function openSidebar() {
                    document.getElementById('publicSidebar').classList.add('is-open');
                    document.getElementById('publicSidebarBackdrop').classList.add('is-visible');
                }

                function closeSidebar() {
                    document.getElementById('publicSidebar').classList.remove('is-open');
                    document.getElementById('publicSidebarBackdrop').classList.remove('is-visible');
                }

                function populateCommuneSelects(selectedName = null) {
                    const options = state.communes.length
                        ? state.communes.map((commune) => `<option value="${commune.name}">${commune.name}</option>`).join('')
                        : '<option value="">Aucune commune disponible</option>';
                    const profileSelect = document.getElementById('profileCommuneSelect');
                    profileSelect.innerHTML = options;
                    if (selectedName && state.communes.some((commune) => commune.name === selectedName)) {
                        profileSelect.value = selectedName;
                    }
                    populateMeterCityOptions();
                    populateMeterCommuneOptions(selectedName);
                    populateMeterNeighborhoodOptions();
                }

                function populateMeterCityOptions(selectedCityName = null) {
                    const select = document.getElementById('meterCitySelect');
                    const options = state.cities.length
                        ? state.cities.map((city) => `<option value="${city.name}">${city.name}</option>`).join('')
                        : '<option value="">Aucune ville disponible</option>';

                    select.innerHTML = options;

                    if (selectedCityName && state.cities.some((city) => city.name === selectedCityName)) {
                        select.value = selectedCityName;
                    }
                }

                function getMeterCommunes() {
                    const selectedCity = document.getElementById('meterCitySelect')?.value || '';

                    if (!selectedCity) {
                        return state.communes;
                    }

                    return state.communes.filter((commune) => commune.city_name === selectedCity);
                }

                function populateMeterCommuneOptions(selectedName = null) {
                    const select = document.getElementById('meterCommuneSelect');
                    const communes = getMeterCommunes();

                    select.innerHTML = communes.length
                        ? communes.map((commune) => `<option value="${commune.name}">${commune.name}</option>`).join('')
                        : '<option value="">Aucune commune disponible</option>';

                    if (selectedName && communes.some((commune) => commune.name === selectedName)) {
                        select.value = selectedName;
                    }
                }

                function getCommuneByName(name) {
                    return getMeterCommunes().find((commune) => commune.name === name)
                        || state.communes.find((commune) => commune.name === name)
                        || null;
                }

                function populateMeterNeighborhoodOptions(selectedNeighborhood = '', selectedSubNeighborhood = '') {
                    const commune = getCommuneByName(document.getElementById('meterCommuneSelect').value);
                    const neighborhoods = commune?.neighborhoods || [];
                    const neighborhoodSelect = document.getElementById('meterNeighborhoodSelect');
                    const subNeighborhoodSelect = document.getElementById('meterSubNeighborhoodSelect');

                    neighborhoodSelect.innerHTML = '<option value="">Aucun quartier</option>' + neighborhoods.map((neighborhood) => `<option value="${neighborhood.name}">${neighborhood.name}</option>`).join('');
                    neighborhoodSelect.value = neighborhoods.some((neighborhood) => neighborhood.name === selectedNeighborhood) ? selectedNeighborhood : '';

                    const selectedNeighborhoodData = neighborhoods.find((neighborhood) => neighborhood.name === neighborhoodSelect.value);
                    const subNeighborhoods = selectedNeighborhoodData?.sub_neighborhoods || [];
                    subNeighborhoodSelect.innerHTML = '<option value="">Aucun sous-quartier</option>' + subNeighborhoods.map((subNeighborhood) => `<option value="${subNeighborhood.name}">${subNeighborhood.name}</option>`).join('');
                    subNeighborhoodSelect.value = subNeighborhoods.some((subNeighborhood) => subNeighborhood.name === selectedSubNeighborhood) ? selectedSubNeighborhood : '';
                }

                function buildOptions(items, placeholder = 'Sélectionner') {
                    if (!items.length) return `<option value="">${placeholder}</option>`;
                    return items.map((item) => `<option value="${item.id ?? item.name}">${item.name}</option>`).join('');
                }

                function findAddressComponent(place, types) {
                    return place?.address_components?.find((component) => types.some((type) => component.types.includes(type))) || null;
                }

                function syncCommuneSelectFromPlace(selectId, place) {
                    const communeSelect = document.getElementById(selectId);
                    const communeCandidate = [
                        findAddressComponent(place, ['sublocality_level_1'])?.long_name,
                        findAddressComponent(place, ['sublocality', 'administrative_area_level_3'])?.long_name,
                        findAddressComponent(place, ['locality'])?.long_name,
                    ].find(Boolean);

                    if (!communeCandidate) {
                        return;
                    }

                    const matchedCommune = state.communes.find((commune) => normalizeText(commune.name) === normalizeText(communeCandidate));

                    if (matchedCommune) {
                        if (selectId === 'meterCommuneSelect') {
                            populateMeterCityOptions(matchedCommune.city_name || null);
                            populateMeterCommuneOptions(matchedCommune.name);
                        }
                        communeSelect.value = matchedCommune.name;
                    }
                }

                function syncMeterNeighborhoodsFromPlace(place) {
                    const neighborhoodName = [
                        findAddressComponent(place, ['neighborhood'])?.long_name,
                        findAddressComponent(place, ['sublocality_level_2'])?.long_name,
                    ].find(Boolean);
                    const subNeighborhoodName = findAddressComponent(place, ['sublocality_level_3'])?.long_name || '';

                    populateMeterNeighborhoodOptions(neighborhoodName || '', subNeighborhoodName || '');
                }

                function syncReportLocationFromPlace(place) {
                    return place;
                }

                function attachAddressAutocomplete(inputId, prefix) {
                    const input = document.getElementById(inputId);

                    if (!input || !window.google?.maps?.places || input.dataset.googleAutocompleteReady === '1') {
                        return;
                    }

                    const autocomplete = new google.maps.places.Autocomplete(input, {
                        fields: ['address_components', 'formatted_address', 'geometry', 'name', 'place_id'],
                        types: ['geocode'],
                    });

                    autocomplete.addListener('place_changed', () => {
                        const place = autocomplete.getPlace();

                        if (!place?.geometry?.location) {
                            showToast('Cette adresse ne fournit pas encore de localisation exploitable.', true);
                            return;
                        }

                        input.value = place.formatted_address || place.name || input.value;
                        setGeoManualMode(prefix, false);
                        fillGeoFields(prefix, {
                            latitude: place.geometry.location.lat(),
                            longitude: place.geometry.location.lng(),
                            accuracy: '',
                        }, 'google_places');

                        if (prefix === 'profile') {
                            syncCommuneSelectFromPlace('profileCommuneSelect', place);
                        }

                        if (prefix === 'meter') {
                            syncCommuneSelectFromPlace('meterCommuneSelect', place);
                            syncMeterNeighborhoodsFromPlace(place);
                        }

                        if (prefix === 'report') {
                            syncReportLocationFromPlace(place);
                        }
                    });

                    input.dataset.googleAutocompleteReady = '1';
                }

                function initGooglePlacesAutocomplete() {
                    if (!googleMapsApiKey || !window.google?.maps?.places) {
                        return;
                    }

                    attachAddressAutocomplete('profileAddressSearch', 'profile');
                    attachAddressAutocomplete('meterAddressSearch', 'meter');
                }

                function populateReportLocationSelects() {
                    return true;
                }

                function getAvailableReportOrganizations() {
                    const applicationId = String(document.getElementById('reportApplicationId')?.value || '');
                    const organizationTypeId = String(document.getElementById('reportOrganizationTypeId')?.value || '');
                    const application = serviceApplications.find((item) => String(item.id) === applicationId);
                    const organizations = application?.organizations || [];

                    if (!reportRequiresOrganizationType() || !organizationTypeId) {
                        return organizations;
                    }

                    return organizations.filter((organization) => String(organization.organization_type_id || '') === organizationTypeId);
                }

                function getSelectedReportApplication() {
                    const applicationId = String(document.getElementById('reportApplicationId')?.value || '');

                    return serviceApplications.find((item) => String(item.id) === applicationId) || null;
                }

                function reportRequiresIdentifier() {
                    const application = getSelectedReportApplication();

                    return application ? application.requires_public_user_identifier !== false : true;
                }

                function reportRequiresOrganizationType() {
                    const application = getSelectedReportApplication();

                    return application?.requires_organization_type_on_report === true;
                }

                function getSelectedReportOrganizationId() {
                    return document.getElementById('reportOrganizationType').value || '';
                }

                function getSelectedReportOrganizationTypeId() {
                    return document.getElementById('reportOrganizationTypeId')?.value || '';
                }

                function getSelectedReportNetwork() {
                    const organizationId = String(getSelectedReportOrganizationId());

                    for (const application of serviceApplications) {
                        const organization = (application.organizations || []).find((item) => String(item.id) === organizationId);

                        if (organization) {
                            return organization.network_type || application.network_type || '';
                        }
                    }

                    return '';
                }

                function getFilteredMetersForSelectedNetwork() {
                    if (!reportRequiresIdentifier()) {
                        return [];
                    }

                    const organizationId = getSelectedReportOrganizationId();

                    if (!organizationId) {
                        return state.meters;
                    }

                    return state.meters.filter((meter) => String(meter.organization_id) === String(organizationId));
                }

                function renderReportNetworkOptions(preferredNetwork = null) {
                    const applicationSelect = document.getElementById('reportApplicationId');
                    const organizationSelect = document.getElementById('reportOrganizationType');

                    applicationSelect.innerHTML = serviceApplications.length
                        ? serviceApplications.map((application) => `<option value="${application.id}">${application.name}</option>`).join('')
                        : '<option value="">Aucune catégorie disponible</option>';

                    if (!serviceApplications.length) {
                        applicationSelect.value = '';
                        organizationSelect.innerHTML = '<option value="">Aucune institution disponible</option>';
                        organizationSelect.value = '';
                        return;
                    }

                    const preferredApplication = serviceApplications.find((application) => application.code === preferredNetwork || application.network_type === preferredNetwork);
                    applicationSelect.value = String(preferredApplication?.id || serviceApplications[0]?.id || '');
                    refreshSearchableSelect('reportApplicationId');

                    renderReportOrganizationTypeOptions();
                    renderReportOrganizationOptions();
                }

                function renderReportOrganizationTypeOptions(preferredOrganizationTypeId = null) {
                    const typeWrap = document.getElementById('reportOrganizationTypeFieldWrap');
                    const typeSelect = document.getElementById('reportOrganizationTypeId');
                    const application = getSelectedReportApplication();
                    const requiresType = reportRequiresOrganizationType();
                    const organizationTypes = application?.organization_types || [];

                    typeWrap.classList.toggle('d-none', !requiresType);
                    typeSelect.required = requiresType;
                    typeSelect.disabled = !requiresType;

                    if (!requiresType) {
                        typeSelect.innerHTML = '<option value="">Sous catégorie non requise</option>';
                        typeSelect.value = '';
                        refreshSearchableSelect('reportOrganizationTypeId');
                        return;
                    }

                    typeSelect.innerHTML = organizationTypes.length
                        ? organizationTypes.map((type) => `<option value="${type.id}">${type.name}</option>`).join('')
                        : '<option value="">Aucune sous catégorie disponible</option>';

                    if (!organizationTypes.length) {
                        typeSelect.value = '';
                        refreshSearchableSelect('reportOrganizationTypeId');
                        return;
                    }

                    const preferredExists = preferredOrganizationTypeId && organizationTypes.some((type) => String(type.id) === String(preferredOrganizationTypeId));
                    typeSelect.value = preferredExists ? String(preferredOrganizationTypeId) : String(organizationTypes[0]?.id || '');
                    refreshSearchableSelect('reportOrganizationTypeId');
                }

                function renderReportOrganizationOptions(preferredOrganizationId = null) {
                    const organizationSelect = document.getElementById('reportOrganizationType');
                    const organizations = getAvailableReportOrganizations();

                    organizationSelect.innerHTML = organizations.length
                        ? organizations.map((organization) => `<option value="${organization.id}">${organization.name}</option>`).join('')
                        : '<option value="">Aucune institution disponible</option>';

                    if (!organizations.length) {
                        organizationSelect.value = '';
                        return;
                    }

                    const preferredExists = preferredOrganizationId && organizations.some((organization) => String(organization.id) === String(preferredOrganizationId));
                    organizationSelect.value = preferredExists ? String(preferredOrganizationId) : String(organizations[0]?.id || '');
                    refreshSearchableSelect('reportOrganizationType');
                }

                function populateMeterApplicationOptions(preferredApplicationCode = null) {
                    const applicationSelect = document.getElementById('meterApplicationId');

                    applicationSelect.innerHTML = serviceApplications.length
                        ? serviceApplications.map((application) => `<option value="${application.id}">${application.name}</option>`).join('')
                        : '<option value="">Aucune catégorie disponible</option>';

                    if (!serviceApplications.length) {
                        applicationSelect.value = '';
                        populateMeterOrganizationOptions();
                        return;
                    }

                    const preferredApplication = preferredApplicationCode
                        ? serviceApplications.find((application) => application.code === preferredApplicationCode)
                        : null;

                    applicationSelect.value = String(preferredApplication?.id || serviceApplications[0]?.id || '');
                    populateMeterOrganizationOptions();
                }

                function populateMeterOrganizationOptions(preferredOrganizationId = null) {
                    const applicationId = String(document.getElementById('meterApplicationId')?.value || '');
                    const organizationSelect = document.getElementById('meterOrganizationId');
                    const networkTypeInput = document.getElementById('meterNetworkType');
                    const application = serviceApplications.find((item) => String(item.id) === applicationId);
                    const organizations = application?.organizations || [];

                    organizationSelect.innerHTML = organizations.length
                        ? organizations.map((organization) => `<option value="${organization.id}">${organization.name}</option>`).join('')
                        : '<option value="">Aucune institution disponible</option>';

                    if (!organizations.length) {
                        organizationSelect.value = '';
                        networkTypeInput.value = application?.network_type || '';
                        return;
                    }

                    const preferredExists = preferredOrganizationId && organizations.some((organization) => String(organization.id) === String(preferredOrganizationId));
                    organizationSelect.value = preferredExists ? String(preferredOrganizationId) : String(organizations[0]?.id || '');
                    networkTypeInput.value = application?.network_type || organizations[0]?.network_type || '';
                }

                function renderReportMeterOptions(preferredMeterId = null) {
                    const meterSelect = document.getElementById('reportMeterId');
                    const meterWrap = document.getElementById('reportMeterFieldWrap');
                    const filteredMeters = getFilteredMetersForSelectedNetwork();
                    const noMeterHint = document.getElementById('reportNoMeterHint');
                    const locationHelp = document.getElementById('reportLocationHelp');
                    const requiresIdentifier = reportRequiresIdentifier();
                    const identifierLabel = getSelectedReportApplication()?.identifier_label || 'Identifiant';
                    const meterFieldLabel = document.getElementById('reportMeterFieldLabel');
                    const meterSearchInput = document.getElementById('reportMeterSearchInput');

                    if (meterFieldLabel) {
                        meterFieldLabel.textContent = identifierLabel;
                    }

                    if (meterSearchInput) {
                        meterSearchInput.placeholder = `Rechercher: ${identifierLabel.toLocaleLowerCase()}`;
                    }

                    meterWrap.classList.toggle('d-none', !requiresIdentifier);
                    meterSelect.required = requiresIdentifier;
                    meterSelect.disabled = !requiresIdentifier;
                    locationHelp.textContent = requiresIdentifier
                        ? `Le pays, la ville, la commune et l’adresse sont récupérés automatiquement à partir de la valeur sélectionnée dans ${identifierLabel.toLocaleLowerCase()}.`
                        : 'Le pays, la ville, la commune et l’adresse sont récupérés automatiquement à partir de votre profil.';

                    if (!requiresIdentifier) {
                        meterSelect.innerHTML = `<option value="">Aucune valeur requise pour ${escapeHtml(identifierLabel.toLocaleLowerCase())}</option>`;
                        meterSelect.value = '';
                        noMeterHint.classList.add('d-none');
                        if (document.getElementById('reportLocationSource').value === 'meter_location') {
                            clearReportGeoFields();
                        }
                        if (!hasGeoCoordinates('report') && state.currentUser?.latitude && state.currentUser?.longitude) {
                            fillGeoFields('report', {
                                latitude: state.currentUser.latitude,
                                longitude: state.currentUser.longitude,
                                accuracy: state.currentUser.location_accuracy || '',
                            }, 'profile_location');
                        }
                        refreshSearchableSelect('reportMeterId');
                        return;
                    }

                    meterSelect.innerHTML = filteredMeters.length
                        ? filteredMeters.map((meter) => `<option value="${meter.id}">${meter.organization_name || meter.network_type} · ${meter.meter_number}${meter.label ? ' · ' + meter.label : ''} · ${meter.assignment_label || 'Compteur personnel'}</option>`).join('')
                        : `<option value="">Aucune valeur disponible pour ${escapeHtml(identifierLabel.toLocaleLowerCase())}</option>`;

                    meterSelect.disabled = filteredMeters.length === 0;
                    noMeterHint.classList.toggle('d-none', filteredMeters.length > 0);

                    if (!filteredMeters.length) {
                        return;
                    }

                    const meterToSelect = preferredMeterId && filteredMeters.some((meter) => String(meter.id) === String(preferredMeterId))
                        ? String(preferredMeterId)
                        : String((filteredMeters.find((meter) => meter.is_primary) || filteredMeters[0]).id);

                    meterSelect.value = meterToSelect;
                    refreshSearchableSelect('reportMeterId');
                }

                function getSignalTypesForCurrentMeter() {
                    const meter = state.meters.find((item) => String(item.id) === String(document.getElementById('reportMeterId').value));
                    const application = getSelectedReportApplication();
                    const organizationId = getSelectedReportOrganizationId();

                    if (!meter && !application) return [];

                    const matchingTypes = state.signalTypes.filter((type) => {
                        const applicationId = meter?.application_id || application?.id;

                        if (String(type.application_id) !== String(applicationId)) {
                            return false;
                        }

                        const selectedOrganizationId = meter?.organization_id || organizationId;
                        const scopedOrganizationIds = Array.isArray(type.organization_ids)
                            ? type.organization_ids.map((id) => String(id))
                            : [];

                        if (
                            scopedOrganizationIds.length === 0 &&
                            (type.organization_id === null || type.organization_id === undefined || type.organization_id === '')
                        ) {
                            return true;
                        }

                        return scopedOrganizationIds.includes(String(selectedOrganizationId)) ||
                            String(type.organization_id) === String(selectedOrganizationId);
                    });

                    const deduplicatedTypes = new Map();

                    matchingTypes.forEach((type) => {
                        const existing = deduplicatedTypes.get(type.code);

                        if (!existing) {
                            deduplicatedTypes.set(type.code, type);
                            return;
                        }

                        const currentSpecificity = (type.organization_id || (type.organization_ids || []).length) ? 1 : 0;
                        const existingSpecificity = (existing.organization_id || (existing.organization_ids || []).length) ? 1 : 0;

                        if (currentSpecificity >= existingSpecificity) {
                            deduplicatedTypes.set(type.code, type);
                        }
                    });

                    return Array.from(deduplicatedTypes.values());
                }

                function renderSignalOptions() {
                    const signalSelect = document.getElementById('reportSignalCode');
                    const signalTypes = getSignalTypesForCurrentMeter();
                    signalSelect.innerHTML = signalTypes.length
                        ? signalTypes.map((type) => `<option value="${type.code}">${type.label}</option>`).join('')
                        : '<option value="">Aucun type disponible</option>';
                    signalSelect.disabled = signalTypes.length === 0;
                    refreshSearchableSelect('reportSignalCode');
                    renderSignalPayloadFields();
                }

                function renderSignalPayloadFields() {
                    const signalSelect = document.getElementById('reportSignalCode');
                    const subTypeWrap = document.getElementById('reportSignalSubTypeWrap');
                    const subTypeSelect = document.getElementById('reportSignalSubTypeCode');
                    const availableSignals = getSignalTypesForCurrentMeter();
                    const signal = availableSignals.find((item) => item.code === signalSelect.value) || availableSignals[0];
                    const selectedMeter = state.meters.find((item) => String(item.id) === String(document.getElementById('reportMeterId')?.value || ''));
                    const selectedOrganization = getAvailableReportOrganizations().find((item) => String(item.id) === String(getSelectedReportOrganizationId()));
                    const organizationTypeId = selectedMeter?.organization_type_id ? String(selectedMeter.organization_type_id) : (selectedOrganization?.organization_type_id ? String(selectedOrganization.organization_type_id) : null);
                    const inlineDescription = document.getElementById('reportSignalInlineDescription');
                    const title = document.getElementById('reportSignalMetaTitle');
                    const description = document.getElementById('reportSignalMetaDescription');
                    const container = document.getElementById('signalPayloadFields');

                    const subTypes = signal?.sub_types || [];
                    subTypeWrap.classList.toggle('d-none', subTypes.length === 0);
                    subTypeSelect.required = subTypes.length > 0;
                    subTypeSelect.disabled = subTypes.length === 0;
                    subTypeSelect.innerHTML = subTypes.length
                        ? subTypes.map((subType) => `<option value="${escapeHtml(subType.code)}">${escapeHtml(subType.label)}</option>`).join('')
                        : '<option value="">Aucun sous-type</option>';
                    refreshSearchableSelect('reportSignalSubTypeCode');

                    if (!signal) {
                        inlineDescription.textContent = 'Sélectionnez un type de signal pour afficher sa description et son délai de résolution.';
                        title.textContent = 'Signalement sélectionné';
                        description.textContent = 'Sélectionnez un type de signal pour afficher les détails.';
                        container.innerHTML = '';
                        return;
                    }

                    const signalDescriptionParts = [];
                    const signalDescription = typeof signal.description === 'string' ? signal.description.trim() : '';
                    const fallbackSla = organizationTypeId ? signal.sla_targets?.[organizationTypeId] : null;
                    const resolvedSlaTarget = signal.sla_target || fallbackSla || null;
                    const slaLabel = resolvedSlaTarget?.label ? String(resolvedSlaTarget.label).trim() : '';

                    signalSelect.value = signal.code;
                    title.textContent = signal.label || signal.code;

                    if (signalDescription) {
                        signalDescriptionParts.push(signalDescription);
                    }

                    signalDescriptionParts.push(slaLabel ? `Délai prévu ${slaLabel}` : 'Délai prévu non défini');
                    inlineDescription.textContent = signalDescriptionParts.join(' · ');
                    description.textContent = signalDescriptionParts.join(' · ');
                    container.innerHTML = '';
                }

                function setTextIfExists(id, value) {
                    const element = document.getElementById(id);

                    if (element) {
                        element.textContent = value;
                    }
                }

                function renderUser(user) {
                    state.currentUser = user;
                    document.getElementById('dashboardGreeting').textContent = `Bienvenue ${user.first_name} ${user.last_name}`;
                    document.getElementById('userStatus').textContent = user.status || '-';
                    document.getElementById('profileStatusPill').textContent = user.status || '-';
                    document.getElementById('sidebarUserLocation').textContent = [user.commune, user.address].filter(Boolean).join(' · ') || 'Localisation non renseignée';
                    const hasSidebarGps = !!(user.latitude && user.longitude);
                    document.getElementById('sidebarUserGps').textContent = hasSidebarGps
                        ? `Position renseignée`
                        : 'Position non renseignée';
                    document.getElementById('sidebarRequestGpsButton')?.classList.toggle('d-none', hasSidebarGps);
                    setTextIfExists('overviewUserName', `${user.first_name} ${user.last_name}`);
                    setTextIfExists('overviewProfileLine', [user.phone, user.commune].filter(Boolean).join(' · ') || 'Informations de profil à compléter');
                    document.getElementById('profileFullNameCard').textContent = `${user.first_name} ${user.last_name}`;
                    document.getElementById('profilePhoneCard').textContent = user.phone || '-';
                    document.getElementById('profileCommuneCard').textContent = user.commune || '-';
                    document.getElementById('profileAddressCard').textContent = user.address || 'Adresse non renseignée';
                    document.getElementById('profileGpsCard').textContent = user.latitude && user.longitude ? 'Position renseignée' : 'Position non renseignée';
                    document.getElementById('profileUserTypeCard').textContent = user.public_user_type?.name || '-';
                    document.getElementById('profileWhatsappCard').textContent = user.is_whatsapp_number ? 'Oui' : 'Non';
                    document.getElementById('profileStatusCard').textContent = user.status || '-';
                    const form = document.getElementById('profileForm');
                    document.getElementById('profilePublicUserTypeSelect').value = user.public_user_type?.id || '{{ $publicUserTypes->first()?->id }}';
                    form.first_name.value = user.first_name || '';
                    form.last_name.value = user.last_name || '';
                    form.is_whatsapp_number.value = user.is_whatsapp_number ? '1' : '0';
                    form.email.value = user.email || '';
                    form.company_name.value = user.company_name || '';
                    form.company_registration_number.value = user.company_registration_number || '';
                    form.tax_identifier.value = user.tax_identifier || '';
                    form.business_sector.value = user.business_sector || '';
                    form.company_address.value = user.company_address || '';
                    form.address.value = user.address || '';
                    populateCommuneSelects(user.commune || null);
                    form.commune.value = user.commune || '';
                    document.getElementById('profileLatitude').value = user.latitude || '';
                    document.getElementById('profileLongitude').value = user.longitude || '';
                    document.getElementById('profileAccuracy').value = user.location_accuracy || '';
                    document.getElementById('profileLocationSource').value = user.location_source || '';
                    syncPublicUserTypeFields('profilePublicUserTypeSelect', 'profileBusinessFields', 'profileSectorFields');
                    renderMemberWalletCard();
                }

                function escapeHtml(value) {
                    return String(value ?? '')
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#39;');
                }

                function buildMemberCardNumber(user, subscription) {
                    const userPart = String(user?.id || 0).padStart(6, '0');
                    const subscriptionPart = String(subscription?.id || 0).padStart(5, '0');

                    return `MS ${userPart.slice(0, 3)} ${userPart.slice(3)} ${subscriptionPart}`;
                }

                function buildMemberQrPayload(user, subscription, discountCard = state.discountCard) {
                    if (discountCard?.qr_payload) {
                        return String(discountCard.qr_payload);
                    }

                    if (discountCard?.card_uuid) {
                        return String(discountCard.card_uuid);
                    }

                    return JSON.stringify({
                        type: 'MYSIGNAL_CONSUMER_MEMBER_CARD',
                        member_id: user?.id || null,
                        card_number: buildMemberCardNumber(user, subscription),
                        subscription_id: subscription?.id || null,
                        status: subscription?.status || null,
                        valid_until: subscription?.end_date || null,
                    });
                }

                function drawFallbackQr(container, payload) {
                    const size = 70;
                    const cells = 21;
                    const cellSize = Math.floor(size / cells);
                    const canvas = document.createElement('canvas');
                    canvas.width = size;
                    canvas.height = size;
                    const context = canvas.getContext('2d');
                    context.fillStyle = '#ffffff';
                    context.fillRect(0, 0, size, size);
                    context.fillStyle = '#183447';

                    const drawFinder = (x, y) => {
                        context.fillRect(x, y, cellSize * 7, cellSize * 7);
                        context.fillStyle = '#ffffff';
                        context.fillRect(x + cellSize, y + cellSize, cellSize * 5, cellSize * 5);
                        context.fillStyle = '#183447';
                        context.fillRect(x + cellSize * 2, y + cellSize * 2, cellSize * 3, cellSize * 3);
                    };

                    drawFinder(0, 0);
                    drawFinder((cells - 7) * cellSize, 0);
                    drawFinder(0, (cells - 7) * cellSize);

                    let hash = 0;
                    for (let index = 0; index < payload.length; index += 1) {
                        hash = ((hash << 5) - hash + payload.charCodeAt(index)) | 0;
                    }

                    for (let y = 0; y < cells; y += 1) {
                        for (let x = 0; x < cells; x += 1) {
                            const inFinder = (x < 8 && y < 8) || (x > 12 && y < 8) || (x < 8 && y > 12);
                            if (inFinder) {
                                continue;
                            }

                            const value = Math.abs(hash + (x * 31) + (y * 17) + (x * y * 7));
                            if (value % 5 < 2) {
                                context.fillRect(x * cellSize, y * cellSize, cellSize, cellSize);
                            }
                        }
                    }

                    container.appendChild(canvas);
                }

                function renderMemberQr(containerId, payload) {
                    const container = document.getElementById(containerId);

                    if (!container) {
                        return;
                    }

                    container.innerHTML = '';

                    if (window.QRCode) {
                        new QRCode(container, {
                            text: payload,
                            width: 70,
                            height: 70,
                            colorDark: '#183447',
                            colorLight: '#ffffff',
                            correctLevel: QRCode.CorrectLevel.M,
                        });
                        return;
                    }

                    drawFallbackQr(container, payload);
                }

                function formatMemberCardExpiry(value) {
                    if (!value) {
                        return 'Actif';
                    }

                    const date = new Date(value);

                    if (Number.isNaN(date.getTime())) {
                        return 'Actif';
                    }

                    return `${String(date.getMonth() + 1).padStart(2, '0')}/${String(date.getFullYear()).slice(-2)}`;
                }

                function renderMemberWalletCard() {
                    const wrap = document.getElementById('memberWalletCardWrap');

                    if (!wrap || !state.currentUser) {
                        return;
                    }

                    const subscription = state.subscription;

                    const cardNumber = buildMemberCardNumber(state.currentUser, subscription);
                    const fullName = `${state.currentUser.first_name || ''} ${state.currentUser.last_name || ''}`.trim() || 'Membre consommateur';
                    const validUntil = formatMemberCardExpiry(subscription?.end_date);
                    const qrContainerId = 'memberWalletQr';
                    const qrPayload = buildMemberQrPayload(state.currentUser, subscription, state.discountCard);

                    wrap.innerHTML = `
                        <div class="member-wallet-card">
                            <div class="member-wallet-content">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div class="member-wallet-brand">Carte My-Signal</div>
                                    <div class="member-wallet-brand">Reduction</div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center gap-3">
                                    <div class="member-wallet-chip"></div>
                                    <div class="member-wallet-number">${escapeHtml(cardNumber)}</div>
                                </div>
                                <div class="member-wallet-footer">
                                    <div>
                                        <div class="member-wallet-meta">Titulaire</div>
                                        <div class="member-wallet-value">${escapeHtml(fullName)}</div>
                                    </div>
                                    <div>
                                        <div class="member-wallet-meta">Expire</div>
                                        <div class="member-wallet-value">${escapeHtml(validUntil)}</div>
                                    </div>
                                    <div>
                                        <div class="member-qr-box" id="${qrContainerId}"></div>
                                        <div class="member-wallet-qr-caption text-center mt-1">Scan</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    renderMemberQr(qrContainerId, qrPayload);
                }

                function renderMeters(meters) {
                    state.meters = meters;
                    document.getElementById('meterCount').textContent = meters.length;
                    document.getElementById('topbarMetersBadge').textContent = `${meters.length} identifiant${meters.length > 1 ? 's' : ''}`;
                    const primaryMeter = meters.find((meter) => meter.is_primary) || meters[0] || null;
                    populateMeterApplicationOptions(primaryMeter?.application_code || null);
                    renderReportNetworkOptions(primaryMeter?.application_code || primaryMeter?.network_type || null);
                    renderReportMeterOptions(primaryMeter?.id || null);
                    const sharedMeterSelect = document.getElementById('householdSharedMeterId');
                    if (sharedMeterSelect) {
                        sharedMeterSelect.innerHTML = meters.length
                            ? meters.map((meter) => `<option value="${meter.id}">${meter.organization_name || meter.network_type} · ${meter.meter_number}${meter.label ? ' · ' + meter.label : ''} · ${meter.assignment_label || 'Compteur personnel'}</option>`).join('')
                            : '<option value="">Aucun identifiant disponible</option>';
                    }
                    renderSignalOptions();

                    setTextIfExists('overviewPrimaryMeter', primaryMeter
                        ? `${primaryMeter.organization_name || primaryMeter.network_type} · ${primaryMeter.meter_number}`
                        : 'Aucun identifiant principal');
                    setTextIfExists('overviewPrimaryMeterMeta', primaryMeter
                        ? [primaryMeter.label, primaryMeter.commune, primaryMeter.address].filter(Boolean).join(' · ') || 'identifiant prêt pour les déclarations'
                        : 'Ajoute un identifiant pour accélérer tes déclarations.');

                    const list = document.getElementById('metersList');
                    if (!meters.length) {
                        list.innerHTML = '<div class="col-12"><div class="mini-card"><div class="fw-bold mb-1">Aucun identifiant enregistré</div><div class="muted-label">Ajoutez vos identifiants pour alimenter vos futurs signalements.</div></div></div>';
                        return;
                    }
                    list.innerHTML = meters.map((meter) => `
                        <div class="col-md-6 col-xl-4">
                            <div class="mini-card h-100">
                                <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                    <div><div class="fw-bold">${meter.label || meter.organization_name || meter.network_type}</div><div class="muted-label">${meter.meter_number}</div></div>
                                    <div class="d-flex flex-column align-items-end gap-2">
                                        <span class="status-pill">${meter.assignment_label || 'Compteur personnel'}</span>
                                        ${meter.is_primary ? '<span class="status-pill">Principal</span>' : ''}
                                    </div>
                                </div>
                                <div class="muted-label mb-2">${meter.application_name || 'Catégorie non définie'}</div>
                                <div class="muted-label mb-3">${[meter.city, meter.commune, meter.neighborhood, meter.sub_neighborhood].filter(Boolean).join(' · ') || 'Localisation non renseignée'}${meter.address ? ' · ' + meter.address : ''}</div>
                                <div class="muted-label mb-3">${meter.latitude && meter.longitude ? 'Position renseignée' : 'Position non renseignée'}</div>
                                <button class="btn btn-ghost-premium w-100" type="button" onclick="window.AcepenPortal.prefillMeter(${meter.id})">Modifier</button>
                            </div>
                        </div>
                    `).join('');
                }

                function renderPurchaseReceiptOptions() {
                    const select = document.getElementById('damagePurchaseReceiptId');

                    if (!select) {
                        return;
                    }

                    const currentValue = select.value;
                    select.innerHTML = '<option value="">Aucun reçu sélectionné</option>'
                        + state.purchaseReceipts.map((receipt) => `
                            <option value="${receipt.id}">
                                ${escapeHtml(receipt.material_name)} · ${formatDateTime(receipt.purchase_date)} · ${formatAmount(receipt.amount)}
                            </option>
                        `).join('');
                    select.value = state.purchaseReceipts.some((receipt) => String(receipt.id) === String(currentValue)) ? currentValue : '';
                }

                function renderPurchaseReceipts(receipts) {
                    state.purchaseReceipts = receipts || [];
                    renderPurchaseReceiptOptions();

                    const list = document.getElementById('purchaseReceiptsList');

                    if (!list) {
                        return;
                    }

                    if (!state.purchaseReceipts.length) {
                        list.innerHTML = '<div class="col-12"><div class="mini-card"><div class="fw-bold mb-1">Aucun reçu enregistré</div><div class="muted-label">Ajoutez vos achats de matériel pour les retrouver rapidement lors d’une déclaration de dommage.</div></div></div>';
                        return;
                    }

                    list.innerHTML = state.purchaseReceipts.map((receipt) => `
                        <div class="col-md-6 col-xl-4">
                            <div class="mini-card h-100">
                                <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                    <div>
                                        <div class="fw-bold">${escapeHtml(receipt.material_name)}</div>
                                        <div class="muted-label">${formatDateTime(receipt.purchase_date)}</div>
                                    </div>
                                    <span class="status-pill">${formatAmount(receipt.amount)}</span>
                                </div>
                                ${receipt.attachment?.temporary_url
                                    ? `<a class="btn btn-ghost-premium w-100 mt-2" href="${escapeHtml(receipt.attachment.temporary_url)}" target="_blank" rel="noopener">Voir le fichier</a>`
                                    : '<div class="muted-label mt-2">Aucun fichier joint</div>'}
                                <div class="d-flex gap-2 mt-3">
                                    <button class="btn btn-ghost-premium flex-fill" type="button" onclick="window.AcepenPortal.prefillPurchaseReceipt(${receipt.id})">Modifier</button>
                                    <button class="btn btn-ghost-premium flex-fill" type="button" onclick="window.AcepenPortal.deletePurchaseReceipt(${receipt.id})">Supprimer</button>
                                </div>
                            </div>
                        </div>
                    `).join('');
                }

                function setHouseholdFormVisible(visible) {
                    const emptyState = document.getElementById('householdEmptyState');
                    const cancelButton = document.getElementById('cancelHouseholdFormButton');

                    emptyState.classList.toggle('d-none', !visible);
                    cancelButton.classList.toggle('d-none', !state.households.length);
                }

                function renderHouseholdsList() {
                    const list = document.getElementById('householdsList');

                    if (!list) {
                        return;
                    }

                    list.innerHTML = state.households.map((household) => `
                        <button class="btn ${String(household.id) === String(state.selectedHouseholdId) ? 'btn-premium' : 'btn-ghost-premium'} w-100 text-start" type="button" data-household-select="${household.id}">
                            <div class="fw-bold">${household.name || 'Gbonhi familial'}</div>
                            <div class="small">${[household.commune, household.address].filter(Boolean).join(' · ') || 'Adresse non renseignée'} · ${household.members?.length ?? 0} membre(s)</div>
                        </button>
                    `).join('');

                    list.querySelectorAll('[data-household-select]').forEach((button) => {
                        button.addEventListener('click', () => {
                            const selectedHousehold = state.households.find((item) => String(item.id) === String(button.dataset.householdSelect));

                            if (selectedHousehold) {
                                renderHousehold(selectedHousehold, state.households);
                            }
                        });
                    });
                }

                function renderHousehold(household, households = null) {
                    if (households !== null) {
                        state.households = households;
                    } else if (household && !state.households.some((item) => String(item.id) === String(household.id))) {
                        state.households = [household, ...state.households];
                    }

                    state.household = household;
                    state.selectedHouseholdId = household?.id || null;
                    document.getElementById('householdMemberCount').textContent = household?.members?.length ?? 0;
                    setTextIfExists('overviewHouseholdSummary', household
                        ? household.name || 'Gbonhi principal'
                        : 'Aucun Gbonhi enregistré');
                    setTextIfExists('overviewHouseholdMeta', household
                        ? `${household.members?.length ?? 0} membre(s) · ${household.pending_invitations?.length ?? 0} invitation(s) en attente`
                        : 'Crée un Gbonhi pour centraliser les signalements familiaux.');

                    const panel = document.getElementById('householdPanel');
                    document.getElementById('showHouseholdFormButton').textContent = state.households.length ? 'Créer un autre Gbonhi' : 'Créer mon Gbonhi';
                    if (!household) {
                        setHouseholdFormVisible(true);
                        panel.classList.add('d-none');
                        return;
                    }
                    setHouseholdFormVisible(false);
                    panel.classList.remove('d-none');
                    renderHouseholdsList();
                    document.getElementById('householdName').textContent = household.name || 'Gbonhi principal';
                    document.getElementById('householdAddress').textContent = [household.commune, household.address].filter(Boolean).join(' · ') || 'Adresse non renseignée';
                    document.getElementById('householdStatus').textContent = household.status || 'active';
                    const deleteButton = document.getElementById('deleteHouseholdButton');
                    const canManageHousehold = String(household.owner_public_user_id) === String(state.currentUser?.id);
                    deleteButton.classList.toggle('d-none', !canManageHousehold);
                    deleteButton.dataset.householdId = String(household.id);
                    document.getElementById('householdMembersList').innerHTML = household.members?.length
                        ? household.members.map((member) => `<div class="d-flex justify-content-between align-items-center rounded-4 border px-3 py-3 gap-3 flex-wrap"><div><div class="fw-semibold">${member.user.first_name ?? ''} ${member.user.last_name ?? ''}</div><div class="muted-label">${member.user.phone ?? ''} · ${member.relationship}</div></div><div class="report-actions"><span class="status-pill">${member.is_owner ? 'Titulaire' : 'Membre'}</span>${canManageHousehold && !member.is_owner ? `<button class="btn btn-ghost-premium btn-sm px-3" type="button" onclick="window.AcepenPortal.removeHouseholdMember(${household.id}, ${member.id})">Retirer</button>` : ''}</div></div>`).join('')
                        : '<div class="muted-label">Aucun membre.</div>';
                    document.getElementById('householdInvitationsList').innerHTML = household.pending_invitations?.length
                        ? household.pending_invitations.map((invitation) => `<div class="d-flex justify-content-between align-items-center rounded-4 border px-3 py-3 gap-3 flex-wrap"><div><div class="fw-semibold">${invitation.phone}</div><div class="muted-label">${invitation.relationship}</div></div><div class="report-actions"><span class="status-pill">En attente</span><button class="btn btn-ghost-premium btn-sm px-3" type="button" onclick="window.AcepenPortal.cancelHouseholdInvitation(${invitation.id})">Annuler</button></div></div>`).join('')
                        : '<div class="muted-label">Aucune invitation en attente.</div>';
                }

                function renderIncomingHouseholdInvitations(invitations) {
                    state.pendingHouseholdInvitations = invitations;
                    const list = document.getElementById('incomingHouseholdInvitationsList');

                    if (!invitations.length) {
                        list.innerHTML = '<div class="muted-label">Aucune invitation reçue pour le moment.</div>';
                        return;
                    }

                    list.innerHTML = invitations.map((invitation) => `
                        <div class="d-flex justify-content-between align-items-center rounded-4 border px-3 py-3 gap-3 flex-wrap">
                            <div>
                                <div class="fw-semibold">${invitation.household?.name || 'Gbonhi familial'}</div>
                                <div class="muted-label">${[invitation.relationship, invitation.household?.commune, invitation.household?.address].filter(Boolean).join(' · ')}</div>
                                <div class="muted-label">${invitation.meter ? `identifiant commun: ${(invitation.meter.organization_name || invitation.meter.network_type)} · ${invitation.meter.meter_number}${invitation.meter.label ? ' · ' + invitation.meter.label : ''}` : 'Aucun identifiant commun défini'}</div>
                                <div class="muted-label">Invitation sans expiration</div>
                            </div>
                            <div class="report-actions">
                                <button class="btn btn-ghost-premium btn-sm px-3" type="button" onclick="window.AcepenPortal.declineInvitation(${invitation.id})">Décliner</button>
                                <button class="btn btn-premium btn-sm px-3" type="button" onclick="window.AcepenPortal.acceptInvitation(${invitation.id})">Accepter</button>
                            </div>
                        </div>
                    `).join('');
                }

                function updateDamageDeclarationAction(reports) {
                    const button = document.getElementById('openDamageDeclarationButton');

                    if (!button) {
                        return;
                    }

                    const eligibleReports = reports.filter((report) => report.damage_declaration?.can_declare);
                    const firstEligibleReport = eligibleReports[0] || null;

                    button.classList.toggle('d-none', !firstEligibleReport);
                    button.textContent = eligibleReports.length > 1
                        ? `Enregistrer un dommage (${eligibleReports.length})`
                        : 'Enregistrer un dommage';
                    button.disabled = !firstEligibleReport;
                    button.dataset.reportId = firstEligibleReport ? String(firstEligibleReport.id) : '';
                }

                function getOverviewFilteredReports(reports) {
                    const search = state.overviewReportFilters.search.trim().toLowerCase();

                    return reports.filter((report) => {
                        const matchesSearch = !search || [
                            report.reference,
                            report.signal_code,
                            report.signal_label,
                            report.incident_type,
                            report.description,
                            report.organization?.name,
                            report.location?.commune,
                            report.location?.city,
                            report.location?.country,
                        ].filter(Boolean).join(' ').toLowerCase().includes(search);

                        const matchesStatus = !state.overviewReportFilters.status || report.status === state.overviewReportFilters.status;

                        return matchesSearch && matchesStatus;
                    });
                }

                function renderOverviewReports(reports) {
                    const list = document.getElementById('overviewReportsList');

                    if (!list) {
                        return;
                    }

                    if (!reports.length) {
                        list.innerHTML = `
                            <div class="overview-report-empty">
                                <div class="fw-bold mb-1">Aucun signalement pour le moment</div>
                                <div class="muted-label mb-3">Commence par faire un signalement. Tu pourras ensuite suivre son évolution ici.</div>
                                <button class="btn btn-premium px-4" type="button" data-panel-target="reports">Faire un signalement</button>
                            </div>
                        `;
                        list.querySelectorAll('[data-panel-target]').forEach((button) => {
                            button.addEventListener('click', () => activatePanel(button.dataset.panelTarget));
                        });
                        return;
                    }

                    const currentReports = reports.slice(0, 3);

                    list.innerHTML = `
                        <div class="overview-report-list">
                            ${currentReports.map((report) => `
                                <div class="overview-report-card">
                                    <div>
                                        <div class="overview-report-title">${report.signal_label || report.incident_type || report.signal_code}</div>
                                        <div class="overview-report-meta">${report.reference} · ${formatDateTime(report.created_at)}</div>
                                        <div class="overview-report-meta">${report.organization?.name || report.network_type || '-'}${report.location?.commune ? ' · ' + report.location.commune : ''}</div>
                                    </div>
                                    <div class="text-end">
                                        <div class="mb-2"><span class="status-pill status-report-${report.status}">${getPublicStatusLabel(report.status)}</span></div>
                                        <button class="btn btn-sm btn-ghost-premium px-3" type="button" onclick="window.AcepenPortal.showReportDetails(${report.id})">Voir</button>
                                    </div>
                                </div>
                            `).join('')}
                            <div class="d-flex justify-content-end">
                                <button class="btn btn-ghost-premium px-4" type="button" data-panel-target="reports">Tous mes signalements</button>
                            </div>
                        </div>
                    `;
                    list.querySelectorAll('[data-panel-target]').forEach((button) => {
                        button.addEventListener('click', () => activatePanel(button.dataset.panelTarget));
                    });
                }

                function updateNotificationBadges() {
                    const count = Number(state.unreadNotificationsCount || 0);

                    ['sidebarNotificationBadge', 'topbarNotificationBadge'].forEach((id) => {
                        const badge = document.getElementById(id);
                        if (!badge) return;
                        badge.textContent = count > 99 ? '99+' : String(count);
                        badge.classList.toggle('d-none', count === 0);
                    });

                    const markAllButton = document.getElementById('markAllNotificationsReadButton');
                    if (markAllButton) {
                        markAllButton.disabled = count === 0;
                    }
                }

                function getFilteredNotifications() {
                    const search = normalizeText(state.notificationFilters.search);
                    const status = state.notificationFilters.status;
                    const category = state.notificationFilters.category;

                    return state.notifications.filter((notification) => {
                        const haystack = normalizeText([notification.title, notification.body, notification.type].filter(Boolean).join(' '));
                        const isRead = !!notification.read_at;
                        const notificationCategory = getNotificationCategoryKey(notification);

                        return (!search || haystack.includes(search))
                            && (!status || (status === 'read' ? isRead : !isRead))
                            && (!category || notificationCategory === category);
                    });
                }

                function getNotificationCategoryKey(notification) {
                    return notification.category || notification.data?.category || notification.data?.source || notification.type || 'general';
                }

                function getNotificationCategoryLabel(notification) {
                    const category = getNotificationCategoryKey(notification);
                    const labels = {
                        mysignal: 'Information My-Signal',
                        super_admin: 'Information My-Signal',
                        super_admin_broadcast: 'Information My-Signal',
                        gbonhi: 'Gbonhi',
                        household: 'Gbonhi',
                        household_invitation_created: 'Gbonhi',
                        report: 'Signalement',
                        reports: 'Signalement',
                        payment: 'Paiement',
                        payments: 'Paiement',
                        subscription: 'Abonnement',
                        subscriptions: 'Abonnement',
                        discount: 'Remise',
                        discounts: 'Remise',
                        partner_discount: 'Remise',
                        partner_discount_applied: 'Remise',
                        public_discount_received: 'Remise',
                    };

                    return labels[category] || 'Général';
                }

                function renderNotificationCategoryFilter() {
                    const select = document.getElementById('notificationCategoryFilter');
                    if (!select) return;

                    const categories = Array.from(new Map(state.notifications.map((notification) => {
                        const key = getNotificationCategoryKey(notification);
                        return [key, notification.category_label || getNotificationCategoryLabel(notification)];
                    })).entries()).sort((a, b) => a[1].localeCompare(b[1]));

                    const currentValue = state.notificationFilters.category;
                    select.innerHTML = '<option value="">Toutes</option>'
                        + categories.map(([key, label]) => `<option value="${escapeHtml(key)}">${escapeHtml(label)}</option>`).join('');
                    select.value = categories.some(([key]) => key === currentValue) ? currentValue : '';
                    state.notificationFilters.category = select.value;
                }

                function renderNotifications() {
                    updateNotificationBadges();
                    renderNotificationCategoryFilter();
                    const list = document.getElementById('notificationsList');
                    if (!list) return;

                    const notifications = getFilteredNotifications();

                    if (!state.notifications.length) {
                        list.innerHTML = '<div class="mini-card text-center text-secondary">Aucune notification pour le moment.</div>';
                        return;
                    }

                    if (!notifications.length) {
                        list.innerHTML = '<div class="mini-card text-center text-secondary">Aucune notification ne correspond aux filtres.</div>';
                        return;
                    }

                    list.innerHTML = `<div class="vstack gap-3">
                        ${notifications.map((notification) => {
                            const unread = !notification.read_at;

                            return `
                                <article class="notification-item ${unread ? 'unread' : ''}">
                                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                        <div>
                                            <div class="notification-category mb-2">${escapeHtml(notification.category_label || getNotificationCategoryLabel(notification))}</div>
                                            <div class="notification-title">${escapeHtml(notification.title || 'Notification')}</div>
                                            <div class="muted-label">${escapeHtml(notification.body || '')}</div>
                                            <div class="small text-secondary mt-2">${formatDateTime(notification.created_at)} · ${unread ? 'Non lu' : 'Lu'}</div>
                                        </div>
                                        ${unread ? `<button class="btn btn-sm btn-ghost-premium" type="button" data-notification-read="${notification.id}">Marquer comme lu</button>` : '<span class="status-pill">Lu</span>'}
                                    </div>
                                </article>
                            `;
                        }).join('')}
                    </div>`;

                    list.querySelectorAll('[data-notification-read]').forEach((button) => {
                        button.addEventListener('click', () => markNotificationAsRead(button.dataset.notificationRead));
                    });
                }

                async function refreshNotifications() {
                    const response = await apiFetch('/notifications?limit=100');
                    state.notifications = response.data.notifications || [];
                    state.unreadNotificationsCount = response.data.unread_count || 0;
                    renderNotifications();
                }

                async function markNotificationAsRead(notificationId) {
                    try {
                        await apiFetch(`/notifications/${notificationId}/read`, { method: 'POST' });
                        await refreshNotifications();
                    } catch (error) {
                        showToast(error.message || 'Impossible de marquer la notification comme lue.', true);
                    }
                }

                async function markAllNotificationsAsRead() {
                    try {
                        await apiFetch('/notifications/read-all', { method: 'POST' });
                        await refreshNotifications();
                    } catch (error) {
                        showToast(error.message || 'Impossible de marquer les notifications comme lues.', true);
                    }
                }

                function renderReports(reports) {
                    state.reports = reports;
                    updateDamageDeclarationAction(reports);
                    document.getElementById('reportCount').textContent = reports.length;
                    document.getElementById('topbarReportsBadge').textContent = `${reports.length} signalement${reports.length > 1 ? 's' : ''}`;
                    renderOverviewReports(reports);

                    const list = document.getElementById('reportsList');
                    renderReportOrganizationFilter(reports);
                    if (!reports.length) {
                        list.innerHTML = '<div class="mini-card"><div class="fw-bold mb-1">Aucun signalement</div><div class="muted-label">Vos futurs signalements apparaîtront ici avec leur référence unique.</div></div>';
                        return;
                    }
                    const filteredReports = getFilteredReports(reports);
                    const totalPages = Math.max(1, Math.ceil(filteredReports.length / state.reportsPageSize));
                    state.reportsPage = Math.min(state.reportsPage, totalPages);

                    if (!filteredReports.length) {
                        list.innerHTML = '<div class="mini-card"><div class="fw-bold mb-1">Aucun signalement ne correspond aux filtres</div><div class="muted-label">Ajuste les filtres pour retrouver plus facilement tes déclarations.</div></div>';
                        return;
                    }

                    const start = (state.reportsPage - 1) * state.reportsPageSize;
                    const currentReports = filteredReports.slice(start, start + state.reportsPageSize);
                    const end = start + currentReports.length;

                    list.innerHTML = `
                        <div class="report-table-shell">
                            <div class="report-table-wrap">
                                <table class="report-table">
                                    <thead>
                                        <tr>
                                            <th>Numéro de suivi</th>
                                            <th>Signal</th>
                                            <th>Localisation</th>
                                            <th>Paiement</th>
                                            <th>Résolution</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${currentReports.map((report) => `
                                            <tr>
                                                <td>
                                                    <div class="report-ref">${report.reference}</div>
                                                    <div class="report-sub">${report.organization?.name || report.network_type} · ${report.signal_code}</div>
                                                    <div class="report-sub">${report.application?.name || 'Catégorie non définie'}</div>
                                                    <div class="report-sub">Délai prévu ${report.target_sla_hours ?? '-'}h</div>
                                                </td>
                                                <td>
                                                    <div class="report-main">${report.signal_label || report.incident_type}</div>
                                                    <div class="report-sub">${report.description || 'Aucune description fournie.'}</div>
                                                    <div class="report-sub mt-1">${report.organization?.name || report.organization_name || report.network_type || 'Institution non définie'}</div>
                                                </td>
                                                <td>
                                                    <div class="report-main">${report.location.commune || '-'}</div>
                                                    <div class="report-sub">${[report.location.country, report.location.city].filter(Boolean).join(' · ')}</div>
                                                    <div class="report-sub">${report.location.address || 'Adresse non renseignée'}</div>
                                                    <div class="report-sub">${report.location.latitude && report.location.longitude ? 'Position renseignée' : 'Position non renseignée'}</div>
                                                </td>
                                                <td>
                                                    <div class="report-main">${report.payment_status === 'paid' ? 'Payé' : 'En attente'}</div>
                                                    <div class="report-sub">Montant: 100 FCFA</div>
                                                    <div class="report-sub">${report.paid_at ? `Confirmé le ${new Date(report.paid_at).toLocaleString()}` : 'Paiement non confirmé'}</div>
                                                </td>
                                                <td>
                                                    <div class="report-main"><span class="status-pill ${getResolutionStatusClass(report)}">${getResolutionLabel(report)}</span></div>
                                                    <div class="report-sub">${getResolutionHelpText(report)}</div>
                                                    <div class="report-sub">Temps de résolution: ${getResolutionDurationText(report)}</div>
                                                    <div class="report-sub">Délai de traitement: ${getSlaText(report)} · ${getSlaRespectText(report)}</div>
                                                    <div class="report-sub">${report.resolution_confirmation?.confirmed_at ? `Confirmée le ${new Date(report.resolution_confirmation.confirmed_at).toLocaleString()}` : ''}</div>
                                                </td>
                                                <td>
                                                    <div class="report-actions">
                                                        <button class="btn btn-ghost-premium btn-sm px-3" type="button" onclick="window.AcepenPortal.showReportDetails(${report.id})">Détails</button>
                                                        ${report.payment_status !== 'paid'
                                                            ? `<button class="btn btn-premium btn-sm px-3" type="button" onclick="window.AcepenPortal.payReport(${report.id})">Payer</button>`
                                                            : ''}
                                                        ${report.resolution_confirmation?.can_confirm
                                                            ? `<button class="btn btn-ghost-premium btn-sm px-3" type="button" onclick="window.AcepenPortal.confirmResolution(${report.id})">Confirmer</button>`
                                                            : ''}
                                                        ${renderRexActionButton('incident_report', report.id, report.reference, 'Avis sur le signalement', canSubmitIncidentRex(report))}
                                                        <button
                                                            class="btn btn-premium btn-sm px-3"
                                                            type="button"
                                                            ${report.damage_declaration?.can_declare ? `onclick="window.AcepenPortal.openDamageForm(${report.id})"` : 'disabled'}
                                                            title="${report.damage_declaration?.can_declare
                                                                ? `Enregistrer les dommages constatés avant le ${formatDateTime(report.damage_declaration.available_until)}.`
                                                                : (report.damage_declaration?.window_expired
                                                                    ? 'Le délai de 24h après confirmation est dépassé.'
                                                                    : 'Disponible après confirmation de résolution du signalement.')}"
                                                        >
                                                            Dommage
                                                        </button>
                                                        ${renderRexActionButton('damage_declaration', report.id, report.reference, 'Avis sur le dommage', canSubmitDamageRex(report))}
                                                    </div>
                                                </td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                            <div class="pagination-shell">
                                <div class="pagination-info">Affichage ${start + 1} à ${end} sur ${filteredReports.length} signalement${filteredReports.length > 1 ? 's' : ''}</div>
                                <div class="pagination-actions">
                                    <button class="pagination-chip" type="button" ${state.reportsPage === 1 ? 'disabled' : ''} onclick="window.AcepenPortal.changeReportsPage(${state.reportsPage - 1})">‹</button>
                                    <div class="small fw-semibold text-secondary">Page ${state.reportsPage} / ${totalPages}</div>
                                    <button class="pagination-chip" type="button" ${state.reportsPage === totalPages ? 'disabled' : ''} onclick="window.AcepenPortal.changeReportsPage(${state.reportsPage + 1})">›</button>
                                </div>
                            </div>
                        </div>
                    `;
                }

                function getPaymentStatusLabel(status) {
                    const labels = {
                        paid: 'Confirmé',
                        pending: 'En attente',
                        failed: 'Echoue',
                    };

                    return labels[status] || status || '-';
                }

                function getPaymentStatusClass(status) {
                    const classes = {
                        paid: 'status-payment-paid',
                        pending: 'status-payment-pending',
                        failed: 'status-payment-failed',
                    };

                    return classes[status] || '';
                }

                function formatMoney(amount, currency = 'FCFA') {
                    return `${Number(amount || 0).toLocaleString()} ${currency}`;
                }

                function getReparationCaseStatusLabel(status) {
                    const labels = {
                        submitted: 'Soumis',
                        under_review: 'En analyse',
                        awaiting_documents: 'Pieces requises',
                        sent_to_organization: 'Transmis à l’institution',
                        organization_responded: 'Réponse institution',
                        awaiting_lawyer_assignment: 'En attente avocat',
                        lawyer_assigned: 'Avocat attribué',
                        judicial_in_progress: 'Procédure judiciaire',
                        approved: 'Valide',
                        rejected: 'Rejeté',
                        compensated: 'Compensé',
                        closed: 'Clos',
                    };

                    return labels[status] || status || '-';
                }

                function getReparationCaseStatusClass(status) {
                    const classes = {
                        submitted: 'status-report-submitted',
                        under_review: 'status-report-in-progress',
                        awaiting_documents: 'status-resolution-waiting',
                        sent_to_organization: 'status-report-in-progress',
                        organization_responded: 'status-report-in-progress',
                        awaiting_lawyer_assignment: 'status-resolution-waiting',
                        lawyer_assigned: 'status-report-in-progress',
                        judicial_in_progress: 'status-report-in-progress',
                        approved: 'status-report-resolved',
                        compensated: 'status-report-resolved',
                        closed: 'status-report-resolved',
                        rejected: 'status-report-rejected',
                    };

                    return classes[status] || '';
                }

                function renderReparationCases(cases) {
                    state.reparationCases = cases;
                    const list = document.getElementById('reparationCasesList');

                    if (!cases.length) {
                        list.innerHTML = '<div class="mini-card"><div class="fw-bold mb-1">Aucun dossier ouvert</div><div class="muted-label">Si un dossier est ouvert à partir d’un signalement, son historique apparaîtra ici.</div></div>';
                        return;
                    }

                    list.innerHTML = `
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Dossier</th>
                                        <th>Signalement</th>
                                        <th>Institution</th>
                                        <th>État</th>
                                        <th>Intervenants</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                            ${cases.map((repairCase) => `
                                    <tr>
                                        <td>
                                            <div class="fw-bold">${repairCase.reference}</div>
                                            <div class="muted-label">${repairCase.opened_at ? `Ouvert le ${formatDateTime(repairCase.opened_at)}` : 'Date indisponible'}</div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">${repairCase.incident_report?.reference || '-'}</div>
                                            <div class="muted-label">${repairCase.incident_report?.signal_label || repairCase.incident_report?.signal_code || 'Signalement'}</div>
                                        </td>
                                        <td>
                                            <div>${repairCase.incident_report?.organization_name || 'Institution non définie'}</div>
                                            <div class="muted-label">${repairCase.incident_report?.application_name || 'Catégorie non définie'}</div>
                                        </td>
                                        <td>
                                            <span class="status-pill ${getReparationCaseStatusClass(repairCase.status)}">${getReparationCaseStatusLabel(repairCase.status)}</span>
                                        </td>
                                        <td>
                                            <div class="small"><strong>Huissier:</strong> ${repairCase.bailiff || '-'}</div>
                                            <div class="small"><strong>Avocat:</strong> ${repairCase.lawyer || '-'}</div>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end gap-2 flex-wrap">
                                                <button class="btn btn-ghost-premium btn-sm px-3" type="button" onclick="window.AcepenPortal.showReparationCaseDetails(${repairCase.id})">Détails</button>
                                                ${renderRexActionButton('reparation_case', repairCase.id, repairCase.reference, 'Donner mon avis', canSubmitCaseRex(repairCase), '')}
                                            </div>
                                        </td>
                                    </tr>
                            `).join('')}
                                </tbody>
                            </table>
                        </div>
                    `;
                }

                function renderReparationCaseAttachments(attachments = []) {
                    if (!attachments.length) {
                        return '';
                    }

                    return `
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            ${attachments.map((attachment) => `
                                ${attachment.temporary_url
                                    ? `<a href="${attachment.temporary_url}" target="_blank" rel="noopener noreferrer" class="btn btn-ghost-premium btn-sm">${attachment.name || 'Pièce jointe'}</a>`
                                    : `<span class="badge rounded-pill text-bg-light border">${attachment.name || 'Pièce jointe'}</span>`
                                }
                            `).join('')}
                        </div>
                    `;
                }

                function renderReparationCaseDetails(repairCase) {
                    document.getElementById('reparationCaseDetailTitle').textContent = repairCase.reference || 'Dossier';

                    const steps = repairCase.steps || [];
                    const histories = repairCase.histories || [];

                    document.getElementById('reparationCaseDetailContent').innerHTML = `
                        <div class="row g-4">
                            <div class="col-lg-4">
                                <div class="mini-card h-100">
                                    <div class="small text-secondary fw-semibold mb-2">Synthèse</div>
                                    <div class="fw-bold fs-5 mb-2">${repairCase.reference}</div>
                                    <div class="mb-3">
                                        <span class="status-pill ${getReparationCaseStatusClass(repairCase.status)}">${getReparationCaseStatusLabel(repairCase.status)}</span>
                                    </div>
                                    <div class="soft-panel mb-3">
                                        <div class="small text-secondary fw-semibold mb-1">Signalement source</div>
                                        <div class="fw-semibold">${repairCase.incident_report?.reference || '-'}</div>
                                        <div class="muted-label">${repairCase.incident_report?.signal_label || repairCase.incident_report?.signal_code || 'Signalement'}</div>
                                    </div>
                                    <div class="soft-panel mb-3">
                                        <div class="small text-secondary fw-semibold mb-1">Institution</div>
                                        <div class="fw-semibold">${repairCase.incident_report?.organization_name || 'Institution non définie'}</div>
                                        <div class="muted-label">${repairCase.incident_report?.application_name || 'Catégorie non définie'}</div>
                                    </div>
                                    <div class="soft-panel mb-3">
                                        <div class="small text-secondary fw-semibold mb-1">Intervenants</div>
                                        <div class="fw-semibold">Huissier: ${repairCase.bailiff || '-'}</div>
                                        <div class="fw-semibold">Avocat: ${repairCase.lawyer || '-'}</div>
                                    </div>
                                    <div class="soft-panel">
                                        <div class="small text-secondary fw-semibold mb-1">Dates</div>
                                        <div class="fw-semibold">${repairCase.opened_at ? `Ouvert le ${formatDateTime(repairCase.opened_at)}` : 'Ouverture non renseignée'}</div>
                                        <div class="muted-label">${repairCase.closed_at ? `Clos le ${formatDateTime(repairCase.closed_at)}` : 'Dossier non clos'}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-8">
                                <div class="mini-card mb-4">
                                    <div class="section-title mb-3" style="font-size: 1rem;">Objet du dossier</div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="soft-panel h-100">
                                                <div class="small text-secondary fw-semibold mb-1">Résumé du dommage</div>
                                                <div class="fw-semibold">${repairCase.damage_summary || 'Aucun résumé de dommage renseigné.'}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="soft-panel h-100">
                                                <div class="small text-secondary fw-semibold mb-1">Montant réclamé</div>
                                                <div class="fw-semibold">${repairCase.damage_amount_claimed !== null ? formatMoney(repairCase.damage_amount_claimed) : 'Non renseigné'}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="soft-panel h-100">
                                                <div class="small text-secondary fw-semibold mb-1">Montant validé</div>
                                                <div class="fw-semibold">${repairCase.damage_amount_validated !== null ? formatMoney(repairCase.damage_amount_validated) : 'Non renseigné'}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-4">
                                    <div class="col-xl-7">
                                        <div class="mini-card h-100">
                                            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                                <div>
                                                    <div class="section-title" style="font-size: 1rem;">Étapes du dossier</div>
                                                    <div class="muted-label">${steps.length} étape${steps.length > 1 ? 's' : ''}</div>
                                                </div>
                                            </div>
                                            <div class="vstack gap-2" style="max-height: 520px; overflow:auto; padding-right:.25rem;">
                                                ${steps.length ? steps.map((step) => `
                                                    <div class="soft-panel">
                                                        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                                            <div>
                                                                <div class="fw-bold mb-1">${step.title}</div>
                                                                <div class="muted-label">${step.summary || 'Aucun détail complémentaire fourni.'}</div>
                                                                <div class="muted-label mt-1">${step.assigned_to ? `Responsable: ${step.assigned_to}` : 'Responsable non assigné'}</div>
                                                            </div>
                                                            <div class="text-end">
                                                                <div class="small fw-semibold">${step.completed_at ? formatDateTime(step.completed_at) : (step.created_at ? formatDateTime(step.created_at) : '-')}</div>
                                                                <div class="muted-label">${step.status || '-'}</div>
                                                            </div>
                                                        </div>
                                                        ${renderReparationCaseAttachments(step.attachments || [])}
                                                    </div>
                                                `).join('') : '<div class="soft-panel"><div class="fw-bold mb-1">Aucune étape visible</div><div class="muted-label">Les prochaines étapes enregistrées apparaîtront ici.</div></div>'}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-5">
                                        <div class="mini-card h-100">
                                            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                                <div>
                                                    <div class="section-title" style="font-size: 1rem;">Historique</div>
                                                    <div class="muted-label">${histories.length} entrée${histories.length > 1 ? 's' : ''}</div>
                                                </div>
                                            </div>
                                            <div class="vstack gap-2" style="max-height: 520px; overflow:auto; padding-right:.25rem;">
                                                ${histories.length ? histories.map((history) => `
                                                    <div class="soft-panel">
                                                        <div class="fw-bold mb-1">${history.title}</div>
                                                        <div class="muted-label">${history.description || 'Aucun détail complémentaire fourni.'}</div>
                                                        <div class="muted-label mt-2">${history.created_at ? formatDateTime(history.created_at) : '-'} · ${history.created_by || 'Système'}</div>
                                                    </div>
                                                `).join('') : '<div class="soft-panel"><div class="fw-bold mb-1">Aucun historique visible</div><div class="muted-label">Les mises à jour du dossier apparaîtront ici.</div></div>'}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }

                function getRexContextLabel(contextType) {
                    const labels = {
                        incident_report: 'Signalement',
                        damage_declaration: 'Dommage',
                        reparation_case: 'Dossier',
                    };

                    return labels[contextType] || contextType || '-';
                }

                function hasRexFeedback(contextType, contextId) {
                    return state.rexFeedbacks.some((feedback) => feedback.context_type === contextType && String(feedback.context_id) === String(contextId));
                }

                function renderRexActionButton(contextType, contextId, title, label, isEligible, extraClass = '') {
                    if (!isEligible) {
                        return '';
                    }

                    if (hasRexFeedback(contextType, contextId)) {
                        return `<button class="btn btn-ghost-premium btn-sm px-3 ${extraClass}" type="button" disabled title="Un avis a déjà été envoyé pour cet élément.">Avis déjà envoyé</button>`;
                    }

                    const encodedTitle = encodeURIComponent(title || '');

                    return `<button class="btn btn-ghost-premium btn-sm px-3 ${extraClass}" type="button" onclick="window.AcepenPortal.openRexForm('${contextType}', ${contextId}, decodeURIComponent('${encodedTitle}'))">${label}</button>`;
                }

                function getFilteredRexFeedbacks() {
                    const search = state.rexFilters.search.trim().toLowerCase();

                    return state.rexFeedbacks.filter((feedback) => {
                        const haystack = [
                            getRexContextLabel(feedback.context_type),
                            feedback.incident_report?.reference,
                            feedback.incident_report?.signal_label,
                            feedback.incident_report?.signal_code,
                            feedback.application?.name,
                            feedback.organization?.name,
                            feedback.comment,
                        ].filter(Boolean).join(' ').toLowerCase();
                        const matchesSearch = !search || haystack.includes(search);
                        const matchesContext = !state.rexFilters.context || feedback.context_type === state.rexFilters.context;
                        const matchesRating = !state.rexFilters.rating || String(feedback.rating) === state.rexFilters.rating;

                        return matchesSearch && matchesContext && matchesRating;
                    });
                }

                function renderRexFeedbacks(feedbacks) {
                    state.rexFeedbacks = feedbacks;
                    const list = document.getElementById('rexFeedbacksList');

                    if (!list) {
                        return;
                    }

                    if (!state.rexFeedbacks.length) {
                        list.innerHTML = '<div class="mini-card"><div class="fw-bold mb-1">Aucun avis envoyé</div><div class="muted-label">Tes retours d’expérience apparaîtront ici après soumission.</div></div>';
                        return;
                    }

                    const filteredFeedbacks = getFilteredRexFeedbacks();
                    const totalPages = Math.max(1, Math.ceil(filteredFeedbacks.length / state.rexFeedbacksPageSize));
                    state.rexFeedbacksPage = Math.min(state.rexFeedbacksPage, totalPages);

                    if (!filteredFeedbacks.length) {
                        list.innerHTML = '<div class="mini-card"><div class="fw-bold mb-1">Aucun avis ne correspond aux filtres</div><div class="muted-label">Ajuste la recherche, le type ou la note globale.</div></div>';
                        return;
                    }

                    const start = (state.rexFeedbacksPage - 1) * state.rexFeedbacksPageSize;
                    const currentFeedbacks = filteredFeedbacks.slice(start, start + state.rexFeedbacksPageSize);
                    const end = start + currentFeedbacks.length;

                    list.innerHTML = `
                        <div class="report-table-shell">
                            <div class="report-table-wrap">
                                <table class="report-table">
                                    <thead>
                                        <tr>
                                            <th>Contexte</th>
                                            <th>Institution</th>
                                            <th>Notes</th>
                                            <th>Commentaire</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${currentFeedbacks.map((feedback) => `
                                            <tr>
                                                <td>
                                                    <div class="report-ref">${getRexContextLabel(feedback.context_type)}</div>
                                                    <div class="report-sub">${escapeHtml(feedback.incident_report?.reference || '-')}</div>
                                                    <div class="report-sub">${escapeHtml(feedback.incident_report?.signal_label || feedback.incident_report?.signal_code || '')}</div>
                                                </td>
                                                <td>
                                                    <div class="report-main">${escapeHtml(feedback.organization?.name || '-')}</div>
                                                    <div class="report-sub">${escapeHtml(feedback.application?.name || '-')}</div>
                                                </td>
                                                <td>
                                                    <div class="report-main"><span class="status-pill">${feedback.rating}/5</span></div>
                                                    <div class="report-sub">Rapidite: ${feedback.response_time_rating || '-'}/5</div>
                                                    <div class="report-sub">Clarte: ${feedback.communication_rating || '-'}/5</div>
                                                    <div class="report-sub">Solution: ${feedback.quality_rating || '-'}/5</div>
                                                    <div class="report-sub">Justice: ${feedback.fairness_rating || '-'}/5</div>
                                                </td>
                                                <td><div class="report-sub">${escapeHtml(feedback.comment || 'Aucun commentaire.')}</div></td>
                                                <td><div class="report-sub">${formatDateTime(feedback.submitted_at)}</div></td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                            <div class="pagination-shell">
                                <div class="pagination-info">Affichage ${start + 1} à ${end} sur ${filteredFeedbacks.length} avis</div>
                                <div class="pagination-actions">
                                    <button class="pagination-chip" type="button" ${state.rexFeedbacksPage === 1 ? 'disabled' : ''} onclick="window.AcepenPortal.changeRexFeedbacksPage(${state.rexFeedbacksPage - 1})">‹</button>
                                    <div class="small fw-semibold text-secondary">Page ${state.rexFeedbacksPage} / ${totalPages}</div>
                                    <button class="pagination-chip" type="button" ${state.rexFeedbacksPage === totalPages ? 'disabled' : ''} onclick="window.AcepenPortal.changeRexFeedbacksPage(${state.rexFeedbacksPage + 1})">›</button>
                                </div>
                            </div>
                        </div>
                    `;
                }

                function renderPayments(payments) {
                    state.payments = payments;
                    document.getElementById('topbarPaymentsBadge').textContent = `${payments.length} paiement${payments.length > 1 ? 's' : ''}`;

                    const latestPaidPayment = payments.find((payment) => payment.status === 'paid') || payments[0];
                    setTextIfExists('overviewPaymentSummary', latestPaidPayment
                        ? `${formatMoney(latestPaidPayment.amount, latestPaidPayment.currency)} · ${getPaymentStatusLabel(latestPaidPayment.status)}`
                        : 'Aucun paiement confirmé');
                    setTextIfExists('overviewPaymentMeta', latestPaidPayment
                        ? `${latestPaidPayment.incident_report?.reference || 'Signalement'} · ${latestPaidPayment.reference}`
                        : 'Ton historique de paiements et tes reçus apparaîtront ici.');

                    const list = document.getElementById('paymentsList');
                    if (!payments.length) {
                        list.innerHTML = '<div class="mini-card"><div class="fw-bold mb-1">Aucun paiement enregistré</div><div class="muted-label">Dès qu’un paiement sera initié pour un signalement, il apparaîtra ici avec son reçu.</div></div>';
                        return;
                    }

                    const filteredPayments = getFilteredPayments(payments);

                    if (!filteredPayments.length) {
                        list.innerHTML = '<div class="mini-card"><div class="fw-bold mb-1">Aucun paiement ne correspond aux filtres</div><div class="muted-label">Ajuste les filtres pour retrouver plus vite un reçu ou un paiement.</div></div>';
                        return;
                    }

                    list.innerHTML = `
                        <div class="payment-history-grid">
                            <div class="payment-table-shell">
                                <div class="report-table-wrap">
                                    <table class="payment-table">
                                        <thead>
                                            <tr>
                                                <th>Numéro de paiement</th>
                                                <th>Montant</th>
                                                <th>Signalement</th>
                                                <th>Mode</th>
                                                <th>Dates</th>
                                                <th>État</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${filteredPayments.map((payment) => `
                                                <tr>
                                                    <td>
                                                        <div class="payment-ref">${payment.reference}</div>
                                                        <div class="payment-sub">${payment.pricing_rule?.label || 'Paiement signalement public'}</div>
                                                    </td>
                                                    <td>
                                                        <div class="payment-amount">${formatMoney(payment.amount, payment.currency)}</div>
                                                        <div class="payment-sub">${payment.currency || 'FCFA'}</div>
                                                    </td>
                                                    <td>
                                                        <div class="payment-ref">${payment.incident_report?.reference || '-'}</div>
                                                        <div class="payment-sub">${payment.incident_report?.signal_label || payment.incident_report?.signal_code || 'Aucune information supplémentaire'}</div>
                                                    </td>
                                                    <td>
                                                        <div class="payment-ref">${formatPaymentProvider(payment.provider)}</div>
                                                        <div class="payment-sub">${payment.provider_reference || 'Confirmation en attente'}</div>
                                                    </td>
                                                    <td>
                                                        <div class="payment-sub"><strong>Initié:</strong> ${formatDateTime(payment.initiated_at)}</div>
                                                        <div class="payment-sub"><strong>Confirmé:</strong> ${formatDateTime(payment.paid_at)}</div>
                                                    </td>
                                                    <td><span class="status-pill ${getPaymentStatusClass(payment.status)}">${getPaymentStatusLabel(payment.status)}</span></td>
                                                    <td>
                                                        <div class="report-actions">
                                                            ${payment.incident_report?.id
                                                                ? `<button class="btn btn-ghost-premium btn-sm px-3" type="button" onclick="window.AcepenPortal.showReportDetails(${payment.incident_report.id})">Signalement</button>`
                                                                : ''}
                                                            ${payment.can_download_receipt
                                                                ? `<button class="btn btn-ghost-premium btn-sm px-3" type="button" onclick="window.AcepenPortal.previewReceipt(${payment.id})">Aperçu reçu</button>`
                                                                : ''}
                                                            ${payment.can_download_receipt
                                                                ? `<button class="btn btn-premium btn-sm px-3" type="button" onclick="window.AcepenPortal.downloadReceipt(${payment.id}, '${payment.reference}')">Reçu PDF</button>`
                                                                : ''}
                                                        </div>
                                                    </td>
                                                </tr>
                                            `).join('')}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="pagination-shell">
                                <div class="pagination-info">${filteredPayments.length} paiement${filteredPayments.length > 1 ? 's' : ''} affiche${filteredPayments.length > 1 ? 's' : ''}</div>
                            </div>
                        </div>
                    `;
                }

                function getFilteredPayments(payments) {
                    const search = state.paymentFilters.search.trim().toLowerCase();

                    return payments.filter((payment) => {
                        const matchesSearch = !search || [
                            payment.reference,
                            payment.provider,
                            payment.provider_reference,
                            payment.pricing_rule?.label,
                            payment.incident_report?.reference,
                            payment.incident_report?.signal_code,
                            payment.incident_report?.signal_label,
                        ].filter(Boolean).join(' ').toLowerCase().includes(search);

                        const matchesStatus = !state.paymentFilters.status || payment.status === state.paymentFilters.status;
                        const matchesReceipt = !state.paymentFilters.receipt
                            || (state.paymentFilters.receipt === 'available' && payment.can_download_receipt)
                            || (state.paymentFilters.receipt === 'unavailable' && !payment.can_download_receipt);

                        return matchesSearch && matchesStatus && matchesReceipt;
                    });
                }

                function getDamageStatusLabel(status) {
                    const labels = {
                        submitted: 'Soumis',
                        in_progress: 'En cours',
                        resolved: 'Résolu',
                        rejected: 'Rejeté',
                    };

                    return labels[status] || status || '-';
                }

                function getDamageStatusClass(status) {
                    const classes = {
                        submitted: 'status-report-submitted',
                        in_progress: 'status-report-in-progress',
                        resolved: 'status-report-resolved',
                        rejected: 'status-report-rejected',
                    };

                    return classes[status] || 'status-pill';
                }

                function renderDamageOrganizationFilter(damages) {
                    const select = document.getElementById('damageOrganizationFilter');
                    const currentValue = state.damageFilters.organization || '';
                    const organizations = Array.from(new Set(
                        damages
                            .map((report) => report.organization?.name || report.organization_name || report.network_type || '')
                            .filter(Boolean)
                    )).sort((left, right) => left.localeCompare(right, 'fr', { sensitivity: 'base' }));

                    select.innerHTML = `
                        <option value="">Toutes</option>
                        ${organizations.map((organization) => `<option value="${organization}">${organization}</option>`).join('')}
                    `;

                    select.value = organizations.includes(currentValue) ? currentValue : '';
                }

                function getFilteredDamages(reports) {
                    const search = state.damageFilters.search.trim().toLowerCase();

                    return reports
                        .filter((report) => report.damage_declaration?.declared_at || report.damage_declaration?.summary || report.damage_declaration?.attachment?.temporary_url)
                        .filter((report) => {
                            const organization = String(report.organization?.name || report.organization_name || report.network_type || '');
                            const resolution = String(report.damage_declaration?.resolution_status || '');
                            const hasAttachment = Boolean(report.damage_declaration?.attachment?.temporary_url);
                            const matchesSearch = !search || [
                                report.reference,
                                report.signal_label,
                                report.signal_code,
                                report.damage_declaration?.summary,
                                report.damage_declaration?.notes,
                                organization,
                            ].filter(Boolean).join(' ').toLowerCase().includes(search);
                            const matchesOrganization = !state.damageFilters.organization || organization === state.damageFilters.organization;
                            const matchesResolution = !state.damageFilters.resolution || resolution === state.damageFilters.resolution;
                            const matchesAttachment = !state.damageFilters.attachment
                                || (state.damageFilters.attachment === 'available' && hasAttachment)
                                || (state.damageFilters.attachment === 'unavailable' && !hasAttachment);

                            return matchesSearch && matchesOrganization && matchesResolution && matchesAttachment;
                        });
                }

                function renderDamages(reports) {
                    const list = document.getElementById('damagesList');
                    renderDamageOrganizationFilter(reports);
                    const damages = getFilteredDamages(reports);

                    if (!damages.length) {
                        list.innerHTML = '<div class="mini-card"><div class="fw-bold mb-1">Aucun dommage à afficher</div><div class="muted-label">Les dommages déclarés après résolution apparaîtront ici avec leur suivi.</div></div>';
                        return;
                    }

                    const totalPages = Math.max(1, Math.ceil(damages.length / state.damagesPageSize));
                    state.damagesPage = Math.min(state.damagesPage, totalPages);
                    const start = (state.damagesPage - 1) * state.damagesPageSize;
                    const currentDamages = damages.slice(start, start + state.damagesPageSize);
                    const end = start + currentDamages.length;

                    list.innerHTML = `
                        <div class="vstack gap-3">
                            ${currentDamages.map((report) => `
                                <div class="mini-card">
                                    <div class="row g-3 align-items-start">
                                        <div class="col-lg-8">
                                            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-2">
                                                <div>
                                                    <div class="fw-bold">${report.damage_declaration?.summary || 'Dommage déclaré'}</div>
                                                    <div class="muted-label">${report.reference} · ${report.organization?.name || report.organization_name || report.network_type || 'Institution non définie'}</div>
                                                </div>
                                                <span class="status-pill ${getDamageStatusClass(report.damage_declaration?.resolution_status)}">${getDamageStatusLabel(report.damage_declaration?.resolution_status)}</span>
                                            </div>
                                            <div class="muted-label mb-2">${report.damage_declaration?.notes || 'Aucun détail complémentaire fourni.'}</div>
                                            <div class="muted-label">Declare le ${formatDateTime(report.damage_declaration?.declared_at)}</div>
                                            ${report.damage_declaration?.amount_estimated !== null
                                                ? `<div class="muted-label">Montant estimé: ${formatAmount(report.damage_declaration.amount_estimated)}</div>`
                                                : ''}
                                            ${report.damage_declaration?.resolved_at
                                                ? `<div class="muted-label">Cloture du dommage: ${formatDateTime(report.damage_declaration.resolved_at)}</div>`
                                                : ''}
                                            ${report.damage_declaration?.resolution_notes
                                                ? `<div class="muted-label">Réponse institutionnelle: ${report.damage_declaration.resolution_notes}</div>`
                                                : ''}
                                            ${report.damage_declaration?.purchase_receipt
                                                ? `<div class="muted-label">Reçu: ${report.damage_declaration.purchase_receipt.material_name} · ${formatDateTime(report.damage_declaration.purchase_receipt.purchase_date)} · ${formatAmount(report.damage_declaration.purchase_receipt.amount)}</div>`
                                                : '<div class="muted-label">Aucun reçu d’achat rattaché.</div>'}
                                        </div>
                                        <div class="col-lg-4">
                                            ${report.damage_declaration?.attachment?.temporary_url
                                                ? (String(report.damage_declaration.attachment.mime_type || '').startsWith('image/')
                                                    ? `
                                                        <div class="border rounded-4 p-2 bg-white">
                                                            <img
                                                                src="${report.damage_declaration.attachment.temporary_url}"
                                                                alt="Justificatif du dommage"
                                                                class="img-fluid rounded-4 border"
                                                                style="max-height: 160px; width: 100%; object-fit: contain; background: #f7f9fc;"
                                                            >
                                                        </div>
                                                    `
                                                    : `
                                                        <div class="d-grid gap-2">
                                                            <a
                                                                href="${report.damage_declaration.attachment.temporary_url}"
                                                                target="_blank"
                                                                rel="noopener noreferrer"
                                                                class="btn btn-ghost-premium btn-sm"
                                                            >
                                                                Ouvrir le justificatif
                                                            </a>
                                                        </div>
                                                    `)
                                                : '<div class="muted-label">Aucun justificatif joint.</div>'}
                                            <div class="d-grid gap-2 mt-3">
                                                <button class="btn btn-premium btn-sm" type="button" onclick="window.AcepenPortal.openDamageEditForm(${report.id})">Modifier le dommage</button>
                                                <button class="btn btn-ghost-premium btn-sm" type="button" onclick="window.AcepenPortal.showReportDetails(${report.id})">Voir le détail</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                            <div class="pagination-shell">
                                <div class="pagination-info">Affichage ${start + 1} à ${end} sur ${damages.length} dommage${damages.length > 1 ? 's' : ''}</div>
                                <div class="pagination-actions">
                                    <button class="pagination-chip" type="button" ${state.damagesPage === 1 ? 'disabled' : ''} onclick="window.AcepenPortal.changeDamagesPage(${state.damagesPage - 1})">‹</button>
                                    <div class="small fw-semibold text-secondary">Page ${state.damagesPage} / ${totalPages}</div>
                                    <button class="pagination-chip" type="button" ${state.damagesPage === totalPages ? 'disabled' : ''} onclick="window.AcepenPortal.changeDamagesPage(${state.damagesPage + 1})">›</button>
                                </div>
                            </div>
                        </div>
                    `;
                }

                function getPublicUserDisplayName() {
                    const firstName = String(state.currentUser?.first_name || '').trim();
                    const lastName = String(state.currentUser?.last_name || '').trim();
                    const fullName = [firstName, lastName].filter(Boolean).join(' ');

                    return fullName || state.currentUser?.company_name || 'Usager public';
                }

                function renderReceiptPreview(payment) {
                    const title = document.getElementById('paymentReceiptPreviewTitle');
                    const content = document.getElementById('paymentReceiptPreviewContent');
                    const downloadButton = document.getElementById('paymentReceiptPreviewDownloadButton');
                    const signalLabel = [payment.incident_report?.signal_code, payment.incident_report?.signal_label].filter(Boolean).join(' · ') || 'Signalement public';

                    title.textContent = `Reçu ${payment.reference}`;
                    content.innerHTML = `
                        <div class="mx-auto" style="max-width: 820px;">
                            <div class="shadow-sm" style="border-radius: 28px; overflow: hidden; background: white; border: 1px solid rgba(12, 36, 53, 0.08);">
                                <div class="px-4 px-lg-5 py-4" style="background: var(--acepen-navy); color: white;">
                                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                        <div>
                                            <div class="small text-white-50 fw-semibold mb-2">Justificatif associé à un signalement public</div>
                                            <div class="fs-3 fw-bold">Reçu de paiement</div>
                                        </div>
                                        <div class="text-lg-end">
                                            <div class="small text-white-50 fw-semibold">Numéro de paiement</div>
                                            <div class="fw-bold fs-5">${payment.reference}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4 p-lg-5">
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-6">
                                            <div class="small text-secondary fw-semibold mb-1">Usager</div>
                                            <div class="fw-bold">${getPublicUserDisplayName()}</div>
                                            <div class="muted-label">${state.currentUser?.phone || '-'}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="small text-secondary fw-semibold mb-1">Montant</div>
                                            <div class="fw-bold fs-4">${formatMoney(payment.amount, payment.currency)}</div>
                                            <div class="muted-label">État: ${getPaymentStatusLabel(payment.status)}</div>
                                        </div>
                                    </div>
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-6">
                                            <div class="soft-panel h-100">
                                                <div class="small text-secondary fw-semibold mb-1">Signalement</div>
                                                <div class="fw-bold">${payment.incident_report?.reference || '-'}</div>
                                                <div class="muted-label">${signalLabel}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="soft-panel h-100">
                                                <div class="small text-secondary fw-semibold mb-1">Mode de paiement</div>
                                                <div class="fw-bold">${formatPaymentProvider(payment.provider)}</div>
                                                <div class="muted-label">${payment.provider_reference || 'Confirmation en attente'}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row g-3 mb-4">
                                        <div class="col-md-6">
                                            <div class="small text-secondary fw-semibold mb-1">Date d initiation</div>
                                            <div class="fw-semibold">${formatDateTime(payment.initiated_at)}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="small text-secondary fw-semibold mb-1">Date de confirmation</div>
                                            <div class="fw-semibold">${formatDateTime(payment.paid_at)}</div>
                                        </div>
                                    </div>
                                    <div class="soft-panel">
                                        <div class="small text-secondary fw-semibold mb-1">Détail de facturation</div>
                                        <div class="fw-semibold">${payment.pricing_rule?.label || 'Paiement signalement public'}</div>
                                        <div class="muted-label">Document généré pour consultation avant téléchargement du reçu PDF.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    downloadButton.onclick = () => window.AcepenPortal.downloadReceipt(payment.id, payment.reference);
                }

                function getPublicStatusLabel(status) {
                    const labels = {
                        submitted: 'Soumis',
                        in_progress: 'En cours de traitement',
                        resolved: 'Résolu par l’institution',
                        rejected: 'Non retenu',
                    };

                    return labels[status] || status || '-';
                }

                function getPublicStatusClass(status) {
                    const classes = {
                        submitted: 'status-report-submitted',
                        in_progress: 'status-report-in-progress',
                        resolved: 'status-report-resolved',
                        rejected: 'status-report-rejected',
                    };

                    return classes[status] || '';
                }

                function formatDateTime(value) {
                    if (!value) {
                        return '-';
                    }

                    return new Date(value).toLocaleString();
                }

                function formatAmount(value, currency = 'FCFA') {
                    if (value === null || value === undefined || value === '') {
                        return '-';
                    }

                    return `${Number(value).toLocaleString()} ${currency}`;
                }

                function formatPaymentProvider(value) {
                    if (!value || String(value).toLowerCase() === 'fineopay') {
                        return 'Paiement sécurisé';
                    }

                    return String(value);
                }

                function getPrivilegeDiscountLabel(type) {
                    if (!type) {
                        return 'Avantages carte privilège';
                    }

                    if (type.discount_type === 'fixed_amount') {
                        return formatAmount(type.discount_value, type.currency || 'FCFA');
                    }

                    return `${Number(type.discount_value || 0).toLocaleString()}%`;
                }

                function isFutureDate(value) {
                    return !value || new Date(value).getTime() > Date.now();
                }

                function hasPaidPrivilegeCardSession(card) {
                    if (!card?.id) {
                        return false;
                    }

                    return state.privilegeCardPaymentSessions.some((session) => (
                        session.status === 'paid'
                        && Number(session.card?.id || 0) === Number(card.id)
                    ));
                }

                function isPrivilegeCardWalletEligible(card) {
                    return Boolean(card)
                        && card.status === 'active'
                        && isFutureDate(card.expires_at)
                        && hasPaidPrivilegeCardSession(card);
                }

                function renderPrivilegeCards() {
                    renderActivePrivilegeCard();
                    renderPrivilegeCardTypes();
                    renderPrivilegeCardPayments();
                }

                function renderActivePrivilegeCard() {
                    const box = document.getElementById('activePrivilegeCardBox');
                    const card = state.privilegeCard;

                    if (!box) {
                        return;
                    }

                    if (!card) {
                        box.innerHTML = `
                            <div class="fw-bold mb-1">Aucune carte active</div>
                            <div class="text-white-50">Achetez une carte privilège pour obtenir votre code à présenter et l’ajouter au portefeuille de votre téléphone.</div>
                        `;
                        return;
                    }

                    const canAddToWallet = isPrivilegeCardWalletEligible(card);

                    box.innerHTML = `
                        <div class="row g-3 align-items-center">
                            <div class="col-lg-7">
                                <div class="small text-white-50 fw-semibold mb-1">Carte active</div>
                                <div class="fw-bold fs-3 mb-1">${card.type?.name || 'Carte privilège'}</div>
                                <div class="privilege-active-number mb-2">${card.card_number || '-'}</div>
                                <div class="text-white-50">Code à présenter: <span class="font-monospace">${card.card_uuid || '-'}</span></div>
                                <div class="text-white-50">Expire le ${formatDateTime(card.expires_at)}</div>
                            </div>
                            <div class="col-lg-5">
                                <div class="d-flex gap-2 flex-wrap justify-content-lg-end">
                                    ${canAddToWallet
                                        ? `<button class="btn btn-premium px-4" type="button" onclick="window.AcepenPortal.addPrivilegeCardToWallet(${card.id}, 'ios')">Ajouter sur iPhone</button>
                                           <button class="btn btn-light px-4" type="button" onclick="window.AcepenPortal.addPrivilegeCardToWallet(${card.id}, 'android')">Ajouter sur Android</button>`
                                        : '<div class="privilege-wallet-disabled">Ajout au téléphone disponible uniquement après paiement confirmé, carte active et non expirée.</div>'
                                    }
                                </div>
                            </div>
                        </div>
                    `;
                }

                function renderPrivilegeCardTypes() {
                    const list = document.getElementById('privilegeCardTypesList');

                    if (!list) {
                        return;
                    }

                    if (!state.privilegeCardTypes.length) {
                        list.innerHTML = '<div class="col-12"><div class="mini-card"><div class="fw-bold mb-1">Aucune carte disponible</div><div class="muted-label">Les cartes privilèges actives seront affichées ici dès leur configuration.</div></div></div>';
                        return;
                    }

                    list.innerHTML = state.privilegeCardTypes.map((type) => `
                        <div class="col-md-6 col-xl-4">
                            <div class="privilege-offer-card">
                                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                    <div>
                                        <div class="fw-bold fs-5">${type.name}</div>
                                        <div class="muted-label">${type.duration_months || 12} mois</div>
                                    </div>
                                    <span class="status-pill">${getPrivilegeDiscountLabel(type)}</span>
                                </div>
                                <div class="privilege-price mb-2">${formatAmount(type.price, type.currency)}</div>
                                <div class="vstack gap-2 mb-3">
                                    ${(type.benefits || []).length
                                        ? type.benefits.map((benefit) => `<div class="privilege-benefit">${benefit}</div>`).join('')
                                        : '<div class="privilege-benefit">Avantages associés à cette carte.</div>'
                                    }
                                </div>
                                <button class="btn btn-premium w-100" type="button" onclick="window.AcepenPortal.buyPrivilegeCard(${type.id})">Acheter</button>
                            </div>
                        </div>
                    `).join('');
                }

                function renderPrivilegeCardPayments() {
                    const list = document.getElementById('privilegeCardPaymentsList');

                    if (!list) {
                        return;
                    }

                    if (!state.privilegeCardPaymentSessions.length) {
                        list.innerHTML = '<div class="mini-card"><div class="fw-bold mb-1">Aucun achat de carte</div><div class="muted-label">Vos achats de cartes privilèges apparaîtront ici après initialisation du paiement.</div></div>';
                        return;
                    }

                    list.innerHTML = `
                        <div class="payment-table-shell">
                            <div class="report-table-wrap">
                                <table class="payment-table">
                                    <thead>
                                        <tr>
                                            <th>Numéro d'achat</th>
                                            <th>Carte</th>
                                            <th>Montant</th>
                                            <th>Dates</th>
                                            <th>État</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${state.privilegeCardPaymentSessions.map((session) => `
                                            <tr>
                                                <td>
                                                    <div class="payment-ref">${session.sync_ref}</div>
                                                    <div class="payment-sub">${session.provider_reference || 'Confirmation en attente'}</div>
                                                </td>
                                                <td>
                                                    <div class="payment-ref">${session.type?.name || 'Carte privilège'}</div>
                                                    <div class="payment-sub">${session.card?.card_number || 'Carte non émise'}</div>
                                                    ${session.card?.card_uuid ? `<div class="payment-sub font-monospace">${session.card.card_uuid}</div>` : ''}
                                                </td>
                                                <td>
                                                    <div class="payment-amount">${formatAmount(session.amount, session.currency)}</div>
                                                    <div class="payment-sub">${formatPaymentProvider(session.provider)}</div>
                                                </td>
                                                <td>
                                                    <div class="payment-sub"><strong>Initié:</strong> ${formatDateTime(session.initiated_at)}</div>
                                                    <div class="payment-sub"><strong>Payé:</strong> ${formatDateTime(session.paid_at)}</div>
                                                </td>
                                                <td>
                                                    <span class="status-pill ${getPaymentStatusClass(session.status)}">${getPaymentStatusLabel(session.status)}</span>
                                                    ${session.card ? `<div class="payment-sub mt-1">Carte: ${getPaymentStatusLabel(session.card.status)}</div>` : ''}
                                                </td>
                                                <td>
                                                    <div class="report-actions">
                                                        ${session.status === 'pending' && session.checkout_link
                                                            ? `<a class="btn btn-premium btn-sm px-3" href="${session.checkout_link}" target="_blank" rel="noopener noreferrer">Payer</a>`
                                                            : ''
                                                        }
                                                        ${session.card?.status === 'active' && session.status === 'paid' && isFutureDate(session.card.expires_at)
                                                            ? `<button class="btn btn-ghost-premium btn-sm px-3" type="button" onclick="window.AcepenPortal.addPrivilegeCardToWallet(${session.card.id}, 'ios')">Ajouter sur iPhone</button>
                                                               <button class="btn btn-ghost-premium btn-sm px-3" type="button" onclick="window.AcepenPortal.addPrivilegeCardToWallet(${session.card.id}, 'android')">Ajouter sur Android</button>`
                                                            : ''
                                                        }
                                                    </div>
                                                </td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `;
                }

                async function refreshPrivilegeCards() {
                    const [types, card, sessions] = await Promise.all([
                        apiFetch('/privilege-cards'),
                        apiFetch('/privilege-card'),
                        apiFetch('/privilege-card-payment-sessions'),
                    ]);

                    state.privilegeCardTypes = types.data.cards || [];
                    state.privilegeCard = card.data.card || null;
                    state.privilegeCardPaymentSessions = sessions.data.payment_sessions || [];
                    renderPrivilegeCards();
                }

                function persistPendingReportPayment(session) {
                    state.pendingReportPayment = session || null;

                    if (session) {
                        sessionStorage.setItem(pendingReportPaymentStorageKey, JSON.stringify(session));
                        return;
                    }

                    sessionStorage.removeItem(pendingReportPaymentStorageKey);
                }

                function restorePendingReportPayment() {
                    const storedSession = sessionStorage.getItem(pendingReportPaymentStorageKey);

                    if (!storedSession) {
                        return;
                    }

                    try {
                        const session = JSON.parse(storedSession);
                        if (session?.sync_ref) {
                            showReportPaymentWaiting(session, { openCheckout: false });
                        }
                    } catch (error) {
                        sessionStorage.removeItem(pendingReportPaymentStorageKey);
                    }
                }

                function stopReportPaymentPolling() {
                    if (state.pendingReportPaymentPoller) {
                        window.clearInterval(state.pendingReportPaymentPoller);
                        state.pendingReportPaymentPoller = null;
                    }
                }

                function reportPaymentStatusLabel(status) {
                    const labels = {
                        pending: 'En attente',
                        paid: 'Payé',
                        failed: 'Échec',
                        expired: 'Expiré',
                    };

                    return labels[status] || status || 'En attente';
                }

                function renderReportPaymentWaiting(session, mode = 'pending') {
                    const isDamagePayment = (session?.payment_context || 'report') === 'damage';
                    document.getElementById('reportPaymentWaitingReference').textContent = session?.sync_ref || '-';
                    document.getElementById('reportPaymentWaitingAmount').textContent = formatAmount(session?.amount, session?.currency || 'FCFA');
                    document.getElementById('reportPaymentWaitingProvider').textContent = formatPaymentProvider(session?.provider);
                    document.getElementById('reportPaymentWaitingStatus').textContent = reportPaymentStatusLabel(session?.status || 'pending');

                    const loader = document.getElementById('reportPaymentWaitingLoader');
                    const refreshButton = document.getElementById('refreshReportPaymentStatusButton');
                    const reopenButton = document.getElementById('reopenReportPaymentButton');
                    const cancelButton = document.getElementById('cancelReportPaymentWaitingButton');

                    if (mode === 'paid') {
                        document.getElementById('reportPaymentWaitingSubtitle').textContent = 'Paiement confirmé. Nous mettons à jour votre espace.';
                        document.getElementById('reportPaymentWaitingMessage').textContent = isDamagePayment
                            ? 'Le paiement est confirmé et le dommage a été enregistré.'
                            : 'Le paiement est confirmé et le signalement a été enregistré.';
                        document.getElementById('reportPaymentWaitingLoaderTitle').textContent = isDamagePayment
                            ? 'Mise à jour du dommage'
                            : 'Mise à jour des éléments du signalement';
                        document.getElementById('reportPaymentWaitingLoaderText').textContent = isDamagePayment
                            ? 'Chargement du dommage, du paiement et des notifications.'
                            : 'Chargement du nouveau signalement, du paiement et des notifications.';
                        loader.classList.remove('d-none');
                        refreshButton.disabled = true;
                        reopenButton.classList.add('d-none');
                        cancelButton.classList.add('d-none');
                        return;
                    }

                    if (mode === 'failed') {
                        document.getElementById('reportPaymentWaitingSubtitle').textContent = 'Le paiement n’a pas été confirmé.';
                        document.getElementById('reportPaymentWaitingMessage').textContent = 'Vous pouvez rouvrir le lien de paiement ou annuler cette attente.';
                        document.getElementById('reportPaymentWaitingLoaderTitle').textContent = 'Paiement échoué ou interrompu';
                        document.getElementById('reportPaymentWaitingLoaderText').textContent = isDamagePayment
                            ? 'Aucun dommage n’a été enregistré pour cette session.'
                            : 'Aucun signalement n’a été enregistré pour cette session.';
                        loader.classList.add('d-none');
                        refreshButton.disabled = false;
                        reopenButton.classList.remove('d-none');
                        cancelButton.classList.remove('d-none');
                        return;
                    }

                    document.getElementById('reportPaymentWaitingSubtitle').textContent = 'Gardez cette page ouverte pendant que vous terminez le paiement dans l’autre onglet.';
                    document.getElementById('reportPaymentWaitingMessage').textContent = 'Nous vérifions automatiquement la confirmation du paiement.';
                    document.getElementById('reportPaymentWaitingLoaderTitle').textContent = 'Vérification du paiement en cours';
                    document.getElementById('reportPaymentWaitingLoaderText').textContent = isDamagePayment
                        ? 'Le dommage sera enregistré automatiquement dès que le paiement est confirmé.'
                        : 'Le signalement sera créé automatiquement dès que le paiement est confirmé.';
                    loader.classList.remove('d-none');
                    refreshButton.disabled = false;
                    reopenButton.classList.remove('d-none');
                    cancelButton.classList.remove('d-none');
                }

                async function pollReportPaymentSession(syncRef, { manual = false } = {}) {
                    if (!syncRef) {
                        return null;
                    }

                    const response = await apiFetch(`/payment-sessions/${encodeURIComponent(syncRef)}`);
                    const session = response.data.payment_session;
                    persistPendingReportPayment(session);

                    if (session.status === 'paid') {
                        stopReportPaymentPolling();
                        renderReportPaymentWaiting(session, 'paid');
                        const isDamagePayment = (session.payment_context || 'report') === 'damage';
                        showToast(isDamagePayment ? 'Paiement confirmé. Mise à jour du dommage...' : 'Paiement confirmé. Mise à jour du signalement...');
                        persistPendingReportPayment(null);
                        await refreshDashboard();
                        activatePanel(isDamagePayment ? 'damages' : 'reports');

                        if (session.incident_report_id) {
                            window.AcepenPortal.showReportDetails(Number(session.incident_report_id));
                        }

                        return session;
                    }

                    if (['failed', 'expired'].includes(session.status)) {
                        stopReportPaymentPolling();
                        renderReportPaymentWaiting(session, 'failed');
                        if (manual) {
                            showToast('Paiement non confirmé. Vous pouvez relancer ou annuler.', true);
                        }
                        return session;
                    }

                    renderReportPaymentWaiting(session, 'pending');

                    if (manual) {
                        showToast('Paiement toujours en attente de confirmation.');
                    }

                    return session;
                }

                function startReportPaymentPolling(syncRef) {
                    stopReportPaymentPolling();
                    state.pendingReportPaymentAttempts = 0;
                    state.pendingReportPaymentPoller = window.setInterval(async () => {
                        state.pendingReportPaymentAttempts += 1;

                        try {
                            await pollReportPaymentSession(syncRef);
                        } catch (error) {
                            console.error('Impossible de vérifier le paiement du signalement.', error);
                        }

                        if (state.pendingReportPaymentAttempts >= 40) {
                            stopReportPaymentPolling();
                            showToast('Paiement en attente. Vous pouvez actualiser manuellement.', true);
                        }
                    }, 4000);
                }

                function showReportPaymentWaiting(session, options = {}) {
                    const { openCheckout = true, paymentWindow = null } = options;
                    persistPendingReportPayment(session);
                    renderReportPaymentWaiting(session, session?.status === 'failed' ? 'failed' : 'pending');
                    activatePanel('report-payment');
                    startReportPaymentPolling(session.sync_ref);

                    if (openCheckout && session.checkout_link) {
                        if (paymentWindow && !paymentWindow.closed) {
                            paymentWindow.location.href = session.checkout_link;
                            return;
                        }

                        const openedWindow = window.open(session.checkout_link, '_blank', 'noopener,noreferrer');
                        if (!openedWindow) {
                            showToast('Le navigateur a bloqué l’ouverture du paiement. Utilisez le bouton Rouvrir le paiement.', true);
                        }
                    }
                }

                function getSubscriptionStatusLabel(status) {
                    const labels = {
                        pending: 'Paiement en attente',
                        active: 'Actif',
                        expired: 'Expiré',
                        cancelled: 'Annule',
                        suspended: 'Suspendu',
                        payment_failed: 'Paiement échoué',
                    };

                    return labels[status] || 'Non actif';
                }

                function isSubscriptionUsable(subscription = state.subscription) {
                    return true;
                }

                function getPendingSubscriptionPayment() {
                    return state.subscriptionPayments.find((payment) => payment.status === 'pending') || null;
                }

                function renderSubscriptionStatus() {
                    const subscription = state.subscription;
                    const active = isSubscriptionUsable(subscription);
                    const pendingPayment = getPendingSubscriptionPayment();
                    const statusLabel = 'Accès libre';
                    const details = 'Vous pouvez effectuer vos signalements sans abonnement.';

                    document.getElementById('topbarSubscriptionBadge').textContent = statusLabel;
                    document.getElementById('subscriptionOverviewText').textContent = details;
                    document.getElementById('subscriptionOverviewButton').textContent = 'Accès libre';
                    document.getElementById('openSubscriptionModalButton').textContent = 'Accès libre';
                    document.getElementById('openSubscriptionModalButton').classList.add('btn-ghost-premium');
                    document.getElementById('openSubscriptionModalButton').classList.remove('btn-premium');
                    renderMemberWalletCard();

                    document.getElementById('subscriptionPromptStatus').textContent = statusLabel;
                    document.getElementById('subscriptionPromptDetails').textContent = details;
                    document.getElementById('subscriptionPromptBadge').textContent = 'libre';

                    const paymentPanel = document.getElementById('subscriptionPaymentPanel');
                    paymentPanel.classList.toggle('d-none', !pendingPayment);
                    if (pendingPayment) {
                        document.getElementById('subscriptionPaymentReference').textContent = pendingPayment.reference;
                        document.getElementById('subscriptionPaymentAmount').textContent = formatAmount(pendingPayment.amount, pendingPayment.currency);
                    }

                    document.getElementById('startSubscriptionPaymentButton').classList.add('d-none');
                    document.getElementById('confirmSubscriptionPaymentButton').classList.add('d-none');
                    renderSubscriptionHistory();
                }

                function renderSubscriptionHistory() {
                    const list = document.getElementById('subscriptionHistoryList');

                    if (!list) {
                        return;
                    }

                    if (!state.subscriptionHistory.length) {
                        list.innerHTML = '<div class="muted-label">Aucun abonnement initié pour le moment.</div>';
                        return;
                    }

                    list.innerHTML = state.subscriptionHistory.map((subscription) => {
                        const payments = subscription.payments || [];
                        const latestPayment = payments[0] || null;

                        return `
                            <div class="soft-panel">
                                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                    <div>
                                        <div class="fw-bold">${subscription.plan?.name || 'Abonnement annuel UP'}</div>
                                        <div class="muted-label">${formatAmount(subscription.amount, subscription.currency)} · ${subscription.plan?.duration_months || 12} mois</div>
                                        <div class="muted-label">Debut: ${formatDateTime(subscription.start_date)} · Fin: ${formatDateTime(subscription.end_date)}</div>
                                        <div class="muted-label">Crée le ${formatDateTime(subscription.created_at)}</div>
                                    </div>
                                    <span class="status-pill">${getSubscriptionStatusLabel(subscription.status)}</span>
                                </div>
                                <div class="mt-3">
                                    <div class="small text-secondary fw-semibold mb-1">Paiement associé</div>
                                    ${latestPayment
                                        ? `<div class="muted-label">${latestPayment.reference} · ${getPaymentStatusLabel(latestPayment.status)} · ${formatAmount(latestPayment.amount, latestPayment.currency)}</div>`
                                        : '<div class="muted-label">Aucun paiement associé.</div>'}
                                </div>
                            </div>
                        `;
                    }).join('');
                    renderSubscriptionHistoryPanel();
                }

                function getFilteredSubscriptionHistory() {
                    const search = state.subscriptionFilters.search.trim().toLowerCase();

                    return state.subscriptionHistory.filter((subscription) => {
                        const payments = subscription.payments || [];
                        const paymentText = payments.map((payment) => [
                            payment.reference,
                            payment.status,
                            payment.amount,
                            payment.currency,
                            payment.provider,
                            payment.provider_reference,
                        ].filter(Boolean).join(' ')).join(' ');

                        const searchableText = [
                            subscription.plan?.name,
                            subscription.plan?.code,
                            subscription.status,
                            subscription.amount,
                            subscription.currency,
                            paymentText,
                        ].filter(Boolean).join(' ').toLowerCase();

                        const matchesSearch = !search || searchableText.includes(search);
                        const matchesStatus = !state.subscriptionFilters.status || subscription.status === state.subscriptionFilters.status;
                        const matchesPayment = !state.subscriptionFilters.payment || payments.some((payment) => payment.status === state.subscriptionFilters.payment);

                        return matchesSearch && matchesStatus && matchesPayment;
                    });
                }

                function renderSubscriptionHistoryPanel() {
                    const list = document.getElementById('subscriptionsList');

                    if (!list) {
                        return;
                    }

                    if (!state.subscriptionHistory.length) {
                        list.innerHTML = '<div class="mini-card"><div class="fw-bold mb-1">Aucun abonnement</div><div class="muted-label">Tes souscriptions annuelles apparaîtront ici avec leur état et leur paiement.</div></div>';
                        return;
                    }

                    const filteredSubscriptions = getFilteredSubscriptionHistory();
                    const totalPages = Math.max(1, Math.ceil(filteredSubscriptions.length / state.subscriptionHistoryPageSize));
                    state.subscriptionHistoryPage = Math.min(state.subscriptionHistoryPage, totalPages);

                    if (!filteredSubscriptions.length) {
                        list.innerHTML = '<div class="mini-card"><div class="fw-bold mb-1">Aucun abonnement ne correspond aux filtres</div><div class="muted-label">Modifie la recherche, l’état ou le paiement pour retrouver une souscription.</div></div>';
                        return;
                    }

                    const start = (state.subscriptionHistoryPage - 1) * state.subscriptionHistoryPageSize;
                    const currentSubscriptions = filteredSubscriptions.slice(start, start + state.subscriptionHistoryPageSize);
                    const end = start + currentSubscriptions.length;

                    list.innerHTML = `
                        <div class="report-table-shell">
                            <div class="report-table-wrap">
                                <table class="payment-table">
                                    <thead>
                                        <tr>
                                            <th>Plan</th>
                                            <th>Période</th>
                                            <th>Montant</th>
                                            <th>État</th>
                                            <th>Paiement</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${currentSubscriptions.map((subscription) => {
                                            const latestPayment = (subscription.payments || [])[0] || null;

                                            return `
                                                <tr>
                                                    <td>
                                                        <div class="payment-ref">${subscription.plan?.name || 'Abonnement annuel UP'}</div>
                                                        <div class="payment-sub">${subscription.plan?.code || '-'} · ${subscription.plan?.duration_months || 12} mois</div>
                                                        <div class="payment-sub">Crée le ${formatDateTime(subscription.created_at)}</div>
                                                    </td>
                                                    <td>
                                                        <div class="payment-sub">Debut: ${formatDateTime(subscription.start_date)}</div>
                                                        <div class="payment-sub">Fin: ${formatDateTime(subscription.end_date)}</div>
                                                        <div class="payment-sub">${subscription.grâce_period_days || 0} jour(s) de grâce</div>
                                                    </td>
                                                    <td><div class="payment-amount">${formatAmount(subscription.amount, subscription.currency)}</div></td>
                                                    <td><span class="status-pill">${getSubscriptionStatusLabel(subscription.status)}</span></td>
                                                    <td>
                                                        ${latestPayment
                                                            ? `
                                                                <div class="payment-ref">${latestPayment.reference}</div>
                                                                <div class="payment-sub">${getPaymentStatusLabel(latestPayment.status)} · ${formatAmount(latestPayment.amount, latestPayment.currency)}</div>
                                                                <div class="payment-sub">${latestPayment.paid_at ? `Confirmé le ${formatDateTime(latestPayment.paid_at)}` : 'Paiement non confirmé'}</div>
                                                            `
                                                            : '<div class="payment-sub">Aucun paiement associé</div>'}
                                                    </td>
                                                </tr>
                                            `;
                                        }).join('')}
                                    </tbody>
                                </table>
                            </div>
                            <div class="pagination-shell">
                                <div class="pagination-info">Affichage ${start + 1} à ${end} sur ${filteredSubscriptions.length} abonnement${filteredSubscriptions.length > 1 ? 's' : ''}</div>
                                <div class="pagination-actions">
                                    <button class="pagination-chip" type="button" ${state.subscriptionHistoryPage === 1 ? 'disabled' : ''} onclick="window.AcepenPortal.changeSubscriptionsPage(${state.subscriptionHistoryPage - 1})">‹</button>
                                    <div class="small fw-semibold text-secondary">Page ${state.subscriptionHistoryPage} / ${totalPages}</div>
                                    <button class="pagination-chip" type="button" ${state.subscriptionHistoryPage === totalPages ? 'disabled' : ''} onclick="window.AcepenPortal.changeSubscriptionsPage(${state.subscriptionHistoryPage + 1})">›</button>
                                </div>
                            </div>
                        </div>
                    `;
                }

                async function refreshSubscriptionData() {
                    const [subscriptionResponse, historyResponse, paymentsResponse] = await Promise.all([
                        apiFetch('/subscription'),
                        apiFetch('/subscriptions'),
                        apiFetch('/subscription/payments'),
                    ]);

                    state.subscription = subscriptionResponse.data.subscription;
                    state.subscriptionHistory = historyResponse.data.subscriptions || [];
                    state.subscriptionPayments = paymentsResponse.data.payments || [];
                    renderSubscriptionStatus();
                }

                function shouldPromptSubscription() {
                    return false;
                }

                function openSubscriptionPrompt(force = false) {
                    renderSubscriptionStatus();
                }

                async function startSubscriptionPayment() {
                    const button = document.getElementById('startSubscriptionPaymentButton');
                    button.disabled = true;
                    button.dataset.originalText = button.dataset.originalText || button.textContent;
                    button.textContent = 'Initialisation...';

                    try {
                        const response = await apiFetch('/subscription/payments', { method: 'POST' });
                        state.subscription = response.data.payment.subscription || state.subscription;
                        await refreshSubscriptionData();
                        showToast(response.message || 'Paiement d’abonnement initialise.');
                    } catch (error) {
                        showToast(error.message, true);
                    } finally {
                        button.disabled = false;
                        button.textContent = button.dataset.originalText || 'Prendre un abonnement';
                    }
                }

                async function confirmSubscriptionPayment() {
                    const payment = getPendingSubscriptionPayment();

                    if (!payment) {
                        showToast('Aucun paiement d’abonnement en attente.', true);
                        return;
                    }

                    const button = document.getElementById('confirmSubscriptionPaymentButton');
                    button.disabled = true;
                    button.dataset.originalText = button.dataset.originalText || button.textContent;
                    button.textContent = 'Confirmation...';

                    try {
                        const response = await apiFetch(`/subscription/payments/${payment.id}/confirm`, { method: 'POST' });
                        state.subscription = response.data.subscription;
                        await refreshSubscriptionData();
                        subscriptionPromptModal?.hide();
                        showToast(response.message || 'Abonnement active avec succes.');
                    } catch (error) {
                        showToast(error.message, true);
                    } finally {
                        button.disabled = false;
                        button.textContent = button.dataset.originalText || 'Confirmer le paiement';
                    }
                }

                function getResolutionDurationText(report) {
                    if (!report.resolved_at || !report.created_at) {
                        return 'En attente de résolution';
                    }

                    const start = new Date(report.created_at);
                    const end = new Date(report.resolved_at);
                    const minutes = Math.max(0, Math.round((end.getTime() - start.getTime()) / 60000));
                    const hours = Math.floor(minutes / 60);
                    const remainingMinutes = minutes % 60;

                    if (hours === 0) {
                        return `${remainingMinutes} min`;
                    }

                    if (remainingMinutes === 0) {
                        return `${hours}h`;
                    }

                    return `${hours}h ${remainingMinutes} min`;
                }

                function getSlaText(report) {
                    if (!report.target_sla_hours) {
                        return 'Délai non configuré';
                    }

                    return `${report.target_sla_hours}h`;
                }

                function getSlaRespectText(report) {
                    if (report.sla?.is_respected === true) {
                        return 'Le délai de résolution a été respecté.';
                    }

                    if (report.sla?.is_respected === false) {
                        return 'Le délai de résolution n’a pas été respecté.';
                    }

                    if (!report.target_sla_hours || !report.resolved_at || !report.created_at) {
                        return 'Évaluation du délai indisponible';
                    }

                    const start = new Date(report.created_at);
                    const end = new Date(report.resolved_at);
                    const elapsedHours = (end.getTime() - start.getTime()) / 3600000;

                    return elapsedHours <= Number(report.target_sla_hours)
                        ? 'Résolution dans le délai prévu'
                        : 'Résolution hors délai prévu';
                }

                function getSlaImportanceText(report) {
                    return report.sla?.importance?.label || 'Priorité non définie';
                }

                function getSlaImportanceDetails(report) {
                    return report.sla?.importance?.details || 'Le délai cible de traitement protège les usagers et limite l’aggravation du sinistre.';
                }

                function getDamageDeclarationLabel(report) {
                    if (report.damage_declaration?.declared_at) {
                        return `Dommage enregistré le ${formatDateTime(report.damage_declaration.declared_at)}`;
                    }

                    if (report.damage_declaration?.can_declare) {
                        return `Tu peux déclarer les dommages liés à ce sinistre jusqu’au ${formatDateTime(report.damage_declaration.available_until)}.`;
                    }

                    if (report.damage_declaration?.window_expired) {
                        return 'Le délai maximum de 24h après ta confirmation est dépassé. La déclaration de dommage n’est plus possible.';
                    }

                    return 'La déclaration de dommage sera disponible après ta confirmation de résolution.';
                }

                function canSubmitIncidentRex(report) {
                    return ['resolved', 'closed'].includes(report.status) || report.resolution_confirmation?.status === 'confirmed';
                }

                function canSubmitDamageRex(report) {
                    return !!report.damage_declaration?.declared_at
                        && ['resolved', 'rejected', 'compensated', 'closed'].includes(report.damage_declaration?.resolution_status);
                }

                function canSubmitCaseRex(repairCase) {
                    return ['approved', 'rejected', 'compensated', 'closed'].includes(repairCase.status);
                }

                function resetDamageAttachmentPreview() {
                    document.getElementById('damageAttachmentPreviewWrap').classList.add('d-none');
                    document.getElementById('damageAttachmentPreviewImage').classList.add('d-none');
                    document.getElementById('damageAttachmentPreviewImage').removeAttribute('src');
                    document.getElementById('damageAttachmentPreviewFile').classList.add('d-none');
                    document.getElementById('damageAttachmentPreviewFile').textContent = '';
                }

                function renderDamageAttachmentPreview(file) {
                    const wrap = document.getElementById('damageAttachmentPreviewWrap');
                    const image = document.getElementById('damageAttachmentPreviewImage');
                    const fileLabel = document.getElementById('damageAttachmentPreviewFile');

                    if (!file) {
                        resetDamageAttachmentPreview();
                        return;
                    }

                    wrap.classList.remove('d-none');

                    if ((file.type || '').startsWith('image/')) {
                        image.src = URL.createObjectURL(file);
                        image.classList.remove('d-none');
                        fileLabel.classList.add('d-none');
                        fileLabel.textContent = '';
                        return;
                    }

                    image.classList.add('d-none');
                    image.removeAttribute('src');
                    fileLabel.classList.remove('d-none');
                    fileLabel.textContent = file.name;
                }

                function getResolutionLabel(report) {
                    if (report.resolution_confirmation?.status === 'confirmed') {
                        return 'Résolution confirmée';
                    }

                    if (report.status === 'resolved') {
                        return 'Action attendue de votre part';
                    }

                    if (report.status === 'rejected') {
                        return 'Signalement non retenu';
                    }

                    if (report.resolution_confirmation?.can_confirm) {
                        return 'Confirmation possible';
                    }

                    return 'Traitement en cours';
                }

                function getResolutionStatusClass(report) {
                    if (report.damage_declaration?.window_expired) {
                        return 'status-resolution-expired';
                    }

                    if (report.resolution_confirmation?.status === 'confirmed') {
                        return 'status-resolution-confirmed';
                    }

                    if (report.status === 'resolved') {
                        return 'status-resolution-waiting';
                    }

                    return 'status-resolution-pending';
                }

                function getResolutionHelpText(report) {
                    if (report.resolution_confirmation?.status === 'confirmed') {
                        return 'Tu as confirmé que le problème a bien été résolu.';
                    }

                    if (report.status === 'resolved') {
                        return report.official_response || 'L’institution indique que le problème est résolu. Vérifie puis confirme si tout est revenu à la normale.';
                    }

                    if (report.status === 'rejected') {
                        return report.official_response || 'L’institution n’a pas retenu ce signalement.';
                    }

                    if (report.resolution_confirmation?.can_confirm) {
                        return 'Si le problème est déjà résolu sur le terrain, tu peux confirmer la résolution sans attendre la validation institutionnelle.';
                    }

                    return 'Ton signalement est toujours en cours de traitement par l’institution.';
                }

                function getResolutionFilterValue(report) {
                    if (report.resolution_confirmation?.status === 'confirmed') {
                        return 'confirmed';
                    }

                    if (report.status === 'resolved') {
                        return 'institution_resolved';
                    }

                    if (report.status === 'rejected') {
                        return 'rejected';
                    }

                    return 'awaiting_institution';
                }

                function getFilteredReports(reports) {
                    const search = state.reportFilters.search.trim().toLowerCase();

                    return reports.filter((report) => {
                        const matchesSearch = !search || [
                            report.application?.name,
                            report.application?.code,
                            report.reference,
                            report.signal_code,
                            report.signal_label,
                            report.incident_type,
                            report.description,
                            report.location?.address,
                            report.location?.commune,
                            report.location?.city,
                            report.location?.country,
                        ].filter(Boolean).join(' ').toLowerCase().includes(search);

                        const matchesStatus = !state.reportFilters.status || report.status === state.reportFilters.status;
                        const matchesPayment = !state.reportFilters.payment || report.payment_status === state.reportFilters.payment;
                        const reportOrganization = String(report.organization?.name || report.organization_name || report.network_type || '');
                        const matchesOrganization = !state.reportFilters.organization || reportOrganization === state.reportFilters.organization;
                        const matchesResolution = !state.reportFilters.resolution || getResolutionFilterValue(report) === state.reportFilters.resolution;

                        return matchesSearch && matchesStatus && matchesPayment && matchesOrganization && matchesResolution;
                    });
                }

                function renderReportOrganizationFilter(reports) {
                    const select = document.getElementById('reportOrganizationFilter');
                    const currentValue = state.reportFilters.organization || '';
                    const organizations = Array.from(new Set(
                        reports
                            .map((report) => report.organization?.name || report.organization_name || report.network_type || '')
                            .filter(Boolean)
                    )).sort((left, right) => left.localeCompare(right, 'fr', { sensitivity: 'base' }));

                    select.innerHTML = `
                        <option value="">Toutes</option>
                        ${organizations.map((organization) => `<option value="${organization}">${organization}</option>`).join('')}
                    `;

                    select.value = organizations.includes(currentValue) ? currentValue : '';
                }

                function renderReportDetails(report) {
                    document.getElementById('reportDetailTitle').textContent = `${report.reference} · ${report.signal_label || report.incident_type || 'Signalement'}`;
                    document.getElementById('reportDetailContent').innerHTML = `
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <div class="mini-card h-100">
                                    <div class="small text-secondary fw-semibold mb-2">Signalement</div>
                                    <div class="fw-bold fs-5 mb-2">${report.signal_label || report.incident_type}</div>
                                    <div class="muted-label mb-3">${report.description || 'Aucune description fournie.'}</div>
                                    <div class="soft-panel mb-3">
                                        <div class="small text-secondary fw-semibold mb-1">État</div>
                                        <div class="fw-semibold">${getPublicStatusLabel(report.status)}</div>
                                    </div>
                                    <div class="soft-panel mb-3">
                                        <div class="small text-secondary fw-semibold mb-1">Catégorie</div>
                                        <div class="fw-semibold">${report.application?.name || '-'}</div>
                                        <div class="muted-label">${report.application?.name || 'Aucune catégorie définie'}</div>
                                    </div>
                                    <div class="soft-panel mb-3">
                                        <div class="small text-secondary fw-semibold mb-1">Date du signalement</div>
                                        <div class="fw-semibold">${formatDateTime(report.created_at)}</div>
                                    </div>
                                    <div class="soft-panel mb-3">
                                        <div class="small text-secondary fw-semibold mb-1">Localisation</div>
                                        <div class="fw-semibold">${[report.location.country, report.location.city, report.location.commune].filter(Boolean).join(' · ') || '-'}</div>
                                        <div class="muted-label">${report.location.address || 'Adresse non renseignée'}</div>
                                        <div class="muted-label">${report.location.latitude && report.location.longitude ? 'Position renseignée' : 'Position non renseignée'}</div>
                                    </div>
                                    <div class="soft-panel">
                                        <div class="small text-secondary fw-semibold mb-1">Identifiant associé</div>
                                        <div class="fw-semibold">${report.meter?.meter_number || '-'}</div>
                                        <div class="muted-label">${report.meter?.label || report.meter?.organization_name || report.meter?.network_type || '-'}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mini-card h-100">
                                    <div class="small text-secondary fw-semibold mb-2">Résolution et solution</div>
                                    <div class="soft-panel mb-3">
                                        <div class="small text-secondary fw-semibold mb-1">État de résolution</div>
                                        <div class="fw-semibold"><span class="status-pill ${getResolutionStatusClass(report)}">${getResolutionLabel(report)}</span></div>
                                        <div class="muted-label">${getResolutionHelpText(report)}</div>
                                    </div>
                                    <div class="soft-panel mb-3">
                                        <div class="small text-secondary fw-semibold mb-1">Temps de résolution</div>
                                        <div class="fw-semibold">${getResolutionDurationText(report)}</div>
                                        <div class="muted-label">${report.resolved_at ? `Problème marqué comme résolu le ${formatDateTime(report.resolved_at)}` : 'Le problème n’est pas encore marqué comme résolu.'}</div>
                                    </div>
                                    <div class="soft-panel mb-3">
                                        <div class="small text-secondary fw-semibold mb-1">Délai de traitement prévu</div>
                                        <div class="fw-semibold">${getSlaText(report)} · ${report.sla?.label || getSlaRespectText(report)}</div>
                                        <div class="muted-label">${getSlaRespectText(report)}</div>
                                        <div class="muted-label">Importance: ${getSlaImportanceText(report)}</div>
                                        <div class="muted-label">${getSlaImportanceDetails(report)}</div>
                                        <div class="muted-label">${report.sla?.elapsed_hours !== null && report.sla?.elapsed_hours !== undefined ? `Temps constaté: ${report.sla.elapsed_hours}h` : 'Le temps exact sera calculé une fois la résolution complète.'}</div>
                                    </div>
                                    <div class="soft-panel">
                                        <div class="small text-secondary fw-semibold mb-1">Détail de la solution</div>
                                        <div class="fw-semibold mb-1">${report.official_response ? 'Réponse institutionnelle disponible' : 'Aucune réponse officielle pour le moment'}</div>
                                        <div class="muted-label">${report.official_response || 'L’institution n’a pas encore détaillé la solution appliquée.'}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mini-card">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <div class="small text-secondary fw-semibold mb-1">Paiement</div>
                                            <div class="fw-semibold">${report.payment_status === 'paid' ? 'Payé' : 'En attente'}</div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="small text-secondary fw-semibold mb-1">Paiement confirmé</div>
                                            <div class="fw-semibold">${formatDateTime(report.paid_at)}</div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="small text-secondary fw-semibold mb-1">Résolution confirmée</div>
                                            <div class="fw-semibold">${report.resolution_confirmation?.status === 'confirmed' ? 'Oui' : 'Non'}</div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="small text-secondary fw-semibold mb-1">Confirmation usager</div>
                                            <div class="fw-semibold">${formatDateTime(report.resolution_confirmation?.confirmed_at)}</div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="small text-secondary fw-semibold mb-1">Respect du délai</div>
                                            <div class="fw-semibold">${report.sla?.is_respected === true ? 'Oui' : (report.sla?.is_respected === false ? 'Non' : 'En attente')}</div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="small text-secondary fw-semibold mb-1">Importance du délai</div>
                                            <div class="fw-semibold">${getSlaImportanceText(report)}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mini-card">
                                    <div class="row g-3 align-items-center">
                                        <div class="col-lg-8">
                                            <div class="small text-secondary fw-semibold mb-1">Déclaration de dommage après résolution</div>
                                            <div class="fw-semibold mb-1">${report.damage_declaration?.summary || 'Aucun dommage enregistré pour le moment'}</div>
                                            <div class="muted-label">${getDamageDeclarationLabel(report)}</div>
                                            ${report.damage_declaration?.amount_estimated !== null
                                                ? `<div class="muted-label">Montant estimé: ${formatAmount(report.damage_declaration.amount_estimated)}</div>`
                                                : ''}
                                            ${report.damage_declaration?.notes
                                                ? `<div class="muted-label">${report.damage_declaration.notes}</div>`
                                                : ''}
                                            ${report.damage_declaration?.attachment?.temporary_url
                                                ? `
                                                    <div class="mt-3">
                                                        <div class="small text-secondary fw-semibold mb-2">Justificatif joint</div>
                                                        ${String(report.damage_declaration.attachment.mime_type || '').startsWith('image/')
                                                            ? `
                                                                <div class="vstack gap-2">
                                                                    <div class="muted-label">${report.damage_declaration.attachment.name || 'Image du dommage'}</div>
                                                                    <img
                                                                        src="${report.damage_declaration.attachment.temporary_url}"
                                                                        alt="Justificatif du dommage"
                                                                        class="img-fluid rounded-4 border"
                                                                        style="max-height: 420px; width: 100%; object-fit: contain; background: #f7f9fc;"
                                                                    >
                                                                </div>
                                                            `
                                                            : `
                                                                <div class="d-flex flex-wrap align-items-center gap-3">
                                                                    <div class="muted-label">${report.damage_declaration.attachment.name || 'Document joint'}</div>
                                                                    <a
                                                                        href="${report.damage_declaration.attachment.temporary_url}"
                                                                        target="_blank"
                                                                        rel="noopener noreferrer"
                                                                        class="btn btn-ghost-premium btn-sm px-3"
                                                                    >
                                                                        Ouvrir le justificatif
                                                                    </a>
                                                                </div>
                                                            `}
                                                    </div>
                                                `
                                                : ''}
                                        </div>
                                        <div class="col-lg-4 text-lg-end">
                                            ${report.damage_declaration?.can_declare
                                                ? `<button class="btn btn-premium px-4" type="button" onclick="window.AcepenPortal.openDamageForm(${report.id})">Enregistrer le dommage</button>`
                                                : ''}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }

                async function loadReferenceData() {
                    populateDialCodeSelects();
                    const response = await fetch('/api/v1/public/locations', { headers: { Accept: 'application/json' } });
                    const data = await response.json();
                    state.countries = data.data.countries || [];
                    state.cities = state.countries.flatMap((country) => (country.cities || []).map((city) => ({
                        ...city,
                        country_id: country.id,
                        country_name: country.name,
                    })));
                    state.communes = state.countries.flatMap((country) => (country.cities || []).flatMap((city) => (city.communes || []).map((commune) => ({
                        ...commune,
                        city_id: city.id,
                        city_name: city.name,
                        country_id: country.id,
                        country_name: country.name,
                    }))));
                    populateCommuneSelects();
                    populateReportLocationSelects();
                    const signalResponse = await fetch('/api/v1/public/signal-types', { headers: { Accept: 'application/json' } });
                    const signalData = await signalResponse.json().catch(() => ({}));
                    state.signalTypes = signalData?.data?.signal_types || [];
                    renderSignalOptions();
                    initGooglePlacesAutocomplete();
                }

                async function refreshDashboard() {
                    await loadReferenceData();
                    const [me, meters, household, reports, payments, purchaseReceipts, subscription, discountCard, privilegeCardTypes, privilegeCard, privilegeCardPaymentSessions, subscriptionHistory, subscriptionPayments, rexFeedbacks, invitations, reparationCases, notifications] = await Promise.all([
                        apiFetch('/me'),
                        apiFetch('/meters'),
                        apiFetch('/households/me'),
                        apiFetch('/reports'),
                        apiFetch('/payments'),
                        apiFetch('/purchase-receipts'),
                        apiFetch('/subscription'),
                        apiFetch('/discount-card'),
                        apiFetch('/privilege-cards'),
                        apiFetch('/privilege-card'),
                        apiFetch('/privilege-card-payment-sessions'),
                        apiFetch('/subscriptions'),
                        apiFetch('/subscription/payments'),
                        apiFetch('/rex-feedbacks'),
                        apiFetch('/households/invitations/pending'),
                        apiFetch('/reparation-cases'),
                        apiFetch('/notifications?limit=100'),
                    ]);
                    renderUser(me.data.user);
                    void registerPublicWebPushToken();
                    state.subscription = subscription.data.subscription;
                    state.discountCard = discountCard.data.card || null;
                    state.privilegeCardTypes = privilegeCardTypes.data.cards || [];
                    state.privilegeCard = privilegeCard.data.card || null;
                    state.privilegeCardPaymentSessions = privilegeCardPaymentSessions.data.payment_sessions || [];
                    state.subscriptionHistory = subscriptionHistory.data.subscriptions || [];
                    state.subscriptionPayments = subscriptionPayments.data.payments || [];
                    state.rexFeedbacks = rexFeedbacks.data.feedbacks || [];
                    state.notifications = notifications.data.notifications || [];
                    state.unreadNotificationsCount = notifications.data.unread_count || 0;
                    renderSubscriptionStatus();
                    renderMeters(meters.data.meters);
                    renderPurchaseReceipts(purchaseReceipts.data.purchase_receipts || []);
                    const households = household.data.households || [];
                    const selectedHousehold = households.find((item) => String(item.id) === String(state.selectedHouseholdId))
                        || household.data.household;
                    renderHousehold(selectedHousehold, households);
                    renderReports(reports.data.reports);
                    renderDamages(reports.data.reports);
                    renderPayments(payments.data.payments);
                    renderPrivilegeCards();
                    renderRexFeedbacks(state.rexFeedbacks);
                    renderNotifications();
                    renderIncomingHouseholdInvitations(invitations.data.invitations);
                    renderReparationCases(reparationCases.data.reparation_cases);
                    restorePendingReportPayment();
                }

                window.AcepenPortal = {
                    prefillMeter(id) {
                        const meter = state.meters.find((item) => item.id === id);
                        if (!meter) return;
                        activatePanel('meters');
                        const form = document.getElementById('meterForm');
                        form.dataset.editId = String(meter.id);
                        populateMeterApplicationOptions(meter.application_code || null);
                        populateMeterOrganizationOptions(meter.organization_id || null);
                        document.getElementById('meterApplicationId').disabled = true;
                        document.getElementById('meterOrganizationId').disabled = true;
                        form.meter_number.value = meter.meter_number;
                        form.meter_number.disabled = true;
                        form.label.value = meter.label || '';
                        const meterCity = meter.city || state.communes.find((commune) => commune.name === meter.commune)?.city_name || null;
                        populateMeterCityOptions(meterCity);
                        populateMeterCommuneOptions(meter.commune || null);
                        form.commune.value = meter.commune || '';
                        populateMeterNeighborhoodOptions(meter.neighborhood || '', meter.sub_neighborhood || '');
                        form.neighborhood.value = meter.neighborhood || '';
                        form.sub_neighborhood.value = meter.sub_neighborhood || '';
                        form.address.value = meter.address || '';
                        document.getElementById('meterLatitude').value = meter.latitude || '';
                        document.getElementById('meterLongitude').value = meter.longitude || '';
                        document.getElementById('meterAccuracy').value = meter.location_accuracy || '';
                        document.getElementById('meterLocationSource').value = meter.location_source || '';
                        form.is_primary.checked = Boolean(meter.is_primary);
                        form.querySelector('button[type="submit"]').textContent = 'Mettre à jour l’identifiant';
                        bootstrap.Collapse.getOrCreateInstance(document.getElementById('meterFormWrap')).show();
                    },
                    async buyPrivilegeCard(typeId) {
                        const paymentWindow = window.open('', '_blank', 'noopener,noreferrer');

                        try {
                            const response = await apiFetch(`/privilege-cards/${typeId}/payments`, { method: 'POST' });
                            const checkoutLink = response.data?.checkout_link || response.data?.payment_session?.checkout_link;

                            showToast(response.message || 'Lien de paiement carte privilège généré.');

                            if (checkoutLink) {
                                if (paymentWindow) {
                                    paymentWindow.location.href = checkoutLink;
                                } else {
                                    window.open(checkoutLink, '_blank', 'noopener,noreferrer');
                                }
                            } else {
                                paymentWindow?.close();
                            }

                            await refreshPrivilegeCards();
                            activatePanel('privilege-cards');
                        } catch (error) {
                            paymentWindow?.close();
                            showToast(error.message, true);
                        }
                    },
                    async addPrivilegeCardToWallet(cardId, platform) {
                        try {
                            const card = state.privilegeCard?.id === cardId
                                ? state.privilegeCard
                                : state.privilegeCardPaymentSessions.find((session) => Number(session.card?.id || 0) === Number(cardId))?.card;

                            if (!isPrivilegeCardWalletEligible(card)) {
                                showToast('Ajout au téléphone disponible uniquement après paiement confirmé, carte active et non expirée.', true);
                                return;
                            }

                            const response = await apiFetch(`/privilege-cards/${cardId}/wallet-pass?platform=${encodeURIComponent(platform)}`);
                            const walletUrl = response.data?.url;

                            if (!walletUrl) {
                                showToast('Lien d’ajout indisponible.', true);
                                return;
                            }

                            window.open(walletUrl, '_blank', 'noopener,noreferrer');
                            showToast(platform === 'ios' ? 'Lien d’ajout iPhone généré.' : 'Lien d’ajout Android généré.');
                        } catch (error) {
                            showToast(error.message, true);
                        }
                    },
                    async payReport(reportId) {
                        try {
                            activatePanel('reports');
                            const initResponse = await apiFetch(`/reports/${reportId}/payments`, { method: 'POST' });
                            const paymentId = initResponse.data.payment.id;
                            const confirmResponse = await apiFetch(`/payments/${paymentId}/confirm`, { method: 'POST' });
                            showToast(`${confirmResponse.message} (${confirmResponse.data.payment.amount} ${confirmResponse.data.payment.currency})`);
                            await refreshDashboard();
                        } catch (error) {
                            showToast(error.message, true);
                        }
                    },
                    async refreshReportPaymentStatus() {
                        const session = state.pendingReportPayment;

                        if (!session?.sync_ref) {
                            showToast('Aucun paiement de signalement en attente.', true);
                            return;
                        }

                        const button = document.getElementById('refreshReportPaymentStatusButton');
                        button.disabled = true;
                        button.dataset.originalText = button.dataset.originalText || button.textContent;
                        button.textContent = 'Vérification...';

                        try {
                            await pollReportPaymentSession(session.sync_ref, { manual: true });
                        } catch (error) {
                            showToast(error.message || 'Impossible de vérifier le paiement.', true);
                        } finally {
                            button.disabled = false;
                            button.textContent = button.dataset.originalText || 'J’ai terminé le paiement';
                        }
                    },
                    reopenReportPayment() {
                        const session = state.pendingReportPayment;

                        if (!session?.checkout_link) {
                            showToast('Lien de paiement indisponible.', true);
                            return;
                        }

                        window.open(session.checkout_link, '_blank', 'noopener,noreferrer');
                    },
                    cancelReportPaymentWaiting() {
                        stopReportPaymentPolling();
                        persistPendingReportPayment(null);
                        activatePanel('reports');
                        showToast('Attente de paiement annulée.');
                    },
                    async confirmResolution(reportId) {
                        try {
                            activatePanel('reports');
                            const response = await apiFetch(`/reports/${reportId}/confirm-resolution`, { method: 'POST' });
                            showToast(response.message);
                            await refreshDashboard();
                            window.AcepenPortal.openDamageForm(reportId);
                        } catch (error) {
                            showToast(error.message, true);
                        }
                    },
                    openDamageForm(reportId) {
                        const report = state.reports.find((item) => item.id === reportId);
                        if (!report || !report.damage_declaration?.can_declare) {
                            return;
                        }

                        document.getElementById('damageDeclarationReportId').value = String(report.id);
                        document.getElementById('damageDeclarationTitle').textContent = `${report.reference} · ${report.signal_label || report.signal_code}`;
                        const form = document.getElementById('damageDeclarationForm');
                        form.reset();
                        form.dataset.mode = 'create';
                        form.querySelector('button[type="submit"]').textContent = 'Enregistrer le dommage';
                        document.getElementById('damageAttachmentInput').required = true;
                        ['receipt_material_name', 'receipt_purchase_date', 'receipt_amount', 'receipt_attachment'].forEach((name) => {
                            form.elements[name].disabled = false;
                        });
                        renderPurchaseReceiptOptions();
                        resetDamageAttachmentPreview();
                        damageDeclarationModal?.show();
                    },
                    openDamageEditForm(reportId) {
                        const report = state.reports.find((item) => item.id === reportId);

                        if (!report?.damage_declaration?.declared_at) {
                            showToast('Aucun dommage à modifier pour ce signalement.', true);
                            return;
                        }

                        const form = document.getElementById('damageDeclarationForm');
                        form.reset();
                        form.dataset.mode = 'edit';
                        document.getElementById('damageDeclarationReportId').value = String(report.id);
                        document.getElementById('damageDeclarationTitle').textContent = `Modifier le dommage · ${report.reference}`;
                        form.damage_summary.value = report.damage_declaration.summary || '';
                        form.damage_amount_estimated.value = report.damage_declaration.amount_estimated ?? '';
                        form.damage_notes.value = report.damage_declaration.notes || '';
                        document.getElementById('damageAttachmentInput').required = false;
                        form.querySelector('button[type="submit"]').textContent = 'Mettre à jour le dommage';
                        ['receipt_material_name', 'receipt_purchase_date', 'receipt_amount', 'receipt_attachment'].forEach((name) => {
                            form.elements[name].disabled = false;
                        });
                        renderPurchaseReceiptOptions();
                        document.getElementById('damagePurchaseReceiptId').value = report.damage_declaration.purchase_receipt?.id || '';
                        document.getElementById('damagePurchaseReceiptId').dispatchEvent(new Event('change', { bubbles: true }));
                        resetDamageAttachmentPreview();
                        damageDeclarationModal?.show();
                    },
                    prefillPurchaseReceipt(receiptId) {
                        const receipt = state.purchaseReceipts.find((item) => Number(item.id) === Number(receiptId));
                        if (!receipt) return;

                        activatePanel('receipts');
                        const form = document.getElementById('purchaseReceiptForm');
                        form.dataset.editId = String(receipt.id);
                        form.material_name.value = receipt.material_name || '';
                        form.purchase_date.value = receipt.purchase_date || '';
                        form.amount.value = receipt.amount || '';
                        form.receipt_file.value = '';
                        form.querySelector('button[type="submit"]').textContent = 'Mettre à jour le reçu';
                        document.getElementById('cancelPurchaseReceiptEditButton')?.classList.remove('d-none');
                    },
                    async deletePurchaseReceipt(receiptId) {
                        try {
                            await apiFetch(`/purchase-receipts/${receiptId}`, { method: 'DELETE' });
                            showToast('Reçu supprimé avec succès.');
                            await refreshDashboard();
                        } catch (error) {
                            showToast(error.message, true);
                        }
                    },
                    async deleteHousehold(householdId) {
                        const household = state.households.find((item) => String(item.id) === String(householdId));
                        const householdName = household?.name || 'ce Gbonhi';

                        if (!window.confirm(`Supprimer ${householdName} ? Les membres et invitations associes seront retires.`)) {
                            return;
                        }

                        try {
                            const response = await apiFetch(`/households/${householdId}`, { method: 'DELETE' });
                            showToast(response.message);
                            state.selectedHouseholdId = response.data.household?.id || null;
                            await refreshDashboard();
                            activatePanel('household');
                        } catch (error) {
                            showToast(error.message, true);
                        }
                    },
                    async cancelHouseholdInvitation(invitationId) {
                        try {
                            const response = await apiFetch(`/households/invitations/${invitationId}`, { method: 'DELETE' });
                            showToast(response.message);
                            renderHousehold(response.data.household, response.data.households || null);
                            await refreshDashboard();
                            activatePanel('household');
                        } catch (error) {
                            showToast(error.message, true);
                        }
                    },
                    async removeHouseholdMember(householdId, memberId) {
                        if (!window.confirm('Retirer ce membre du Gbonhi ?')) {
                            return;
                        }

                        try {
                            const response = await apiFetch(`/households/${householdId}/members/${memberId}`, { method: 'DELETE' });
                            showToast(response.message);
                            renderHousehold(response.data.household, response.data.households || null);
                            await refreshDashboard();
                            activatePanel('household');
                        } catch (error) {
                            showToast(error.message, true);
                        }
                    },
                    async acceptInvitation(invitationId) {
                        try {
                            activatePanel('household');
                            const response = await apiFetch('/households/invitations/accept', {
                                method: 'POST',
                                body: JSON.stringify({ invitation_id: invitationId }),
                            });
                            renderHousehold(response.data.household);
                            showToast(response.message);
                            await refreshDashboard();
                        } catch (error) {
                            showToast(error.message, true);
                        }
                    },
                    async declineInvitation(invitationId) {
                        try {
                            activatePanel('household');
                            const response = await apiFetch('/households/invitations/decline', {
                                method: 'POST',
                                body: JSON.stringify({ invitation_id: invitationId }),
                            });
                            renderIncomingHouseholdInvitations(response.data.invitations || []);
                            showToast(response.message);
                            await refreshDashboard();
                        } catch (error) {
                            showToast(error.message, true);
                        }
                    },
                    async downloadReceipt(paymentId, paymentReference) {
                        try {
                            const response = await fetch(`${apiBase}/payments/${paymentId}/receipt`, {
                                headers: {
                                    Accept: 'text/html',
                                    Authorization: `Bearer ${state.token}`,
                                },
                            });

                            if (!response.ok) {
                                const payload = await response.json().catch(() => ({}));
                                throw new Error(payload.message || 'Impossible de télécharger le reçu.');
                            }

                            const blob = await response.blob();
                            const url = window.URL.createObjectURL(blob);
                            const link = document.createElement('a');
                            link.href = url;
                            link.download = `reçu-${paymentReference}.pdf`;
                            document.body.appendChild(link);
                            link.click();
                            link.remove();
                            window.URL.revokeObjectURL(url);
                            showToast('Le reçu a été téléchargé.');
                        } catch (error) {
                            showToast(error.message, true);
                        }
                    },
                    previewReceipt(paymentId) {
                        const payment = state.payments.find((item) => String(item.id) === String(paymentId));

                        if (!payment || !payment.can_download_receipt) {
                            showToast('Le reçu n’est disponible que pour un paiement confirmé.', true);
                            return;
                        }

                        renderReceiptPreview(payment);
                        paymentReceiptPreviewModal?.show();
                    },
                    changeReportsPage(page) {
                        const totalPages = Math.max(1, Math.ceil(getFilteredReports(state.reports).length / state.reportsPageSize));
                        state.reportsPage = Math.min(Math.max(1, page), totalPages);
                        renderReports(state.reports);
                    },
                    changeOverviewReportsPage(page) {
                        const totalPages = Math.max(1, Math.ceil(getOverviewFilteredReports(state.reports).length / state.overviewReportsPageSize));
                        state.overviewReportsPage = Math.min(Math.max(1, page), totalPages);
                        renderOverviewReports(state.reports);
                    },
                    changeDamagesPage(page) {
                        const totalPages = Math.max(1, Math.ceil(getFilteredDamages(state.reports).length / state.damagesPageSize));
                        state.damagesPage = Math.min(Math.max(1, page), totalPages);
                        renderDamages(state.reports);
                    },
                    changeSubscriptionsPage(page) {
                        const totalPages = Math.max(1, Math.ceil(getFilteredSubscriptionHistory().length / state.subscriptionHistoryPageSize));
                        state.subscriptionHistoryPage = Math.min(Math.max(1, page), totalPages);
                        renderSubscriptionHistoryPanel();
                    },
                    changeRexFeedbacksPage(page) {
                        const totalPages = Math.max(1, Math.ceil(getFilteredRexFeedbacks().length / state.rexFeedbacksPageSize));
                        state.rexFeedbacksPage = Math.min(Math.max(1, page), totalPages);
                        renderRexFeedbacks(state.rexFeedbacks);
                    },
                    showReportDetails(reportId) {
                        const report = state.reports.find((item) => item.id === reportId);
                        if (!report) {
                            return;
                        }

                        renderReportDetails(report);
                        reportDetailModal?.show();
                    },
                    showReparationCaseDetails(caseId) {
                        const repairCase = state.reparationCases.find((item) => item.id === caseId);
                        if (!repairCase) {
                            return;
                        }

                        renderReparationCaseDetails(repairCase);
                        reparationCaseDetailModal?.show();
                    },
                    openRexForm(contextType, contextId, title) {
                        if (hasRexFeedback(contextType, contextId)) {
                            showToast('Un avis a déjà été envoyé pour cet élément.', true);
                            return;
                        }

                        const form = document.getElementById('rexFeedbackForm');
                        form.reset();
                        document.getElementById('rexContextType').value = contextType;
                        document.getElementById('rexContextId').value = String(contextId);
                        document.getElementById('rexFeedbackTitle').textContent = `Avis · ${title || getRexContextLabel(contextType)}`;
                        rexFeedbackModal?.show();
                    },
                };

                document.querySelectorAll('[data-panel-target]').forEach((button) => {
                    button.addEventListener('click', () => activatePanel(button.dataset.panelTarget));
                });
                document.getElementById('overviewReportSearchFilter')?.addEventListener('input', (event) => {
                    state.overviewReportFilters.search = event.currentTarget.value || '';
                    state.overviewReportsPage = 1;
                    renderOverviewReports(state.reports);
                });
                document.getElementById('overviewReportStatusFilter')?.addEventListener('change', (event) => {
                    state.overviewReportFilters.status = event.currentTarget.value || '';
                    state.overviewReportsPage = 1;
                    renderOverviewReports(state.reports);
                });
                document.getElementById('resetOverviewReportFiltersButton')?.addEventListener('click', () => {
                    state.overviewReportFilters = { search: '', status: '' };
                    state.overviewReportsPage = 1;
                    document.getElementById('overviewReportSearchFilter').value = '';
                    document.getElementById('overviewReportStatusFilter').value = '';
                    renderOverviewReports(state.reports);
                });
                document.getElementById('notificationSearchFilter')?.addEventListener('input', (event) => {
                    state.notificationFilters.search = event.currentTarget.value || '';
                    renderNotifications();
                });
                document.getElementById('notificationReadFilter')?.addEventListener('change', (event) => {
                    state.notificationFilters.status = event.currentTarget.value || '';
                    renderNotifications();
                });
                document.getElementById('notificationCategoryFilter')?.addEventListener('change', (event) => {
                    state.notificationFilters.category = event.currentTarget.value || '';
                    renderNotifications();
                });
                document.getElementById('resetNotificationFiltersButton')?.addEventListener('click', () => {
                    state.notificationFilters = { search: '', status: '', category: '' };
                    document.getElementById('notificationSearchFilter').value = '';
                    document.getElementById('notificationReadFilter').value = '';
                    document.getElementById('notificationCategoryFilter').value = '';
                    renderNotifications();
                });
                document.getElementById('markAllNotificationsReadButton')?.addEventListener('click', markAllNotificationsAsRead);
                navigator.serviceWorker?.addEventListener('message', (event) => {
                    if (event.data?.type === 'MYSIGNAL_NOTIFICATION_CLICK') {
                        activatePanel('notifications');
                        void refreshNotifications();
                    }
                });
                document.getElementById('reportSearchFilter').addEventListener('input', (event) => {
                    state.reportFilters.search = event.currentTarget.value || '';
                    state.reportsPage = 1;
                    renderReports(state.reports);
                });
                document.getElementById('reportStatusFilter').addEventListener('change', (event) => {
                    state.reportFilters.status = event.currentTarget.value || '';
                    state.reportsPage = 1;
                    renderReports(state.reports);
                });
                document.getElementById('reportPaymentFilter').addEventListener('change', (event) => {
                    state.reportFilters.payment = event.currentTarget.value || '';
                    state.reportsPage = 1;
                    renderReports(state.reports);
                });
                document.getElementById('reportOrganizationFilter').addEventListener('change', (event) => {
                    state.reportFilters.organization = event.currentTarget.value || '';
                    state.reportsPage = 1;
                    renderReports(state.reports);
                });
                document.getElementById('reportResolutionFilter').addEventListener('change', (event) => {
                    state.reportFilters.resolution = event.currentTarget.value || '';
                    state.reportsPage = 1;
                    renderReports(state.reports);
                });
                document.getElementById('resetReportFiltersButton').addEventListener('click', () => {
                    state.reportFilters = { search: '', status: '', payment: '', organization: '', resolution: '' };
                    state.reportsPage = 1;
                    document.getElementById('reportSearchFilter').value = '';
                    document.getElementById('reportStatusFilter').value = '';
                    document.getElementById('reportPaymentFilter').value = '';
                    document.getElementById('reportOrganizationFilter').value = '';
                    document.getElementById('reportResolutionFilter').value = '';
                    renderReports(state.reports);
                });
                document.getElementById('refreshReportPaymentStatusButton')?.addEventListener('click', () => window.AcepenPortal.refreshReportPaymentStatus());
                document.getElementById('reopenReportPaymentButton')?.addEventListener('click', () => window.AcepenPortal.reopenReportPayment());
                document.getElementById('cancelReportPaymentWaitingButton')?.addEventListener('click', () => window.AcepenPortal.cancelReportPaymentWaiting());
                document.getElementById('damageSearchFilter').addEventListener('input', (event) => {
                    state.damageFilters.search = event.currentTarget.value || '';
                    state.damagesPage = 1;
                    renderDamages(state.reports);
                });
                document.getElementById('damageOrganizationFilter').addEventListener('change', (event) => {
                    state.damageFilters.organization = event.currentTarget.value || '';
                    state.damagesPage = 1;
                    renderDamages(state.reports);
                });
                document.getElementById('damageResolutionFilter').addEventListener('change', (event) => {
                    state.damageFilters.resolution = event.currentTarget.value || '';
                    state.damagesPage = 1;
                    renderDamages(state.reports);
                });
                document.getElementById('damageAttachmentFilter').addEventListener('change', (event) => {
                    state.damageFilters.attachment = event.currentTarget.value || '';
                    state.damagesPage = 1;
                    renderDamages(state.reports);
                });
                document.getElementById('resetDamageFiltersButton').addEventListener('click', () => {
                    state.damageFilters = { search: '', organization: '', resolution: '', attachment: '' };
                    state.damagesPage = 1;
                    document.getElementById('damageSearchFilter').value = '';
                    document.getElementById('damageOrganizationFilter').value = '';
                    document.getElementById('damageResolutionFilter').value = '';
                    document.getElementById('damageAttachmentFilter').value = '';
                    renderDamages(state.reports);
                });
                document.getElementById('paymentSearchFilter').addEventListener('input', (event) => {
                    state.paymentFilters.search = event.currentTarget.value || '';
                    renderPayments(state.payments);
                });
                document.getElementById('paymentStatusFilter').addEventListener('change', (event) => {
                    state.paymentFilters.status = event.currentTarget.value || '';
                    renderPayments(state.payments);
                });
                document.getElementById('paymentReceiptFilter').addEventListener('change', (event) => {
                    state.paymentFilters.receipt = event.currentTarget.value || '';
                    renderPayments(state.payments);
                });
                document.getElementById('resetPaymentFiltersButton').addEventListener('click', () => {
                    state.paymentFilters = { search: '', status: '', receipt: '' };
                    document.getElementById('paymentSearchFilter').value = '';
                    document.getElementById('paymentStatusFilter').value = '';
                    document.getElementById('paymentReceiptFilter').value = '';
                    renderPayments(state.payments);
                });
                document.getElementById('refreshPrivilegeCardsButton')?.addEventListener('click', async () => {
                    try {
                        await refreshPrivilegeCards();
                        showToast('Cartes privilèges actualisées.');
                    } catch (error) {
                        showToast(error.message, true);
                    }
                });
                document.getElementById('subscriptionSearchFilter').addEventListener('input', (event) => {
                    state.subscriptionFilters.search = event.currentTarget.value || '';
                    state.subscriptionHistoryPage = 1;
                    renderSubscriptionHistoryPanel();
                });
                document.getElementById('subscriptionStatusFilter').addEventListener('change', (event) => {
                    state.subscriptionFilters.status = event.currentTarget.value || '';
                    state.subscriptionHistoryPage = 1;
                    renderSubscriptionHistoryPanel();
                });
                document.getElementById('subscriptionPaymentStatusFilter').addEventListener('change', (event) => {
                    state.subscriptionFilters.payment = event.currentTarget.value || '';
                    state.subscriptionHistoryPage = 1;
                    renderSubscriptionHistoryPanel();
                });
                document.getElementById('resetSubscriptionFiltersButton').addEventListener('click', () => {
                    state.subscriptionFilters = { search: '', status: '', payment: '' };
                    state.subscriptionHistoryPage = 1;
                    document.getElementById('subscriptionSearchFilter').value = '';
                    document.getElementById('subscriptionStatusFilter').value = '';
                    document.getElementById('subscriptionPaymentStatusFilter').value = '';
                    renderSubscriptionHistoryPanel();
                });
                document.getElementById('rexSearchFilter').addEventListener('input', (event) => {
                    state.rexFilters.search = event.currentTarget.value || '';
                    state.rexFeedbacksPage = 1;
                    renderRexFeedbacks(state.rexFeedbacks);
                });
                document.getElementById('rexContextFilter').addEventListener('change', (event) => {
                    state.rexFilters.context = event.currentTarget.value || '';
                    state.rexFeedbacksPage = 1;
                    renderRexFeedbacks(state.rexFeedbacks);
                });
                document.getElementById('rexRatingFilter').addEventListener('change', (event) => {
                    state.rexFilters.rating = event.currentTarget.value || '';
                    state.rexFeedbacksPage = 1;
                    renderRexFeedbacks(state.rexFeedbacks);
                });
                document.getElementById('resetRexFiltersButton').addEventListener('click', () => {
                    state.rexFilters = { search: '', context: '', rating: '' };
                    state.rexFeedbacksPage = 1;
                    document.getElementById('rexSearchFilter').value = '';
                    document.getElementById('rexContextFilter').value = '';
                    document.getElementById('rexRatingFilter').value = '';
                    renderRexFeedbacks(state.rexFeedbacks);
                });
                document.getElementById('openDamageDeclarationButton').addEventListener('click', () => {
                    const reportId = Number(document.getElementById('openDamageDeclarationButton').dataset.reportId || 0);
                    if (!reportId) {
                        return;
                    }

                    window.AcepenPortal.openDamageForm(reportId);
                });
                document.getElementById('openPublicSidebarButton').addEventListener('click', openSidebar);
                document.getElementById('openSubscriptionModalButton').addEventListener('click', () => activatePanel('reports'));
                document.getElementById('subscriptionOverviewButton').addEventListener('click', () => activatePanel('reports'));
                document.getElementById('openSubscriptionFromHistoryButton').addEventListener('click', () => activatePanel('reports'));
                document.getElementById('startSubscriptionPaymentButton').addEventListener('click', startSubscriptionPayment);
                document.getElementById('confirmSubscriptionPaymentButton').addEventListener('click', confirmSubscriptionPayment);
                subscriptionPromptModalElement?.addEventListener('hidden.bs.modal', () => {
                    return;
                });
                document.getElementById('publicSidebarBackdrop').addEventListener('click', closeSidebar);

                document.getElementById('meterCitySelect').addEventListener('change', () => {
                    populateMeterCommuneOptions();
                    populateMeterNeighborhoodOptions();
                });
                document.getElementById('meterCommuneSelect').addEventListener('change', () => populateMeterNeighborhoodOptions());
                document.getElementById('meterNeighborhoodSelect').addEventListener('change', () => {
                    populateMeterNeighborhoodOptions(document.getElementById('meterNeighborhoodSelect').value);
                });
                document.getElementById('meterApplicationId').addEventListener('change', () => populateMeterOrganizationOptions());
                document.getElementById('meterOrganizationId').addEventListener('change', () => populateMeterOrganizationOptions(document.getElementById('meterOrganizationId').value));
                document.getElementById('reportApplicationId').addEventListener('change', () => {
                    renderReportOrganizationTypeOptions();
                    renderReportOrganizationOptions();
                    renderReportMeterOptions();
                    renderSignalOptions();
                    applyReportMeterLocationIfAvailable(true);
                });
                document.getElementById('reportOrganizationTypeId').addEventListener('change', () => {
                    renderReportOrganizationOptions();
                    renderReportMeterOptions();
                    renderSignalOptions();
                    applyReportMeterLocationIfAvailable(true);
                });
                document.getElementById('reportOrganizationType').addEventListener('change', () => {
                    renderReportMeterOptions();
                    renderSignalOptions();
                    applyReportMeterLocationIfAvailable(true);
                });
                document.getElementById('reportMeterId').addEventListener('change', () => {
                    renderSignalOptions();
                    applyReportMeterLocationIfAvailable(true);
                });
                document.getElementById('reportSignalCode').addEventListener('change', renderSignalPayloadFields);
                document.getElementById('captureProfileLocationButton').addEventListener('click', () => captureCurrentPosition('profile', { force: true }));
                document.getElementById('sidebarRequestGpsButton')?.addEventListener('click', () => {
                    activatePanel('profile');
                    captureCurrentPosition('profile', { force: true });
                });
                document.getElementById('profilePublicUserTypeSelect').addEventListener('change', () => syncPublicUserTypeFields('profilePublicUserTypeSelect', 'profileBusinessFields', 'profileSectorFields'));
                document.getElementById('toggleProfileManualLocationButton').addEventListener('click', () => {
                    const enabled = document.getElementById('profileLatitude').readOnly;
                    setGeoManualMode('profile', enabled);
                    showToast(enabled ? 'Saisie manuelle activée pour la position du profil.' : 'Saisie manuelle désactivée.');
                });

                meterFormWrapElement?.addEventListener('shown.bs.collapse', () => {
                    if (!document.getElementById('meterForm').dataset.editId) {
                        maybeCaptureCurrentPosition('meter');
                    }
                });

                reportFormModalElement?.addEventListener('shown.bs.modal', () => {
                    const primaryMeter = state.meters.find((meter) => meter.is_primary) || state.meters[0] || null;
                    document.getElementById('reportOccurredAt').value = currentLocalDateTimeValue();
                    bindSearchableSelects();
                    renderReportNetworkOptions(primaryMeter?.application_code || primaryMeter?.network_type || null);
                    renderReportMeterOptions(primaryMeter?.id || null);
                    renderSignalOptions();
                    maybeCaptureCurrentPosition('report');
                    applyReportMeterLocationIfAvailable(true);
                });

                damageDeclarationModalElement?.addEventListener('hidden.bs.modal', () => {
                    const form = document.getElementById('damageDeclarationForm');
                    form.reset();
                    delete form.dataset.mode;
                    document.getElementById('damageDeclarationReportId').value = '';
                    document.getElementById('damageAttachmentInput').required = true;
                    form.querySelector('button[type="submit"]').textContent = 'Enregistrer le dommage';
                    resetDamageAttachmentPreview();
                });

                document.getElementById('rexFeedbackForm').addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const form = event.currentTarget;
                    const payload = Object.fromEntries(new FormData(form).entries());
                    ['rating', 'context_id', 'response_time_rating', 'communication_rating', 'quality_rating', 'fairness_rating'].forEach((key) => {
                        if (payload[key] !== '' && payload[key] !== undefined) {
                            payload[key] = Number(payload[key]);
                        } else {
                            delete payload[key];
                        }
                    });
                    if (payload.is_resolved === '') {
                        delete payload.is_resolved;
                    } else if (payload.is_resolved !== undefined) {
                        payload.is_resolved = payload.is_resolved === '1';
                    }

                    setLoading(form, true);
                    try {
                        const response = await apiFetch('/rex-feedbacks', { method: 'POST', body: JSON.stringify(payload) });
                        rexFeedbackModal?.hide();
                        showToast(response.message);
                        const feedbacksResponse = await apiFetch('/rex-feedbacks');
                        renderRexFeedbacks(feedbacksResponse.data.feedbacks || []);
                        renderReports(state.reports);
                        renderDamages(state.reports);
                        renderReparationCases(state.reparationCases);
                    } catch (error) {
                        showToast(error.message, true);
                    } finally {
                        setLoading(form, false);
                    }
                });

                document.getElementById('profileForm').addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const form = event.currentTarget;
                    setLoading(form, true);
                    try {
                        const payload = Object.fromEntries(new FormData(form).entries());
                        const response = await apiFetch('/profile', { method: 'PUT', body: JSON.stringify(payload) });
                        renderUser(response.data.user);
                        showToast(response.message);
                    } catch (error) {
                        showToast(error.message, true);
                    } finally {
                        setLoading(form, false);
                    }
                });

                document.getElementById('meterForm').addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const form = event.currentTarget;
                    setLoading(form, true);
                    try {
                        const payload = Object.fromEntries(new FormData(form).entries());
                        payload.application_id = document.getElementById('meterApplicationId').value;
                        payload.organization_id = document.getElementById('meterOrganizationId').value;
                        payload.is_primary = new FormData(form).get('is_primary') === '1';
                        const editId = form.dataset.editId;
                        const response = await apiFetch(editId ? `/meters/${editId}` : '/meters', { method: editId ? 'PATCH' : 'POST', body: JSON.stringify(payload) });
                        form.reset();
                        delete form.dataset.editId;
                        document.getElementById('meterApplicationId').disabled = false;
                        document.getElementById('meterOrganizationId').disabled = false;
                        form.meter_number.disabled = false;
                        clearMeterGeoFields();
                        setGeoManualMode('meter', false);
                        form.querySelector('button[type="submit"]').textContent = 'Enregistrer';
                        populateMeterApplicationOptions();
                        populateCommuneSelects(state.currentUser?.commune || null);
                        form.neighborhood.value = '';
                        form.sub_neighborhood.value = '';
                        populateMeterNeighborhoodOptions();
                        state.autoGeoAttempts.meter = false;
                        showToast(response.message);
                        await refreshDashboard();
                    } catch (error) {
                        showToast(error.message, true);
                    } finally {
                        setLoading(form, false);
                    }
                });

                document.getElementById('purchaseReceiptForm')?.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const form = event.currentTarget;
                    setLoading(form, true);

                    try {
                        const editId = form.dataset.editId;
                        const formData = new FormData(form);
                        const receiptFile = formData.get('receipt_file');

                        if (receiptFile instanceof File && receiptFile.size === 0) {
                            formData.delete('receipt_file');
                        }

                        if (editId) {
                            formData.append('_method', 'PATCH');
                        }

                        const response = await apiFetch(editId ? `/purchase-receipts/${editId}` : '/purchase-receipts', {
                            method: 'POST',
                            body: formData,
                        });

                        form.reset();
                        delete form.dataset.editId;
                        form.querySelector('button[type="submit"]').textContent = 'Enregistrer le reçu';
                        document.getElementById('cancelPurchaseReceiptEditButton')?.classList.add('d-none');
                        showToast(response.message);
                        await refreshDashboard();
                    } catch (error) {
                        showToast(error.message, true);
                    } finally {
                        setLoading(form, false);
                    }
                });

                document.getElementById('cancelPurchaseReceiptEditButton')?.addEventListener('click', () => {
                    const form = document.getElementById('purchaseReceiptForm');
                    form.reset();
                    delete form.dataset.editId;
                    form.querySelector('button[type="submit"]').textContent = 'Enregistrer le reçu';
                    document.getElementById('cancelPurchaseReceiptEditButton')?.classList.add('d-none');
                });

                document.getElementById('damagePurchaseReceiptId')?.addEventListener('change', (event) => {
                    const form = document.getElementById('damageDeclarationForm');
                    const hasSelectedReceipt = !!event.currentTarget.value;

                    ['receipt_material_name', 'receipt_purchase_date', 'receipt_amount', 'receipt_attachment'].forEach((name) => {
                        const input = form.elements[name];
                        input.disabled = hasSelectedReceipt;
                        if (hasSelectedReceipt) {
                            input.value = '';
                        }
                    });
                });

                document.getElementById('householdForm').addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const form = event.currentTarget;
                    setLoading(form, true);
                    try {
                        const payload = Object.fromEntries(new FormData(form).entries());
                        const response = await apiFetch('/households', { method: 'POST', body: JSON.stringify(payload) });
                        showToast(response.message);
                        form.reset();
                        state.selectedHouseholdId = response.data.household?.id || null;
                        await refreshDashboard();
                        activatePanel('household');
                    } catch (error) {
                        showToast(error.message, true);
                    } finally {
                        setLoading(form, false);
                    }
                });

                document.getElementById('showHouseholdFormButton').addEventListener('click', () => {
                    document.getElementById('householdForm').reset();
                    setHouseholdFormVisible(true);
                });

                document.getElementById('cancelHouseholdFormButton').addEventListener('click', () => {
                    document.getElementById('householdForm').reset();
                    setHouseholdFormVisible(false);
                });

                document.getElementById('deleteHouseholdButton')?.addEventListener('click', (event) => {
                    const householdId = event.currentTarget.dataset.householdId;

                    if (householdId) {
                        window.AcepenPortal.deleteHousehold(householdId);
                    }
                });

                document.getElementById('householdInvitationForm').addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const form = event.currentTarget;
                    setLoading(form, true);
                    try {
                        composePhoneNumber(form);
                        const payload = Object.fromEntries(new FormData(form).entries());
                        const response = await apiFetch(`/households/${state.household.id}/invitations`, { method: 'POST', body: JSON.stringify(payload) });
                        showToast(response.message);
                        form.reset();
                        populateDialCodeSelects();
                        if (state.meters.length) {
                            document.getElementById('householdSharedMeterId').value = String(state.meters[0].id);
                        }
                        await refreshDashboard();
                    } catch (error) {
                        showToast(error.message, true);
                    } finally {
                        setLoading(form, false);
                    }
                });

                document.getElementById('reportForm').addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const form = event.currentTarget;
                    let paymentWindow = null;
                    setLoading(form, true);
                    try {
                        const payload = new FormData();
                        const rawFormData = new FormData(form);

                        ['meter_id', 'signal_code', 'signal_sub_type_code', 'occurred_at', 'description', 'latitude', 'longitude', 'location_accuracy', 'location_source'].forEach((key) => {
                            const value = rawFormData.get(key);

                            if (value !== null && value !== '') {
                                payload.append(key, value);
                            }
                        });

                        payload.append('application_id', document.getElementById('reportApplicationId').value);
                        if (document.getElementById('reportOrganizationTypeId')?.value) {
                            payload.append('organization_type_id', document.getElementById('reportOrganizationTypeId').value);
                        }
                        payload.append('organization_id', document.getElementById('reportOrganizationType').value);

                        const signalAttachment = rawFormData.get('signal_attachment');

                        if (signalAttachment instanceof File && signalAttachment.size > 0) {
                            payload.append('signal_attachment', signalAttachment);
                        }

                        paymentWindow = window.open('', '_blank', 'noopener,noreferrer');
                        const response = await apiFetch('/reports', { method: 'POST', body: payload });

                        const checkoutLink = response.data?.checkout_link || response.data?.payment_session?.checkout_link;
                        showToast(response.message || 'Lien de paiement généré.');
                        form.reset();
                        clearReportGeoFields();
                        setGeoManualMode('report', false);
                        populateReportLocationSelects();
                        populateMeterApplicationOptions();
                        renderReportNetworkOptions();
                        renderReportMeterOptions();
                        renderSignalOptions();
                        state.autoGeoAttempts.report = false;
                        reportFormModal?.hide();
                        if (response.data?.payment_session) {
                            showReportPaymentWaiting({
                                ...response.data.payment_session,
                                checkout_link: checkoutLink,
                            }, {
                                paymentWindow,
                            });
                            return;
                        }
                        paymentWindow?.close();
                        await refreshDashboard();
                        activatePanel('payments');
                    } catch (error) {
                        paymentWindow?.close();
                        showToast(error.message, true);
                    } finally {
                        setLoading(form, false);
                    }
                });

                document.getElementById('damageAttachmentInput').addEventListener('change', (event) => {
                    renderDamageAttachmentPreview(event.currentTarget.files?.[0] || null);
                });

                document.getElementById('damageDeclarationForm').addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const form = event.currentTarget;
                    setLoading(form, true);

                    try {
                        const payload = Object.fromEntries(new FormData(form).entries());
                        const reportId = payload.report_id;
                        const isEdit = form.dataset.mode === 'edit';
                        const formData = new FormData(form);
                        formData.delete('report_id');
                        const receiptAttachment = formData.get('receipt_attachment');
                        if (receiptAttachment instanceof File && receiptAttachment.size === 0) {
                            formData.delete('receipt_attachment');
                        }
                        if (isEdit) {
                            formData.append('_method', 'PATCH');
                            const attachment = formData.get('damage_attachment');
                            if (attachment instanceof File && attachment.size === 0) {
                                formData.delete('damage_attachment');
                            }
                        }

                        const response = await apiFetch(`/reports/${reportId}/damages`, {
                            method: 'POST',
                            body: formData,
                        });

                        const session = response.data?.payment_session;
                        showToast(response.message);
                        damageDeclarationModal?.hide();
                        if (!isEdit && session?.checkout_link) {
                            showReportPaymentWaiting(session);
                        } else {
                            await refreshDashboard();
                            window.AcepenPortal.showReportDetails(Number(reportId));
                        }
                    } catch (error) {
                        showToast(error.message, true);
                    } finally {
                        setLoading(form, false);
                    }
                });

                window.AcepenInitGooglePlaces = () => {
                    initGooglePlacesAutocomplete();
                };

                document.getElementById('logoutButton').addEventListener('click', () => logout(true));

                enhancePublicFormSelects();
                annotateRequiredFields();
                setGeoManualMode('profile', false);
                syncPublicUserTypeFields('profilePublicUserTypeSelect', 'profileBusinessFields', 'profileSectorFields');
                setGeoManualMode('meter', false);
                setGeoManualMode('report', false);
                restoreActivePanel();
                populateDialCodeSelects();
                populateMeterApplicationOptions();
                renderReportNetworkOptions();
                renderReportMeterOptions();
                renderSignalOptions();
                refreshDashboard().catch((error) => {
                    if (error?.status === 401) {
                        logout(false);
                        return;
                    }

                    showToast(error?.message || 'Impossible de charger certaines données du dashboard.', true);
                });
            })();
        </script>
        @if (config('services.google_maps.key'))
            <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=places&callback=AcepenInitGooglePlaces"></script>
        @endif
    </body>
</html>
