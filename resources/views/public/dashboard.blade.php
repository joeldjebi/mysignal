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
                letter-spacing: -0.03em;
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
                                <span class="small text-white-50">Synthese et raccourcis</span>
                            </span>
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
                                <span class="small text-white-50">Declaration et suivi</span>
                            </span>
                        </button>
                        <button class="nav-pill" type="button" data-panel-target="payments">
                            <span class="nav-icon">PM</span>
                            <span>
                                <span class="d-block fw-semibold">Mes paiements</span>
                                <span class="small text-white-50">Historique et recus</span>
                            </span>
                        </button>
                        <button class="nav-pill" type="button" data-panel-target="damages">
                            <span class="nav-icon">DG</span>
                            <span>
                                <span class="d-block fw-semibold">Mes dommages</span>
                                <span class="small text-white-50">Historique et suivi</span>
                            </span>
                        </button>
                        <button class="nav-pill" type="button" data-panel-target="cases">
                            <span class="nav-icon">DC</span>
                            <span>
                                <span class="d-block fw-semibold">Mes dossiers</span>
                                <span class="small text-white-50">Avancement et historique</span>
                            </span>
                        </button>
                    </div>

                </aside>

                <main class="content">
                    <header class="topbar d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                        <div>
                            <button class="btn btn-ghost-premium topbar-menu-button mb-3 px-3" id="openPublicSidebarButton" type="button">Menu</button>
                            <div class="small text-secondary fw-semibold mb-1">Dashboard utilisateur</div>
                            <div class="h4 mb-1 fw-bold" id="dashboardGreeting">Bienvenue</div>
                            <div class="text-secondary">Un espace plus clair pour piloter votre profil, vos identifiants, votre Gbonhi et vos declarations.</div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="status-pill" id="userStatus">active</span>
                            <span class="status-pill" id="topbarMetersBadge">0 identifiant</span>
                            <span class="status-pill" id="topbarReportsBadge">0 signalement</span>
                            <span class="status-pill" id="topbarPaymentsBadge">0 paiement</span>
                        </div>
                        <div class="topbar-session">
                            <div class="small text-white-50 mb-1">Session active</div>
                            <div class="topbar-session-meta mt-1" id="sidebarUserLocation">Localisation non renseignee</div>
                            <div class="topbar-session-meta mt-1" id="sidebarUserGps">GPS non renseigne</div>
                            <div class="topbar-session-actions">
                                <button type="button" class="btn btn-sm btn-topbar-session d-none" id="sidebarRequestGpsButton">Renseigner le GPS</button>
                                <button id="logoutButton" class="btn btn-sm btn-topbar-session" type="button">Se deconnecter</button>
                            </div>
                        </div>
                    </header>

                    <section class="public-panel active" data-panel="overview">
                        <div class="panel-grid">
                            <section class="hero-card">
                                <div class="row g-4 align-items-end position-relative" style="z-index:1;">
                                    <div class="col-lg-7">
                                        <div class="text-uppercase small fw-bold opacity-75 mb-2">Vue d'ensemble</div>
                                        <h1 class="display-6 fw-bold mb-2">Un cockpit personnel.</h1>
                                        <p class="text-white text-opacity-75 mb-0">Retrouve rapidement tes informations essentielles, puis accede aux actions importantes depuis la sidebar ou les raccourcis ci-dessous.</p>
                                    </div>
                                    <div class="col-lg-5">
                                        <div class="row g-3">
                                            <div class="col-4"><div class="metric-tile"><div class="small opacity-75">identifiants</div><div class="h3 fw-bold mb-0" id="meterCount">0</div></div></div>
                                            <div class="col-4"><div class="metric-tile"><div class="small opacity-75">Membres</div><div class="h3 fw-bold mb-0" id="householdMemberCount">0</div></div></div>
                                            <div class="col-4"><div class="metric-tile"><div class="small opacity-75">Signalements</div><div class="h3 fw-bold mb-0" id="reportCount">0</div></div></div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <div class="overview-grid">
                                <section class="dashboard-card">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                        <div>
                                            <div class="section-title">Raccourcis essentiels</div>
                                            <div class="muted-label">Accede directement aux zones les plus utilisees de ton espace public.</div>
                                        </div>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6 col-xl-3">
                                            <button class="quick-action" type="button" data-panel-target="meters">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div class="quick-action-icon">ID</div>
                                                    <div class="quick-action-arrow">›</div>
                                                </div>
                                                <div class="small text-secondary fw-semibold mb-2">Identifiants</div>
                                                <div class="quick-action-title mb-2">Ajouter ou mettre a jour un identifiant</div>
                                                <div class="muted-label">CIE, SODECI, commune, adresse et position GPS.</div>
                                            </button>
                                        </div>
                                        <div class="col-md-6 col-xl-3">
                                            <button class="quick-action" type="button" data-panel-target="reports">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div class="quick-action-icon">SG</div>
                                                    <div class="quick-action-arrow">›</div>
                                                </div>
                                                <div class="small text-secondary fw-semibold mb-2">Signalements</div>
                                                <div class="quick-action-title mb-2">Faire un nouveau signalement</div>
                                                <div class="muted-label">Declaration, geolocalisation, paiement et suivi.</div>
                                            </button>
                                        </div>
                                        <div class="col-md-6 col-xl-3">
                                            <button class="quick-action" type="button" data-panel-target="profile">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div class="quick-action-icon">PR</div>
                                                    <div class="quick-action-arrow">›</div>
                                                </div>
                                                <div class="small text-secondary fw-semibold mb-2">Profil</div>
                                                <div class="quick-action-title mb-2">Verifier mes informations</div>
                                                <div class="muted-label">Identite, email et commune de rattachement.</div>
                                            </button>
                                        </div>
                                        <div class="col-md-6 col-xl-3">
                                            <button class="quick-action" type="button" data-panel-target="household">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div class="quick-action-icon">GB</div>
                                                    <div class="quick-action-arrow">›</div>
                                                </div>
                                                <div class="small text-secondary fw-semibold mb-2">Gbonhi</div>
                                                <div class="quick-action-title mb-2">Inviter ma famille</div>
                                                <div class="muted-label">Membres, invitations et identifiant commun du Gbonhi.</div>
                                            </button>
                                        </div>
                                    </div>
                                </section>

                                <section class="dashboard-card">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                        <div>
                                            <div class="section-title">Mes derniers signalements</div>
                                            <div class="muted-label">Suivi rapide de vos declarations, avec recherche, filtre et pagination.</div>
                                        </div>
                                        <button class="btn btn-premium px-4" type="button" data-panel-target="reports">Nouveau signalement</button>
                                    </div>
                                    <div class="mini-card mb-3">
                                        <div class="row g-3 align-items-end">
                                            <div class="col-lg-6">
                                                <label class="form-label fw-semibold">Recherche</label>
                                                <input class="form-control" id="overviewReportSearchFilter" placeholder="Reference, type, commune...">
                                            </div>
                                            <div class="col-md-6 col-lg-3">
                                                <label class="form-label fw-semibold">Statut</label>
                                                <select class="form-select" id="overviewReportStatusFilter">
                                                    <option value="">Tous</option>
                                                    <option value="submitted">Soumis</option>
                                                    <option value="in_progress">En cours</option>
                                                    <option value="resolved">Resolu</option>
                                                    <option value="rejected">Non retenu</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 col-lg-3">
                                                <button class="btn btn-ghost-premium w-100" type="button" id="resetOverviewReportFiltersButton">Reset</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="overviewReportsList"></div>
                                </section>
                            </div>
                        </div>
                    </section>

                    <section class="public-panel" data-panel="profile">
                        <div class="dashboard-card">
                            <div class="d-flex justify-content-between align-items-start mb-4">
                                <div>
                                    <div class="section-title">Mon profil public</div>
                                    <div class="muted-label">Votre identite, votre adresse et votre position de reference pour accelerer les futures declarations.</div>
                                </div>
                                <span class="status-pill" id="profileStatusPill">active</span>
                            </div>
                            <div class="row g-4">
                                <div class="col-xl-7">
                                    <form id="profileForm" class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Type d usager public</label>
                                            <select class="form-select" id="profilePublicUserTypeSelect" required disabled>
                                                @foreach ($publicUserTypes as $publicUserType)
                                                    <option value="{{ $publicUserType->id }}" data-profile-kind="{{ $publicUserType->profile_kind }}" data-type-code="{{ $publicUserType->code }}">{{ $publicUserType->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="location-search-hint">Ce type est defini a la creation du compte et ne peut pas etre modifie ici.</div>
                                        </div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Prenom</label><input class="form-control" name="first_name" required></div>
                                        <div class="col-md-6"><label class="form-label fw-semibold">Nom</label><input class="form-control" name="last_name" required></div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Numero WhatsApp</label>
                                            <select class="form-select" name="is_whatsapp_number">
                                                <option value="0">Non</option>
                                                <option value="1">Oui</option>
                                            </select>
                                        </div>
                                        <div class="col-12"><label class="form-label fw-semibold">Email</label><input class="form-control" type="email" name="email"></div>
                                        <div class="col-12 hidden" id="profileSectorFields">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Secteur d activite</label>
                                                    <select class="form-select" name="business_sector">
                                                        <option value="">Selectionner un secteur</option>
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
                                                <div class="col-md-6"><label class="form-label fw-semibold">Identifiant fiscal</label><input class="form-control" name="tax_identifier"></div>
                                                <div class="col-12"><label class="form-label fw-semibold">Adresse de l entreprise</label><input class="form-control" name="company_address"></div>
                                            </div>
                                        </div>
                                        <div class="col-12"><label class="form-label fw-semibold">Commune</label><select class="form-select" name="commune" id="profileCommuneSelect" required></select></div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Adresse</label>
                                            <input class="form-control" name="address" id="profileAddressSearch" placeholder="Rechercher une adresse ou laisser la position automatique">
                                            <div class="location-search-hint">Si Google Places est configure, ce champ propose des adresses et place automatiquement la position.</div>
                                        </div>
                                        <div class="col-12">
                                            <div class="geo-box">
                                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                                                    <div>
                                                        <div class="fw-bold">Position du compte</div>
                                                        <div class="muted-label">Votre position est recuperee automatiquement quand vous ouvrez ce profil.</div>
                                                    </div>
                                                    <div class="d-flex gap-2 flex-wrap">
                                                        <button class="btn btn-ghost-premium px-4" type="button" id="captureProfileLocationButton">Recuperer ma position</button>
                                                        <button class="btn btn-ghost-premium px-4" type="button" id="toggleProfileManualLocationButton">Saisie manuelle</button>
                                                    </div>
                                                </div>
                                                <div class="row g-3">
                                                    <div class="col-md-4"><label class="form-label fw-semibold">Latitude</label><input class="form-control" name="latitude" id="profileLatitude" readonly></div>
                                                    <div class="col-md-4"><label class="form-label fw-semibold">Longitude</label><input class="form-control" name="longitude" id="profileLongitude" readonly></div>
                                                    <div class="col-md-4"><label class="form-label fw-semibold">Precision (m)</label><input class="form-control" name="location_accuracy" id="profileAccuracy" readonly></div>
                                                </div>
                                                <div class="geo-help mt-2">Si le navigateur refuse la geolocalisation, vous pouvez choisir une adresse ou activer la saisie manuelle.</div>
                                                <input type="hidden" name="location_source" id="profileLocationSource" value="">
                                            </div>
                                        </div>
                                        <div class="col-12"><button class="btn btn-premium w-100" type="submit">Mettre a jour mon profil</button></div>
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
                                            <div class="muted-label" id="profileGpsCard">Position GPS non renseignee</div>
                                        </div>
                                        <div class="soft-panel mb-3">
                                            <div class="small text-secondary fw-semibold mb-1">Type d usager</div>
                                            <div class="fw-semibold" id="profileUserTypeCard">-</div>
                                        </div>
                                        <div class="soft-panel mb-3">
                                            <div class="small text-secondary fw-semibold mb-1">Numero WhatsApp</div>
                                            <div class="fw-semibold" id="profileWhatsappCard">Non</div>
                                        </div>
                                        <div class="soft-panel">
                                            <div class="small text-secondary fw-semibold mb-1">Statut du compte</div>
                                            <div class="fw-semibold" id="profileStatusCard">-</div>
                                        </div>
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
                                    <div class="muted-label">Ajoutez vos identifiants CIE et SODECI, ... avec la localisation et la commune associees.</div>
                                </div>
                                <button class="btn btn-ghost-premium px-4" type="button" data-bs-toggle="collapse" data-bs-target="#meterFormWrap">Ajouter ou modifier</button>
                            </div>
                            <div id="meterFormWrap" class="collapse show mb-4">
                                <form id="meterForm" class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Application</label>
                                        <select class="form-select" id="meterApplicationId" required></select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Organisation</label>
                                        <select class="form-select" id="meterOrganizationId" required></select>
                                    </div>
                                    <input type="hidden" name="network_type" id="meterNetworkType" required>
                                    <div class="col-md-4"><label class="form-label fw-semibold">Mon identifiant</label><input class="form-control" name="meter_number" required></div>
                                    <div class="col-md-4"><label class="form-label fw-semibold">Libelle</label><input class="form-control" name="label"></div>
                                    <div class="col-md-4"><label class="form-label fw-semibold">Commune</label><select class="form-select" name="commune" id="meterCommuneSelect"></select></div>
                                    <div class="col-md-4"><label class="form-label fw-semibold">Quartier</label><select class="form-select" name="neighborhood" id="meterNeighborhoodSelect"></select></div>
                                    <div class="col-md-4"><label class="form-label fw-semibold">Sous-quartier</label><select class="form-select" name="sub_neighborhood" id="meterSubNeighborhoodSelect"></select></div>
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold">Situation géographique</label>
                                        <input class="form-control" name="address" id="meterAddressSearch" placeholder="Ex: Abatta carrefour Ab Center">
                                        <div class="location-search-hint">Une adresse Google peut remplir automatiquement la position et aider a retrouver la bonne commune.</div>
                                    </div>
                                    <div class="col-12">
                                        <div class="soft-panel">
                                            <div class="fw-bold mb-1">Position du identifiant</div>
                                            <div class="muted-label">La position de l'identifiant est recuperee automatiquement en arriere-plan pour vous. Si vous choisissez une adresse, la localisation est mise a jour automatiquement.</div>
                                        </div>
                                        <input type="hidden" name="latitude" id="meterLatitude">
                                        <input type="hidden" name="longitude" id="meterLongitude">
                                        <input type="hidden" name="location_accuracy" id="meterAccuracy">
                                        <input type="hidden" name="location_source" id="meterLocationSource" value="">
                                    </div>
                                    <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" value="1" name="is_primary" id="isPrimaryMeter"><label class="form-check-label fw-semibold" for="isPrimaryMeter">Definir comme identifiant principal</label></div></div>
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
                                    <div class="muted-label">Preparer les futurs signalements familiaux a partir d un Gbonhi principal.</div>
                                </div>
                            </div>
                            <div id="householdEmptyState" class="mini-card">
                                <div class="fw-bold mb-2">Aucun Gbonhi enregistre</div>
                                <p class="muted-label mb-3">Creez votre Gbonhi principal pour inviter vos proches et partager un identifiant commun.</p>
                                <form id="householdForm" class="row g-3">
                                    <div class="col-md-4"><label class="form-label fw-semibold">Nom du Gbonhi</label><input class="form-control" name="name"></div>
                                    <div class="col-md-4"><label class="form-label fw-semibold">Commune</label><select class="form-select" name="commune" id="householdCommuneSelect"></select></div>
                                    <div class="col-md-4"><label class="form-label fw-semibold">Adresse</label><input class="form-control" name="address"></div>
                                    <div class="col-12"><button class="btn btn-premium" type="submit">Creer mon Gbonhi</button></div>
                                </form>
                            </div>
                            <div id="householdPanel" class="d-none">
                                <div class="mini-card mb-4">
                                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                                        <div><div class="fw-bold fs-5" id="householdName">-</div><div class="muted-label" id="householdAddress">-</div></div>
                                        <span class="status-pill" id="householdStatus">active</span>
                                    </div>
                                </div>
                                <div class="row g-4">
                                    <div class="col-lg-5">
                                        <div class="mini-card h-100">
                                            <div class="fw-bold mb-3">Inviter un membre</div>
                                            <form id="householdInvitationForm" class="row g-3">
                                                <div class="col-12">
                                                    <label class="form-label fw-semibold">Numero</label>
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
                                                <div class="col-12"><label class="form-label fw-semibold">Lien</label><select class="form-select" name="relationship" required><option value="spouse">Conjoint(e)</option><option value="child">Enfant</option><option value="parent">Parent</option><option value="sibling">Frere / soeur</option></select></div>
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
                                <div class="fw-bold mb-3">Invitations Gbonhi recues</div>
                                <div id="incomingHouseholdInvitationsList" class="vstack gap-2"></div>
                            </div>
                        </div>
                    </section>

                    <section class="public-panel" data-panel="reports">
                        <div class="dashboard-card">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                <div>
                                    <div class="section-title">Mes signalements</div>
                                    <div class="muted-label">Un parcours de declaration moderne, ancre sur les identifiants et les references geographiques.</div>
                                </div>
                                <div class="d-flex flex-wrap gap-2 justify-content-end">
                                    <button class="btn btn-ghost-premium px-4 d-none" type="button" id="openDamageDeclarationButton">Enregistrer un dommage</button>
                                    <button class="btn btn-premium px-4" type="button" data-bs-toggle="modal" data-bs-target="#reportFormModal">Signaler un probleme</button>
                                </div>
                            </div>
                            <div class="mini-card mb-4">
                                <div class="row g-3">
                                    <div class="col-lg-4">
                                        <label class="form-label fw-semibold">Recherche</label>
                                        <input class="form-control" id="reportSearchFilter" placeholder="Reference, type, commune...">
                                    </div>
                                    <div class="col-md-4 col-lg-2">
                                        <label class="form-label fw-semibold">Statut</label>
                                        <select class="form-select" id="reportStatusFilter">
                                            <option value="">Tous</option>
                                            <option value="submitted">Soumis</option>
                                            <option value="in_progress">En cours de traitement</option>
                                            <option value="resolved">Resolu par l'institution</option>
                                            <option value="rejected">Non retenu</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-lg-2">
                                        <label class="form-label fw-semibold">Paiement</label>
                                        <select class="form-select" id="reportPaymentFilter">
                                            <option value="">Tous</option>
                                            <option value="paid">Paye</option>
                                            <option value="pending">En attente</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-lg-2">
                                        <label class="form-label fw-semibold">Organisation</label>
                                        <select class="form-select" id="reportOrganizationFilter">
                                            <option value="">Toutes</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-lg-3">
                                        <label class="form-label fw-semibold">Resolution</label>
                                        <select class="form-select" id="reportResolutionFilter">
                                            <option value="">Toutes</option>
                                            <option value="awaiting_institution">En attente de traitement</option>
                                            <option value="institution_resolved">Resolu, en attente de votre confirmation</option>
                                            <option value="confirmed">Resolution confirmee</option>
                                            <option value="rejected">Signalement non retenu</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-1 d-flex align-items-end">
                                        <button class="btn btn-ghost-premium w-100" type="button" id="resetReportFiltersButton">Reset</button>
                                    </div>
                                </div>
                            </div>
                            <div id="reportsList"></div>
                        </div>
                    </section>

                    <section class="public-panel" data-panel="payments">
                        <div class="dashboard-card">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                <div>
                                    <div class="section-title">Historique des paiements</div>
                                    <div class="muted-label">Retrouve tous tes paiements confirmes ou en attente, puis telecharge ton recu quand il est disponible.</div>
                                </div>
                                <button class="btn btn-ghost-premium px-4" type="button" data-panel-target="reports">Voir les signalements</button>
                            </div>
                            <div class="mini-card mb-4">
                                <div class="row g-3">
                                    <div class="col-lg-5">
                                        <label class="form-label fw-semibold">Recherche</label>
                                        <input class="form-control" id="paymentSearchFilter" placeholder="Reference paiement, signalement, canal...">
                                    </div>
                                    <div class="col-md-4 col-lg-3">
                                        <label class="form-label fw-semibold">Statut</label>
                                        <select class="form-select" id="paymentStatusFilter">
                                            <option value="">Tous</option>
                                            <option value="paid">Confirmes</option>
                                            <option value="pending">En attente</option>
                                            <option value="failed">Echoues</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-lg-3">
                                        <label class="form-label fw-semibold">Recu</label>
                                        <select class="form-select" id="paymentReceiptFilter">
                                            <option value="">Tous</option>
                                            <option value="available">Disponible</option>
                                            <option value="unavailable">Indisponible</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-lg-1 d-flex align-items-end">
                                        <button class="btn btn-ghost-premium w-100" type="button" id="resetPaymentFiltersButton">Reset</button>
                                    </div>
                                </div>
                            </div>
                            <div id="paymentsList"></div>
                        </div>
                    </section>

                    <section class="public-panel" data-panel="damages">
                        <div class="dashboard-card">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                <div>
                                    <div class="section-title">Historique des dommages</div>
                                    <div class="muted-label">Retrouvez les dommages declares apres resolution, leur statut de traitement et leurs justificatifs.</div>
                                </div>
                                <button class="btn btn-ghost-premium px-4" type="button" data-panel-target="reports">Voir les signalements</button>
                            </div>
                            <div class="mini-card mb-4">
                                <div class="row g-3">
                                    <div class="col-lg-4">
                                        <label class="form-label fw-semibold">Recherche</label>
                                        <input class="form-control" id="damageSearchFilter" placeholder="Reference, resume, organisation...">
                                    </div>
                                    <div class="col-md-4 col-lg-3">
                                        <label class="form-label fw-semibold">Organisation</label>
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
                                            <option value="resolved">Resolu</option>
                                            <option value="rejected">Rejete</option>
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
                                        <button class="btn btn-ghost-premium w-100" type="button" id="resetDamageFiltersButton">Reset</button>
                                    </div>
                                </div>
                            </div>
                            <div id="damagesList"></div>
                        </div>
                    </section>

                    <section class="public-panel" data-panel="cases">
                        <div class="dashboard-card">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                <div>
                                    <div class="section-title">Historique de mes dossiers</div>
                                    <div class="muted-label">Consulte l avancement des dossiers ouverts a partir de tes signalements et les mises a jour enregistrees par le traitement du dossier.</div>
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
                            <div class="h5 fw-bold mb-0">Declarer un probleme</div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                            <div class="modal-body p-4 p-lg-4">
                        <form id="reportForm" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Application concernee<span class="required-star">*</span></label>
                                <div class="select-search-shell">
                                    <input class="form-control select-search-input" style="display:block;width:100%;" type="search" data-search-select-target="reportApplicationId" autocomplete="off" placeholder="Rechercher une application">
                                    <button class="select-search-toggle" type="button" data-search-toggle-target="reportApplicationId" aria-label="Afficher les options"></button>
                                </div>
                                <div class="select-search-help">Champ de selection avec recherche.</div>
                                <select class="form-select d-none" id="reportApplicationId" required></select>
                                <div class="select-search-results" id="reportApplicationIdResults"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Organisation concernee<span class="required-star">*</span></label>
                                <div class="select-search-shell">
                                    <input class="form-control select-search-input" style="display:block;width:100%;" type="search" data-search-select-target="reportOrganizationType" autocomplete="off" placeholder="Rechercher une organisation">
                                    <button class="select-search-toggle" type="button" data-search-toggle-target="reportOrganizationType" aria-label="Afficher les options"></button>
                                </div>
                                <div class="select-search-help">Champ de selection avec recherche.</div>
                                <select class="form-select d-none" id="reportOrganizationType" required></select>
                                <div class="select-search-results" id="reportOrganizationTypeResults"></div>
                                <div class="location-search-hint">Choisissez d abord l application, puis l organisation concernee, pour afficher uniquement les identifiants et types de signal compatibles.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">identifiant<span class="required-star">*</span></label>
                                <div class="select-search-shell">
                                    <input class="form-control select-search-input" style="display:block;width:100%;" type="search" data-search-select-target="reportMeterId" autocomplete="off" placeholder="Rechercher un identifiant">
                                    <button class="select-search-toggle" type="button" data-search-toggle-target="reportMeterId" aria-label="Afficher les options"></button>
                                </div>
                                <div class="select-search-help">Champ de selection avec recherche.</div>
                                <select class="form-select d-none" name="meter_id" id="reportMeterId" required></select>
                                <div class="select-search-results" id="reportMeterIdResults"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Type de signal<span class="required-star">*</span></label>
                                <div class="select-search-shell">
                                    <input class="form-control select-search-input" style="display:block;width:100%;" type="search" data-search-select-target="reportSignalCode" autocomplete="off" placeholder="Rechercher un type de signal">
                                    <button class="select-search-toggle" type="button" data-search-toggle-target="reportSignalCode" aria-label="Afficher les options"></button>
                                </div>
                                <div class="select-search-help">Champ de selection avec recherche.</div>
                                <select class="form-select d-none" name="signal_code" id="reportSignalCode" required></select>
                                <div class="select-search-results" id="reportSignalCodeResults"></div>
                                <div class="location-search-hint mt-2" id="reportSignalInlineDescription">Selectionnez un type de signal pour afficher sa description et son delai de resolution.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Date et heure<span class="required-star">*</span></label>
                                <input class="form-control" type="datetime-local" name="occurred_at" id="reportOccurredAt" readonly>
                                <div class="location-search-hint">La date et l heure actuelles sont appliquees automatiquement au signalement.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Pays<span class="required-star">*</span></label>
                                <div class="select-search-shell">
                                    <input class="form-control select-search-input" style="display:block;width:100%;" type="search" data-search-select-target="reportCountryId" autocomplete="off" placeholder="Rechercher un pays">
                                    <button class="select-search-toggle" type="button" data-search-toggle-target="reportCountryId" aria-label="Afficher les options"></button>
                                </div>
                                <div class="select-search-help">Champ de selection avec recherche.</div>
                                <select class="form-select d-none" name="country_id" id="reportCountryId" required></select>
                                <div class="select-search-results" id="reportCountryIdResults"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Ville<span class="required-star">*</span></label>
                                <div class="select-search-shell">
                                    <input class="form-control select-search-input" style="display:block;width:100%;" type="search" data-search-select-target="reportCityId" autocomplete="off" placeholder="Rechercher une ville">
                                    <button class="select-search-toggle" type="button" data-search-toggle-target="reportCityId" aria-label="Afficher les options"></button>
                                </div>
                                <div class="select-search-help">Champ de selection avec recherche.</div>
                                <select class="form-select d-none" name="city_id" id="reportCityId" required></select>
                                <div class="select-search-results" id="reportCityIdResults"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Commune<span class="required-star">*</span></label>
                                <div class="select-search-shell">
                                    <input class="form-control select-search-input" style="display:block;width:100%;" type="search" data-search-select-target="reportCommuneId" autocomplete="off" placeholder="Rechercher une commune">
                                    <button class="select-search-toggle" type="button" data-search-toggle-target="reportCommuneId" aria-label="Afficher les options"></button>
                                </div>
                                <div class="select-search-help">Champ de selection avec recherche.</div>
                                <select class="form-select d-none" name="commune_id" id="reportCommuneId" required></select>
                                <div class="select-search-results" id="reportCommuneIdResults"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Adresse du lieu</label>
                                <input class="form-control" name="address" id="reportAddressSearch" placeholder="Rechercher une adresse ou utiliser la position automatique">
                                <div class="location-search-hint">Ce champ peut recuperer automatiquement la localisation du signalement a partir de Google Places.</div>
                            </div>
                            <div class="col-12">
                                <div class="soft-panel">
                                    <div class="fw-bold mb-1">Position du signalement</div>
                                    <div class="muted-label">La position du signalement est recuperee automatiquement en arriere-plan. Si elle n est pas disponible, la position du identifiant ou l adresse choisie est utilisee automatiquement.</div>
                                </div>
                                <input type="hidden" name="latitude" id="reportLatitude">
                                <input type="hidden" name="longitude" id="reportLongitude">
                                <input type="hidden" name="location_accuracy" id="reportAccuracy">
                                <input type="hidden" name="location_source" id="reportLocationSource" value="">
                            </div>
                            <div class="col-12">
                                <div class="soft-panel d-none" id="reportNoMeterHint">
                                    <div class="fw-bold mb-1">Aucun identifiant disponible pour ce reseau</div>
                                    <div class="muted-label">Ajoutez d abord un identifiant sur ce reseau dans la section <strong>Mes identifiants</strong>, puis revenez declarer votre signalement.</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="signal-field-card">
                                    <div class="fw-bold mb-2" id="reportSignalMetaTitle">Donnees complementaires</div>
                                    <div class="muted-label mb-3" id="reportSignalMetaDescription">Selectionnez un type de signal pour voir les donnees requises.</div>
                                    <div id="signalPayloadFields" class="row g-3"></div>
                                </div>
                            </div>
                            <div class="col-12"><label class="form-label fw-semibold">Description</label><textarea class="form-control" name="description" rows="4"></textarea></div>
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
                            <div class="small text-white-50 fw-semibold mb-1">Detail du signalement</div>
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

        <div class="modal fade" id="damageDeclarationModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content border-0" style="border-radius: 28px; overflow: hidden;">
                    <div class="modal-header px-4 py-3 border-0" style="background: var(--acepen-copper); color: white;">
                        <div>
                            <div class="small text-white-50 fw-semibold mb-1">Declaration de dommage</div>
                            <div class="h5 fw-bold mb-0" id="damageDeclarationTitle">Signaler un dommage apres resolution</div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <form id="damageDeclarationForm" class="row g-3">
                            <input type="hidden" name="report_id" id="damageDeclarationReportId">
                            <div class="col-12">
                                <div class="soft-panel">
                                    <div class="fw-bold mb-1">Quand utiliser ce bouton ?</div>
                                    <div class="muted-label">Utilisez cette declaration si le probleme a bien ete traite mais qu un dommage materiel, financier ou d usage reste a signaler apres la resolution du sinistre.</div>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Resume du dommage</label>
                                <input class="form-control" type="text" name="damage_summary" maxlength="255" placeholder="Ex: appareils endommages, denrees perdues, installation interne touchee" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Montant estime (FCFA)</label>
                                <input class="form-control" type="number" min="0" step="0.01" name="damage_amount_estimated" placeholder="15000">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Justificatif</label>
                                <input class="form-control" type="file" id="damageAttachmentInput" accept="image/*,application/pdf">
                                <div class="geo-help mt-2">Photo ou PDF facultatif. Sur mobile, l appareil peut proposer directement la camera si disponible.</div>
                            </div>
                            <div class="col-12 d-none" id="damageAttachmentPreviewWrap">
                                <div class="soft-panel">
                                    <div class="small text-secondary fw-semibold mb-2">Apercu du justificatif</div>
                                    <img id="damageAttachmentPreviewImage" alt="Apercu du justificatif" class="img-fluid rounded-4 d-none" style="max-height: 220px; object-fit: cover;">
                                    <div id="damageAttachmentPreviewFile" class="fw-semibold d-none"></div>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Details complementaires</label>
                                <textarea class="form-control" name="damage_notes" rows="4" placeholder="Expliquez l impact du sinistre, ce qui reste endommage et les informations utiles pour l analyse."></textarea>
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
                            <div class="small text-white-50 fw-semibold mb-1">Apercu du recu</div>
                            <div class="h5 fw-bold mb-0" id="paymentReceiptPreviewTitle">Recu de paiement</div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4" style="background: #f4f7fb;">
                        <div id="paymentReceiptPreviewContent"></div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4 pt-0" style="background: #f4f7fb;">
                        <button class="btn btn-ghost-premium px-4" type="button" data-bs-dismiss="modal">Fermer</button>
                        <button class="btn btn-premium px-4" type="button" id="paymentReceiptPreviewDownloadButton">Telecharger le PDF</button>
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
                const dialCodeOptions = @json($dialCodeOptions);
                const publicUserTypes = @json($publicUserTypesPayload);
                const serviceApplications = @json($serviceApplicationsPayload);
                const state = {
                    token: localStorage.getItem(tokenKey),
                    currentUser: null,
                    household: null,
                    pendingHouseholdInvitations: [],
                    meters: [],
                    payments: [],
                    reparationCases: [],
                    countries: [],
                    communes: [],
                    reports: [],
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
                    damageFilters: {
                        search: '',
                        organization: '',
                        resolution: '',
                        attachment: '',
                    },
                    autoGeoAttempts: {
                        profile: false,
                        meter: false,
                        report: false,
                    },
                };
                const toast = new bootstrap.Toast(document.getElementById('appToast'));
                const reportFormModalElement = document.getElementById('reportFormModal');
                const meterFormWrapElement = document.getElementById('meterFormWrap');
                const reportFormModal = reportFormModalElement ? bootstrap.Modal.getOrCreateInstance(reportFormModalElement) : null;
                const reportDetailModal = document.getElementById('reportDetailModal') ? bootstrap.Modal.getOrCreateInstance(document.getElementById('reportDetailModal')) : null;
                const damageDeclarationModalElement = document.getElementById('damageDeclarationModal');
                const damageDeclarationModal = damageDeclarationModalElement ? bootstrap.Modal.getOrCreateInstance(damageDeclarationModalElement) : null;
                const paymentReceiptPreviewModalElement = document.getElementById('paymentReceiptPreviewModal');
                const paymentReceiptPreviewModal = paymentReceiptPreviewModalElement ? bootstrap.Modal.getOrCreateInstance(paymentReceiptPreviewModalElement) : null;

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
                        : '<div class="select-search-empty">Aucun resultat</div>';

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
                            <input class="form-control public-select-input" id="${selectId}PublicInput" type="search" autocomplete="off" placeholder="Rechercher ou selectionner">
                            <button class="public-select-toggle" id="${selectId}PublicToggle" type="button" aria-label="Afficher les options"></button>
                        `;
                        const help = document.createElement('div');
                        help.className = 'public-select-help';
                        help.textContent = 'Champ de selection avec recherche.';
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
                        : '<div class="select-search-empty">Aucun resultat</div>';

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
                        field.required = showBusinessFields;
                    });

                    if (sectorFieldsContainer) {
                        sectorFieldsContainer.classList.toggle('hidden', !showSectorFields);
                        sectorFieldsContainer.querySelectorAll('input, select, textarea').forEach((field) => {
                            field.disabled = !showSectorFields;
                            field.required = showSectorFields;
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
                        showToast('Position du identifiant appliquee au signalement.');
                    }
                }

                function fillGeoFields(prefix, coords, source = 'device_gps') {
                    document.getElementById(`${prefix}Latitude`).value = Number(coords.latitude).toFixed(7);
                    document.getElementById(`${prefix}Longitude`).value = Number(coords.longitude).toFixed(7);
                    document.getElementById(`${prefix}Accuracy`).value = coords.accuracy ? Math.round(coords.accuracy) : '';
                    document.getElementById(`${prefix}LocationSource`).value = source;

                    if (prefix === 'report') {
                        syncAutoSignalPayloadFields();
                    }
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
                            return 'Acces a la position refuse. Active la localisation dans les parametres puis reessaie.';
                        case error.POSITION_UNAVAILABLE:
                            return 'Position indisponible. Active les services de localisation du telephone ou du navigateur, ou utilise la saisie manuelle.';
                        case error.TIMEOUT:
                            return 'Le delai de recuperation de la position a expire. Reessaie ou utilise la saisie manuelle.';
                        default:
                            return error.message || 'Impossible de recuperer la position.';
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
                            showToast('La geolocalisation n est pas disponible sur cet appareil. Utilise la saisie manuelle.', true);
                        }
                        return;
                    }

                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            setGeoManualMode(prefix, false);
                            fillGeoFields(prefix, position.coords);
                            if (!silent) {
                                showToast('Position geographique recuperee.');
                            }
                        },
                        (error) => {
                            if (!silent) {
                                showToast(translateGeoError(error), true);

                                if (error.code === error.PERMISSION_DENIED && force) {
                                    setTimeout(() => {
                                        if (!openLocationSettings()) {
                                            showToast('Ouvre les parametres de ton navigateur et active la localisation pour ce site.', true);
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
                    const headers = {
                        Accept: 'application/json',
                        ...(options.body ? { 'Content-Type': 'application/json' } : {}),
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
                    const savedPanel = sessionStorage.getItem(dashboardPanelStorageKey);
                    activatePanel(savedPanel || 'overview');
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
                    ['profileCommuneSelect', 'meterCommuneSelect', 'householdCommuneSelect'].forEach((id) => {
                        const select = document.getElementById(id);
                        select.innerHTML = options;
                        if (selectedName && state.communes.some((commune) => commune.name === selectedName)) {
                            select.value = selectedName;
                        }
                    });
                    populateMeterNeighborhoodOptions();
                }

                function getCommuneByName(name) {
                    return state.communes.find((commune) => commune.name === name) || null;
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

                function buildOptions(items, placeholder = 'Selectionner') {
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
                    const countryCandidate = [
                        findAddressComponent(place, ['country'])?.long_name,
                        findAddressComponent(place, ['country'])?.short_name,
                    ].find(Boolean);
                    const cityCandidate = [
                        findAddressComponent(place, ['locality'])?.long_name,
                        findAddressComponent(place, ['administrative_area_level_2'])?.long_name,
                    ].find(Boolean);
                    const communeCandidate = [
                        findAddressComponent(place, ['sublocality_level_1'])?.long_name,
                        findAddressComponent(place, ['sublocality'])?.long_name,
                        cityCandidate,
                    ].find(Boolean);

                    const country = state.countries.find((item) => normalizeText(item.name) === normalizeText(countryCandidate) || normalizeText(item.code) === normalizeText(countryCandidate));

                    if (country) {
                        document.getElementById('reportCountryId').value = String(country.id);
                        document.getElementById('reportCityId').innerHTML = buildOptions(country.cities || [], 'Aucune ville');
                    }

                    const selectedCountry = country || state.countries.find((item) => String(item.id) === String(document.getElementById('reportCountryId').value));
                    const city = (selectedCountry?.cities || []).find((item) => normalizeText(item.name) === normalizeText(cityCandidate));

                    if (city) {
                        document.getElementById('reportCityId').value = String(city.id);
                        document.getElementById('reportCommuneId').innerHTML = buildOptions(city.communes || [], 'Aucune commune');
                    }

                    const selectedCity = city || (selectedCountry?.cities || []).find((item) => String(item.id) === String(document.getElementById('reportCityId').value));
                    const commune = (selectedCity?.communes || []).find((item) => normalizeText(item.name) === normalizeText(communeCandidate));

                    if (commune) {
                        document.getElementById('reportCommuneId').value = String(commune.id);
                    }
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
                    attachAddressAutocomplete('reportAddressSearch', 'report');
                }

                function populateReportLocationSelects() {
                    const countrySelect = document.getElementById('reportCountryId');
                    const citySelect = document.getElementById('reportCityId');
                    const communeSelect = document.getElementById('reportCommuneId');
                    countrySelect.innerHTML = buildOptions(state.countries, 'Aucun pays');
                    const country = state.countries.find((item) => String(item.id) === String(countrySelect.value)) || state.countries[0];
                    if (!country) {
                        citySelect.innerHTML = '<option value="">Aucune ville</option>';
                        communeSelect.innerHTML = '<option value="">Aucune commune</option>';
                        return;
                    }
                    countrySelect.value = country.id;
                    citySelect.innerHTML = buildOptions(country.cities || [], 'Aucune ville');
                    const city = (country.cities || []).find((item) => String(item.id) === String(citySelect.value)) || (country.cities || [])[0];
                    if (!city) {
                        communeSelect.innerHTML = '<option value="">Aucune commune</option>';
                        return;
                    }
                    citySelect.value = city.id;
                    communeSelect.innerHTML = buildOptions(city.communes || [], 'Aucune commune');
                    communeSelect.value = city.communes?.[0]?.id || '';
                    refreshSearchableSelect('reportCountryId');
                    refreshSearchableSelect('reportCityId');
                    refreshSearchableSelect('reportCommuneId');
                }

                function getAvailableReportOrganizations() {
                    const applicationId = String(document.getElementById('reportApplicationId')?.value || '');
                    const application = serviceApplications.find((item) => String(item.id) === applicationId);

                    return application?.organizations || [];
                }

                function getSelectedReportOrganizationId() {
                    return document.getElementById('reportOrganizationType').value || '';
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
                        : '<option value="">Aucune application disponible</option>';

                    if (!serviceApplications.length) {
                        applicationSelect.value = '';
                        organizationSelect.innerHTML = '<option value="">Aucune organisation disponible</option>';
                        organizationSelect.value = '';
                        return;
                    }

                    const preferredApplication = serviceApplications.find((application) => application.code === preferredNetwork || application.network_type === preferredNetwork);
                    applicationSelect.value = String(preferredApplication?.id || serviceApplications[0]?.id || '');
                    refreshSearchableSelect('reportApplicationId');

                    renderReportOrganizationOptions();
                }

                function renderReportOrganizationOptions(preferredOrganizationId = null) {
                    const organizationSelect = document.getElementById('reportOrganizationType');
                    const organizations = getAvailableReportOrganizations();

                    organizationSelect.innerHTML = organizations.length
                        ? organizations.map((organization) => `<option value="${organization.id}">${organization.name}</option>`).join('')
                        : '<option value="">Aucune organisation disponible</option>';

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
                        : '<option value="">Aucune application disponible</option>';

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
                        : '<option value="">Aucune organisation disponible</option>';

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
                    const filteredMeters = getFilteredMetersForSelectedNetwork();
                    const noMeterHint = document.getElementById('reportNoMeterHint');

                    meterSelect.innerHTML = filteredMeters.length
                        ? filteredMeters.map((meter) => `<option value="${meter.id}">${meter.organization_name || meter.network_type} · ${meter.meter_number}${meter.label ? ' · ' + meter.label : ''}</option>`).join('')
                        : '<option value="">Aucun identifiant disponible</option>';

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
                    if (!meter) return [];
                    const matchingTypes = state.signalTypes.filter((type) => {
                        if (String(type.application_id) !== String(meter.application_id)) {
                            return false;
                        }

                        if (type.organization_id === null || type.organization_id === undefined || type.organization_id === '') {
                            return true;
                        }

                        return String(type.organization_id) === String(meter.organization_id);
                    });

                    const deduplicatedTypes = new Map();

                    matchingTypes.forEach((type) => {
                        const existing = deduplicatedTypes.get(type.code);

                        if (!existing) {
                            deduplicatedTypes.set(type.code, type);
                            return;
                        }

                        const currentSpecificity = type.organization_id ? 1 : 0;
                        const existingSpecificity = existing.organization_id ? 1 : 0;

                        if (currentSpecificity >= existingSpecificity) {
                            deduplicatedTypes.set(type.code, type);
                        }
                    });

                    return Array.from(deduplicatedTypes.values());
                }

                function isPhotoSignalField(field) {
                    return ['photo_reference', 'meter_photo_reference'].includes(field.key);
                }

                function isGpsSignalField(field) {
                    return ['precise_gps', 'gps_location'].includes(field.key);
                }

                function getCurrentReportGpsValue() {
                    const latitude = document.getElementById('reportLatitude').value;
                    const longitude = document.getElementById('reportLongitude').value;

                    if (latitude && longitude) {
                        return `${latitude}, ${longitude}`;
                    }

                    const meter = state.meters.find((item) => String(item.id) === String(document.getElementById('reportMeterId').value));

                    if (meter?.latitude && meter?.longitude) {
                        return `${meter.latitude}, ${meter.longitude}`;
                    }

                    return '';
                }

                function syncAutoSignalPayloadFields() {
                    const gpsValue = getCurrentReportGpsValue();

                    document.querySelectorAll('[data-signal-auto-gps="1"]').forEach((field) => {
                        field.value = gpsValue;
                    });
                }

                function readFileAsDataUrl(file) {
                    return new Promise((resolve, reject) => {
                        const reader = new FileReader();
                        reader.onload = () => resolve(String(reader.result || ''));
                        reader.onerror = () => reject(new Error('Impossible de lire le fichier image.'));
                        reader.readAsDataURL(file);
                    });
                }

                function renderPhotoPreview(input) {
                    const preview = document.querySelector(`[data-signal-preview-for="${input.dataset.signalKey}"]`);

                    if (!preview) {
                        return;
                    }

                    const file = input.files?.[0];

                    if (!file) {
                        preview.classList.add('d-none');
                        preview.src = '';
                        return;
                    }

                    document.querySelectorAll(`[data-signal-field-type="photo"][data-signal-key="${input.dataset.signalKey}"]`).forEach((candidate) => {
                        if (candidate !== input) {
                            candidate.value = '';
                        }
                    });

                    const objectUrl = URL.createObjectURL(file);
                    preview.src = objectUrl;
                    preview.classList.remove('d-none');
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
                    const availableSignals = getSignalTypesForCurrentMeter();
                    const signal = availableSignals.find((item) => item.code === signalSelect.value) || availableSignals[0];
                    const selectedMeter = state.meters.find((item) => String(item.id) === String(document.getElementById('reportMeterId')?.value || ''));
                    const organizationTypeId = selectedMeter?.organization_type_id ? String(selectedMeter.organization_type_id) : null;
                    const inlineDescription = document.getElementById('reportSignalInlineDescription');
                    const title = document.getElementById('reportSignalMetaTitle');
                    const description = document.getElementById('reportSignalMetaDescription');
                    const container = document.getElementById('signalPayloadFields');

                    if (!signal) {
                        inlineDescription.textContent = 'Selectionnez un type de signal pour afficher sa description et son delai de resolution.';
                        title.textContent = 'Donnees complementaires';
                        description.textContent = 'Selectionnez un type de signal pour voir les donnees requises.';
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

                    signalDescriptionParts.push(slaLabel ? `SLA cible ${slaLabel}` : 'SLA cible non defini');
                    inlineDescription.textContent = signalDescriptionParts.join(' · ');
                    description.textContent = signalDescriptionParts.join(' · ');

                    if (!signal.data_fields?.length) {
                        container.innerHTML = '<div class="col-12"><div class="muted-label">Aucune donnee complementaire requise pour ce signal.</div></div>';
                        return;
                    }

                    container.innerHTML = signal.data_fields.map((field) => `
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">${field.label}${field.required ? '<span class="required-star">*</span>' : ''}</label>
                            ${isPhotoSignalField(field)
                                ? `
                                    <div class="d-flex gap-2 flex-wrap">
                                        <label class="btn btn-ghost-premium px-4 mb-0">
                                            Prendre une photo
                                            <input class="d-none" type="file" accept="image/*" capture="environment" data-signal-key="${field.key}" data-signal-field-type="photo" ${field.required ? 'required' : ''}>
                                        </label>
                                        <label class="btn btn-ghost-premium px-4 mb-0">
                                            Choisir depuis la galerie
                                            <input class="d-none" type="file" accept="image/*" data-signal-key="${field.key}" data-signal-field-type="photo" ${field.required ? 'required' : ''}>
                                        </label>
                                    </div>
                                    <div class="muted-label mt-2">Choisissez une image existante ou prenez-la directement depuis l appareil.</div>
                                    <img class="img-fluid rounded-4 border mt-3 d-none" data-signal-preview-for="${field.key}" alt="Apercu photo ${field.label}" style="max-height: 220px; object-fit: cover;">
                                `
                                : isGpsSignalField(field)
                                    ? `<input class="form-control" type="text" data-signal-key="${field.key}" data-signal-auto-gps="1" placeholder="${field.label}" readonly ${field.required ? 'required' : ''}>`
                                    : field.type === 'select'
                                        ? `
                                            <select class="form-select" data-signal-key="${field.key}" ${field.required ? 'required' : ''}>
                                                <option value="">Selectionner</option>
                                                ${(field.options || []).map((option) => `<option value="${escapeHtml(option)}">${escapeHtml(option)}</option>`).join('')}
                                            </select>
                                        `
                                        : field.type === 'textarea'
                                            ? `<textarea class="form-control" rows="3" data-signal-key="${field.key}" placeholder="${field.label}" ${field.required ? 'required' : ''}></textarea>`
                                            : `<input class="form-control" type="${field.type === 'number' ? 'number' : 'text'}" data-signal-key="${field.key}" placeholder="${field.label}" ${field.required ? 'required' : ''}>`}
                        </div>
                    `).join('');

                    container.querySelectorAll('[data-signal-field-type="photo"]').forEach((input) => {
                        input.addEventListener('change', () => renderPhotoPreview(input));
                    });

                    syncAutoSignalPayloadFields();
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
                    document.getElementById('sidebarUserLocation').textContent = [user.commune, user.address].filter(Boolean).join(' · ') || 'Localisation non renseignee';
                    const hasSidebarGps = !!(user.latitude && user.longitude);
                    document.getElementById('sidebarUserGps').textContent = hasSidebarGps
                        ? `GPS ${user.latitude}, ${user.longitude}`
                        : 'GPS non renseigne';
                    document.getElementById('sidebarRequestGpsButton')?.classList.toggle('d-none', hasSidebarGps);
                    setTextIfExists('overviewUserName', `${user.first_name} ${user.last_name}`);
                    setTextIfExists('overviewProfileLine', [user.phone, user.commune].filter(Boolean).join(' · ') || 'Informations de profil a completer');
                    document.getElementById('profileFullNameCard').textContent = `${user.first_name} ${user.last_name}`;
                    document.getElementById('profilePhoneCard').textContent = user.phone || '-';
                    document.getElementById('profileCommuneCard').textContent = user.commune || '-';
                    document.getElementById('profileAddressCard').textContent = user.address || 'Adresse non renseignee';
                    document.getElementById('profileGpsCard').textContent = user.latitude && user.longitude ? `GPS ${user.latitude}, ${user.longitude}` : 'Position GPS non renseignee';
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
                }

                function escapeHtml(value) {
                    return String(value ?? '')
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#39;');
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
                            ? meters.map((meter) => `<option value="${meter.id}">${meter.organization_name || meter.network_type} · ${meter.meter_number}${meter.label ? ' · ' + meter.label : ''}</option>`).join('')
                            : '<option value="">Aucun identifiant disponible</option>';
                    }
                    renderSignalOptions();

                    setTextIfExists('overviewPrimaryMeter', primaryMeter
                        ? `${primaryMeter.organization_name || primaryMeter.network_type} · ${primaryMeter.meter_number}`
                        : 'Aucun identifiant principal');
                    setTextIfExists('overviewPrimaryMeterMeta', primaryMeter
                        ? [primaryMeter.label, primaryMeter.commune, primaryMeter.address].filter(Boolean).join(' · ') || 'identifiant pret pour les declarations'
                        : 'Ajoute un identifiant pour accelerer tes declarations.');

                    const list = document.getElementById('metersList');
                    if (!meters.length) {
                        list.innerHTML = '<div class="col-12"><div class="mini-card"><div class="fw-bold mb-1">Aucun identifiant enregistre</div><div class="muted-label">Ajoutez vos identifiants pour alimenter vos futurs signalements.</div></div></div>';
                        return;
                    }
                    list.innerHTML = meters.map((meter) => `
                        <div class="col-md-6 col-xl-4">
                            <div class="mini-card h-100">
                                <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                                    <div><div class="fw-bold">${meter.label || meter.organization_name || meter.network_type}</div><div class="muted-label">${meter.meter_number}</div></div>
                                    <span class="status-pill">${meter.is_primary ? 'Principal' : (meter.organization_name || meter.network_type)}</span>
                                </div>
                                <div class="muted-label mb-2">${meter.application_name || 'Application non definie'}</div>
                                <div class="muted-label mb-3">${[meter.commune, meter.neighborhood, meter.sub_neighborhood].filter(Boolean).join(' · ') || 'Commune non renseignee'}${meter.address ? ' · ' + meter.address : ''}</div>
                                <div class="muted-label mb-3">${meter.latitude && meter.longitude ? `GPS ${meter.latitude}, ${meter.longitude}` : 'Position GPS non renseignee'}</div>
                                <button class="btn btn-ghost-premium w-100" type="button" onclick="window.AcepenPortal.prefillMeter(${meter.id})">Modifier</button>
                            </div>
                        </div>
                    `).join('');
                }

                function renderHousehold(household) {
                    state.household = household;
                    document.getElementById('householdMemberCount').textContent = household?.members?.length ?? 0;
                    setTextIfExists('overviewHouseholdSummary', household
                        ? household.name || 'Gbonhi principal'
                        : 'Aucun Gbonhi enregistre');
                    setTextIfExists('overviewHouseholdMeta', household
                        ? `${household.members?.length ?? 0} membre(s) · ${household.pending_invitations?.length ?? 0} invitation(s) en attente`
                        : 'Cree un Gbonhi pour centraliser les signalements familiaux.');

                    const emptyState = document.getElementById('householdEmptyState');
                    const panel = document.getElementById('householdPanel');
                    if (!household) {
                        emptyState.classList.remove('d-none');
                        panel.classList.add('d-none');
                        return;
                    }
                    emptyState.classList.add('d-none');
                    panel.classList.remove('d-none');
                    document.getElementById('householdName').textContent = household.name || 'Gbonhi principal';
                    document.getElementById('householdAddress').textContent = [household.commune, household.address].filter(Boolean).join(' · ') || 'Adresse non renseignee';
                    document.getElementById('householdStatus').textContent = household.status || 'active';
                    document.getElementById('householdMembersList').innerHTML = household.members?.length
                        ? household.members.map((member) => `<div class="d-flex justify-content-between align-items-center rounded-4 border px-3 py-3"><div><div class="fw-semibold">${member.user.first_name ?? ''} ${member.user.last_name ?? ''}</div><div class="muted-label">${member.user.phone ?? ''} · ${member.relationship}</div></div><span class="status-pill">${member.is_owner ? 'Titulaire' : 'Membre'}</span></div>`).join('')
                        : '<div class="muted-label">Aucun membre.</div>';
                    document.getElementById('householdInvitationsList').innerHTML = household.pending_invitations?.length
                        ? household.pending_invitations.map((invitation) => `<div class="d-flex justify-content-between align-items-center rounded-4 border px-3 py-3"><div><div class="fw-semibold">${invitation.phone}</div><div class="muted-label">${invitation.relationship}</div></div><span class="status-pill">En attente</span></div>`).join('')
                        : '<div class="muted-label">Aucune invitation en attente.</div>';
                }

                function renderIncomingHouseholdInvitations(invitations) {
                    state.pendingHouseholdInvitations = invitations;
                    const list = document.getElementById('incomingHouseholdInvitationsList');

                    if (!invitations.length) {
                        list.innerHTML = '<div class="muted-label">Aucune invitation recue pour le moment.</div>';
                        return;
                    }

                    list.innerHTML = invitations.map((invitation) => `
                        <div class="d-flex justify-content-between align-items-center rounded-4 border px-3 py-3 gap-3 flex-wrap">
                            <div>
                                <div class="fw-semibold">${invitation.household?.name || 'Gbonhi familial'}</div>
                                <div class="muted-label">${[invitation.relationship, invitation.household?.commune, invitation.household?.address].filter(Boolean).join(' · ')}</div>
                                <div class="muted-label">${invitation.meter ? `identifiant commun: ${(invitation.meter.organization_name || invitation.meter.network_type)} · ${invitation.meter.meter_number}${invitation.meter.label ? ' · ' + invitation.meter.label : ''}` : 'Aucun identifiant commun defini'}</div>
                                <div class="muted-label">Expire le ${formatDateTime(invitation.expires_at)}</div>
                            </div>
                            <div class="report-actions">
                                <button class="btn btn-ghost-premium btn-sm px-3" type="button" onclick="window.AcepenPortal.declineInvitation(${invitation.id})">Decliner</button>
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
                                <div class="muted-label mb-3">Vos futures declarations apparaitront ici avec leur statut, leur organisation et leur paiement.</div>
                                <button class="btn btn-premium px-4" type="button" data-panel-target="reports">Faire un signalement</button>
                            </div>
                        `;
                        list.querySelectorAll('[data-panel-target]').forEach((button) => {
                            button.addEventListener('click', () => activatePanel(button.dataset.panelTarget));
                        });
                        return;
                    }

                    const filteredReports = getOverviewFilteredReports(reports);
                    const totalPages = Math.max(1, Math.ceil(filteredReports.length / state.overviewReportsPageSize));
                    state.overviewReportsPage = Math.min(state.overviewReportsPage, totalPages);

                    if (!filteredReports.length) {
                        list.innerHTML = `
                            <div class="overview-report-empty">
                                <div class="fw-bold mb-1">Aucun signalement ne correspond aux filtres</div>
                                <div class="muted-label">Modifiez la recherche ou le statut pour retrouver vos declarations.</div>
                            </div>
                        `;
                        return;
                    }

                    const start = (state.overviewReportsPage - 1) * state.overviewReportsPageSize;
                    const currentReports = filteredReports.slice(start, start + state.overviewReportsPageSize);
                    const end = start + currentReports.length;

                    list.innerHTML = `
                        <div class="report-table-shell">
                            <div class="report-table-wrap">
                                <table class="overview-report-table">
                                    <thead>
                                        <tr>
                                            <th>Reference</th>
                                            <th>Signalement</th>
                                            <th>Organisation</th>
                                            <th>Statut</th>
                                            <th>Paiement</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${currentReports.map((report) => `
                                            <tr>
                                                <td>
                                                    <div class="report-ref">${report.reference}</div>
                                                    <div class="report-sub">${formatDateTime(report.created_at)}</div>
                                                </td>
                                                <td>
                                                    <div class="report-main">${report.signal_label || report.incident_type || report.signal_code}</div>
                                                    <div class="report-sub">${report.location?.commune || '-'}${report.location?.city ? ' · ' + report.location.city : ''}</div>
                                                </td>
                                                <td>
                                                    <div class="report-main">${report.organization?.name || report.network_type || '-'}</div>
                                                    <div class="report-sub">${report.application?.name || 'Application non definie'}</div>
                                                </td>
                                                <td><span class="status-pill status-report-${report.status}">${getPublicStatusLabel(report.status)}</span></td>
                                                <td><span class="status-pill status-payment-${report.payment_status}">${report.payment_status === 'paid' ? 'Paye' : 'En attente'}</span></td>
                                                <td class="text-end">
                                                    <button class="btn btn-sm btn-ghost-premium px-3" type="button" onclick="window.AcepenPortal.showReportDetails(${report.id})">Details</button>
                                                </td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                            <div class="pagination-shell">
                                <div class="pagination-info">Affichage ${start + 1} à ${end} sur ${filteredReports.length} signalement${filteredReports.length > 1 ? 's' : ''}</div>
                                <div class="pagination-actions">
                                    <button class="pagination-chip" type="button" ${state.overviewReportsPage === 1 ? 'disabled' : ''} onclick="window.AcepenPortal.changeOverviewReportsPage(${state.overviewReportsPage - 1})">‹</button>
                                    <div class="small fw-semibold text-secondary">Page ${state.overviewReportsPage} / ${totalPages}</div>
                                    <button class="pagination-chip" type="button" ${state.overviewReportsPage === totalPages ? 'disabled' : ''} onclick="window.AcepenPortal.changeOverviewReportsPage(${state.overviewReportsPage + 1})">›</button>
                                </div>
                            </div>
                        </div>
                    `;
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
                        list.innerHTML = '<div class="mini-card"><div class="fw-bold mb-1">Aucun signalement</div><div class="muted-label">Vos futurs signalements apparaitront ici avec leur reference unique.</div></div>';
                        return;
                    }
                    const filteredReports = getFilteredReports(reports);
                    const totalPages = Math.max(1, Math.ceil(filteredReports.length / state.reportsPageSize));
                    state.reportsPage = Math.min(state.reportsPage, totalPages);

                    if (!filteredReports.length) {
                        list.innerHTML = '<div class="mini-card"><div class="fw-bold mb-1">Aucun signalement ne correspond aux filtres</div><div class="muted-label">Ajuste les filtres pour retrouver plus facilement tes declarations.</div></div>';
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
                                            <th>Reference</th>
                                            <th>Signal</th>
                                            <th>Localisation</th>
                                            <th>Paiement</th>
                                            <th>Resolution</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${currentReports.map((report) => `
                                            <tr>
                                                <td>
                                                    <div class="report-ref">${report.reference}</div>
                                                    <div class="report-sub">${report.organization?.name || report.network_type} · ${report.signal_code}</div>
                                                    <div class="report-sub">${report.application?.name || 'Application non definie'}</div>
                                                    <div class="report-sub">SLA ${report.target_sla_hours ?? '-'}h</div>
                                                </td>
                                                <td>
                                                    <div class="report-main">${report.signal_label || report.incident_type}</div>
                                                    <div class="report-sub">${report.description || 'Aucune description fournie.'}</div>
                                                    <div class="report-sub mt-1">${report.organization?.name || report.organization_name || report.network_type || 'Organisation non definie'}</div>
                                                </td>
                                                <td>
                                                    <div class="report-main">${report.location.commune || '-'}</div>
                                                    <div class="report-sub">${[report.location.country, report.location.city].filter(Boolean).join(' · ')}</div>
                                                    <div class="report-sub">${report.location.address || 'Adresse non renseignee'}</div>
                                                    <div class="report-sub">${report.location.latitude && report.location.longitude ? `GPS ${report.location.latitude}, ${report.location.longitude}` : 'Position non renseignee'}</div>
                                                </td>
                                                <td>
                                                    <div class="report-main">${report.payment_status === 'paid' ? 'Paye' : 'En attente'}</div>
                                                    <div class="report-sub">Montant: 100 FCFA</div>
                                                    <div class="report-sub">${report.paid_at ? `Confirme le ${new Date(report.paid_at).toLocaleString()}` : 'Paiement non confirme'}</div>
                                                </td>
                                                <td>
                                                    <div class="report-main"><span class="status-pill ${getResolutionStatusClass(report)}">${getResolutionLabel(report)}</span></div>
                                                    <div class="report-sub">${getResolutionHelpText(report)}</div>
                                                    <div class="report-sub">Temps de resolution: ${getResolutionDurationText(report)}</div>
                                                    <div class="report-sub">SLA institution: ${getSlaText(report)} · ${getSlaRespectText(report)}</div>
                                                    <div class="report-sub">${report.resolution_confirmation?.confirmed_at ? `Confirmee le ${new Date(report.resolution_confirmation.confirmed_at).toLocaleString()}` : ''}</div>
                                                </td>
                                                <td>
                                                    <div class="report-actions">
                                                        <button class="btn btn-ghost-premium btn-sm px-3" type="button" onclick="window.AcepenPortal.showReportDetails(${report.id})">Details</button>
                                                        ${report.payment_status !== 'paid'
                                                            ? `<button class="btn btn-premium btn-sm px-3" type="button" onclick="window.AcepenPortal.payReport(${report.id})">Payer</button>`
                                                            : ''}
                                                        ${report.resolution_confirmation?.can_confirm
                                                            ? `<button class="btn btn-ghost-premium btn-sm px-3" type="button" onclick="window.AcepenPortal.confirmResolution(${report.id})">Confirmer</button>`
                                                            : ''}
                                                        <button
                                                            class="btn btn-premium btn-sm px-3"
                                                            type="button"
                                                            ${report.damage_declaration?.can_declare ? `onclick="window.AcepenPortal.openDamageForm(${report.id})"` : 'disabled'}
                                                            title="${report.damage_declaration?.can_declare
                                                                ? `Enregistrer les dommages constates avant le ${formatDateTime(report.damage_declaration.available_until)}.`
                                                                : (report.damage_declaration?.window_expired
                                                                    ? 'Le delai de 24h apres confirmation est depasse.'
                                                                    : 'Disponible apres confirmation de resolution du signalement.')}"
                                                        >
                                                            Dommage
                                                        </button>
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
                        paid: 'Confirme',
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
                        sent_to_organization: 'Transmis a l organisation',
                        organization_responded: 'Reponse organisation',
                        approved: 'Valide',
                        rejected: 'Rejete',
                        compensated: 'Compense',
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
                        list.innerHTML = '<div class="mini-card"><div class="fw-bold mb-1">Aucun dossier ouvert</div><div class="muted-label">Si un dossier est ouvert a partir d un signalement, son historique apparaitra ici.</div></div>';
                        return;
                    }

                    list.innerHTML = `
                        <div class="vstack gap-3">
                            ${cases.map((repairCase) => `
                                <div class="mini-card">
                                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                                        <div>
                                            <div class="fw-bold">${repairCase.reference}</div>
                                            <div class="muted-label">${repairCase.incident_report?.reference || '-'} · ${repairCase.incident_report?.signal_label || repairCase.incident_report?.signal_code || 'Signalement'}</div>
                                            <div class="muted-label">${repairCase.incident_report?.organization_name || 'Organisation non definie'} · ${repairCase.incident_report?.application_name || 'Application non definie'}</div>
                                        </div>
                                        <div class="text-end">
                                            <span class="status-pill ${getReparationCaseStatusClass(repairCase.status)}">${getReparationCaseStatusLabel(repairCase.status)}</span>
                                            <div class="muted-label mt-2">${repairCase.opened_at ? `Ouvert le ${formatDateTime(repairCase.opened_at)}` : 'Date indisponible'}</div>
                                        </div>
                                    </div>
                                    <div class="soft-panel mb-3">
                                        <div class="small text-secondary fw-semibold mb-1">Objet du dossier</div>
                                        <div class="fw-bold mb-1">${repairCase.damage_summary || 'Aucun resume de dommage renseigne.'}</div>
                                        <div class="muted-label">
                                            Montant reclame: ${repairCase.damage_amount_claimed !== null ? formatMoney(repairCase.damage_amount_claimed) : 'Non renseigne'}
                                            ${repairCase.damage_amount_validated !== null ? ` · Montant valide: ${formatMoney(repairCase.damage_amount_validated)}` : ''}
                                        </div>
                                        <div class="muted-label mt-2">
                                            Type: ${repairCase.case_type || '-'} · Priorite: ${repairCase.priority || '-'}
                                            ${repairCase.bailiff ? ` · Huissier: ${repairCase.bailiff}` : ''}
                                            ${repairCase.lawyer ? ` · Avocat: ${repairCase.lawyer}` : ''}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="section-title mb-3" style="font-size: 0.95rem;">Historique du dossier</div>
                                        <div class="vstack gap-2">
                                            ${(repairCase.steps || []).length
                                                ? repairCase.steps.map((step) => `
                                                    <div class="soft-panel">
                                                        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                                            <div>
                                                                <div class="fw-bold mb-1">${step.title}</div>
                                                                <div class="muted-label">${step.summary || 'Aucun detail complementaire fourni.'}</div>
                                                                <div class="muted-label mt-1">${step.assigned_to ? `Responsable: ${step.assigned_to}` : 'Responsable non assigne'}</div>
                                                            </div>
                                                            <div class="text-end">
                                                                <div class="small fw-semibold">${step.completed_at ? formatDateTime(step.completed_at) : (step.created_at ? formatDateTime(step.created_at) : '-')}</div>
                                                                <div class="muted-label">${step.status || '-'}</div>
                                                                <div class="muted-label">${step.due_at ? `Echeance ${formatDateTime(step.due_at)}` : ''}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                `).join('')
                                                : (repairCase.histories || []).length
                                                    ? repairCase.histories.map((history) => `
                                                        <div class="soft-panel">
                                                            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                                                <div>
                                                                    <div class="fw-bold mb-1">${history.title}</div>
                                                                    <div class="muted-label">${history.description || 'Aucun detail complementaire fourni.'}</div>
                                                                </div>
                                                                <div class="text-end">
                                                                    <div class="small fw-semibold">${history.created_at ? formatDateTime(history.created_at) : '-'}</div>
                                                                    <div class="muted-label">${history.created_by || 'Systeme'}</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    `).join('')
                                                : '<div class="soft-panel"><div class="fw-bold mb-1">Aucun historique visible</div><div class="muted-label">Les prochaines etapes enregistrees apparaitront ici.</div></div>'
                                            }
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    `;
                }

                function renderPayments(payments) {
                    state.payments = payments;
                    document.getElementById('topbarPaymentsBadge').textContent = `${payments.length} paiement${payments.length > 1 ? 's' : ''}`;

                    const latestPaidPayment = payments.find((payment) => payment.status === 'paid') || payments[0];
                    setTextIfExists('overviewPaymentSummary', latestPaidPayment
                        ? `${formatMoney(latestPaidPayment.amount, latestPaidPayment.currency)} · ${getPaymentStatusLabel(latestPaidPayment.status)}`
                        : 'Aucun paiement confirme');
                    setTextIfExists('overviewPaymentMeta', latestPaidPayment
                        ? `${latestPaidPayment.incident_report?.reference || 'Signalement'} · ${latestPaidPayment.reference}`
                        : 'Ton historique de paiements et tes recus apparaitront ici.');

                    const list = document.getElementById('paymentsList');
                    if (!payments.length) {
                        list.innerHTML = '<div class="mini-card"><div class="fw-bold mb-1">Aucun paiement enregistre</div><div class="muted-label">Des qu un paiement sera initie pour un signalement, il apparaitra ici avec son recu.</div></div>';
                        return;
                    }

                    const filteredPayments = getFilteredPayments(payments);

                    if (!filteredPayments.length) {
                        list.innerHTML = '<div class="mini-card"><div class="fw-bold mb-1">Aucun paiement ne correspond aux filtres</div><div class="muted-label">Ajuste les filtres pour retrouver plus vite un recu ou un paiement.</div></div>';
                        return;
                    }

                    list.innerHTML = `
                        <div class="payment-history-grid">
                            <div class="payment-table-shell">
                                <div class="report-table-wrap">
                                    <table class="payment-table">
                                        <thead>
                                            <tr>
                                                <th>Reference</th>
                                                <th>Montant</th>
                                                <th>Signalement</th>
                                                <th>Canal</th>
                                                <th>Dates</th>
                                                <th>Statut</th>
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
                                                        <div class="payment-sub">${[payment.incident_report?.signal_code, payment.incident_report?.signal_label].filter(Boolean).join(' · ') || 'Aucune information supplementaire'}</div>
                                                    </td>
                                                    <td>
                                                        <div class="payment-ref">${payment.provider || '-'}</div>
                                                        <div class="payment-sub">${payment.provider_reference || 'Reference fournisseur indisponible'}</div>
                                                    </td>
                                                    <td>
                                                        <div class="payment-sub"><strong>Initie:</strong> ${formatDateTime(payment.initiated_at)}</div>
                                                        <div class="payment-sub"><strong>Confirme:</strong> ${formatDateTime(payment.paid_at)}</div>
                                                    </td>
                                                    <td><span class="status-pill ${getPaymentStatusClass(payment.status)}">${getPaymentStatusLabel(payment.status)}</span></td>
                                                    <td>
                                                        <div class="report-actions">
                                                            ${payment.incident_report?.id
                                                                ? `<button class="btn btn-ghost-premium btn-sm px-3" type="button" onclick="window.AcepenPortal.showReportDetails(${payment.incident_report.id})">Signalement</button>`
                                                                : ''}
                                                            ${payment.can_download_receipt
                                                                ? `<button class="btn btn-ghost-premium btn-sm px-3" type="button" onclick="window.AcepenPortal.previewReceipt(${payment.id})">Apercu recu</button>`
                                                                : ''}
                                                            ${payment.can_download_receipt
                                                                ? `<button class="btn btn-premium btn-sm px-3" type="button" onclick="window.AcepenPortal.downloadReceipt(${payment.id}, '${payment.reference}')">Recu PDF</button>`
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
                        resolved: 'Resolu',
                        rejected: 'Rejete',
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
                        list.innerHTML = '<div class="mini-card"><div class="fw-bold mb-1">Aucun dommage a afficher</div><div class="muted-label">Les dommages declares apres resolution apparaitront ici avec leur suivi.</div></div>';
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
                                                    <div class="fw-bold">${report.damage_declaration?.summary || 'Dommage declare'}</div>
                                                    <div class="muted-label">${report.reference} · ${report.organization?.name || report.organization_name || report.network_type || 'Organisation non definie'}</div>
                                                </div>
                                                <span class="status-pill ${getDamageStatusClass(report.damage_declaration?.resolution_status)}">${getDamageStatusLabel(report.damage_declaration?.resolution_status)}</span>
                                            </div>
                                            <div class="muted-label mb-2">${report.damage_declaration?.notes || 'Aucun detail complementaire fourni.'}</div>
                                            <div class="muted-label">Declare le ${formatDateTime(report.damage_declaration?.declared_at)}</div>
                                            ${report.damage_declaration?.amount_estimated !== null
                                                ? `<div class="muted-label">Montant estime: ${formatAmount(report.damage_declaration.amount_estimated)}</div>`
                                                : ''}
                                            ${report.damage_declaration?.resolved_at
                                                ? `<div class="muted-label">Cloture du dommage: ${formatDateTime(report.damage_declaration.resolved_at)}</div>`
                                                : ''}
                                            ${report.damage_declaration?.resolution_notes
                                                ? `<div class="muted-label">Reponse institutionnelle: ${report.damage_declaration.resolution_notes}</div>`
                                                : ''}
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
                                                <button class="btn btn-ghost-premium btn-sm" type="button" onclick="window.AcepenPortal.showReportDetails(${report.id})">Voir le detail</button>
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

                    title.textContent = `Recu ${payment.reference}`;
                    content.innerHTML = `
                        <div class="mx-auto" style="max-width: 820px;">
                            <div class="shadow-sm" style="border-radius: 28px; overflow: hidden; background: white; border: 1px solid rgba(12, 36, 53, 0.08);">
                                <div class="px-4 px-lg-5 py-4" style="background: var(--acepen-navy); color: white;">
                                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                        <div>
                                            <div class="small text-white-50 fw-semibold mb-2">Justificatif associe a un signalement public</div>
                                            <div class="fs-3 fw-bold">Recu de paiement</div>
                                        </div>
                                        <div class="text-lg-end">
                                            <div class="small text-white-50 fw-semibold">Reference</div>
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
                                            <div class="muted-label">Statut: ${getPaymentStatusLabel(payment.status)}</div>
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
                                                <div class="small text-secondary fw-semibold mb-1">Canal de paiement</div>
                                                <div class="fw-bold">${payment.provider || '-'}</div>
                                                <div class="muted-label">${payment.provider_reference || 'Reference fournisseur indisponible'}</div>
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
                                        <div class="small text-secondary fw-semibold mb-1">Detail de facturation</div>
                                        <div class="fw-semibold">${payment.pricing_rule?.label || 'Paiement signalement public'}</div>
                                        <div class="muted-label">Document genere pour consultation avant telechargement du recu PDF.</div>
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
                        resolved: 'Resolu par l institution',
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

                function getResolutionDurationText(report) {
                    if (!report.resolved_at || !report.created_at) {
                        return 'En attente de resolution';
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
                        return 'SLA non configure';
                    }

                    return `${report.target_sla_hours}h`;
                }

                function getSlaRespectText(report) {
                    if (report.sla?.is_respected === true) {
                        return 'Le delai de resolution a ete respecte.';
                    }

                    if (report.sla?.is_respected === false) {
                        return 'Le delai de resolution n a pas ete respecte.';
                    }

                    if (!report.target_sla_hours || !report.resolved_at || !report.created_at) {
                        return 'Evaluation SLA indisponible';
                    }

                    const start = new Date(report.created_at);
                    const end = new Date(report.resolved_at);
                    const elapsedHours = (end.getTime() - start.getTime()) / 3600000;

                    return elapsedHours <= Number(report.target_sla_hours)
                        ? 'Resolution dans le SLA'
                        : 'Resolution hors SLA';
                }

                function getSlaImportanceText(report) {
                    return report.sla?.importance?.label || 'Priorite non definie';
                }

                function getSlaImportanceDetails(report) {
                    return report.sla?.importance?.details || 'Le SLA fixe un delai cible de traitement pour proteger les usagers et limiter l aggravation du sinistre.';
                }

                function getDamageDeclarationLabel(report) {
                    if (report.damage_declaration?.declared_at) {
                        return `Dommage enregistre le ${formatDateTime(report.damage_declaration.declared_at)}`;
                    }

                    if (report.damage_declaration?.can_declare) {
                        return `Tu peux declarer les dommages lies a ce sinistre jusqu au ${formatDateTime(report.damage_declaration.available_until)}.`;
                    }

                    if (report.damage_declaration?.window_expired) {
                        return 'Le delai maximum de 24h apres ta confirmation est depasse. La declaration de dommage n est plus possible.';
                    }

                    return 'La declaration de dommage sera disponible apres ta confirmation de resolution.';
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
                        return 'Resolution confirmee';
                    }

                    if (report.status === 'resolved') {
                        return 'Action attendue de votre part';
                    }

                    if (report.status === 'rejected') {
                        return 'Signalement non retenu';
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
                        return 'Tu as confirme que le probleme a bien ete resolu.';
                    }

                    if (report.status === 'resolved') {
                        return report.official_response || 'L institution indique que le probleme est resolu. Verifie puis confirme si tout est revenu a la normale.';
                    }

                    if (report.status === 'rejected') {
                        return report.official_response || 'L institution n a pas retenu ce signalement.';
                    }

                    return 'Ton signalement est toujours en cours de traitement par l institution.';
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
                    document.getElementById('reportDetailTitle').textContent = `${report.reference} · ${report.signal_code}`;
                    document.getElementById('reportDetailContent').innerHTML = `
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <div class="mini-card h-100">
                                    <div class="small text-secondary fw-semibold mb-2">Signalement</div>
                                    <div class="fw-bold fs-5 mb-2">${report.signal_label || report.incident_type}</div>
                                    <div class="muted-label mb-3">${report.description || 'Aucune description fournie.'}</div>
                                    <div class="soft-panel mb-3">
                                        <div class="small text-secondary fw-semibold mb-1">Statut</div>
                                        <div class="fw-semibold">${getPublicStatusLabel(report.status)}</div>
                                    </div>
                                    <div class="soft-panel mb-3">
                                        <div class="small text-secondary fw-semibold mb-1">Application</div>
                                        <div class="fw-semibold">${report.application?.name || '-'}</div>
                                        <div class="muted-label">${report.application?.code || 'Aucun univers metier defini'}</div>
                                    </div>
                                    <div class="soft-panel mb-3">
                                        <div class="small text-secondary fw-semibold mb-1">Date du signalement</div>
                                        <div class="fw-semibold">${formatDateTime(report.created_at)}</div>
                                    </div>
                                    <div class="soft-panel mb-3">
                                        <div class="small text-secondary fw-semibold mb-1">Localisation</div>
                                        <div class="fw-semibold">${[report.location.country, report.location.city, report.location.commune].filter(Boolean).join(' · ') || '-'}</div>
                                        <div class="muted-label">${report.location.address || 'Adresse non renseignee'}</div>
                                        <div class="muted-label">${report.location.latitude && report.location.longitude ? `GPS ${report.location.latitude}, ${report.location.longitude}` : 'Position GPS non renseignee'}</div>
                                    </div>
                                    <div class="soft-panel">
                                        <div class="small text-secondary fw-semibold mb-1">identifiant associe</div>
                                        <div class="fw-semibold">${report.meter?.meter_number || '-'}</div>
                                        <div class="muted-label">${report.meter?.label || report.meter?.organization_name || report.meter?.network_type || '-'}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mini-card h-100">
                                    <div class="small text-secondary fw-semibold mb-2">Resolution et solution</div>
                                    <div class="soft-panel mb-3">
                                        <div class="small text-secondary fw-semibold mb-1">Etat de resolution</div>
                                        <div class="fw-semibold"><span class="status-pill ${getResolutionStatusClass(report)}">${getResolutionLabel(report)}</span></div>
                                        <div class="muted-label">${getResolutionHelpText(report)}</div>
                                    </div>
                                    <div class="soft-panel mb-3">
                                        <div class="small text-secondary fw-semibold mb-1">Temps de resolution</div>
                                        <div class="fw-semibold">${getResolutionDurationText(report)}</div>
                                        <div class="muted-label">${report.resolved_at ? `Probleme marque comme resolu le ${formatDateTime(report.resolved_at)}` : 'Le probleme n est pas encore marque comme resolu.'}</div>
                                    </div>
                                    <div class="soft-panel mb-3">
                                        <div class="small text-secondary fw-semibold mb-1">SLA applique par l institution</div>
                                        <div class="fw-semibold">${getSlaText(report)} · ${report.sla?.label || getSlaRespectText(report)}</div>
                                        <div class="muted-label">${getSlaRespectText(report)}</div>
                                        <div class="muted-label">Importance: ${getSlaImportanceText(report)}</div>
                                        <div class="muted-label">${getSlaImportanceDetails(report)}</div>
                                        <div class="muted-label">${report.sla?.elapsed_hours !== null && report.sla?.elapsed_hours !== undefined ? `Temps constate: ${report.sla.elapsed_hours}h` : 'Le temps exact sera calcule une fois la resolution complete.'}</div>
                                    </div>
                                    <div class="soft-panel">
                                        <div class="small text-secondary fw-semibold mb-1">Detail de la solution</div>
                                        <div class="fw-semibold mb-1">${report.official_response ? 'Reponse institutionnelle disponible' : 'Aucune reponse officielle pour le moment'}</div>
                                        <div class="muted-label">${report.official_response || 'L institution n a pas encore detaille la solution appliquee.'}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mini-card">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <div class="small text-secondary fw-semibold mb-1">Paiement</div>
                                            <div class="fw-semibold">${report.payment_status === 'paid' ? 'Paye' : 'En attente'}</div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="small text-secondary fw-semibold mb-1">Paiement confirme</div>
                                            <div class="fw-semibold">${formatDateTime(report.paid_at)}</div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="small text-secondary fw-semibold mb-1">Resolution confirmee</div>
                                            <div class="fw-semibold">${report.resolution_confirmation?.status === 'confirmed' ? 'Oui' : 'Non'}</div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="small text-secondary fw-semibold mb-1">Confirmation usager</div>
                                            <div class="fw-semibold">${formatDateTime(report.resolution_confirmation?.confirmed_at)}</div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="small text-secondary fw-semibold mb-1">Respect du SLA</div>
                                            <div class="fw-semibold">${report.sla?.is_respected === true ? 'Oui' : (report.sla?.is_respected === false ? 'Non' : 'En attente')}</div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="small text-secondary fw-semibold mb-1">Importance du SLA</div>
                                            <div class="fw-semibold">${getSlaImportanceText(report)}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mini-card">
                                    <div class="row g-3 align-items-center">
                                        <div class="col-lg-8">
                                            <div class="small text-secondary fw-semibold mb-1">Declaration de dommage apres resolution</div>
                                            <div class="fw-semibold mb-1">${report.damage_declaration?.summary || 'Aucun dommage enregistre pour le moment'}</div>
                                            <div class="muted-label">${getDamageDeclarationLabel(report)}</div>
                                            ${report.damage_declaration?.amount_estimated !== null
                                                ? `<div class="muted-label">Montant estime: ${formatAmount(report.damage_declaration.amount_estimated)}</div>`
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
                    state.communes = state.countries.flatMap((country) => (country.cities || []).flatMap((city) => city.communes || []));
                    populateCommuneSelects();
                    populateReportLocationSelects();
                    const signalResponse = await fetch('/api/v1/public/signal-types', { headers: { Accept: 'application/json' } });
                    const signalData = await signalResponse.json();
                    state.signalTypes = signalData.data.signal_types || [];
                    renderSignalOptions();
                    initGooglePlacesAutocomplete();
                }

                async function refreshDashboard() {
                    await loadReferenceData();
                    const [me, meters, household, reports, payments, invitations, reparationCases] = await Promise.all([
                        apiFetch('/me'),
                        apiFetch('/meters'),
                        apiFetch('/households/me'),
                        apiFetch('/reports'),
                        apiFetch('/payments'),
                        apiFetch('/households/invitations/pending'),
                        apiFetch('/reparation-cases'),
                    ]);
                    renderUser(me.data.user);
                    renderMeters(meters.data.meters);
                    renderHousehold(household.data.household);
                    renderReports(reports.data.reports);
                    renderDamages(reports.data.reports);
                    renderPayments(payments.data.payments);
                    renderIncomingHouseholdInvitations(invitations.data.invitations);
                    renderReparationCases(reparationCases.data.reparation_cases);
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
                        populateCommuneSelects(meter.commune || null);
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
                        form.querySelector('button[type="submit"]').textContent = 'Mettre a jour le identifiant';
                        bootstrap.Collapse.getOrCreateInstance(document.getElementById('meterFormWrap')).show();
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
                        document.getElementById('damageDeclarationForm').reset();
                        resetDamageAttachmentPreview();
                        damageDeclarationModal?.show();
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
                                throw new Error(payload.message || 'Impossible de telecharger le recu.');
                            }

                            const blob = await response.blob();
                            const url = window.URL.createObjectURL(blob);
                            const link = document.createElement('a');
                            link.href = url;
                            link.download = `recu-${paymentReference}.pdf`;
                            document.body.appendChild(link);
                            link.click();
                            link.remove();
                            window.URL.revokeObjectURL(url);
                            showToast('Le recu a ete telecharge.');
                        } catch (error) {
                            showToast(error.message, true);
                        }
                    },
                    previewReceipt(paymentId) {
                        const payment = state.payments.find((item) => String(item.id) === String(paymentId));

                        if (!payment || !payment.can_download_receipt) {
                            showToast('Le recu n est disponible que pour un paiement confirme.', true);
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
                    showReportDetails(reportId) {
                        const report = state.reports.find((item) => item.id === reportId);
                        if (!report) {
                            return;
                        }

                        renderReportDetails(report);
                        reportDetailModal?.show();
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
                document.getElementById('openDamageDeclarationButton').addEventListener('click', () => {
                    const reportId = Number(document.getElementById('openDamageDeclarationButton').dataset.reportId || 0);
                    if (!reportId) {
                        return;
                    }

                    window.AcepenPortal.openDamageForm(reportId);
                });
                document.getElementById('openPublicSidebarButton').addEventListener('click', openSidebar);
                document.getElementById('publicSidebarBackdrop').addEventListener('click', closeSidebar);

                document.getElementById('meterCommuneSelect').addEventListener('change', () => populateMeterNeighborhoodOptions());
                document.getElementById('meterNeighborhoodSelect').addEventListener('change', () => {
                    populateMeterNeighborhoodOptions(document.getElementById('meterNeighborhoodSelect').value);
                });
                document.getElementById('meterApplicationId').addEventListener('change', () => populateMeterOrganizationOptions());
                document.getElementById('meterOrganizationId').addEventListener('change', () => populateMeterOrganizationOptions(document.getElementById('meterOrganizationId').value));
                document.getElementById('reportApplicationId').addEventListener('change', () => {
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
                    showToast(enabled ? 'Saisie manuelle activee pour la position du profil.' : 'Saisie manuelle desactivee.');
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
                    document.getElementById('damageDeclarationForm').reset();
                    document.getElementById('damageDeclarationReportId').value = '';
                    resetDamageAttachmentPreview();
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

                document.getElementById('householdForm').addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const form = event.currentTarget;
                    setLoading(form, true);
                    try {
                        const payload = Object.fromEntries(new FormData(form).entries());
                        const response = await apiFetch('/households', { method: 'POST', body: JSON.stringify(payload) });
                        renderHousehold(response.data.household);
                        showToast(response.message);
                        form.reset();
                        clearProfileGeoFields();
                        setGeoManualMode('profile', false);
                        state.autoGeoAttempts.profile = false;
                        maybeCaptureCurrentPosition('profile');
                    } catch (error) {
                        showToast(error.message, true);
                    } finally {
                        setLoading(form, false);
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

                document.getElementById('reportCountryId').addEventListener('change', () => {
                    const country = state.countries.find((item) => String(item.id) === String(document.getElementById('reportCountryId').value));
                    document.getElementById('reportCityId').innerHTML = buildOptions(country?.cities || [], 'Aucune ville');
                    const firstCity = (country?.cities || [])[0];
                    document.getElementById('reportCityId').value = firstCity?.id || '';
                    document.getElementById('reportCommuneId').innerHTML = buildOptions(firstCity?.communes || [], 'Aucune commune');
                    document.getElementById('reportCommuneId').value = firstCity?.communes?.[0]?.id || '';
                });

                document.getElementById('reportCityId').addEventListener('change', () => {
                    const country = state.countries.find((item) => String(item.id) === String(document.getElementById('reportCountryId').value));
                    const city = (country?.cities || []).find((item) => String(item.id) === String(document.getElementById('reportCityId').value));
                    document.getElementById('reportCommuneId').innerHTML = buildOptions(city?.communes || [], 'Aucune commune');
                    document.getElementById('reportCommuneId').value = city?.communes?.[0]?.id || '';
                });

                document.getElementById('reportForm').addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const form = event.currentTarget;
                    setLoading(form, true);
                    try {
                        const payload = Object.fromEntries(new FormData(form).entries());
                        payload.application_id = document.getElementById('reportApplicationId').value;
                        payload.organization_id = document.getElementById('reportOrganizationType').value;
                        payload.signal_payload = {};
                        const signalFields = Array.from(form.querySelectorAll('[data-signal-key]'));
                        const processedPhotoKeys = new Set();

                        for (const field of signalFields) {
                            if (field.type === 'file') {
                                if (processedPhotoKeys.has(field.dataset.signalKey)) {
                                    continue;
                                }

                                processedPhotoKeys.add(field.dataset.signalKey);
                                const candidates = signalFields.filter((candidate) => candidate.type === 'file' && candidate.dataset.signalKey === field.dataset.signalKey);
                                const selectedInput = candidates.find((candidate) => candidate.files?.[0]);
                                const file = selectedInput?.files?.[0];

                                if (file) {
                                    payload.signal_payload[field.dataset.signalKey] = {
                                        type: 'image',
                                        name: file.name,
                                        mime_type: file.type || 'image/jpeg',
                                        data_url: await readFileAsDataUrl(file),
                                    };
                                }

                                continue;
                            }

                            if (field.value !== '') {
                                payload.signal_payload[field.dataset.signalKey] = field.value;
                            }
                        }
                        const response = await apiFetch('/reports', { method: 'POST', body: JSON.stringify(payload) });
                        showToast(response.message);
                        form.reset();
                        clearReportGeoFields();
                        setGeoManualMode('report', false);
                        populateReportLocationSelects();
                        populateMeterApplicationOptions();
                        renderReportNetworkOptions();
                        renderReportMeterOptions();
                        renderSignalOptions();
                        state.autoGeoAttempts.report = false;
                        await refreshDashboard();
                        activatePanel('reports');
                        reportFormModal?.hide();
                    } catch (error) {
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
                        const attachment = document.getElementById('damageAttachmentInput').files?.[0];

                        if (attachment) {
                            payload.damage_attachment = {
                                name: attachment.name,
                                mime_type: attachment.type || 'application/octet-stream',
                                data_url: await readFileAsDataUrl(attachment),
                            };
                        }

                        const reportId = payload.report_id;
                        delete payload.report_id;

                        const response = await apiFetch(`/reports/${reportId}/damages`, {
                            method: 'POST',
                            body: JSON.stringify(payload),
                        });

                        showToast(response.message);
                        damageDeclarationModal?.hide();
                        await refreshDashboard();
                        window.AcepenPortal.showReportDetails(Number(reportId));
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

                    showToast(error?.message || 'Impossible de charger certaines donnees du dashboard.', true);
                });
            })();
        </script>
        @if (config('services.google_maps.key'))
            <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=places&callback=AcepenInitGooglePlaces"></script>
        @endif
    </body>
</html>
