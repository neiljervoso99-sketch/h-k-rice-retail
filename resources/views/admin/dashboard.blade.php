@extends('admin.layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<style>
    .store-dashboard {
        --dashboard-green: #38bdf8;
        --dashboard-green-dark: #0284c7;
        --dashboard-green-soft: rgba(56, 189, 248, .13);
        --dashboard-border: rgba(148, 193, 221, .17);
        --dashboard-muted: #a8bbce;
        --dashboard-shell: rgba(9, 20, 34, .82);
        --dashboard-heading-bg: rgba(20, 37, 57, .72);
        --dashboard-panel-bg: rgba(17, 33, 51, .68);
        --dashboard-kpi-bg: linear-gradient(145deg, rgba(28, 53, 78, .76), rgba(15, 30, 48, .72));
        --dashboard-text: #ecf1ff;
        --dashboard-heading-text: #f2f8ff;
        --dashboard-kpi-label: #b7c9dc;
        --dashboard-kpi-value: #f1f5ff;
        --dashboard-row-bg: rgba(28, 49, 70, .72);
        --dashboard-row-text: #d3dbed;
        --dashboard-table-line: rgba(148, 193, 221, .12);
        --dashboard-order-link: #7dd3fc;
        --dashboard-status-text: #7dd3fc;
        --dashboard-table-heading: rgba(28, 49, 70, .72);
        min-width: 0;
        padding: 18px;
        border: 1px solid rgba(125, 211, 252, .17);
        border-radius: 18px;
        background:
            radial-gradient(ellipse at 82% 0%, rgba(14, 165, 233, .17), transparent 38%),
            linear-gradient(145deg, rgba(11, 25, 42, .92), rgba(7, 16, 29, .96));
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
        color: var(--dashboard-text);
        box-shadow: 0 22px 50px rgba(2, 8, 23, .28);
    }

    :root[data-theme="light"] .store-dashboard .dashboard-heading {
        border-color: rgba(125, 174, 202, .28);
        background: linear-gradient(125deg, rgba(255, 255, 255, .88), rgba(235, 247, 253, .7));
        box-shadow: 0 14px 32px rgba(37, 89, 120, .08), inset 0 1px 0 rgba(255, 255, 255, .95);
    }

    :root[data-theme="light"] .store-dashboard {
        --dashboard-green: #0284c7;
        --dashboard-green-dark: #0369a1;
        --dashboard-green-soft: rgba(2, 132, 199, .1);
        --dashboard-border: rgba(91, 145, 177, .2);
        --dashboard-muted: #607789;
        --dashboard-shell: rgba(242, 249, 253, .86);
        --dashboard-heading-bg: rgba(255, 255, 255, .76);
        --dashboard-panel-bg: rgba(255, 255, 255, .72);
        --dashboard-kpi-bg: linear-gradient(145deg, rgba(255, 255, 255, .9), rgba(232, 245, 253, .82));
        --dashboard-text: #183247;
        --dashboard-heading-text: #12324b;
        --dashboard-kpi-label: #557187;
        --dashboard-kpi-value: #12324b;
        --dashboard-row-bg: rgba(232, 244, 251, .78);
        --dashboard-row-text: #29475d;
        --dashboard-table-line: rgba(91, 145, 177, .14);
        --dashboard-order-link: #0369a1;
        --dashboard-status-text: #0369a1;
        --dashboard-table-heading: rgba(228, 242, 250, .85);
        border-color: rgba(91, 145, 177, .22);
        background: transparent;
        color: var(--dashboard-text);
        box-shadow: none;
        backdrop-filter: none;
        -webkit-backdrop-filter: none;
    }

    :root[data-theme="light"] body.admin-dashboard-page .store-dashboard {
        border-color: transparent;
    }

    :root[data-theme="light"] .store-dashboard .dashboard-kpi.primary {
        border-color: rgba(2, 132, 199, .25);
        background: linear-gradient(145deg, rgba(255, 255, 255, .96), rgba(217, 240, 251, .88));
        color: #12324b;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .95), 0 14px 30px rgba(37, 99, 145, .12);
    }

    :root[data-theme="light"] .store-dashboard .dashboard-kpi.primary .dashboard-kpi-label,
    :root[data-theme="light"] .store-dashboard .dashboard-kpi.primary .dashboard-kpi-note {
        color: #557187;
    }

    :root[data-theme="light"] .store-dashboard .dashboard-kpi.primary .dashboard-kpi-value {
        color: #0369a1;
    }

    :root[data-theme="light"] .admin-main .store-dashboard .dashboard-panel {
        border-color: rgba(125, 174, 202, .24) !important;
        background: linear-gradient(145deg, rgba(255, 255, 255, .88), rgba(235, 247, 253, .78)) !important;
        box-shadow: 0 14px 32px rgba(37, 89, 120, .09), inset 0 1px 0 rgba(255, 255, 255, .9) !important;
    }

    :root[data-theme="light"] body.admin-dashboard-page .sidebar-nav a {
        border-radius: 12px;
    }

    :root[data-theme="light"] body.admin-dashboard-page .sidebar-nav a.active {
        background: rgba(2, 132, 199, .1);
        box-shadow: inset 3px 0 #0284c7;
        color: #0369a1;
    }

    .store-dashboard * {
        box-sizing: border-box;
    }

    body.admin-dashboard-page {
        position: relative;
        isolation: isolate;
    }

    body.admin-dashboard-page::before,
    body.admin-dashboard-page::after {
        position: fixed;
        z-index: -1;
        width: min(68vw, 820px);
        height: min(68vw, 820px);
        border-radius: 50%;
        content: "";
        pointer-events: none;
        opacity: .34;
        background: radial-gradient(ellipse, rgba(56, 189, 248, .16), rgba(37, 99, 235, .07) 38%, transparent 70%);
        animation: dashboard-light-enter .7s ease-out both, dashboard-light-drift 28s ease-in-out infinite alternate;
        animation-delay: 0s, 0s;
        animation-duration: .7s, 28s;
    }

    body.admin-dashboard-page::before {
        top: -34vw;
        left: -24vw;
    }

    body.admin-dashboard-page::after {
        right: -30vw;
        bottom: -42vw;
        background: radial-gradient(ellipse, rgba(125, 211, 252, .13), rgba(14, 165, 233, .06) 40%, transparent 70%);
        animation-delay: 0s, -12s;
        animation-duration: .7s, 34s;
    }

    @keyframes dashboard-light-enter {
        from { opacity: 0; }
        to { opacity: .34; }
    }

    body.admin-dashboard-page .sidebar {
        background: rgba(247, 252, 255, .76);
        border-right-color: rgba(125, 174, 202, .28);
        box-shadow: 12px 0 36px rgba(37, 89, 120, .1);
        animation: dashboard-sidebar-enter .48s cubic-bezier(.2, .7, .2, 1) both;
    }

    :root[data-theme="dark"] body.admin-dashboard-page .sidebar {
        background: rgba(8, 21, 34, .82);
        border-right-color: rgba(125, 211, 252, .18);
        box-shadow: 12px 0 36px rgba(1, 7, 14, .24);
    }

    body.admin-dashboard-page .admin-header {
        background: linear-gradient(110deg, rgba(11, 39, 64, .8), rgba(18, 70, 109, .74));
        animation: dashboard-header-enter .42s ease-out both;
    }

    @media (min-width: 701px) {
        body.admin-dashboard-page .sidebar {
            border-top-right-radius: 20px;
            border-bottom-right-radius: 20px;
        }
    }

    body.admin-dashboard-page .admin-main {
        background: transparent;
        backdrop-filter: none;
        -webkit-backdrop-filter: none;
    }

    @keyframes dashboard-light-drift {
        from { transform: translate3d(-1.5%, -1%, 0) scale(.96); }
        to { transform: translate3d(2%, 1.5%, 0) scale(1.04); }
    }

    @keyframes dashboard-sidebar-enter {
        from { opacity: 0; translate: -12px 0; }
        to { opacity: 1; translate: 0 0; }
    }

    @keyframes dashboard-header-enter {
        from { opacity: 0; translate: 0 -8px; }
        to { opacity: 1; translate: 0 0; }
    }

    @keyframes dashboard-rise-in {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .store-dashboard {
        position: relative;
        z-index: 1;
        animation: dashboard-rise-in .42s ease-out backwards;
    }

    .dashboard-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 18px 20px;
        margin-bottom: 16px;
        border: 1px solid var(--dashboard-border);
        border-radius: 16px;
        background: var(--dashboard-heading-bg);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .07), 0 10px 24px rgba(0, 0, 0, .08);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        animation: dashboard-rise-in .38s ease-out backwards;
    }

    .dashboard-heading h1 {
        margin: 0 0 4px;
        color: var(--dashboard-heading-text);
        font-size: clamp(24px, 3vw, 32px);
        letter-spacing: -.04em;
    }

    .dashboard-eyebrow {
        display: block;
        margin-bottom: 5px;
        color: var(--dashboard-green);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .dashboard-heading p {
        margin: 0;
        color: var(--dashboard-muted);
        font-size: 12px;
    }

    .dashboard-live {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        flex-shrink: 0;
        padding: 7px 10px;
        border: 1px solid rgba(56, 189, 248, .3);
        border-radius: 20px;
        background: rgba(14, 165, 233, .12);
        color: #7dd3fc;
        font-size: 11px;
        font-weight: 700;
    }

    .dashboard-live::before {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #38bdf8;
        content: "";
    }

    :root[data-theme="light"] .dashboard-live {
        color: #0369a1;
    }

    .dashboard-heading-tools {
        display: flex;
        align-items: flex-end;
        gap: 12px;
        flex-shrink: 0;
    }

    .dashboard-period-filter {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0 0 12px;
    }

    .dashboard-period-filter label {
        color: var(--dashboard-muted);
        font-size: 11px;
        font-weight: 700;
    }

    .dashboard-period-filter select {
        width: auto;
        min-width: 130px;
        padding: 8px 32px 8px 11px;
        border: 1px solid var(--dashboard-border);
        border-radius: 9px;
        background: var(--dashboard-panel-bg);
        color: var(--dashboard-heading-text);
        font-size: 12px;
    }

    .dashboard-period-filter button {
        flex-shrink: 0;
    }

    .dashboard-clock {
        color: var(--dashboard-muted);
        text-align: right;
    }

    .dashboard-clock-label {
        display: block;
        font-size: 10px;
    }

    .dashboard-clock time {
        display: block;
        margin-top: 2px;
        color: var(--dashboard-heading-text);
        font-size: 13px;
        font-weight: 700;
    }

    .dashboard-kpis {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 14px;
    }

    .dashboard-kpi {
        position: relative;
        isolation: isolate;
        min-width: 0;
        overflow: hidden;
        padding: 16px;
        border: 1px solid var(--dashboard-border);
        border-radius: 16px;
        background: var(--dashboard-kpi-bg);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .08), 0 12px 28px rgba(0, 0, 0, .13);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
        animation: dashboard-rise-in .46s ease-out backwards;
    }

    .dashboard-kpi::before {
        position: absolute;
        z-index: 0;
        inset: 0;
        border-radius: inherit;
        background: radial-gradient(260px circle at var(--pointer-x, 50%) var(--pointer-y, 0%), rgba(255, 255, 255, .17), transparent 72%);
        content: "";
        opacity: 0;
        pointer-events: none;
        transition: opacity .2s ease;
    }

    .dashboard-kpi > * {
        position: relative;
        z-index: 1;
    }

    .dashboard-kpi::after,
    .dashboard-panel::after {
        position: absolute;
        top: 0;
        right: 12%;
        left: 12%;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .7), transparent);
        content: "";
        opacity: .7;
        pointer-events: none;
    }

    .dashboard-kpi.primary {
        border-color: rgba(56, 189, 248, .42);
        background:
            radial-gradient(ellipse at 100% 0%, rgba(56, 189, 248, .28), transparent 50%),
            linear-gradient(135deg, rgba(15, 76, 115, .94), rgba(15, 40, 67, .94));
        color: #fff;
    }

    .dashboard-kpi:hover,
    .dashboard-panel:hover {
        transform: translateY(-2px);
        border-color: rgba(56, 189, 248, .36);
        box-shadow: 0 14px 30px rgba(2, 8, 23, .18);
    }

    .dashboard-kpi:nth-child(2) { animation-delay: 50ms; }
    .dashboard-kpi:nth-child(3) { animation-delay: 100ms; }
    .dashboard-kpi:nth-child(4) { animation-delay: 150ms; }
    .dashboard-kpi:nth-child(5) { animation-delay: 200ms; }
    .dashboard-kpi:nth-child(6) { animation-delay: 250ms; }
    .dashboard-kpi:nth-child(7) { animation-delay: 300ms; }

    :root[data-theme="light"] .dashboard-kpi.primary {
        background:
            radial-gradient(ellipse at 100% 0%, rgba(56, 189, 248, .3), transparent 50%),
            linear-gradient(135deg, #0c78aa, #145d89);
    }

    .dashboard-kpi-label {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 12px;
        color: var(--dashboard-kpi-label);
        font-size: 12px;
        font-weight: 600;
    }

    .dashboard-kpi.primary .dashboard-kpi-label {
        color: #f3e8ff;
    }

    .dashboard-kpi-value {
        display: block;
        overflow-wrap: anywhere;
        color: var(--dashboard-kpi-value);
        font-size: clamp(20px, 2vw, 26px);
        font-weight: 800;
        line-height: 1.2;
    }

    .dashboard-kpi.primary .dashboard-kpi-value {
        color: #fff;
    }

    .dashboard-kpi-note {
        display: block;
        margin-top: 7px;
        color: var(--dashboard-muted);
        font-size: 11px;
    }

    .dashboard-kpi.primary .dashboard-kpi-note {
        color: #e9d5ff;
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.7fr) minmax(260px, .9fr);
        gap: 14px;
        align-items: stretch;
        margin-bottom: 14px;
    }

    .dashboard-panel {
        position: relative;
        min-width: 0;
        padding: 16px;
        border: 1px solid var(--dashboard-border);
        border-radius: 16px;
        background: var(--dashboard-panel-bg);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .06), 0 8px 22px rgba(0, 0, 0, .08);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease;
        animation: dashboard-rise-in .48s ease-out backwards;
    }

    .dashboard-grid > .dashboard-panel:nth-child(2) { animation-delay: 100ms; }
    .dashboard-grid > .dashboard-panel:nth-child(3) { animation-delay: 180ms; }
    .dashboard-lower-grid > .dashboard-panel:nth-child(2) { animation-delay: 100ms; }

    .dashboard-status-count {
        animation: dashboard-rise-in .42s ease-out backwards;
    }

    .dashboard-status-count:nth-child(2) { animation-delay: 60ms; }
    .dashboard-status-count:nth-child(3) { animation-delay: 120ms; }
    .dashboard-status-count:nth-child(4) { animation-delay: 180ms; }

    .dashboard-orders tbody tr {
        animation: dashboard-rise-in .36s ease-out backwards;
    }

    .dashboard-orders tbody tr:nth-child(2) { animation-delay: 40ms; }
    .dashboard-orders tbody tr:nth-child(3) { animation-delay: 80ms; }
    .dashboard-orders tbody tr:nth-child(4) { animation-delay: 120ms; }
    .dashboard-orders tbody tr:nth-child(5) { animation-delay: 160ms; }
    .dashboard-orders tbody tr:nth-child(6) { animation-delay: 200ms; }
    .dashboard-orders tbody tr:nth-child(7) { animation-delay: 240ms; }
    .dashboard-orders tbody tr:nth-child(8) { animation-delay: 280ms; }

    @keyframes dashboard-card-float {
        0%, 100% { translate: 0 0; }
        50% { translate: 0 -2px; }
    }

    @media (min-width: 761px) and (hover: hover) and (pointer: fine) and (prefers-reduced-motion: no-preference) {
        .dashboard-kpi {
            animation: dashboard-rise-in .46s ease-out backwards, dashboard-card-float 10s ease-in-out infinite;
            transform-style: preserve-3d;
        }

        .dashboard-kpi:hover {
            transform: perspective(900px) rotateX(var(--dashboard-tilt-y, 0deg)) rotateY(var(--dashboard-tilt-x, 0deg)) translateY(-3px);
        }

        .dashboard-kpi:hover::before {
            opacity: 1;
        }

        .dashboard-panel:hover,
        .team-member:hover {
            transform: translateY(-2px);
            border-color: rgba(56, 189, 248, .36);
            box-shadow: 0 14px 30px rgba(2, 8, 23, .18);
        }
    }

    .dashboard-panel-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 12px;
    }

    .dashboard-panel-heading h2 {
        margin: 0;
        color: var(--dashboard-heading-text);
        font-size: 15px;
    }

    .dashboard-panel-heading small {
        color: var(--dashboard-muted);
        font-size: 11px;
    }

    .dashboard-chart {
        position: relative;
        width: 100%;
        height: 235px;
    }

    .dashboard-chart.bar {
        height: 235px;
    }

    .dashboard-chart.doughnut {
        height: 210px;
        max-width: 300px;
        margin: 0 auto;
    }

    .category-legend {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 7px 12px;
        margin-top: 12px;
    }

    .category-legend-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        min-width: 0;
        color: var(--dashboard-muted);
        font-size: 11px;
    }

    .category-legend-item span {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .category-legend-item strong {
        color: var(--dashboard-heading-text);
        white-space: nowrap;
    }

    .dashboard-lower-grid {
        display: grid;
        grid-template-columns: minmax(220px, .8fr) minmax(0, 1.7fr);
        gap: 14px;
        align-items: start;
        margin-bottom: 14px;
    }

    .top-product-list {
        display: grid;
        gap: 8px;
    }

    .top-product {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 10px;
        border-radius: 8px;
        background: var(--dashboard-row-bg);
    }

    .top-product-name {
        min-width: 0;
        color: var(--dashboard-heading-text);
        font-size: 12px;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .top-product-quantity {
        display: block;
        margin-top: 3px;
        color: var(--dashboard-muted);
        font-size: 10px;
        font-weight: 400;
    }

    .top-product-sales {
        flex-shrink: 0;
        color: var(--dashboard-green-dark);
        font-size: 12px;
        font-weight: 700;
    }

    .dashboard-orders-wrap {
        overflow-x: auto;
    }

    .dashboard-orders {
        width: 100%;
        min-width: 500px;
        border-collapse: collapse;
    }

    .dashboard-orders th {
        padding: 9px 8px;
        border-bottom: 1px solid var(--dashboard-border);
        background: var(--dashboard-table-heading);
        color: var(--dashboard-muted);
        font-size: 10px;
        font-weight: 700;
        text-align: left;
        white-space: nowrap;
    }

    .dashboard-orders td {
        padding: 10px 8px;
        border-bottom: 1px solid var(--dashboard-table-line);
        color: var(--dashboard-row-text);
        font-size: 11px;
        vertical-align: middle;
    }

    .dashboard-orders tbody tr:last-child td {
        border-bottom: 0;
    }

    .dashboard-order-number {
        color: var(--dashboard-order-link);
        font-weight: 700;
    }

    .dashboard-status {
        display: inline-flex;
        padding: 4px 8px;
        border-radius: 20px;
        background: var(--dashboard-green-soft);
        color: var(--dashboard-status-text);
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .dashboard-status.pending,
    .dashboard-status.accepted {
        background: rgba(251, 191, 36, .12);
        color: #fcd34d;
    }

    .dashboard-status.cancelled {
        background: rgba(248, 113, 113, .12);
        color: #fca5a5;
    }

    .dashboard-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 14px;
    }

    .dashboard-action-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 36px;
        padding: 8px 12px;
        border: 1px solid #0284c7;
        border-radius: 8px;
        background: #0284c7;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
    }

    .dashboard-action-button:hover,
    .dashboard-action-button:focus-visible {
        border-color: #0369a1;
        background: #0369a1;
        color: #fff;
        transform: translateY(-1px);
    }

    .dashboard-empty {
        padding: 18px 10px;
        color: var(--dashboard-muted);
        font-size: 12px;
        text-align: center;
    }

    .dashboard-status-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 8px;
        margin-bottom: 14px;
    }

    .dashboard-status-count {
        padding: 11px;
        border: 1px solid var(--dashboard-border);
        border-radius: 12px;
        background: var(--dashboard-kpi-bg);
        backdrop-filter: blur(12px);
    }

    .dashboard-status-count span {
        display: block;
        color: var(--dashboard-muted);
        font-size: 10px;
    }

    .dashboard-status-count strong {
        display: block;
        margin-top: 4px;
        color: #f0f4ff;
        font-size: 17px;
    }

    :root[data-theme="light"] .dashboard-status-count strong {
        color: var(--dashboard-heading-text);
    }

    .team-overview {
        margin-bottom: 14px;
    }

    .team-overview-metrics {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .team-overview-metrics > div {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        min-height: 58px;
        padding: 12px 14px;
        border: 1px solid var(--dashboard-border);
        border-radius: 12px;
        background: var(--dashboard-kpi-bg);
    }

    .team-overview-metrics > div > span:first-child {
        color: var(--dashboard-muted);
        font-size: 12px;
    }

    .team-overview-metrics strong {
        color: var(--dashboard-heading-text);
        font-size: 20px;
    }

    .team-overview-metrics .team-access-note {
        justify-content: flex-start;
        color: var(--dashboard-muted);
        font-size: 11px;
    }

    .team-access-icon {
        display: inline-grid;
        width: 28px;
        height: 28px;
        flex: 0 0 28px;
        place-items: center;
        border-radius: 50%;
        background: rgba(56, 189, 248, .16);
        color: #7dd3fc;
        font-weight: 800;
    }

    .team-member-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .team-member-grid-spaced {
        margin-top: 12px;
    }

    .team-member {
        display: grid;
        grid-template-columns: 42px minmax(0, 1fr);
        align-items: center;
        gap: 10px;
        min-width: 0;
        padding: 12px;
        border: 1px solid var(--dashboard-border);
        border-radius: 12px;
        background: var(--dashboard-kpi-bg);
        transition: transform .18s ease, border-color .18s ease;
    }

    .team-member:hover {
        transform: translateY(-2px);
        border-color: rgba(56, 189, 248, .42);
    }

    .team-avatar {
        display: grid;
        width: 42px;
        height: 42px;
        place-items: center;
        overflow: hidden;
        border: 1px solid rgba(56, 189, 248, .34);
        border-radius: 50%;
        background: rgba(56, 189, 248, .13);
        color: var(--dashboard-heading-text);
        font-size: 13px;
        font-weight: 800;
    }

    .team-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .team-member-copy {
        min-width: 0;
    }

    .team-member-name {
        display: block;
        overflow: hidden;
        color: var(--dashboard-heading-text);
        font-size: 12px;
        font-weight: 700;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .team-member-meta {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-top: 5px;
        color: var(--dashboard-muted);
        font-size: 10px;
    }

    .team-activity-dot {
        width: 7px;
        height: 7px;
        flex: 0 0 7px;
        border-radius: 50%;
        background: #64748b;
    }

    .team-activity-dot.has-recent-activity {
        background: #22c55e;
        box-shadow: 0 0 8px rgba(34, 197, 94, .5);
    }

    @media (max-width: 1050px) {
        .dashboard-kpis {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .dashboard-grid {
            grid-template-columns: minmax(0, 1.3fr) minmax(240px, .9fr);
        }
    }

    @media (max-width: 760px) {
        .dashboard-grid,
        .dashboard-lower-grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .dashboard-analytics-heading {
            align-items: stretch;
            flex-direction: column;
        }

        .dashboard-period-filter {
            margin: 0;
        }

        .dashboard-period-filter select {
            flex: 1;
            min-width: 0;
        }

        .team-overview-metrics {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .team-member-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .team-overview-metrics .team-access-note {
            grid-column: 1 / -1;
        }

        .dashboard-chart.doughnut {
            height: 190px;
        }
    }

    @media (max-width: 520px) {
        .store-dashboard {
            padding: 10px;
            border-radius: 14px;
        }

        .dashboard-heading {
            align-items: flex-start;
            padding: 14px;
        }

        .dashboard-heading h1 {
            font-size: 24px;
        }

        .dashboard-live {
            padding: 6px 8px;
            font-size: 10px;
        }

        .dashboard-heading-tools {
            flex-direction: column;
            align-items: flex-end;
            gap: 6px;
        }

        .dashboard-clock-label {
            font-size: 9px;
        }

        .dashboard-clock time {
            font-size: 11px;
        }

        .dashboard-kpis {
            gap: 8px;
        }

        .dashboard-kpi {
            padding: 12px;
        }

        .dashboard-kpi-label {
            font-size: 10px;
        }

        .dashboard-kpi-note {
            font-size: 10px;
        }

        .dashboard-status-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .team-overview-metrics {
            grid-template-columns: 1fr;
        }

        .team-member-grid {
            grid-template-columns: 1fr;
        }

        .team-overview-metrics .team-access-note {
            grid-column: auto;
        }

        .dashboard-chart {
            height: 205px;
        }

        .dashboard-panel {
            padding: 13px;
        }
    }

    @media (max-width: 760px) {
        body.admin-dashboard-page::before,
        body.admin-dashboard-page::after {
            animation: dashboard-light-enter .7s ease-out backwards;
        }

        .dashboard-kpi {
            animation: dashboard-rise-in .46s ease-out backwards;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        body.admin-dashboard-page::before,
        body.admin-dashboard-page::after,
        body.admin-dashboard-page .sidebar,
        body.admin-dashboard-page .admin-header,
        .store-dashboard,
        .dashboard-heading,
        .dashboard-kpi,
        .dashboard-panel,
        .dashboard-status-count,
        .dashboard-orders tbody tr,
        .team-member {
            animation: none !important;
            transition: none !important;
        }

        .dashboard-kpi::before {
            transition: none;
        }

        .dashboard-kpi:hover,
        .dashboard-panel:hover,
        .team-member:hover {
            transform: none;
        }
    }
</style>

<div class="store-dashboard">
    <header class="dashboard-heading">
        <div>
            <span class="dashboard-eyebrow">Dashboard Overview</span>
            <h1>Dashboard V28</h1>
        </div>
        <div class="dashboard-heading-tools">
            <div class="dashboard-clock">
                <span class="dashboard-clock-label">Philippine time · Asia/Manila</span>
                <time
                    id="manila-clock"
                    datetime="{{ now('Asia/Manila')->toIso8601String() }}"
                >{{ now('Asia/Manila')->format('M d, Y · g:i:s A') }}</time>
            </div>
            <span class="dashboard-live">Live data</span>
        </div>
    </header>

    <section class="dashboard-kpis" aria-label="Store overview">
        @if (auth()->user()->isAdmin())
            <article class="dashboard-kpi primary">
                <span class="dashboard-kpi-label">Total Revenue</span>
                <strong class="dashboard-kpi-value" data-counter-value="{{ (float) $periodRevenue }}" data-counter-decimals="2" data-counter-prefix="₱">₱{{ number_format((float) $periodRevenue, 2) }}</strong>
                <span class="dashboard-kpi-note">{{ ucfirst($chartPeriod) }} · Completed orders only</span>
            </article>
            <article class="dashboard-kpi">
                <span class="dashboard-kpi-label">Total Sales</span>
                <strong class="dashboard-kpi-value" data-counter-value="{{ $periodSalesCount }}">{{ number_format($periodSalesCount) }}</strong>
                <span class="dashboard-kpi-note">{{ ucfirst($chartPeriod) }} completed orders</span>
            </article>
            <article class="dashboard-kpi">
                <span class="dashboard-kpi-label">Growth Percentage</span>
                <strong class="dashboard-kpi-value">
                    @if ($growthPercentage !== null)
                        <span data-counter-value="{{ abs($growthPercentage) }}" data-counter-decimals="1" data-counter-prefix="{{ $growthPercentage < 0 ? '-' : '+' }}" data-counter-suffix="%">{{ $growthPercentage < 0 ? '-' : '+' }}{{ number_format(abs($growthPercentage), 1) }}%</span>
                    @else
                        <span aria-label="No previous-period sales">—</span>
                    @endif
                </strong>
                <span class="dashboard-kpi-note">Compared with the preceding period</span>
            </article>
            <article class="dashboard-kpi">
                <span class="dashboard-kpi-label">Monthly Performance</span>
                <strong class="dashboard-kpi-value" data-counter-value="{{ (float) $monthSales }}" data-counter-decimals="2" data-counter-prefix="₱">₱{{ number_format((float) $monthSales, 2) }}</strong>
                <span class="dashboard-kpi-note">{{ number_format($ordersThisMonth) }} orders placed this month</span>
            </article>
        @endif
        <article class="dashboard-kpi">
            <span class="dashboard-kpi-label">Orders this month</span>
            <strong class="dashboard-kpi-value">{{ number_format($ordersThisMonth) }}</strong>
            <span class="dashboard-kpi-note">{{ number_format($pendingOrders) }} waiting for action</span>
        </article>
        <article class="dashboard-kpi">
            <span class="dashboard-kpi-label">Active products</span>
            <strong class="dashboard-kpi-value">{{ number_format($activeProductCount) }}</strong>
            <span class="dashboard-kpi-note">{{ number_format($lowStockCount) }} low-stock alerts</span>
        </article>
        <article class="dashboard-kpi">
            <span class="dashboard-kpi-label">Customers</span>
            <strong class="dashboard-kpi-value">{{ number_format($customerCount) }}</strong>
            <span class="dashboard-kpi-note">Registered customer accounts</span>
        </article>
    </section>

    @if (auth()->user()->isAdmin())
    <section class="dashboard-grid">
        <article class="dashboard-panel">
            <div class="dashboard-panel-heading dashboard-analytics-heading">
                <div>
                    <h2>Sales Trend</h2>
                    <small>Completed sales · {{ $chartPeriod === 'week' ? 'this week' : 'this '.$chartPeriod }}</small>
                </div>
                <form class="dashboard-period-filter" method="GET" action="{{ route('admin.dashboard') }}">
                    <label for="analytics-period">Period</label>
                    <select id="analytics-period" name="period">
                        <option value="week" @selected($chartPeriod === 'week')>Weekly</option>
                        <option value="month" @selected($chartPeriod === 'month')>Monthly</option>
                        <option value="year" @selected($chartPeriod === 'year')>Yearly</option>
                    </select>
                    <button class="dashboard-action-button" type="submit">Apply</button>
                </form>
            </div>
            <div class="dashboard-chart">
                <canvas id="salesTrendsChart" aria-label="Sales revenue for the selected period"></canvas>
            </div>
        </article>

        <article class="dashboard-panel">
            <div class="dashboard-panel-heading">
                <div>
                    <h2>Sales by Category</h2>
                    <small>{{ ucfirst($chartPeriod) }} completed sales</small>
                </div>
            </div>
            @if ($salesByCategory->isNotEmpty())
                <div class="dashboard-chart doughnut">
                    <canvas id="categorySalesChart" aria-label="Sales split by product category"></canvas>
                </div>
                <div class="category-legend">
                    @foreach ($salesByCategory as $categorySale)
                        <div class="category-legend-item">
                            <span>{{ $categorySale->category }}</span>
                            <strong>₱{{ number_format((float) $categorySale->total, 0) }}</strong>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="dashboard-empty">No completed category sales in this period yet.</div>
            @endif
        </article>

        <article class="dashboard-panel">
            <div class="dashboard-panel-heading">
                <div>
                    <h2>Sales Count</h2>
                    <small>Completed orders by {{ $chartPeriod === 'year' ? 'month' : 'day' }}</small>
                </div>
            </div>
            <div class="dashboard-chart bar">
                <canvas id="salesCountChart" aria-label="Completed sales count for the selected period"></canvas>
            </div>
        </article>
    </section>
    @endif

    <section class="dashboard-lower-grid">
        <article class="dashboard-panel">
            <div class="dashboard-panel-heading">
                <h2>Top Products</h2>
                <small>{{ ucfirst($chartPeriod) }} completed sales</small>
            </div>
            @if ($topProducts->isNotEmpty())
                <div class="top-product-list">
                    @foreach ($topProducts as $product)
                        <div class="top-product">
                            <div class="top-product-name">
                                {{ $product->product_name }}
                                <span class="top-product-quantity">{{ number_format((float) $product->quantity_sold, 0) }} sold</span>
                            </div>
                            @if (auth()->user()->isAdmin())
                                <span class="top-product-sales">₱{{ number_format((float) $product->sales_total, 2) }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="dashboard-empty">No completed product sales in this period yet.</div>
            @endif
        </article>

        @if (auth()->user()->isAdmin())
        <article class="dashboard-panel">
            <div class="dashboard-panel-heading">
                <h2>Recent Orders</h2>
                <a class="dashboard-action-button" href="{{ route('admin.orders.index') }}">All orders</a>
            </div>
            @if ($recentOrders->isNotEmpty())
                <div class="dashboard-orders-wrap">
                    <table class="dashboard-orders">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentOrders as $order)
                                <tr>
                                    <td>
                                        <a class="dashboard-order-number" href="{{ route('admin.orders.show', $order) }}">
                                            {{ $order->order_number }}
                                        </a>
                                    </td>
                                    <td>{{ $order->recipient_name ?: ($order->customer?->name ?? 'Customer') }}</td>
                                    <td>{{ $order->created_at?->format('M d') }}</td>
                                    <td><span class="dashboard-status {{ strtolower($order->status) }}">{{ ucfirst($order->status) }}</span></td>
                                    <td>₱{{ number_format((float) $order->total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="dashboard-empty">No orders have been placed yet.</div>
            @endif
        </article>
        @endif
    </section>

    @if (auth()->user()->isAdmin())
        <section class="dashboard-panel team-overview">
            <div class="dashboard-panel-heading">
                <div>
                    <h2>Team &amp; Access</h2>
                    <small>Manage administrator and staff accounts</small>
                </div>
                <a class="dashboard-action-button" href="{{ route('admin.users.index') }}">Manage team</a>
            </div>
            <div class="team-overview-metrics">
                <div>
                    <span>Administrators</span>
                    <strong>{{ number_format($adminCount) }}</strong>
                </div>
                <div>
                    <span>Staff accounts</span>
                    <strong>{{ number_format($staffCount) }}</strong>
                </div>
                <div class="team-access-note">
                    <span class="team-access-icon" aria-hidden="true">✓</span>
                    <span>Role-based access is managed per account.</span>
                </div>
            </div>
            <div class="team-member-grid team-member-grid-spaced">
                @forelse ($teamMembers as $member)
                    @php
                        $hasRecentActivity = $member->recent_activity_count > 0;
                        $initials = collect(explode(' ', trim($member->name)))
                            ->filter()
                            ->take(2)
                            ->map(fn ($part) => mb_substr($part, 0, 1))
                            ->implode('');
                    @endphp
                    <article class="team-member">
                        <span class="team-avatar" aria-hidden="true">
                            @if ($member->profile_picture)
                                <img src="{{ asset('storage/'.$member->profile_picture) }}" alt="">
                            @else
                                {{ $initials }}
                            @endif
                        </span>
                        <div class="team-member-copy">
                            <strong class="team-member-name">{{ $member->name }}</strong>
                            <span class="team-member-meta">
                                <span class="team-activity-dot {{ $hasRecentActivity ? 'has-recent-activity' : '' }}" aria-hidden="true"></span>
                                {{ $member->isAdmin() ? 'Administrator' : 'Staff' }} ·
                                {{ $hasRecentActivity ? number_format($member->recent_activity_count).' recent actions' : 'No recent activity' }}
                            </span>
                        </div>
                    </article>
                @empty
                    <div class="dashboard-empty">No administrator or staff accounts found.</div>
                @endforelse
            </div>
        </section>
    @endif

    @if (auth()->user()->isAdmin())
        <nav class="dashboard-actions" aria-label="Dashboard shortcuts">
            <a class="dashboard-action-button" href="{{ route('admin.orders.index') }}">Manage Orders</a>
            <a class="dashboard-action-button" href="{{ route('admin.inventory.index') }}">Inventory</a>
            <a class="dashboard-action-button" href="{{ route('admin.products.index') }}">Products</a>
            <a class="dashboard-action-button" href="{{ route('admin.users.index') }}">Team Management</a>
            <a class="dashboard-action-button" href="{{ route('admin.reports.index') }}">Reports</a>
            <a class="dashboard-action-button" href="{{ route('admin.expenses.index') }}">Expenses</a>
        </nav>
    @else
        <nav class="dashboard-actions" aria-label="Dashboard shortcuts">
            <a class="dashboard-action-button" href="{{ route('admin.manual-transactions.index') }}">Manual Transactions</a>
        </nav>
    @endif

    <section class="dashboard-status-summary" aria-label="Order status totals">
        <div class="dashboard-status-count"><span>Pending</span><strong>{{ number_format($pendingOrders) }}</strong></div>
        <div class="dashboard-status-count"><span>Accepted</span><strong>{{ number_format($acceptedOrders) }}</strong></div>
        <div class="dashboard-status-count"><span>Completed</span><strong>{{ number_format($completedOrders) }}</strong></div>
        <div class="dashboard-status-count"><span>Cancelled</span><strong>{{ number_format($cancelledOrders) }}</strong></div>
    </section>

    <section class="dashboard-panel">
        <div class="dashboard-panel-heading">
            <h2>Low Stock Alerts</h2>
            @if (auth()->user()->isAdmin())
                <a class="dashboard-action-button" href="{{ route('admin.inventory.index') }}">Open inventory</a>
            @endif
        </div>
        @if ($lowStockProducts->isNotEmpty())
            <div class="dashboard-orders-wrap">
                <table class="dashboard-orders">
                    <thead>
                        <tr><th>Product</th><th>Available stock</th><th>Alert threshold</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($lowStockProducts as $product)
                            @php
                                $isGenericProduct = ($product->inventory_type ?? 'rice') === 'generic';
                                $stockQuantity = (float) ($isGenericProduct ? $product->stock_base_quantity : $product->stock_kg);
                                $stockUnit = $isGenericProduct
                                    ? ($product->base_unit ?: $product->stock_unit ?: 'unit')
                                    : 'kg';
                                $thresholdQuantity = (float) ($isGenericProduct
                                    ? $product->low_stock_threshold_quantity
                                    : $product->low_stock_threshold);
                            @endphp
                            <tr>
                                <td>{{ $product->name }}</td>
                                <td>{{ number_format($stockQuantity, 2) }} {{ $stockUnit }}</td>
                                <td>{{ number_format($thresholdQuantity, 2) }} {{ $stockUnit }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="dashboard-empty">No active products are currently at or below their stock threshold.</div>
        @endif
    </section>
</div>

@if (auth()->user()->isAdmin())
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endif
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const counterNodes = document.querySelectorAll('[data-counter-value]');
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const supportsCardTilt = window.matchMedia(
            '(min-width: 761px) and (hover: hover) and (pointer: fine) and (prefers-reduced-motion: no-preference)'
        ).matches;

        if (supportsCardTilt) {
            document.querySelectorAll('.dashboard-kpi').forEach(function (card) {
                let bounds = null;
                let tiltFrame = 0;
                let pointerX = 0;
                let pointerY = 0;

                card.addEventListener('pointerenter', function (event) {
                    if (event.pointerType !== 'touch') {
                        bounds = card.getBoundingClientRect();
                    }
                });

                card.addEventListener('pointermove', function (event) {
                    if (!bounds || event.pointerType === 'touch') {
                        return;
                    }

                    pointerX = (event.clientX - bounds.left) / bounds.width;
                    pointerY = (event.clientY - bounds.top) / bounds.height;

                    if (tiltFrame) {
                        window.cancelAnimationFrame(tiltFrame);
                    }

                    tiltFrame = window.requestAnimationFrame(function () {
                        card.style.setProperty('--dashboard-tilt-x', ((pointerX - .5) * 2.8).toFixed(2) + 'deg');
                        card.style.setProperty('--dashboard-tilt-y', ((.5 - pointerY) * 2.8).toFixed(2) + 'deg');
                        card.style.setProperty('--pointer-x', (pointerX * 100).toFixed(1) + '%');
                        card.style.setProperty('--pointer-y', (pointerY * 100).toFixed(1) + '%');
                        tiltFrame = 0;
                    });
                });

                card.addEventListener('pointerleave', function () {
                    bounds = null;

                    if (tiltFrame) {
                        window.cancelAnimationFrame(tiltFrame);
                        tiltFrame = 0;
                    }

                    card.style.removeProperty('--dashboard-tilt-x');
                    card.style.removeProperty('--dashboard-tilt-y');
                    card.style.removeProperty('--pointer-x');
                    card.style.removeProperty('--pointer-y');
                });
            });
        }

        counterNodes.forEach(function (node) {
            const target = Number(node.dataset.counterValue);
            const decimals = Number(node.dataset.counterDecimals || 0);
            const prefix = node.dataset.counterPrefix || '';
            const suffix = node.dataset.counterSuffix || '';
            const formatter = new Intl.NumberFormat('en-PH', {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            });
            const render = function (value) {
                node.textContent = prefix + formatter.format(value) + suffix;
            };

            if (!Number.isFinite(target) || reduceMotion) {
                render(target);
                return;
            }

            const startedAt = performance.now();
            const duration = 850;
            const animate = function (timestamp) {
                const progress = Math.min((timestamp - startedAt) / duration, 1);
                const easedProgress = 1 - Math.pow(1 - progress, 3);
                render(target * easedProgress);

                if (progress < 1) {
                    window.requestAnimationFrame(animate);
                }
            };

            window.requestAnimationFrame(animate);
        });

        const manilaClock = document.getElementById('manila-clock');
        if (manilaClock) {
            const dateFormatter = new Intl.DateTimeFormat('en-PH', {
                timeZone: 'Asia/Manila',
                year: 'numeric',
                month: 'short',
                day: '2-digit'
            });
            const timeFormatter = new Intl.DateTimeFormat('en-PH', {
                timeZone: 'Asia/Manila',
                hour: 'numeric',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });
            const updateManilaClock = function () {
                const currentTime = new Date();
                manilaClock.dateTime = currentTime.toISOString();
                manilaClock.textContent = dateFormatter.format(currentTime)
                    + ' · ' + timeFormatter.format(currentTime);
            };

            updateManilaClock();
            window.setInterval(updateManilaClock, 1000);
        }

        @if (auth()->user()->isAdmin())
        if (typeof Chart === 'undefined') {
            return;
        }

        const charts = [];
        const isDarkTheme = function () {
            return document.documentElement.dataset.theme === 'dark';
        };

        const salesCanvas = document.getElementById('salesTrendsChart');
        if (salesCanvas) {
            const salesChart = new Chart(salesCanvas, {
                type: 'line',
                data: {
                    labels: @json($salesChartLabels),
                    datasets: [{
                        label: 'Sales (₱)',
                        data: @json($salesChartData),
                        borderColor: isDarkTheme() ? '#7dd3fc' : '#0284c7',
                        backgroundColor: isDarkTheme()
                            ? 'rgba(56, 189, 248, .18)'
                            : 'rgba(2, 132, 199, .12)',
                        fill: true,
                        tension: .38,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        pointBackgroundColor: isDarkTheme() ? '#7dd3fc' : '#0284c7',
                        pointBorderColor: isDarkTheme() ? '#131b2e' : '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: reduceMotion ? 0 : 650, easing: 'easeOutQuart' },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return '₱' + Number(context.parsed.y).toLocaleString('en-PH', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: isDarkTheme() ? '#9aa5bd' : '#748078' }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: isDarkTheme()
                                    ? 'rgba(154, 165, 189, .14)'
                                    : 'rgba(116, 128, 120, .18)'
                            },
                            ticks: {
                                color: isDarkTheme() ? '#9aa5bd' : '#748078',
                                callback: function (value) {
                                    return '₱' + Number(value).toLocaleString('en-PH');
                                }
                            }
                        }
                    }
                }
            });
            charts.push(salesChart);
        }

        const categoryCanvas = document.getElementById('categorySalesChart');
        if (categoryCanvas) {
            const categoryChart = new Chart(categoryCanvas, {
                type: 'doughnut',
                data: {
                    labels: @json($salesByCategory->pluck('category')->values()),
                    datasets: [{
                        data: @json($salesByCategory->pluck('total')->map(fn ($total) => (float) $total)->values()),
                        backgroundColor: ['#0284c7', '#38bdf8', '#0ea5e9', '#60a5fa', '#22d3ee', '#7dd3fc'],
                        borderWidth: 3,
                        borderColor: isDarkTheme() ? '#131b2e' : '#fff',
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: reduceMotion ? 0 : 650, easing: 'easeOutQuart' },
                    cutout: '66%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return context.label + ': ₱' + Number(context.parsed).toLocaleString('en-PH', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                }
                            }
                        }
                    }
                }
            });
            charts.push(categoryChart);
        }

        const salesCountCanvas = document.getElementById('salesCountChart');
        if (salesCountCanvas) {
            const salesCountChart = new Chart(salesCountCanvas, {
                type: 'bar',
                data: {
                    labels: @json($salesChartLabels),
                    datasets: [{
                        label: 'Completed sales',
                        data: @json($salesCountChartData),
                        backgroundColor: isDarkTheme() ? 'rgba(56, 189, 248, .7)' : 'rgba(2, 132, 199, .72)',
                        borderColor: isDarkTheme() ? '#7dd3fc' : '#0284c7',
                        borderWidth: 1,
                        borderRadius: 6,
                        maxBarThickness: 30
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: reduceMotion ? 0 : 650, easing: 'easeOutQuart' },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return Number(context.parsed.y).toLocaleString('en-PH') + ' completed sales';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: isDarkTheme() ? '#9aa5bd' : '#748078' }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                color: isDarkTheme() ? '#9aa5bd' : '#748078'
                            },
                            grid: {
                                color: isDarkTheme()
                                    ? 'rgba(154, 165, 189, .14)'
                                    : 'rgba(116, 128, 120, .18)'
                            }
                        }
                    }
                }
            });
            charts.push(salesCountChart);
        }

        document.addEventListener('admin-theme-change', function (event) {
            const isDark = event.detail.theme === 'dark';

            charts.forEach(function (chart) {
                if (chart.config.type === 'line') {
                    chart.data.datasets[0].backgroundColor = isDark
                        ? 'rgba(56, 189, 248, .18)'
                        : 'rgba(2, 132, 199, .12)';
                    chart.data.datasets[0].pointBackgroundColor = isDark
                        ? '#67e8f9'
                        : '#0284c7';
                    chart.data.datasets[0].pointBorderColor = isDark
                        ? '#131b2e'
                        : '#fff';
                    chart.options.scales.x.ticks.color = isDark
                        ? '#9aa5bd'
                        : '#748078';
                    chart.options.scales.y.ticks.color = isDark
                        ? '#9aa5bd'
                        : '#748078';
                    chart.options.scales.y.grid.color = isDark
                        ? 'rgba(154, 165, 189, .14)'
                        : 'rgba(116, 128, 120, .18)';
                } else if (chart.config.type === 'bar') {
                    chart.data.datasets[0].backgroundColor = isDark
                        ? 'rgba(56, 189, 248, .7)'
                        : 'rgba(2, 132, 199, .72)';
                    chart.data.datasets[0].borderColor = isDark ? '#7dd3fc' : '#0284c7';
                    chart.options.scales.x.ticks.color = isDark ? '#9aa5bd' : '#748078';
                    chart.options.scales.y.ticks.color = isDark ? '#9aa5bd' : '#748078';
                    chart.options.scales.y.grid.color = isDark
                        ? 'rgba(154, 165, 189, .14)'
                        : 'rgba(116, 128, 120, .18)';
                } else {
                    chart.data.datasets[0].borderColor = isDark ? '#131b2e' : '#fff';
                }

                chart.update();
            });
        });
        @endif
    });

    window.setTimeout(function () {
        window.location.reload();
    }, 60000);
</script>
@endsection
