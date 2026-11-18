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
        const adminAccentKey = 'admin-accent-{{ auth()->id() }}';
        const adminAccentOptions = {
            green: { primary: '#245333', dark: '#193d25', light: '#e8f1e5', darkLight: '#1a2439' },
            blue: { primary: '#1d4ed8', dark: '#1e3a8a', light: '#eaf1ff', darkLight: '#17233c' },
            teal: { primary: '#0f766e', dark: '#134e4a', light: '#e5f5f2', darkLight: '#123433' },
            amber: { primary: '#b45309', dark: '#78350f', light: '#fff4d6', darkLight: '#392915' },
            red: { primary: '#b42318', dark: '#7f1d1d', light: '#fff0ef', darkLight: '#3b2022' },
            slate: { primary: '#475569', dark: '#1e293b', light: '#eef2f6', darkLight: '#222b3c' },
        };
        const savedAdminTheme = localStorage.getItem(adminThemeKey);
        const root = document.documentElement;
        const defaultAdminTheme = 'light';
        root.dataset.theme = savedAdminTheme === 'light' || savedAdminTheme === 'dark'
            ? savedAdminTheme
            : defaultAdminTheme;
        const savedAdminAccent = localStorage.getItem(adminAccentKey);

        function applyAdminAccent(accentName) {
            const selectedAccent = adminAccentOptions[accentName]
                ? accentName
                : 'blue';
            const colors = adminAccentOptions[selectedAccent];

            root.dataset.accent = selectedAccent;
            root.style.setProperty('--green', colors.primary);
            root.style.setProperty('--green-dark', colors.dark);
            root.style.setProperty('--green-light', root.dataset.theme === 'dark'
                ? colors.darkLight
                : colors.light);
        }

        applyAdminAccent(savedAdminAccent);
    </script>

    <style>
        :root {
            --green: #1d8acb;
            --green-dark: #126da5;
            --green-light: #e7f4fb;
            --page-bg: #f1f7fb;
            --border: #d7e6ef;
            --text: #183247;
            --muted: #5f7688;
            --danger: #a52b24;
        }

        :root[data-theme="dark"] {
            color-scheme: dark;
            --page-bg: #07121e;
            --border: rgba(148, 193, 221, .17);
            --text: #eaf4fb;
            --muted: #a8bbce;
            --green-light: #142a3b;
            --danger: #fca5a5;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background:
                radial-gradient(ellipse at 14% 10%, rgba(56, 189, 248, .18), transparent 34%),
                radial-gradient(ellipse at 88% 4%, rgba(37, 99, 235, .12), transparent 32%),
                linear-gradient(145deg, #f8fcff, #eaf4fb 52%, #f5faff);
            color: var(--text);
            font-size: 14px;
            background-attachment: fixed;
        }

        :root[data-theme="dark"] body {
            background:
                radial-gradient(ellipse at 14% 10%, rgba(14, 165, 233, .2), transparent 34%),
                radial-gradient(ellipse at 88% 4%, rgba(37, 99, 235, .16), transparent 32%),
                linear-gradient(145deg, #091827, #07121e 55%, #0c1e30);
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
            background: linear-gradient(110deg, #0b2740, #12466d);
            color: white;
            border-bottom: 1px solid rgba(125, 211, 252, .2);
            box-shadow: 0 10px 28px rgba(2, 8, 23, .18);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
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

        .sidebar-toggle {
            display: inline-grid;
            width: 40px;
            height: 40px;
            flex: 0 0 40px;
            place-items: center;
            border: 1px solid rgba(255, 255, 255, .35);
            border-radius: 12px;
            background: rgba(255, 255, 255, .1);
            color: #fff;
            cursor: pointer;
            font-size: 19px;
            transition: background .18s ease, transform .18s ease;
        }

        .sidebar-toggle:hover,
        .sidebar-toggle:focus-visible {
            background: rgba(255, 255, 255, .2);
            transform: translateY(-1px);
        }

        .theme-toggle-icon {
            font-size: 16px;
            line-height: 1;
        }

        .accent-picker { position: relative; }

        .accent-picker-trigger {
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

        .accent-picker-trigger:hover,
        .accent-picker-trigger:focus-visible { background: rgba(255, 255, 255, .20); }

        .accent-preview {
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, .85);
            border-radius: 50%;
            background: var(--green);
        }

        .accent-picker-popover {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            z-index: 60;
            width: 226px;
            padding: 14px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--page-bg);
            color: var(--text);
            box-shadow: 0 12px 30px rgba(0, 0, 0, .2);
        }

        .accent-picker-popover[hidden] { display: none; }
        .accent-picker-title { margin: 0 0 12px; font-size: 13px; font-weight: 700; }
        .theme-mode-options { display: grid; grid-template-columns: repeat(2, 1fr); gap: 7px; margin-bottom: 14px; }
        .theme-mode-option {
            min-height: 36px;
            border: 1px solid var(--border);
            border-radius: 6px;
            background: var(--page-bg);
            color: var(--text);
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }
        .theme-mode-option[aria-pressed="true"] { border-color: var(--green); background: var(--green); color: #fff; }
        .accent-swatches { display: grid; grid-template-columns: repeat(3, 1fr); gap: 9px; }

        .accent-swatch {
            display: grid;
            width: 48px;
            height: 42px;
            place-items: center;
            border: 1px solid var(--border);
            border-radius: 7px;
            background: var(--page-bg);
            cursor: pointer;
        }

        .accent-swatch:hover,
        .accent-swatch[aria-pressed="true"] { outline: 2px solid var(--green); outline-offset: 2px; }

        .accent-swatch-color {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--swatch-color);
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
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 6px;
            overflow-wrap: anywhere;
        }

        .profile-dropdown-avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 52px;
            height: 52px;
            flex: 0 0 52px;
            overflow: hidden;
            border: 2px solid var(--green);
            border-radius: 50%;
            background: var(--green-light);
            color: var(--green);
            font-size: 17px;
            font-weight: 700;
            text-decoration: none;
        }

        .profile-dropdown-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-dropdown-identity {
            min-width: 0;
        }

        .profile-dropdown-header strong {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .profile-dropdown-identity span {
            display: block;
            color: var(--muted);
            font-size: 12px;
            overflow-wrap: anywhere;
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
            background: rgba(255, 255, 255, .86);
            border-right: 1px solid var(--border);
            box-shadow: 8px 0 28px rgba(20, 52, 74, .06);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            transition: width .2s ease, transform .2s ease;
        }

        body.sidebar-collapsed .sidebar {
            width: 76px;
            padding-right: 8px;
            padding-left: 8px;
        }

        body.sidebar-collapsed .sidebar-brand {
            justify-content: center;
            padding-right: 0;
            padding-left: 0;
        }

        body.sidebar-collapsed .sidebar-brand-text,
        body.sidebar-collapsed .menu-heading {
            display: none;
        }

        body.sidebar-collapsed .menu-label {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        body.sidebar-collapsed .sidebar-nav a,
        body.sidebar-collapsed .sidebar-logout {
            justify-content: center;
            padding-right: 5px;
            padding-left: 5px;
        }

        :root[data-theme="dark"] .sidebar {
            background: rgba(8, 21, 34, .94);
            border-right-color: rgba(125, 211, 252, .15);
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
            border-radius: 10px;
            color: var(--text);
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: background .18s ease, color .18s ease, transform .18s ease, box-shadow .18s ease;
        }

        .sidebar-nav a:hover {
            background: rgba(56, 189, 248, .12);
            color: #0284c7;
            transform: translateX(2px);
        }

        .sidebar-nav a.active {
            background: linear-gradient(115deg, #1188c7, #276ec0);
            color: white;
            box-shadow: 0 6px 16px rgba(14, 116, 178, .24);
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

        body.sidebar-collapsed .admin-header {
            left: 76px;
            transition: left .2s ease;
        }

        body.sidebar-collapsed .admin-main {
            margin-left: 76px;
            transition: margin-left .2s ease;
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

            .accent-picker-trigger {
                width: 38px;
                min-width: 38px;
                min-height: 38px;
                padding: 6px;
            }

            .accent-picker-label { display: none; }

            .theme-toggle-label,
            .accent-picker-label {
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

            body.sidebar-collapsed .sidebar {
                width: 100%;
                transform: translateY(calc(100% + env(safe-area-inset-bottom)));
            }

            body.sidebar-collapsed .admin-header {
                left: 0;
            }

            body.sidebar-collapsed .admin-main {
                margin-left: 0;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .sidebar,
            .admin-header,
            .admin-main,
            .sidebar-toggle {
                transition: none;
            }
        }
    </style>
</head>
<body class="admin-glass-interface {{ request()->routeIs('admin.dashboard') ? 'admin-dashboard-page' : '' }}">
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

        $profilePicture = $adminUser && !empty($adminUser->profile_picture)
            ? url('/storage/' . ltrim($adminUser->profile_picture, '/'))
            : null;
    @endphp

    <aside class="sidebar" id="adminSidebar">
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

                @if ($isAdministrator || $adminUser->hasStaffPermission('sales'))
                <a href="{{ route('admin.sales.index') }}"
               class="{{ request()->routeIs('admin.sales.*') ? 'active' : '' }}">
                <span class="sidebar-icon">🧾</span>
                <span class="menu-label">Sales (Benta)</span>
            </a>
                @endif

                @if ($isAdministrator || $adminUser->hasStaffPermission('manual_transactions'))
            <a href="{{ route('admin.manual-transactions.index') }}"
               class="{{ request()->routeIs('admin.manual-transactions.*') ? 'active' : '' }}">
                <span class="menu-icon">▤</span>
                <span class="menu-label">Manual Transactions</span>
            </a>
                @endif

                @if ($isAdministrator || $adminUser->hasStaffPermission('debts'))
                <a href="{{ route('admin.debts.index') }}"
                    class="{{ request()->routeIs('admin.debts.*') ? 'active' : '' }}">
                <span class="menu-icon">₱</span>
                <span class="menu-label">Utang</span>
            </a>
                @endif

                @if ($isAdministrator || $adminUser->hasStaffPermission('products'))
            <a href="{{ route('admin.products.index') }}"
               class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                <span class="menu-icon">▦</span>
                <span class="menu-label">Products</span>
            </a>

            <a href="{{ route('admin.bundles.index') }}"
               class="{{ request()->routeIs('admin.bundles.*') ? 'active' : '' }}">
                <span class="menu-icon">▣</span>
                <span class="menu-label">Bundle Packs</span>
            </a>
            @endif

            @if ($isAdministrator || $adminUser->hasStaffPermission('inventory'))
            <a href="{{ route('admin.inventory.index') }}"
               class="{{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}">
                <span class="menu-icon">▤</span>
                <span class="menu-label">Inventory</span>
            </a>
            @endif

            @if ($isAdministrator || $adminUser->hasStaffPermission('purchase_orders'))
            <a href="{{ route('admin.purchase-orders.index') }}"
               class="{{ request()->routeIs('admin.purchase-orders.*') ? 'active' : '' }}">
                <span class="menu-icon">▧</span>
                <span class="menu-label">Purchase Orders</span>
            </a>
            @endif

            @if ($isAdministrator || $adminUser->hasStaffPermission('suppliers'))
            <a href="{{ route('admin.suppliers.index') }}"
               class="{{ request()->routeIs('admin.suppliers.*') ? 'active' : '' }}">
                <span class="menu-icon">♧</span>
                <span class="menu-label">Suppliers</span>
            </a>
            @endif

            @if ($isAdministrator || $adminUser->hasStaffPermission('customer_orders'))
<a href="{{ route('admin.orders.index') }}"
   class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
    <span class="menu-icon">☷</span>
    <span class="menu-label">Customer Orders</span>
</a>
            @endif

            @if ($isAdministrator || $adminUser->hasStaffPermission('profit_pricing'))
            <a href="{{ route('admin.profit-pricing.index') }}"
               class="{{ request()->routeIs('admin.profit-pricing.*') ? 'active' : '' }}">
                <span class="menu-icon">₱</span>
                <span class="menu-label">Profit &amp; Pricing</span>
            </a>
            @endif

            @if ($isAdministrator || $adminUser->hasStaffPermission('expenses'))
            <a href="{{ route('admin.expenses.index') }}"
               class="{{ request()->routeIs('admin.expenses.*') ? 'active' : '' }}">
                <span class="menu-icon">▧</span>
                <span class="menu-label">Expenses</span>
            </a>
            @endif

            @if ($isAdministrator || $adminUser->hasStaffPermission('reports'))
            <a href="{{ route('admin.reports.index') }}"
               class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <span class="menu-icon">▥</span>
                <span class="menu-label">Reports</span>
            </a>
            @endif

            @if ($isAdministrator)
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
        <button
            class="sidebar-toggle"
            id="sidebarToggle"
            type="button"
            aria-label="Toggle navigation"
            aria-expanded="true"
            aria-controls="adminSidebar"
        ><span aria-hidden="true">☰</span></button>
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
            <div class="accent-picker">
                <button
                    class="accent-picker-trigger"
                    id="accentPickerToggle"
                    type="button"
                    aria-label="Open dashboard theme settings"
                    aria-expanded="false"
                    aria-controls="accentPickerPanel"
                >
                    <span class="accent-preview" aria-hidden="true"></span>
                    <span class="accent-picker-label">Theme</span>
                    <span class="theme-toggle-icon" id="themeModeIcon" aria-hidden="true">☾</span>
                    <span class="theme-toggle-label" id="themeModeLabel">Dark mode</span>
                </button>
                <div class="accent-picker-popover" id="accentPickerPanel" role="group" aria-label="Dashboard theme settings" hidden>
                    <p class="accent-picker-title">Appearance</p>
                    <div class="theme-mode-options" role="group" aria-label="Color mode">
                        <button class="theme-mode-option" type="button" data-theme-choice="light" aria-pressed="false">☀ Light</button>
                        <button class="theme-mode-option" type="button" data-theme-choice="dark" aria-pressed="true">☾ Dark mode</button>
                    </div>
                    <p class="accent-picker-title">Color</p>
                    <div class="accent-swatches">
                        <button class="accent-swatch" type="button" data-accent="green" aria-label="Green accent" aria-pressed="false" title="Green"><span class="accent-swatch-color" style="--swatch-color:#245333"></span></button>
                        <button class="accent-swatch" type="button" data-accent="blue" aria-label="Blue accent" aria-pressed="false" title="Blue"><span class="accent-swatch-color" style="--swatch-color:#1d4ed8"></span></button>
                        <button class="accent-swatch" type="button" data-accent="teal" aria-label="Teal accent" aria-pressed="false" title="Teal"><span class="accent-swatch-color" style="--swatch-color:#0f766e"></span></button>
                        <button class="accent-swatch" type="button" data-accent="amber" aria-label="Amber accent" aria-pressed="false" title="Amber"><span class="accent-swatch-color" style="--swatch-color:#b45309"></span></button>
                        <button class="accent-swatch" type="button" data-accent="red" aria-label="Red accent" aria-pressed="false" title="Red"><span class="accent-swatch-color" style="--swatch-color:#b42318"></span></button>
                        <button class="accent-swatch" type="button" data-accent="slate" aria-label="Slate accent" aria-pressed="false" title="Slate"><span class="accent-swatch-color" style="--swatch-color:#475569"></span></button>
                    </div>
                </div>
            </div>

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
                <span class="profile-avatar" data-initials="{{ $adminInitials }}">
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
                    <a class="profile-dropdown-avatar" data-initials="{{ $adminInitials }}" href="{{ route('admin.profile.edit') }}" aria-label="View profile picture and manage profile">
                        @if ($profilePicture)
                            <img src="{{ $profilePicture }}" alt="{{ $adminName }} profile picture">
                        @else
                            {{ $adminInitials }}
                        @endif
                    </a>
                    <div class="profile-dropdown-identity">
                    <strong>{{ $adminRoleLabel }} Account</strong>
                    <span>{{ $adminName }}</span>
                    <span>{{ $adminEmail }}</span>
                    </div>
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

        .admin-main .button:not(.button-secondary):not([class*="danger"]):not([class*="remove"]),
        .admin-main .btn:not(.btn-secondary):not([class*="outline"]):not([class*="danger"]):not([class*="remove"]),
        .admin-main button:not([class*="danger"]):not([class*="remove"]):not(.button-secondary):not(.btn-secondary):not([class*="outline"]):not(.btn-close):not(.quantity-button):not(.product-picker-close):not(.assistant-close),
        .admin-main .dashboard-action-button,
        .admin-main [role="tab"][aria-selected="true"],
        .admin-main .tab-button.active,
        .admin-main .pagination .page-item.active .page-link {
            border-color: var(--green) !important;
            background-color: var(--green) !important;
            color: #fff !important;
        }

        .admin-main .button:not(.button-secondary):not([class*="danger"]):not([class*="remove"]):hover,
        .admin-main .btn:not(.btn-secondary):not([class*="outline"]):not([class*="danger"]):not([class*="remove"]):hover,
        .admin-main button:not([class*="danger"]):not([class*="remove"]):not(.button-secondary):not(.btn-secondary):not([class*="outline"]):not(.btn-close):not(.quantity-button):not(.product-picker-close):not(.assistant-close):hover,
        .admin-main .dashboard-action-button:hover,
        .admin-main .tab-button.active:hover {
            border-color: var(--green-dark) !important;
            background-color: var(--green-dark) !important;
        }

        .admin-main {
            background:
                radial-gradient(ellipse at 86% 0%, rgba(56, 189, 248, .12), transparent 38%),
                linear-gradient(145deg, rgba(255, 255, 255, .34), rgba(226, 243, 252, .2));
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .admin-main .page-heading {
            padding: 18px 20px;
            border: 1px solid rgba(125, 174, 202, .22);
            border-radius: 16px;
            background: linear-gradient(120deg, rgba(255, 255, 255, .6), rgba(226, 243, 252, .38));
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .68), 0 10px 24px rgba(22, 58, 82, .06);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
        }

        .admin-main .page-heading h1 {
            color: var(--text);
            font-size: clamp(24px, 3vw, 32px);
            letter-spacing: -.035em;
        }

        .admin-main .panel,
        .admin-main .summary-card,
        .admin-main .card,
        .admin-main .report-panel,
        .admin-main .pos-product-panel {
            border: 1px solid rgba(125, 174, 202, .24);
            border-radius: 16px;
            background: rgba(255, 255, 255, .76);
            box-shadow: 0 10px 28px rgba(22, 58, 82, .07), inset 0 1px 0 rgba(255, 255, 255, .75);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }

        .admin-main .panel:hover,
        .admin-main .summary-card:hover,
        .admin-main .card:hover,
        .admin-main .report-panel:hover {
            border-color: rgba(56, 189, 248, .38);
            box-shadow: 0 14px 32px rgba(22, 58, 82, .11), inset 0 1px 0 rgba(255, 255, 255, .8);
        }

        .admin-main .panel h2,
        .admin-main .panel h3,
        .admin-main .card h2,
        .admin-main .card h3 {
            color: var(--text);
        }

        .admin-main .table-wrap {
            border: 1px solid rgba(125, 174, 202, .18);
            border-radius: 12px;
            background: rgba(255, 255, 255, .35);
        }

        .admin-main .table-wrap th,
        .admin-main th {
            background: rgba(224, 240, 249, .78);
            color: #35566d;
        }

        .admin-main .table-wrap td {
            border-color: rgba(125, 174, 202, .16);
        }

        .admin-main .table-wrap tbody tr {
            transition: background-color .16s ease;
        }

        .admin-main .table-wrap tbody tr:hover {
            background: rgba(56, 189, 248, .07);
        }

        .admin-main input:not([type="checkbox"]):not([type="radio"]),
        .admin-main select,
        .admin-main textarea,
        .admin-main .form-control,
        .admin-main .form-select {
            border-color: rgba(93, 143, 171, .32);
            border-radius: 8px;
            background-color: rgba(255, 255, 255, .82);
            color: #183247;
            transition: border-color .16s ease, box-shadow .16s ease;
        }

        .admin-main input:focus,
        .admin-main select:focus,
        .admin-main textarea:focus,
        .admin-main .form-control:focus,
        .admin-main .form-select:focus {
            border-color: #38bdf8;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, .16);
            outline: 0;
        }

        .admin-main :is(button, .button, .btn, a.button, a.btn, .dashboard-action-button) {
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease, background-color .18s ease, color .18s ease;
        }

        .admin-main :is(button, .button, .btn, a.button, a.btn, .dashboard-action-button):focus-visible {
            outline: 3px solid rgba(56, 189, 248, .48);
            outline-offset: 2px;
        }

        .admin-main .page-heading + .panel {
            margin-top: 16px;
        }

        :root[data-theme="dark"] .admin-main {
            background:
                radial-gradient(ellipse at 86% 0%, rgba(14, 165, 233, .14), transparent 38%),
                linear-gradient(145deg, rgba(17, 38, 57, .34), rgba(7, 18, 30, .22));
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        :root[data-theme="dark"] .admin-main .page-heading {
            border-color: rgba(125, 211, 252, .17);
            background: linear-gradient(120deg, rgba(20, 43, 62, .75), rgba(10, 25, 40, .52));
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .055), 0 12px 30px rgba(1, 7, 14, .16);
        }

        :root[data-theme="dark"] .admin-main .panel,
        :root[data-theme="dark"] .admin-main .summary-card,
        :root[data-theme="dark"] .admin-main .card,
        :root[data-theme="dark"] .admin-main .report-panel,
        :root[data-theme="dark"] .admin-main .pos-product-panel {
            border-color: rgba(125, 211, 252, .16) !important;
            background: linear-gradient(145deg, rgba(17, 38, 57, .88), rgba(11, 26, 42, .84)) !important;
            box-shadow: 0 12px 30px rgba(1, 7, 14, .2), inset 0 1px 0 rgba(255, 255, 255, .045);
            color: #eaf4fb;
        }

        :root[data-theme="dark"] .admin-main .table-wrap,
        :root[data-theme="dark"] .admin-main .table,
        :root[data-theme="dark"] .admin-main table {
            border-color: rgba(125, 211, 252, .14);
            background: rgba(8, 22, 36, .34);
            --bs-table-bg: transparent;
            --bs-table-color: #d6e5ef;
            --bs-table-border-color: rgba(125, 211, 252, .14);
            color: #d6e5ef;
        }

        :root[data-theme="dark"] .admin-main .table-wrap th,
        :root[data-theme="dark"] .admin-main th {
            background: rgba(39, 72, 96, .55) !important;
            color: #cde7f5 !important;
        }

        :root[data-theme="dark"] .admin-main .table-wrap td,
        :root[data-theme="dark"] .admin-main td {
            border-color: rgba(125, 211, 252, .12);
            color: #d6e5ef !important;
        }

        :root[data-theme="dark"] .admin-main .table-wrap tbody tr:hover,
        :root[data-theme="dark"] .admin-main tbody tr:hover > td {
            background: rgba(56, 189, 248, .08) !important;
        }

        :root[data-theme="dark"] .admin-main input:not([type="checkbox"]):not([type="radio"]),
        :root[data-theme="dark"] .admin-main select,
        :root[data-theme="dark"] .admin-main textarea,
        :root[data-theme="dark"] .admin-main .form-control,
        :root[data-theme="dark"] .admin-main .form-select {
            border-color: rgba(125, 211, 252, .2);
            background-color: rgba(8, 22, 36, .88);
            color: #eaf4fb;
        }

        :root[data-theme="dark"] .admin-main input::placeholder,
        :root[data-theme="dark"] .admin-main textarea::placeholder {
            color: #9ab2c3;
        }

        :root[data-theme="dark"] .admin-main .table-wrap a,
        :root[data-theme="dark"] .admin-main a:not(.button):not(.btn):not(.dashboard-action-button):not(.page-link) {
            color: #7dd3fc;
        }

        :root[data-theme="light"] .admin-header {
            background: linear-gradient(110deg, #0b2740, #12466d);
        }

        :root[data-theme="light"] .sidebar {
            background: rgba(247, 252, 255, .9);
        }

        :root[data-theme="light"] .sidebar-nav a.active {
            color: #fff;
        }

        @media (prefers-reduced-motion: reduce) {
            .admin-main .panel,
            .admin-main .summary-card,
            .admin-main .card,
            .admin-main .report-panel,
            .sidebar-nav a {
                transition: none;
            }
        }

        .admin-main :is(
            .panel,
            .card,
            .summary-card,
            .summary-grid > div,
            .report-panel,
            .pos-product-panel,
            .pos-checkout-panel,
            .order-item,
            .transaction-item,
            [class*="-panel"],
            [class*="-card"],
            [class*="-tile"],
            [class*="-widget"]
        ) {
            border: 1px solid rgba(125, 174, 202, .24) !important;
            border-radius: 15px !important;
            background: rgba(255, 255, 255, .66) !important;
            box-shadow: 0 10px 28px rgba(22, 58, 82, .07), inset 0 1px 0 rgba(255, 255, 255, .72) !important;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease, background-color .18s ease;
        }

        .admin-main :is(
            .panel,
            .card,
            .summary-card,
            .summary-grid > div,
            .report-panel,
            .pos-product-panel,
            .pos-checkout-panel,
            .order-item,
            .transaction-item,
            [class*="-panel"],
            [class*="-card"],
            [class*="-tile"],
            [class*="-widget"]
        ):hover {
            border-color: rgba(56, 189, 248, .38) !important;
            box-shadow: 0 15px 34px rgba(22, 58, 82, .12), inset 0 1px 0 rgba(255, 255, 255, .78) !important;
            transform: translateY(-2px);
        }

        .admin-main :is(.panel-header, .card-header, .card-footer, .panel-heading) {
            border-color: rgba(125, 174, 202, .17) !important;
            background: rgba(221, 240, 251, .38) !important;
        }

        .admin-main .table-wrap {
            border: 1px solid rgba(125, 174, 202, .2);
            border-radius: 13px;
            background: rgba(255, 255, 255, .3);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .42);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .admin-main :is(table, .table) {
            --bs-table-bg: transparent;
            background: transparent !important;
        }

        .admin-main :is(th, .table th, .table thead th) {
            border-color: rgba(125, 174, 202, .2) !important;
            background: rgba(224, 240, 249, .66) !important;
            color: #35566d;
        }

        .admin-main :is(td, .table td) {
            border-color: rgba(125, 174, 202, .16) !important;
        }

        .admin-main tbody tr {
            transition: background-color .16s ease;
        }

        .admin-main tbody tr:hover > td {
            background: rgba(56, 189, 248, .07) !important;
        }

        .admin-main :is(.notice, .alert, .staff-module-picker, fieldset) {
            border: 1px solid rgba(125, 174, 202, .22);
            border-radius: 12px;
            background: rgba(255, 255, 255, .52);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .5);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .admin-main :is(.pagination .page-link, .dropdown-menu, .modal-content, dialog) {
            border-color: rgba(125, 174, 202, .25);
            border-radius: 12px;
            background: rgba(247, 252, 255, .9);
            box-shadow: 0 16px 38px rgba(22, 58, 82, .14);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }

        :root[data-theme="dark"] .admin-main :is(
            .panel,
            .card,
            .summary-card,
            .summary-grid > div,
            .report-panel,
            .pos-product-panel,
            .pos-checkout-panel,
            .order-item,
            .transaction-item,
            [class*="-panel"],
            [class*="-card"],
            [class*="-tile"],
            [class*="-widget"]
        ) {
            border-color: rgba(125, 211, 252, .17) !important;
            background: linear-gradient(145deg, rgba(20, 43, 62, .78), rgba(10, 25, 40, .78)) !important;
            box-shadow: 0 12px 30px rgba(1, 7, 14, .2), inset 0 1px 0 rgba(255, 255, 255, .055) !important;
            color: #eaf4fb;
        }

        :root[data-theme="dark"] .admin-main :is(.panel-header, .card-header, .card-footer, .panel-heading) {
            border-color: rgba(125, 211, 252, .15) !important;
            background: rgba(39, 72, 96, .34) !important;
            color: #eaf4fb !important;
        }

        :root[data-theme="dark"] .admin-main .table-wrap {
            border-color: rgba(125, 211, 252, .15);
            background: rgba(8, 22, 36, .34);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .04);
        }

        :root[data-theme="dark"] .admin-main :is(th, .table th, .table thead th) {
            border-color: rgba(125, 211, 252, .15) !important;
            background: rgba(39, 72, 96, .48) !important;
            color: #cde7f5 !important;
        }

        :root[data-theme="dark"] .admin-main :is(td, .table td) {
            border-color: rgba(125, 211, 252, .12) !important;
            color: #d6e5ef;
        }

        :root[data-theme="dark"] .admin-main :is(.notice, .alert, .staff-module-picker, fieldset) {
            border-color: rgba(125, 211, 252, .17);
            background: rgba(15, 34, 51, .72);
            color: #eaf4fb;
        }

        :root[data-theme="dark"] .admin-main :is(.pagination .page-link, .dropdown-menu, .modal-content, dialog) {
            border-color: rgba(125, 211, 252, .2);
            background: rgba(12, 29, 45, .94);
            color: #eaf4fb;
        }

        :root[data-theme="dark"] .profile-dropdown,
        :root[data-theme="dark"] .accent-picker-popover,
        :root[data-theme="dark"] .notification-panel {
            border-color: rgba(125, 211, 252, .2);
            background: rgba(9, 25, 40, .94);
            color: #eaf4fb;
            box-shadow: 0 18px 44px rgba(0, 0, 0, .35);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        :root[data-theme="dark"] .notification-panel-header,
        :root[data-theme="dark"] .notification-item {
            border-color: rgba(125, 211, 252, .13);
            background: rgba(16, 37, 55, .72);
            color: #eaf4fb;
        }

        :root[data-theme="dark"] .notification-item:hover {
            background: rgba(56, 189, 248, .14);
        }

        :root[data-theme="light"] .profile-dropdown,
        :root[data-theme="light"] .accent-picker-popover,
        :root[data-theme="light"] .notification-panel {
            border-color: rgba(125, 174, 202, .28);
            background: rgba(249, 253, 255, .9);
            box-shadow: 0 18px 44px rgba(22, 58, 82, .16);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        body.admin-glass-interface {
            isolation: isolate;
        }

        body.admin-glass-interface::before,
        body.admin-glass-interface::after {
            position: fixed;
            z-index: -1;
            width: min(68vw, 820px);
            height: min(68vw, 820px);
            border-radius: 50%;
            background: radial-gradient(ellipse, rgba(56, 189, 248, .11), rgba(37, 99, 235, .045) 40%, transparent 70%);
            content: "";
            pointer-events: none;
            animation: admin-ambient-drift 32s ease-in-out infinite alternate;
        }

        body.admin-glass-interface::before {
            top: -34vw;
            left: -24vw;
        }

        body.admin-glass-interface::after {
            right: -30vw;
            bottom: -42vw;
            background: radial-gradient(ellipse, rgba(125, 211, 252, .1), rgba(14, 165, 233, .04) 40%, transparent 70%);
            animation-delay: -14s;
            animation-duration: 38s;
        }

        @keyframes admin-ambient-drift {
            from { transform: translate3d(-1.5%, -1%, 0) scale(.96); }
            to { transform: translate3d(2%, 1.5%, 0) scale(1.04); }
        }

        @keyframes admin-sidebar-enter {
            from { opacity: 0; translate: -10px 0; }
            to { opacity: 1; translate: 0 0; }
        }

        @keyframes admin-header-enter {
            from { opacity: 0; translate: 0 -7px; }
            to { opacity: 1; translate: 0 0; }
        }

        @keyframes admin-page-enter {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        body.admin-glass-interface .sidebar {
            animation: admin-sidebar-enter .4s ease-out both;
        }

        body.admin-glass-interface .admin-header {
            animation: admin-header-enter .36s ease-out both;
        }

        body.admin-glass-interface:not(.admin-dashboard-page) .admin-main > :not(style):not(script) {
            animation: admin-page-enter .4s ease-out backwards;
        }

        :root[data-theme="dark"] .assistant-window {
            border-color: rgba(125, 211, 252, .2);
            background: rgba(9, 25, 40, .96);
            color: #eaf4fb;
            box-shadow: 0 18px 48px rgba(0, 0, 0, .4);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        :root[data-theme="dark"] .assistant-header {
            background: linear-gradient(110deg, rgba(11, 39, 64, .96), rgba(18, 70, 109, .94));
        }

        :root[data-theme="dark"] .assistant-messages {
            background: rgba(8, 22, 36, .72);
        }

        :root[data-theme="dark"] .assistant-message,
        :root[data-theme="dark"] .assistant-product {
            border-color: rgba(125, 211, 252, .16);
            background: rgba(17, 38, 57, .82);
            color: #eaf4fb;
        }

        :root[data-theme="dark"] .assistant-quick-reply {
            border-color: rgba(125, 211, 252, .24);
            background: rgba(17, 38, 57, .82);
            color: #bae6fd;
        }

        @media (prefers-reduced-motion: reduce) {
            body.admin-glass-interface::before,
            body.admin-glass-interface::after,
            body.admin-glass-interface .sidebar,
            body.admin-glass-interface .admin-header,
            body.admin-glass-interface:not(.admin-dashboard-page) .admin-main > :not(style):not(script) {
                animation: none !important;
            }

            .admin-main :is(
                .panel,
                .card,
                .summary-card,
                .summary-grid > div,
                .report-panel,
                .pos-product-panel,
                .pos-checkout-panel,
                .order-item,
                .transaction-item,
                [class*="-panel"],
                [class*="-card"],
                [class*="-tile"],
                [class*="-widget"]
            ),
            .admin-main tbody tr {
                transition: none;
            }
        }

        @media (hover: hover) and (prefers-reduced-motion: no-preference) {
            .admin-main :is(
                .panel,
                .card,
                .summary-card,
                .summary-grid > div,
                .report-panel,
                .pos-product-panel,
                .pos-checkout-panel,
                .order-item,
                .transaction-item,
                [class*="-panel"],
                [class*="-card"],
                [class*="-tile"],
                [class*="-widget"]
            ):hover {
                transform: translateY(-2px);
            }

            .admin-main :is(button, .button, .btn, a.button, a.btn, .dashboard-action-button):hover {
                box-shadow: 0 8px 18px rgba(2, 132, 199, .2);
                transform: translateY(-1px);
            }
        }

        @media (max-width: 700px) {
            body.admin-glass-interface::before,
            body.admin-glass-interface::after {
                animation: none;
                opacity: .7;
            }

            .admin-main .page-heading {
                padding: 15px;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarStorageKey = 'admin-sidebar-collapsed-{{ auth()->id() }}';
            const setSidebarCollapsed = function (collapsed) {
                document.body.classList.toggle('sidebar-collapsed', collapsed);
                sidebarToggle.setAttribute('aria-expanded', String(!collapsed));
                sidebarToggle.setAttribute(
                    'aria-label',
                    collapsed ? 'Expand navigation' : 'Collapse navigation'
                );
            };

            setSidebarCollapsed(localStorage.getItem(sidebarStorageKey) === 'true');
            sidebarToggle.addEventListener('click', function () {
                const collapsed = !document.body.classList.contains('sidebar-collapsed');
                setSidebarCollapsed(collapsed);
                localStorage.setItem(sidebarStorageKey, String(collapsed));
            });

            const accentToggle = document.getElementById('accentPickerToggle');
            const accentPanel = document.getElementById('accentPickerPanel');
            const accentSwatches = document.querySelectorAll('.accent-swatch');
            const themeModeIcon = document.getElementById('themeModeIcon');
            const themeModeLabel = document.getElementById('themeModeLabel');
            const themeModeButtons = document.querySelectorAll('.theme-mode-option');

            function updateAccentPicker() {
                const selectedAccent = document.documentElement.dataset.accent || 'blue';

                accentSwatches.forEach((swatch) => {
                    swatch.setAttribute(
                        'aria-pressed',
                        String(swatch.dataset.accent === selectedAccent)
                    );
                });
            }

            updateAccentPicker();
            accentToggle?.addEventListener('click', function (event) {
                event.stopPropagation();
                const willOpen = accentPanel.hidden;
                accentPanel.hidden = !willOpen;
                accentToggle.setAttribute('aria-expanded', String(willOpen));
            });

            accentSwatches.forEach((swatch) => {
                swatch.addEventListener('click', function () {
                    const selectedAccent = swatch.dataset.accent;
                    applyAdminAccent(selectedAccent);
                    localStorage.setItem(adminAccentKey, selectedAccent);
                    updateAccentPicker();
                    accentPanel.hidden = true;
                    accentToggle.setAttribute('aria-expanded', 'false');
                });
            });

            function updateThemeMode() {
                const currentTheme = document.documentElement.dataset.theme === 'dark'
                    ? 'dark'
                    : 'light';

                if (themeModeIcon) themeModeIcon.textContent = currentTheme === 'dark' ? '☾' : '☀';
                if (themeModeLabel) themeModeLabel.textContent = currentTheme === 'dark' ? 'Dark mode' : 'Light mode';
                themeModeButtons.forEach((button) => {
                    button.setAttribute('aria-pressed', String(button.dataset.themeChoice === currentTheme));
                });
            }

            updateThemeMode();
            themeModeButtons.forEach((button) => {
                button.addEventListener('click', function () {
                    const nextTheme = button.dataset.themeChoice;
                    document.documentElement.dataset.theme = nextTheme;
                    localStorage.setItem(adminThemeKey, nextTheme);
                    applyAdminAccent(document.documentElement.dataset.accent);
                    updateThemeMode();
                    document.dispatchEvent(new CustomEvent('admin-theme-change', {
                        detail: { theme: nextTheme },
                    }));
                });
            });

            document.addEventListener('click', function (event) {
                if (!accentPanel?.contains(event.target) && !accentToggle?.contains(event.target)) {
                    if (accentPanel) accentPanel.hidden = true;
                    accentToggle?.setAttribute('aria-expanded', 'false');
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && accentPanel && !accentPanel.hidden) {
                    accentPanel.hidden = true;
                    accentToggle?.setAttribute('aria-expanded', 'false');
                    accentToggle?.focus();
                }
            });

            const trigger = document.getElementById('profileTrigger');
            const dropdown = document.getElementById('profileDropdown');

            document.querySelectorAll('.profile-avatar img, .profile-dropdown-avatar img').forEach((image) => {
                image.addEventListener('error', () => {
                    const avatar = image.parentElement;
                    image.remove();
                    avatar.textContent = avatar.dataset.initials || 'A';
                }, { once: true });
            });

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