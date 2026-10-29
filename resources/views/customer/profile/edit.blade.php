<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>My Profile | H&K Consumer Goods Store</title>
    @vite('resources/js/app.js')

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f3f7f2;
            color: #28372d;
        }

        /* Header */
        header {
            background: #245b38;
            color: white;
            padding: 18px 24px;
        }

        .header-inner {
            max-width: 1000px;
            margin: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
        }

        .brand {
            color: white;
            text-decoration: none;
            font-size: 18px;
            font-weight: bold;
        }

        /* Profile dropdown */
        .profile-menu {
            position: relative;
        }

        .profile-menu summary {
            display: flex;
            align-items: center;
            gap: 9px;
            cursor: pointer;
            list-style: none;
            padding: 7px 12px;
            border: 1px solid rgba(255, 255, 255, 0.35);
            border-radius: 28px;
            font-weight: 600;
            color: white;
        }

        .profile-menu summary::-webkit-details-marker {
            display: none;
        }

        .profile-menu summary:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .profile-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            flex-shrink: 0;
            border-radius: 50%;
            background: #edf6ed;
            color: #245b38;
            font-size: 19px;
            overflow: hidden;
        }

        .profile-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .chevron {
            font-size: 16px;
        }

        .profile-dropdown {
            position: absolute;
            right: 0;
            top: calc(100% + 10px);
            width: 220px;
            background: white;
            border: 1px solid #e3e9e2;
            border-radius: 10px;
            padding: 8px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.18);
            z-index: 100;
        }

        .profile-dropdown a,
        .profile-dropdown button {
            display: block;
            width: 100%;
            padding: 11px 12px;
            color: #28372d;
            background: transparent;
            border: 0;
            border-radius: 7px;
            text-align: left;
            text-decoration: none;
            font: inherit;
            font-size: 14px;
            cursor: pointer;
        }

        .profile-dropdown a:hover,
        .profile-dropdown button:hover {
            background: #edf6ed;
        }

        .profile-dropdown form {
            border-top: 1px solid #e3e9e2;
            margin-top: 5px;
            padding-top: 5px;
        }

        /* Main content */
        .container {
            max-width: 760px;
            margin: 35px auto;
            padding: 0 16px;
        }

        .card {
            background: white;
            border-radius: 16px;
            padding: 28px;
            box-shadow: 0 5px 22px rgba(0, 0, 0, 0.07);
        }

        h1 {
            margin-top: 0;
            color: #245b38;
        }

        .description {
            color: #66746a;
            line-height: 1.6;
        }

        /* Profile picture */
        .picture-section {
            margin-bottom: 25px;
        }

        .avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #e2eee3;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
            background: #edf6ed;
            color: #245b38;
            font-size: 42px;
            font-weight: bold;
        }

        /* Form */
        label {
            display: block;
            margin: 17px 0 7px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccd8cc;
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
        }

        input:focus {
            outline: 2px solid #9ac7a5;
            border-color: #245b38;
        }

        input[type="file"] {
            background: #f8faf7;
        }

        .hint {
            color: #728075;
            font-size: 13px;
            margin-top: 7px;
        }

        /* Messages */
        .success {
            padding: 12px;
            background: #e4f6e7;
            color: #205c32;
            border-radius: 8px;
            margin-bottom: 18px;
        }

        .errors {
            padding: 12px 12px 12px 32px;
            background: #fff0ee;
            color: #a1261d;
            border-radius: 8px;
            margin-bottom: 18px;
        }

        /* Action buttons */
        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 25px;
        }

        .btn {
            display: inline-block;
            padding: 12px 18px;
            border: 0;
            border-radius: 8px;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
            font-family: inherit;
            text-align: center;
        }

        .btn-primary {
            background: #245b38;
            color: white;
        }

        .btn-primary:hover {
            background: #183f27;
        }

        .btn-secondary {
            background: #edf2ec;
            color: #28372d;
        }

        .btn-secondary:hover {
            background: #dfe8dd;
        }

        footer {
            text-align: center;
            padding: 22px;
            color: #728075;
            font-size: 13px;
        }

        @media (max-width: 520px) {
            header {
                padding: 14px 16px;
            }

            .header-inner {
                gap: 12px;
            }

            .brand {
                font-size: 16px;
            }

            .profile-menu summary {
                gap: 6px;
                padding: 5px 8px;
                font-size: 14px;
            }

            .profile-icon {
                width: 32px;
                height: 32px;
            }

            .container {
                margin-top: 20px;
            }

            .card {
                padding: 20px;
            }

            .profile-dropdown {
                width: 210px;
            }

            input:not([type="checkbox"]):not([type="radio"]),
            select,
            textarea {
                min-height: 44px;
                font-size: 16px;
            }

            button,
            .btn-primary,
            .btn-secondary {
                min-height: 44px;
            }

            .profile-dropdown {
                max-width: calc(100vw - 28px);
            }
        }
    </style>
</head>

<body>
<div class="notification-floating">
    @include('components.notification-center')
</div>

<header>
    <div class="header-inner">
        <a href="{{ route('home') }}" class="brand">
            H&amp;K Consumer Goods Store
        </a>

        <details class="profile-menu">
            <summary>
                <span class="profile-icon">
                    @if ($user->profile_picture)
                        <img
                            src="{{ asset('storage/' . $user->profile_picture) }}"
                            alt="Profile picture"
                        >
                    @else
                        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                    @endif
                </span>

                <span>{{ $user->name }}</span>
                <span class="chevron">⌄</span>
            </summary>

            <div class="profile-dropdown">
                <a href="{{ route('customer.profile.edit') }}">
                    My Profile / Edit Profile
                </a>

                <a href="{{ route('customer.orders.index') }}">
                    My Orders
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Sign Out</button>
                </form>
            </div>
        </details>
    </div>
</header>

<main class="container">
    <section class="card">
        <h1>My Profile</h1>

        <p class="description">
            Manage your account information and profile picture.
        </p>

        @if (session('success'))
            <div class="success" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="errors" role="alert">
                <strong>Please check the following:</strong>

                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            action="{{ route('customer.profile.update') }}"
            method="POST"
            enctype="multipart/form-data"
        >
            @csrf
            @method('PATCH')

            <div class="picture-section">
                <label for="profile_picture">Profile Picture</label>

                @if ($user->profile_picture)
                    <img
                        class="avatar"
                        src="{{ asset('storage/' . $user->profile_picture) }}"
                        alt="Your profile picture"
                    >
                @else
                    <div class="avatar" aria-label="Default profile avatar">
                        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                    </div>
                @endif

                <input
                    id="profile_picture"
                    type="file"
                    name="profile_picture"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                >

                <p class="hint">
                    JPG, JPEG, PNG, or WEBP. Maximum size: 2 MB.
                </p>
            </div>

            <label for="name">Full Name</label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name', $user->name) }}"
                required
                maxlength="255"
                autocomplete="name"
            >

            <label for="email">Email Address</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email', $user->email) }}"
                required
                maxlength="255"
                autocomplete="email"
            >

            <label for="phone">Contact Number</label>
            <input
                id="phone"
                type="text"
                name="phone"
                value="{{ old('phone', $user->phone) }}"
                maxlength="30"
                autocomplete="tel"
            >

            <div class="actions">
                <button type="submit" class="btn btn-primary">
                    Save Changes
                </button>

                <a href="{{ route('home') }}" class="btn btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </section>
</main>

<footer>
    H&amp;K Consumer Goods Store — Quality goods for every home
</footer>

@include('components.smart-assistant')
</body>
</html>