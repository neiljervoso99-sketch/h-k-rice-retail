<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') | H&amp;K Consumer Goods Store</title>
    <link rel="icon" type="image/png" href="{{ asset('images/HK_Consumer_Goods_Store_logo.png') }}">
    @vite('resources/js/app.js')
    <script>
        const adminThemeKey = 'admin-theme-{{ auth()->id() }}';
        const savedAdminTheme = localStorage.getItem(adminThemeKey);
        document.documentElement.dataset.theme = savedAdminTheme === 'light'
            ? 'light'
            : 'dark';
    </script>

    <style>
        :root {
            --green: #245333;
            --green-dark: #193d25;
            --green-light: #e8f1e5;
            --page-bg: #f4f6f0;
            --border: #dfe6dc;
            --text: #263b2b;
            --muted: #657267;
            --danger: #a52b24;
        }

        :root[data-theme="dark"] {
            color-scheme: dark;
            --page-bg: #0d1323;
            --border: #293149;
            --text: #ecf1ff;
            --muted: #9aa5bd;
            --green-light: #1a2439;
            --danger: #fca5a5;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: var(--page-bg);
            color: var(--text);
            font-size: 14px;
        }

        button, input, select, textarea {
            font: inherit;
        }

        /* Top header */
        .admin-header {
            position: fixed;
            top: 0;
            left: 250px;
            right: 0;
            z-index: 30;
            min-height: 76px;
            padding: 12px 26px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            background: var(--green);
            color: white;
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 13px;
            min-width: 0;
        }

        .header-logo {
            display: block;
            width: 54px;
            height: 54px;
            flex-shrink: 0;
            object-fit: contain;
            border-radius: 6px;
        }

        .header-brand-text {
            min-width: 0;
        }

        .brand-title {
            margin: 0;
            font-size: 20px;
            font-weight: 700;
            line-height: 1.3;
        }

        .brand-subtitle {
            margin: 5px 0 0;
            font-size: 13px;
            color: #e5efe5;
        }

        /* Admin profile */
        .profile-container {
            position: relative;
            flex-shrink: 0;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .theme-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            min-height: 40px;
            padding: 8px 12px;
            border: 1px solid rgba(255, 255, 255, .35);
            border-radius: 22px;
            background: rgba(255, 255, 255, .10);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            white-space: nowrap;
        }

        .theme-toggle:hover,
        .theme-toggle:focus-visible {
            background: rgba(255, 255, 255, .20);
        }

        .theme-toggle-icon {
            font-size: 16px;
            line-height: 1;
        }

        .profile-trigger {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 5px 9px;
            border: 1px solid rgba(255, 255, 255, .35);
            border-radius: 30px;
            background: rgba(255, 255, 255, .10);
            color: white;
            cursor: pointer;
        }

        .profile-trigger:hover,
        .profile-trigger:focus-visible {
            background: rgba(255, 255, 255, .20);
        }

        .profile-avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            flex-shrink: 0;
            overflow: hidden;
            border: 2px solid white;
            border-radius: 50%;
            background: #e8f1e5;
            color: var(--green);
            font-size: 15px;
            font-weight: 700;
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-info {
            max-width: 150px;
            text-align: left;
        }

        .profile-name {
            display: block;
            overflow: hidden;
            font-size: 13px;
            font-weight: 700;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .profile-role {
            display: block;
            margin-top: 3px;
            color: #e5efe5;
            font-size: 11px;
        }

        .profile-arrow {
            font-size: 12px;
        }

        .profile-dropdown {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            z-index: 50;
            width: 245px;
            padding: 8px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: white;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .15);
            color: var(--text);
        }

        .profile-dropdown[hidden] {
            display: none;
        }

        .profile-dropdown-header {
            padding: 12px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 6px;
            overflow-wrap: anywhere;
        }

        .profile-dropdown-header strong {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .profile-dropdown-header span {
            color: var(--muted);
            font-size: 12px;
        }

        .profile-menu-link,
        .profile-logout-button {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            padding: 11px 12px;
            border: 0;
            border-radius: 6px;
            background: transparent;
            color: var(--text);
            text-align: left;
            text-decoration: none;
            cursor: pointer;
            font-size: 13px;
        }

        .profile-menu-link:hover {
            background: var(--green-light);
        }

        .profile-logout-button {
            color: var(--danger);
        }

        .profile-logout-button:hover {
            background: #fff0ee;
        }

        .profile-menu-icon {
            width: 20px;
            text-align: center;
            font-size: 16px;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 40;
            width: 250px;
            overflow-y: auto;
            padding: 22px 14px;
            background: #fff;
            border-right: 1px solid var(--border);
        }

        :root[data-theme="dark"] .sidebar {
            background: #11192a;
        }

        :root[data-theme="dark"] .sidebar-brand h2,
        :root[data-theme="dark"] .page-heading h1,
        :root[data-theme="dark"] .panel h2,
        :root[data-theme="dark"] .panel h3,
        :root[data-theme="dark"] .summary-card strong,
        :root[data-theme="dark"] .summary-card p {
            color: var(--text);
        }

        :root[data-theme="dark"] .panel,
        :root[data-theme="dark"] .summary-card,
        :root[data-theme="dark"] .summary-grid > div {
            background-color: #131b2e;
            border-color: var(--border);
            color: var(--text);
        }

        :root[data-theme="dark"] .table-wrap th,
        :root[data-theme="dark"] th {
            background: #1a2439;
            color: #d3dbed;
        }

        :root[data-theme="dark"] td {
            color: #d3dbed;
        }

        :root[data-theme="dark"] input,
        :root[data-theme="dark"] select,
        :root[data-theme="dark"] textarea {
            border-color: #3a465e;
            background: #11192a;
            color: #ecf1ff;
        }

        :root[data-theme="dark"] input::placeholder,
        :root[data-theme="dark"] textarea::placeholder {
            color: #9aa5bd;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 6px 8px 22px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 20px;
            min-width: 0;
        }

        .sidebar-logo {
            display: block;
            width: 52px;
            height: 52px;
            flex-shrink: 0;
            object-fit: contain;
        }

        .sidebar-brand-text {
            min-width: 0;
        }

        .sidebar-brand h2 {
            margin: 0;
            font-size: 16px;
            line-height: 1.3;
            color: var(--green);
            overflow-wrap: anywhere;
        }

        .sidebar-brand p {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 12px;
        }

        .menu-heading {
            margin: 0 10px 10px;
            color: var(--muted);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 12px;
            border-radius: 7px;
            color: var(--text);
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: background .15s ease;
        }

        .sidebar-nav a:hover {
            background: var(--green-light);
        }

        .sidebar-nav a.active {
            background: var(--green);
            color: white;
        }

        .menu-icon {
            display: inline-flex;
            width: 22px;
            flex-shrink: 0;
            justify-content: center;
            font-size: 17px;
        }

        .sidebar-divider {
            height: 1px;
            background: var(--border);
            margin: 15px 5px;
        }

        .sidebar-logout {
            display: flex;
            align-items: center;
            gap: 11px;
            width: 100%;
            padding: 12px;
            text-align: left;
            border: 0;
            border-radius: 7px;
            background: transparent;
            color: var(--danger);
            cursor: pointer;
            font-weight: 700;
        }

        .sidebar-logout:hover {
            background: #fff0ee;
        }

        /* Main content */
        .admin-main {
            margin-left: 250px;
            padding: 102px 26px 32px;
            min-height: 100vh;
        }

        .page-heading {
            margin-bottom: 22px;
        }

        .page-heading h1 {
            margin: 0 0 8px;
            font-size: 24px;
        }

        .page-heading p {
            margin: 0;
            color: var(--muted);
            line-height: 1.5;
        }

        .panel {
            margin-bottom: 20px;
            padding: 20px;
            background: white;
            border: 1px solid var(--border);
            border-radius: 9px;
        }

        .panel h2 {
            margin-top: 0;
            font-size: 18px;
        }

        .notice {
            padding: 13px 15px;
            margin-bottom: 18px;
            border-radius: 7px;
            line-height: 1.5;
        }

        .notice.success {
            background: #e1f4e4;
            color: #205c30;
        }

        .notice.error {
            background: #fff0ee;
            color: #9c2920;
        }

        .notice ul {
            margin-bottom: 0;
        }

        .fields {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 15px;
        }

        .field label,
        .panel label {
            display: block;
            margin-bottom: 6px;
            font-weight: 700;
            font-size: 13px;
        }

        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #cbd5ca;
            border-radius: 5px;
            background: white;
            color: var(--text);
        }

        input:focus, select:focus, textarea:focus {
            outline: 2px solid #9cc6a4;
            outline-offset: 1px;
        }

        .button {
            display: inline-block;
            padding: 11px 15px;
            border: 0;
            border-radius: 5px;
            background: var(--green);
            color: white;
            text-decoration: none;
            cursor: pointer;
            font-weight: 700;
        }

        .button:hover {
            background: var(--green-dark);
        }

        .button-danger {
            background: var(--danger);
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 11px;
            border-bottom: 1px solid #e2e8df;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f4f6f0;
            font-size: 13px;
        }

        .muted {
            color: var(--muted);
            line-height: 1.5;
        }

       
.summary-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
}

.summary-card {
    padding: 15px 16px;
    background: var(--green-light);
    border: 1px solid rgba(22, 101, 52, 0.10);
    border-radius: 12px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
    min-width: 0;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.summary-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 12px rgba(0, 0, 0, 0.07);
}

.summary-card p {
    margin: 0 0 6px;
    color: #526454;
    font-size: 13px;
    line-height: 1.4;
}

.summary-card strong {
    display: block;
    font-size: 22px;
    line-height: 1.3;
    overflow-wrap: anywhere;
}

.order-status-badge {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
    line-height: 1.3;
    white-space: nowrap;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-accepted {
    background: #dbeafe;
    color: #1d4ed8;
}

.status-completed {
    background: #dcfce7;
    color: #166534;
}

.status-cancelled {
    background: #fee2e2;
    color: #991b1b;
}

@media (max-width: 900px) {
    .summary-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 520px) {
    .summary-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 9px;
    }

    .summary-card {
        padding: 12px;
        border-radius: 10px;
    }

    .summary-card strong {
        font-size: 19px;
    }

    .summary-card p {
        font-size: 12px;
    }
}

        /* Tablet layout */
        @media (max-width: 800px) {
            .sidebar {
                width: 205px;
            }

            .sidebar-brand {
                gap: 8px;
                padding-left: 5px;
                padding-right: 5px;
            }

            .sidebar-logo {
                width: 43px;
                height: 43px;
            }

            .sidebar-brand h2 {
                font-size: 14px;
            }

            .admin-header {
                left: 205px;
                padding: 12px 16px;
            }

            .admin-main {
                margin-left: 205px;
                padding: 98px 16px 25px;
            }

            .summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .profile-info {
                max-width: 100px;
            }
        }

        /* Mobile layout */
        @media (max-width: 560px) {
            .sidebar {
                width: 66px;
                padding: 15px 7px;
            }

            .sidebar-brand {
                flex-direction: column;
                gap: 7px;
                padding: 8px 0 16px;
                text-align: center;
            }

            .sidebar-logo {
                width: 42px;
                height: 42px;
            }

            .sidebar-brand h2 {
                font-size: 10px;
                line-height: 1.2;
                overflow-wrap: anywhere;
            }

            .sidebar-brand p,
            .menu-heading,
            .menu-label {
                display: none;
            }

            .sidebar-nav a {
                justify-content: center;
                padding: 13px 5px;
            }

            .menu-icon {
                font-size: 19px;
            }

            .sidebar-logout {
                justify-content: center;
                padding: 12px 5px;
            }

            .admin-header {
                left: 66px;
                min-height: 68px;
                padding: 9px 10px;
                gap: 6px;
            }

            .header-brand {
                gap: 7px;
            }

            .header-logo {
                width: 34px;
                height: 34px;
            }

            .brand-title {
                font-size: 12px;
            }

            .brand-subtitle {
                margin-top: 3px;
                font-size: 9px;
            }

            .profile-trigger {
                gap: 0;
                padding: 3px;
            }

            .header-actions {
                gap: 5px;
            }

            .theme-toggle {
                width: 38px;
                min-width: 38px;
                min-height: 38px;
                padding: 6px;
            }

            .theme-toggle-label {
                display: none;
            }

            .profile-avatar {
                width: 34px;
                height: 34px;
                font-size: 12px;
            }

            .profile-info,
            .profile-arrow {
                display: none;
            }

            .profile-dropdown {
                width: 220px;
            }

            .admin-main {
                margin-left: 66px;
                padding: 88px 12px 20px;
            }

            .fields {
                grid-template-columns: 1fr;
            }

            .summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 9px;
            }

            .page-heading h1 {
                font-size: 21px;
            }

            .panel {
                padding: 14px;
            }
        }

        /* Admin tables: cleaner spacing and mobile-friendly scrolling */
        .table-wrap {
            width: 100%;
            overflow-x: auto;
            border-radius: 10px;
        }

        .table-wrap table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table-wrap th,
        .table-wrap td {
            padding: 12px 14px;
            text-align: left;
            vertical-align: middle;
        }

        .table-wrap th {
            background: var(--green-light);
            color: #354b39;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            white-space: nowrap;
        }

        .table-wrap td {
            border-bottom: 1px solid #e8eee8;
            font-size: 13px;
        }

        .table-wrap tbody tr:last-child td {
            border-bottom: none;
        }

        .table-wrap tbody tr:hover {
            background: #f8fbf8;
        }

        .table-wrap td .button {
            white-space: nowrap;
        }

        @media (max-width: 520px) {
            .table-wrap th,
            .table-wrap td {
                padding: 10px;
            }
        }

        /* Quick action buttons */
        .panel .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 10px 15px;
            margin: 4px 5px 4px 0;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.4;
            text-decoration: none;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .panel .button:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.12);
        }

        @media (max-width: 520px) {
            .panel .button {
                padding: 9px 11px;
                font-size: 12px;
                margin: 3px 3px 3px 0;
            }
        }

        /* Consistent panel styling */
        .panel {
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e5ebe5;
            background: #ffffff;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.035);
        }

        .panel h2 {
            margin-top: 0;
            margin-bottom: 14px;
            font-size: 18px;
            line-height: 1.4;
            color: #23452c;
        }

        .panel > p {
            line-height: 1.6;
        }

        @media (max-width: 520px) {
            .panel {
                padding: 14px;
                border-radius: 10px;
            }

            .panel h2 {
                font-size: 16px;
            }
        }

        /* Small-screen layout refinements */
        .admin-main {
            min-width: 0;
        }

        .panel,
        .table-wrap {
            min-width: 0;
        }

        .table-wrap {
            -webkit-overflow-scrolling: touch;
        }

        @media (max-width: 560px) {
            .page-heading {
                margin-bottom: 16px;
            }

            .page-heading h1 {
                font-size: 20px;
            }

            .panel {
                margin-bottom: 14px;
            }

            .summary-card strong {
                font-size: 18px;
            }

            #salesTrendsChart {
                max-width: 100%;
            }
        }

        @media (max-width: 560px) {
            .sidebar {
                top: auto;
                bottom: 0;
                width: 100%;
                max-height: calc(86px + env(safe-area-inset-bottom));
                overflow-x: auto;
                overflow-y: hidden;
                padding: 6px 6px calc(6px + env(safe-area-inset-bottom));
                border-top: 1px solid var(--border);
                border-right: 0;
                box-shadow: 0 -3px 14px rgba(0, 0, 0, .08);
                scrollbar-width: none;
                -webkit-overflow-scrolling: touch;
            }

            .sidebar::-webkit-scrollbar {
                display: none;
            }

            .sidebar-brand,
            .menu-heading,
            .sidebar-divider,
            .sidebar > form {
                display: none;
            }

            .sidebar-nav {
                width: max-content;
                min-width: 100%;
                flex-direction: row;
                justify-content: flex-start;
                gap: 3px;
            }

            .sidebar-nav a {
                min-width: 72px;
                min-height: 60px;
                flex: 0 0 auto;
                flex-direction: column;
                justify-content: center;
                gap: 3px;
                padding: 5px 4px;
                text-align: center;
            }

            .sidebar-nav .menu-icon,
            .sidebar-nav .sidebar-icon {
                width: auto;
                font-size: 17px;
            }

            .sidebar-nav .menu-label,
            .sidebar-nav a > span:last-child {
                display: block;
                max-width: 68px;
                font-size: 9px;
                line-height: 1.1;
                white-space: normal;
            }

            .admin-header {
                left: 0;
                right: 0;
            }

            .admin-main {
                margin-left: 0;
                padding-bottom: calc(100px + env(safe-area-inset-bottom));
            }

            .table-wrap {
                max-width: 100%;
                overscroll-behavior-x: contain;
            }
        }
    </style>
</head>
<body>
    @php
        $adminUser = auth()->user();
        $adminName = $adminUser?->name ?? 'Administrator';
        $adminEmail = $adminUser?->email ?? 'No email available';
        $isAdministrator = $adminUser?->isAdmin() ?? false;
        $adminRoleLabel = $isAdministrator ? 'Administrator' : 'Staff';

        $adminInitials = collect(preg_split('/\s+/', trim($adminName)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');

        $adminInitials = $adminInitials ?: 'A';

        // Optional: use a public profile image if your User model has a profile_picture field.
        $profilePicture = $adminUser && !empty($adminUser->profile_picture)
            ? asset('storage/' . ltrim($adminUser->profile_picture, '/'))
            : null;
    @endphp

    <aside class="sidebar">
        <div class="sidebar-brand">
            <img
                class="sidebar-logo"
                src="{{ asset('images/HK_Consumer_Goods_Store_logo.png') }}"
                alt="H&amp;K Consumer Goods Store Logo"
            >

            <div class="sidebar-brand-text">
                <h2>H&amp;K Consumer Goods Store</h2>
                <p>{{ $adminRoleLabel }} Panel</p>
            </div>
        </div>

        <p class="menu-heading">Main Menu</p>

        <nav class="sidebar-nav" aria-label="Admin navigation">
            <a href="{{ route('admin.dashboard') }}"
               class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <span class="menu-icon">⌂</span>
                <span class="menu-label">Dashboard</span>
            </a>

            <a href="{{ route('admin.manual-transactions.index') }}"
               class="{{ request()->routeIs('admin.manual-transactions.*') ? 'active' : '' }}">
                <span class="sidebar-icon">🧾</span>
                <span class="menu-label">Manual Transactions</span>
            </a>

            @if ($isAdministrator)
            <a href="{{ route('admin.products.index') }}"
               class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                <span class="menu-icon">▦</span>
                <span class="menu-label">Products</span>
            </a>

            <a href="{{ route('admin.reviews.index') }}"
               class="{{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
                <span class="menu-icon">&#9733;</span>
                <span class="menu-label">Product Reviews</span>
            </a>

            <a href="{{ route('admin.bundles.index') }}"
               class="{{ request()->routeIs('admin.bundles.*') ? 'active' : '' }}">
                <span class="menu-icon">▣</span>
                <span class="menu-label">Bundle Packs</span>
            </a>

            <a href="{{ route('admin.inventory.index') }}"
               class="{{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}">
                <span class="menu-icon">▤</span>
                <span class="menu-label">Inventory</span>
            </a>
<a href="{{ route('admin.orders.index') }}"
   class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
    <span class="menu-icon">☷</span>
    <span class="menu-label">Customer Orders</span>
</a>

            <a href="{{ route('admin.profit-pricing.index') }}"
               class="{{ request()->routeIs('admin.profit-pricing.*') ? 'active' : '' }}">
                <span class="menu-icon">₱</span>
                <span class="menu-label">Profit &amp; Pricing</span>
            </a>

            <a href="{{ route('admin.expenses.index') }}"
               class="{{ request()->routeIs('admin.expenses.*') ? 'active' : '' }}">
                <span class="menu-icon">▧</span>
                <span class="menu-label">Expenses</span>
            </a>

            <a href="{{ route('admin.reports.index') }}"
               class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <span class="menu-icon">▥</span>
                <span class="menu-label">Reports</span>
            </a>

            <a href="{{ route('admin.users.index') }}"
               class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <span class="menu-icon">♙</span>
                <span class="menu-label">Users &amp; Roles</span>
            </a>
            @endif
        </nav>

        <div class="sidebar-divider"></div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="sidebar-logout" type="submit">
                <span class="menu-icon">↪</span>
                <span class="menu-label">Sign Out</span>
            </button>
        </form>
    </aside>

    <header class="admin-header">
        <div class="header-brand">
            <img
                class="header-logo"
                src="{{ asset('images/HK_Consumer_Goods_Store_logo.png') }}"
                alt="H&amp;K Consumer Goods Store Logo"
            >

            <div class="header-brand-text">
                <h1 class="brand-title">H&amp;K Consumer Goods Store</h1>
                <p class="brand-subtitle">Business Management System</p>
            </div>
        </div>

        <div class="header-actions">
            <button
                class="theme-toggle"
                id="themeToggle"
                type="button"
                aria-label="Switch to light mode"
                aria-pressed="true"
            >
                <span class="theme-toggle-icon" aria-hidden="true">☀</span>
                <span class="theme-toggle-label">Light mode</span>
            </button>

            @include('components.notification-center')

        <div class="profile-container">
            <button
                class="profile-trigger"
                id="profileTrigger"
                type="button"
                aria-label="Open account menu"
                aria-haspopup="true"
                aria-expanded="false"
                aria-controls="profileDropdown"
            >
                <span class="profile-avatar">
                    @if ($profilePicture)
                        <img src="{{ $profilePicture }}" alt="Admin profile picture">
                    @else
                        {{ $adminInitials }}
                    @endif
                </span>

                <span class="profile-info">
                    <span class="profile-name">{{ $adminName }}</span>
                    <span class="profile-role">{{ $adminRoleLabel }}</span>
                </span>

                <span class="profile-arrow" aria-hidden="true">▼</span>
            </button>

            <div class="profile-dropdown" id="profileDropdown" hidden>
                <div class="profile-dropdown-header">
                    <strong>{{ $adminRoleLabel }} Account</strong>
                    <span>{{ $adminName }}</span>
                    <br>
                    <span>{{ $adminEmail }}</span>
                </div>

                <a class="profile-menu-link" href="{{ route('admin.profile.edit') }}">
                    <span class="profile-menu-icon">👤</span>
                    <span>My Profile &amp; Activity</span>
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="profile-logout-button" type="submit">
                        <span class="profile-menu-icon">↪</span>
                        <span>Sign Out</span>
                    </button>
                </form>
            </div>
        </div>
        </div>
    </header>

    <main class="admin-main">
        @if (session('success'))
            <div class="notice success" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="notice error" role="alert">
                <strong>Please correct the following errors:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <style>
        :root[data-theme="dark"] .admin-main {
            color: #ecf1ff;
        }

        :root[data-theme="dark"] .admin-main h1,
        :root[data-theme="dark"] .admin-main h2,
        :root[data-theme="dark"] .admin-main h3,
        :root[data-theme="dark"] .admin-main h4,
        :root[data-theme="dark"] .admin-main h5,
        :root[data-theme="dark"] .admin-main h6,
        :root[data-theme="dark"] .admin-main .fw-bold,
        :root[data-theme="dark"] .admin-main .fw-semibold {
            color: #ecf1ff;
        }

        :root[data-theme="dark"] .admin-main .text-muted,
        :root[data-theme="dark"] .admin-main .muted,
        :root[data-theme="dark"] .admin-main small,
        :root[data-theme="dark"] .admin-main .page-heading p {
            color: #aab6cb !important;
        }

        :root[data-theme="dark"] .admin-main .panel,
        :root[data-theme="dark"] .admin-main .summary-card,
        :root[data-theme="dark"] .admin-main .card,
        :root[data-theme="dark"] .admin-main .report-panel,
        :root[data-theme="dark"] .admin-main .pos-product-panel {
            border-color: #293149;
            background-color: #131b2e;
            color: #ecf1ff;
        }

        :root[data-theme="dark"] .admin-main .card-header,
        :root[data-theme="dark"] .admin-main .card-footer,
        :root[data-theme="dark"] .admin-main .bg-white {
            border-color: #293149 !important;
            background-color: #131b2e !important;
            color: #ecf1ff !important;
        }

        :root[data-theme="dark"] .admin-main .bg-light,
        :root[data-theme="dark"] .admin-main .table-light {
            --bs-bg-opacity: 1;
            --bs-table-bg: #1a2439;
            --bs-table-color: #d3dbed;
            background-color: #1a2439 !important;
            color: #d3dbed !important;
        }

        :root[data-theme="dark"] .admin-main label,
        :root[data-theme="dark"] .admin-main .form-label,
        :root[data-theme="dark"] .admin-main legend {
            color: #d3dbed;
        }

        :root[data-theme="dark"] .admin-main input:not([type="checkbox"]):not([type="radio"]),
        :root[data-theme="dark"] .admin-main select,
        :root[data-theme="dark"] .admin-main textarea,
        :root[data-theme="dark"] .admin-main .form-control,
        :root[data-theme="dark"] .admin-main .form-select {
            border-color: #3a465e;
            background-color: #11192a;
            color: #ecf1ff;
        }

        :root[data-theme="dark"] .admin-main input::placeholder,
        :root[data-theme="dark"] .admin-main textarea::placeholder {
            color: #9aa5bd;
        }

        :root[data-theme="dark"] .admin-main option {
            background: #11192a;
            color: #ecf1ff;
        }

        :root[data-theme="dark"] .admin-main .table,
        :root[data-theme="dark"] .admin-main table {
            --bs-table-bg: #11192a;
            --bs-table-color: #d3dbed;
            --bs-table-border-color: #293149;
            color: #d3dbed;
        }

        :root[data-theme="dark"] .admin-main th {
            border-color: #293149;
            background-color: #1a2439;
            color: #d3dbed;
        }

        :root[data-theme="dark"] .admin-main td {
            border-color: #293149;
            color: #d3dbed;
        }

        :root[data-theme="dark"] .admin-main tbody tr:hover > td {
            background-color: #1a2439;
        }

        :root[data-theme="dark"] .admin-main .border,
        :root[data-theme="dark"] .admin-main .border-top,
        :root[data-theme="dark"] .admin-main .border-bottom {
            border-color: #293149 !important;
        }

        :root[data-theme="dark"] .admin-main .alert-success {
            border-color: #35533e;
            background-color: #172a22;
            color: #c9ead0;
        }

        :root[data-theme="dark"] .admin-main .alert-danger {
            border-color: #653b42;
            background-color: #332027;
            color: #ffb4ad;
        }

        :root[data-theme="dark"] .admin-main .alert-warning {
            border-color: #66512a;
            background-color: #332a18;
            color: #f5d894;
        }

        :root[data-theme="dark"] .admin-main .pagination .page-link {
            border-color: #3a465e;
            background-color: #131b2e;
            color: #d3dbed;
        }

        :root[data-theme="dark"] .admin-main .pagination .page-item.active .page-link {
            border-color: #245b38;
            background-color: #245b38;
            color: #fff;
        }

        :root[data-theme="dark"] .admin-main .manual-transaction-page .transaction-item,
        :root[data-theme="dark"] .admin-main .manual-transaction-page #cashPaymentFields,
        :root[data-theme="dark"] .admin-main .manual-transaction-page #paymentMessage {
            border-color: #293149 !important;
            background-color: #11192a !important;
            color: #d3dbed;
        }

        :root[data-theme="dark"] .admin-main .manual-transaction-page .transaction-item h6,
        :root[data-theme="dark"] .admin-main .manual-transaction-page .form-label {
            color: #d3dbed;
        }

        :root[data-theme="dark"] .admin-main .manual-transaction-page .line-total,
        :root[data-theme="dark"] .admin-main .manual-transaction-page #grandTotal {
            color: #9fe0b0;
        }

        :root[data-theme="dark"] .admin-main .manual-transaction-page > .d-flex:first-child {
            border-color: #293149;
            background: #131b2e;
        }

        :root[data-theme="dark"] .admin-main .manual-transaction-page .pos-search,
        :root[data-theme="dark"] .admin-main .manual-transaction-page .pos-category-filters button,
        :root[data-theme="dark"] .admin-main .manual-transaction-page .pos-product-tile {
            border-color: #3a465e;
            background: #11192a;
            color: #ecf1ff;
        }

        :root[data-theme="dark"] .admin-main .manual-transaction-page .pos-search > span,
        :root[data-theme="dark"] .admin-main .manual-transaction-page .pos-product-tile .pos-tile-type,
        :root[data-theme="dark"] .admin-main .manual-transaction-page .pos-product-tile .pos-tile-stock,
        :root[data-theme="dark"] .admin-main .manual-transaction-page .pos-empty-products {
            color: #aab6cb;
        }

        :root[data-theme="dark"] .admin-main .manual-transaction-page .pos-product-count {
            background: #1a2b43;
            color: #b8d7ff;
        }

        :root[data-theme="dark"] .admin-main .manual-transaction-page .pos-cart-tip {
            background: #172a22;
            color: #c9ead0;
        }

        :root[data-theme="dark"] .admin-main .manual-transaction-page .pos-product-tile .pos-tile-price {
            color: #9fe0b0;
        }

        :root[data-theme="dark"] .admin-main .manual-transaction-page #paymentSummary {
            border-color: #35533e !important;
            background-color: #131f24 !important;
        }

        :root[data-theme="dark"] .admin-main .manual-transaction-page #paymentSummary .card-header {
            border-color: #35533e !important;
            background-color: #172a22 !important;
            color: #c9ead0 !important;
        }

        :root[data-theme="dark"] .admin-main .manual-transaction-page #paymentSummary .border.rounded,
        :root[data-theme="dark"] .admin-main .manual-transaction-page .d-flex.justify-content-end.mt-4 > div {
            border-color: #35533e !important;
            background-color: #172a22 !important;
            color: #c9ead0;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const themeToggle = document.getElementById('themeToggle');
            const themeIcon = themeToggle?.querySelector('.theme-toggle-icon');
            const themeLabel = themeToggle?.querySelector('.theme-toggle-label');

            function updateThemeToggle() {
                const isDark = document.documentElement.dataset.theme === 'dark';

                if (!themeToggle || !themeIcon || !themeLabel) {
                    return;
                }

                themeToggle.setAttribute('aria-pressed', String(isDark));
                themeToggle.setAttribute(
                    'aria-label',
                    isDark ? 'Switch to light mode' : 'Switch to dark mode'
                );
                themeIcon.textContent = isDark ? '☀' : '☾';
                themeLabel.textContent = isDark ? 'Light mode' : 'Dark mode';
            }

            updateThemeToggle();
            themeToggle?.addEventListener('click', function () {
                const nextTheme = document.documentElement.dataset.theme === 'dark'
                    ? 'light'
                    : 'dark';

                document.documentElement.dataset.theme = nextTheme;
                localStorage.setItem(adminThemeKey, nextTheme);
                updateThemeToggle();
                document.dispatchEvent(new CustomEvent('admin-theme-change', {
                    detail: { theme: nextTheme },
                }));
            });

            const trigger = document.getElementById('profileTrigger');
            const dropdown = document.getElementById('profileDropdown');

            if (!trigger || !dropdown) {
                return;
            }

            function closeProfileMenu() {
                dropdown.hidden = true;
                trigger.setAttribute('aria-expanded', 'false');
            }

            trigger.addEventListener('click', function (event) {
                event.stopPropagation();

                const willOpen = dropdown.hidden;
                dropdown.hidden = !willOpen;
                trigger.setAttribute('aria-expanded', String(willOpen));
            });

            document.addEventListener('click', function (event) {
                if (!dropdown.contains(event.target) && !trigger.contains(event.target)) {
                    closeProfileMenu();
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeProfileMenu();
                    trigger.focus();
                }
            });
        });
    </script>
    @include('components.smart-assistant')
</body>
</html>