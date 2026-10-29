
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Rider Dashboard') | H&amp;K Consumer Goods Store</title>
    <link rel="icon" type="image/png" href="{{ asset('images/HK_Consumer_Goods_Store_logo.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite('resources/js/app.js')

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

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: var(--page-bg);
            color: var(--text);
            font-size: 14px;
        }

        button, input, select, textarea { font: inherit; }

        .rider-header {
            position: fixed;
            top: 0;
            left: 0;
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
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 13px;
            min-width: 0;
        }

        .header-logo {
            width: 54px;
            height: 54px;
            flex-shrink: 0;
            object-fit: contain;
            border-radius: 6px;
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

        .profile-container {
            position: relative;
            flex-shrink: 0;
        }

        .profile-trigger {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 5px 9px;
            border: 1px solid rgba(255,255,255,.35);
            border-radius: 30px;
            background: rgba(255,255,255,.10);
            color: white;
            cursor: pointer;
        }

        .profile-trigger:hover,
        .profile-trigger:focus-visible {
            background: rgba(255,255,255,.20);
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

        .profile-arrow { font-size: 12px; }

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
            box-shadow: 0 8px 24px rgba(0,0,0,.15);
            color: var(--text);
        }

        .profile-dropdown[hidden] { display: none; }

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

        .profile-menu-link:hover { background: var(--green-light); }

        .profile-logout-button { color: var(--danger); }
        .profile-logout-button:hover { background: #fff0ee; }

        .profile-menu-icon {
            width: 20px;
            text-align: center;
            font-size: 16px;
        }

        .rider-main {
            max-width: 1200px;
            min-height: 100vh;
            margin: 0 auto;
            padding: 108px 26px 32px;
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

        .notice ul { margin-bottom: 0; }

        @media (max-width: 560px) {
            .rider-header {
                min-height: 68px;
                padding: 9px 10px;
                gap: 6px;
            }

            .header-brand { gap: 7px; }
            .header-logo { width: 38px; height: 38px; }
            .brand-title { font-size: 13px; }
            .brand-subtitle { margin-top: 3px; font-size: 10px; }

            .profile-trigger { gap: 0; padding: 3px; }
            .profile-avatar { width: 36px; height: 36px; font-size: 12px; }
            .profile-info, .profile-arrow { display: none; }
            .profile-dropdown { width: 220px; }

            .rider-main { padding: 90px 12px 22px; }

            body {
                overflow-x: hidden;
            }

            .rider-main {
                padding-bottom: calc(24px + env(safe-area-inset-bottom));
            }

            .rider-main input,
            .rider-main select,
            .rider-main textarea {
                min-height: 44px;
                font-size: 16px;
            }

            .rider-main button,
            .rider-main .btn,
            .rider-main a {
                touch-action: manipulation;
            }

            .rider-main img,
            .rider-main table {
                max-width: 100%;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
    @php
        $riderUser = auth()->user();
        $riderName = $riderUser?->name ?? 'Rider';
        $riderEmail = $riderUser?->email ?? 'No email available';

        $riderInitials = collect(preg_split('/\s+/', trim($riderName)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');

        $riderInitials = $riderInitials ?: 'R';

        // Matches the profile_picture field used by the Admin layout.
        $profilePicture = $riderUser && !empty($riderUser->profile_picture)
            ? asset('storage/' . ltrim($riderUser->profile_picture, '/'))
            : null;
    @endphp

    <header class="rider-header">
        <div class="header-brand">
            <img
                class="header-logo"
                src="{{ asset('images/HK_Consumer_Goods_Store_logo.png') }}"
                alt="H&amp;K Consumer Goods Store Logo"
            >

            <div>
                <h1 class="brand-title">H&amp;K Consumer Goods Store</h1>
                <p class="brand-subtitle">Business Management System</p>
            </div>
        </div>

        @include('components.notification-center')

        <div class="profile-container">
            <button
                class="profile-trigger"
                id="profileTrigger"
                type="button"
                aria-label="Open rider profile menu"
                aria-haspopup="true"
                aria-expanded="false"
                aria-controls="profileDropdown"
            >
                <span class="profile-avatar">
                    @if ($profilePicture)
                        <img src="{{ $profilePicture }}" alt="Rider profile picture">
                    @else
                        {{ $riderInitials }}
                    @endif
                </span>

                <span class="profile-info">
                    <span class="profile-name">{{ $riderName }}</span>
                    <span class="profile-role">Delivery Rider</span>
                </span>

                <span class="profile-arrow" aria-hidden="true">▼</span>
            </button>

            <div class="profile-dropdown" id="profileDropdown" hidden>
                <div class="profile-dropdown-header">
                    <strong>Rider Profile</strong>
                    <span>{{ $riderName }}</span>
                    <br>
                    <span>{{ $riderEmail }}</span>
                </div>

               <a class="profile-menu-link" href="{{ route('rider.profile.edit') }}">
    <span class="profile-menu-icon">👤</span>
    <span>Edit Profile</span>
</a>

<a class="profile-menu-link" href="{{ route('rider.dashboard') }}">
    <span class="profile-menu-icon">▦</span>
    <span>Rider Dashboard</span>
</a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="profile-logout-button" type="submit">
                        <span class="profile-menu-icon">↪</span>
                        <span>Log Out</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="rider-main">
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const trigger = document.getElementById('profileTrigger');
            const dropdown = document.getElementById('profileDropdown');

            if (!trigger || !dropdown) return;

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
                if (!dropdown.contains(event.target) &&
                    !trigger.contains(event.target)) {
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

    @stack('scripts')
</body>
</html>
