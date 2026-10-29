﻿
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>H&amp;K Consumer Goods Store</title>
    <link rel="icon" type="image/png" href="{{ asset('images/HK_Consumer_Goods_Store_logo.png') }}?v=2">
    @vite('resources/js/app.js')

    <style>
        :root {
            --green: #247447;
            --dark: #174d32;
            --light: #edf6ed;
            --text: #28372d;
            --muted: #728075;
            --border: #e1e9df;
            --gold: #fff4d4;
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }

        body {
            margin: 0;
            background: #fafcf9;
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
        }

        a { text-decoration: none; }
        button, input, select { font: inherit; }
        button, select { cursor: pointer; }

        .container {
            width: min(1200px, 92%);
            margin: 0 auto;
        }

        header {
            background: #245b38;
            border-bottom: 1px solid #174d32;
        }

        header .brand-name,
        header .brand-caption,
        header .nav-link {
            color: #ffffff;
        }

        header .nav-link:hover { color: #e5f3e8; }

        header .btn-outline {
            color: #ffffff;
            border-color: #ffffff;
            background: transparent;
        }

        header .btn-outline:hover {
            color: #245b38;
            background: #ffffff;
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 9px 0;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .brand-logo {
            width: 50px;
            height: 50px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .brand-name {
            display: block;
            font-size: 19px;
            font-weight: 800;
        }

        .brand-caption {
            display: block;
            font-size: 11px;
            margin-top: 3px;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 13px;
            flex-wrap: wrap;
        }

        .nav-actions form { margin: 0; }

        .nav-link {
            font-size: 13px;
            font-weight: bold;
        }

        .btn, .btn-outline {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 9px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            text-align: center;
            transition: background .15s ease;
        }

        .btn {
            border: 1px solid var(--green);
            background: var(--green);
            color: white;
        }

        .btn:hover { background: var(--dark); }

        .btn-outline {
            border: 1px solid var(--green);
            background: white;
            color: var(--green);
        }

        .btn-outline:hover { background: var(--light); }

        /* Profile button and dropdown — Admin Panel style */
        .admin-profile-menu {
            position: relative;
            display: inline-block;
            flex-shrink: 0;
        }

        .admin-profile-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 5px 12px 5px 8px;
            min-height: 54px;
            border: 1px solid rgb(255 255 255 / 35%);
            border-radius: 30px;
            background: rgb(255 255 255 / 12%);
            color: #ffffff;
            text-decoration: none;
            transition: background 0.2s ease;
        }

        .admin-profile-header:hover,
        .admin-profile-header:focus-visible,
        .admin-profile-menu.is-open .admin-profile-header {
            background: rgb(255 255 255 / 22%);
            border-color: rgb(255 255 255 / 55%);
            outline: none;
        }

        .admin-profile-picture {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            flex-shrink: 0;
            overflow: hidden;
            border: 2px solid #ffffff;
            border-radius: 50%;
            background: #f3f4f6;
            color: #245b38;
        }

        .admin-profile-picture img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-placeholder {
            font-size: 22px;
        }

        .admin-profile-identity {
            display: flex;
            flex-direction: column;
            gap: 3px;
            min-width: 0;
        }

        .admin-profile-identity strong {
            max-width: 155px;
            overflow: hidden;
            color: #ffffff;
            font-size: 13px;
            line-height: 1.2;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .admin-profile-identity small {
            color: #ffffff;
            font-size: 11px;
            font-weight: normal;
        }

        .profile-chevron {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-left: 3px;
            color: #ffffff;
            font-size: 12px;
            transition: transform 0.2s ease;
        }

        .admin-profile-menu.is-open .profile-chevron {
            transform: rotate(180deg);
        }

        .admin-profile-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 12px);
            right: 0;
            z-index: 1000;
            width: 270px;
            max-width: calc(100vw - 24px);
            padding: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            background: #ffffff;
            color: #26352b;
            box-shadow: 0 10px 30px rgb(0 0 0 / 16%);
        }

        .admin-profile-menu:hover .admin-profile-dropdown,
        .admin-profile-menu:focus-within .admin-profile-dropdown,
        .admin-profile-menu.is-open .admin-profile-dropdown {
            display: block;
        }

        .admin-profile-info {
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding: 4px 6px 14px;
            border-bottom: 1px solid #e5e7eb;
            overflow-wrap: anywhere;
        }

        .admin-profile-info strong {
            color: #26352b;
            font-size: 15px;
        }

        .admin-profile-info span {
            color: #6b7280;
            font-size: 13px;
        }

        .admin-profile-info small {
            color: #6b7280;
            font-size: 12px;
        }

        .admin-profile-action,
        .admin-signout {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            min-height: 39px;
            margin-top: 7px;
            padding: 10px 8px;
            border: 0;
            border-radius: 6px;
            background: transparent;
            color: #37483c;
            text-align: left;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
        }

        .admin-profile-action:hover {
            background: #f0f7f1;
        }

        .admin-signout {
            margin-top: 8px;
            padding-top: 12px;
            border-top: 1px solid #e5e7eb;
            border-radius: 0;
            color: #c62828;
        }

        .admin-signout:hover {
            background: #fff1f0;
        }

        .admin-profile-dropdown form {
            margin: 0;
        }

        @media (max-width: 600px) {
            .admin-profile-dropdown {
                right: auto;
                left: 0;
                width: 270px;
            }

            .admin-profile-identity strong {
                max-width: 120px;
            }
        }

        .hero-banner-wrap {
            position: relative;
            width: 100%;
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: #f4f2e7;
        }

        .hero-banner {
            display: block;
            width: 100%;
            height: auto;
            object-fit: contain;
            object-position: center;
        }

        .hero-order-btn {
            position: absolute;
            top: 78%;
            left: 50%;
            min-height: 44px;
            padding: 11px 20px;
            border-radius: 999px;
            font-size: 14px;
            border-color: var(--gold);
            background: var(--gold);
            color: var(--dark);
            box-shadow: 0 3px 10px rgb(0 0 0 / 20%);
            transform: translate(-50%, -50%);
        }

        .hero-order-btn:hover { background: #ffe7a6; }

        @media (max-width: 600px) {
            .hero-order-btn {
                min-height: 36px;
                padding: 8px 12px;
                font-size: 11px;
            }
        }

        .benefits {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-top: 12px;
        }

        .benefit {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: white;
        }

        .benefit-icon {
            display: grid;
            place-items: center;
            width: 38px;
            height: 38px;
            flex-shrink: 0;
            border-radius: 50%;
            background: var(--light);
            font-size: 20px;
        }

        .benefit strong {
            display: block;
            color: var(--dark);
            font-size: 13px;
        }

        .benefit small {
            display: block;
            color: var(--muted);
            font-size: 11px;
            margin-top: 3px;
        }

        main { padding: 25px 0 36px; }

        .section-heading {
            display: flex;
            justify-content: space-between;
            align-items: end;
            gap: 14px;
            margin-bottom: 16px;
        }

        .section-heading h2 {
            margin: 0;
            color: var(--dark);
            font-size: clamp(24px, 3vw, 32px);
            line-height: 1.2;
        }

        .section-heading p {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 13px;
        }

        .count {
            flex-shrink: 0;
            padding: 6px 12px;
            border-radius: 30px;
            background: var(--light);
            color: var(--green);
            font-size: 12px;
            font-weight: bold;
        }

        .favorites-link {
            display: inline-flex;
            align-items: center;
            min-height: 38px;
            padding: 8px 12px;
            border: 1px solid var(--border);
            border-radius: 6px;
            background: #fff;
            color: var(--dark);
            font-weight: 700;
            white-space: nowrap;
        }

        .favorite-form { margin-top: 10px; }
        .favorite-button {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            min-height: 38px;
            padding: 8px 11px;
            border: 1px solid var(--border);
            border-radius: 6px;
            background: #fff;
            color: var(--dark);
            font-weight: 700;
            cursor: pointer;
        }
        .favorite-button[aria-pressed="true"] { color: #a52b2b; }

        .filters {
            display: grid;
            grid-template-columns:
                minmax(190px, 1.5fr)
                minmax(145px, 1fr)
                minmax(155px, 1fr)
                auto;
            gap: 10px;
            margin-bottom: 10px;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background: white;
        }

        .field {
            width: 100%;
            min-width: 0;
            min-height: 42px;
            padding: 9px 11px;
            border: 1px solid #d5dfd5;
            border-radius: 8px;
            background: white;
            color: var(--text);
            font-size: 13px;
        }

        .field:focus {
            outline: 2px solid #a6d3b5;
            outline-offset: 1px;
            border-color: var(--green);
        }

        .reset-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 8px 10px;
            color: var(--green);
            font-size: 12px;
            font-weight: bold;
        }

        .results-summary {
            margin: 0 0 16px;
            color: var(--muted);
            font-size: 12px;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            align-items: stretch;
            gap: 18px;
        }

        .product-card {
            display: flex;
            flex-direction: column;
            min-width: 0;
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: white;
            box-shadow: 0 2px 8px rgb(16 75 48 / 4%);
            transition: transform .15s ease, box-shadow .15s ease;
        }

        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 20px rgb(16 75 48 / 9%);
        }

        .product-card[hidden] { display: none !important; }

        .product-visual {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 200px;
            padding: 10px;
            overflow: hidden;
            background: #f2f5eb;
        }

        .product-visual > img {
            display: block;
            width: 100%;
            height: 100%;
            min-width: 0;
            min-height: 0;
            padding: 0;
            object-fit: contain;
            object-position: center;
        }

        .product-visual .image-fallback {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 8px;
            padding: 12px;
            background: #f2f5eb;
            text-align: center;
            color: var(--green);
        }

        .product-visual .image-fallback[hidden] {
            display: none !important;
        }

        .image-fallback span { font-size: 42px; }
        .image-fallback small { font-size: 11px; font-weight: bold; }

        .product-body {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-width: 0;
            padding: 15px;
        }

        .product-type {
            align-self: flex-start;
            margin-bottom: 8px;
            padding: 4px 9px;
            border-radius: 30px;
            background: var(--light);
            color: var(--green);
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .3px;
        }

        .product-body h3 {
            margin: 0;
            color: var(--dark);
            font-size: 17px;
            line-height: 1.35;
            overflow-wrap: anywhere;
        }

        .product-brand {
            margin-top: 4px;
            color: var(--muted);
            font-size: 12px;
        }

        .product-description {
            margin: 8px 0 0;
            color: #657268;
            font-size: 12px;
            overflow-wrap: anywhere;
        }

        .price-panel {
            display: grid;
            gap: 9px;
            margin-top: 14px;
            padding: 12px 0;
            border-top: 1px solid #edf0eb;
            border-bottom: 1px solid #edf0eb;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            align-items: start;
            gap: 10px;
            font-size: 12px;
        }

        .price-label { color: var(--muted); }

        .price-value {
            color: var(--dark);
            font-size: 14px;
            font-weight: 800;
            text-align: right;
            overflow-wrap: anywhere;
        }

        .price-pending {
            margin: 8px 0;
            padding: 10px;
            border-radius: 8px;
            background: var(--gold);
            color: #70591a;
            font-size: 12px;
            font-weight: bold;
        }

        .add-cart-panel { margin-top: 14px; }

        .add-cart-panel form { display: grid; gap: 8px; }

        .add-cart-panel label {
            margin-top: 2px;
            color: var(--text);
            font-size: 12px;
            font-weight: bold;
        }

        .quantity-control {
            display: grid;
            grid-template-columns: 42px minmax(0, 1fr) 42px;
            gap: 6px;
        }

        .quantity-button {
            min-height: 42px;
            border: 1px solid #d5dfd5;
            border-radius: 8px;
            background: #f7faf6;
            color: var(--dark);
            font-size: 20px;
            font-weight: bold;
        }

        .quantity-button:hover { background: var(--light); }

        .quantity-input {
            text-align: center;
            font-weight: bold;
            appearance: textfield;
            -moz-appearance: textfield;
        }

        .quantity-input::-webkit-inner-spin-button,
        .quantity-input::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .add-cart-panel .btn {
            width: 100%;
            min-height: 43px;
            margin-top: 4px;
            font-size: 13px;
        }

        .stock {
            display: flex;
            align-items: center;
            gap: 7px;
            margin-top: 12px;
            font-size: 11px;
            font-weight: bold;
            color: #237a42;
        }

        .stock-dot {
            width: 8px;
            height: 8px;
            flex-shrink: 0;
            border-radius: 50%;
            background: #239354;
        }

        .stock.low { color: #94651a; }
        .stock.low .stock-dot { background: #d69a27; }
        .stock.out { color: #a33a34; }
        .stock.out .stock-dot { background: #c94b44; }

        .empty {
            grid-column: 1 / -1;
            padding: 45px 18px;
            border: 1px dashed #cbd8ca;
            border-radius: 14px;
            background: white;
            text-align: center;
        }

        .empty[hidden] { display: none !important; }
        .empty h3 { margin: 0 0 8px; color: var(--dark); }
        .empty p { margin: 0 0 16px; color: var(--muted); }

        .info-strip {
            margin-top: 22px;
            padding: 15px 17px;
            border: 1px solid #eee4bd;
            border-radius: 10px;
            background: #fff8dc;
            color: #665627;
            font-size: 12px;
        }

        .success-message {
            margin-bottom: 18px;
            padding: 13px 15px;
            border: 1px solid #b7dfc3;
            border-radius: 10px;
            background: #e8f7ed;
            color: #155c34;
            font-weight: bold;
        }

        footer {
            padding: 22px 0;
            border-top: 1px solid var(--dark);
            background: var(--green);
        }

        .footer-inner {
            display: grid;
            gap: 16px;
        }

        .footer-inner h2 {
            margin: 0;
            color: white;
            font-size: 17px;
        }

        .footer-inner p {
            margin: 5px 0 0;
            color: #e4f1e8;
            font-size: 12px;
            line-height: 1.6;
        }

        .contact-details {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
        }

        .contact-item {
            padding-top: 12px;
            border-top: 1px solid rgba(255, 255, 255, 0.3);
        }

        .contact-item strong {
            color: white;
            font-size: 13px;
        }

        .footer-inner a {
            color: white;
            font-size: 12px;
            font-weight: bold;
        }

        .footer-inner a:hover { text-decoration: underline; }

        @media (max-width: 1150px) {
            .product-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .filters { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 850px) {
            .product-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .product-visual { height: 175px; }
        }

        @media (max-width: 600px) {
            .container { width: 92%; }

            body {
                overflow-x: hidden;
            }

            .nav {
                align-items: flex-start;
                flex-direction: column;
                padding: 12px 0;
            }

            .brand-logo { width: 46px; height: 46px; }
            .brand-name { font-size: 16px; }
            .brand-caption { font-size: 10px; }
            .nav-actions {
                width: 100%;
                justify-content: flex-start;
                gap: 10px 14px;
            }
            .nav-actions > a,
            .nav-actions .admin-profile-header {
                min-height: 44px;
            }
            .admin-profile-identity strong { max-width: 120px; }
            .contact-details { grid-template-columns: 1fr; gap: 14px; }
            .hero { padding-top: 10px; }
            .hero-banner-wrap { border-radius: 10px; }
            .hero-order-btn {
                min-height: 40px;
                padding: 9px 14px;
                font-size: 12px;
            }

            .benefits { grid-template-columns: 1fr; gap: 8px; }
            .benefit { padding: 10px 12px; }
            .benefit-icon { width: 36px; height: 36px; }
            main { padding-top: 22px; }
            .section-heading {
                align-items: flex-start;
                flex-direction: column;
                gap: 8px;
            }
            .section-heading h2 { font-size: 25px; }
            .count { align-self: flex-start; }

            .filters {
                grid-template-columns: 1fr;
                padding: 10px;
            }

            .filters .btn,
            .filters .field,
            .filters .reset-link { width: 100%; }
            .filters .field,
            .filters .reset-link {
                min-height: 44px;
                font-size: 16px;
            }
            .filters .reset-link {
                justify-content: center;
                border: 1px solid var(--border);
                border-radius: 8px;
            }

            .product-grid { grid-template-columns: 1fr; gap: 14px; }

            .product-card {
                display: grid;
                grid-template-columns: 1fr;
            }

            .product-visual { height: 180px; min-height: 180px; }
            .product-body { padding: 15px; }
            .product-body h3 { font-size: 15px; }
            .product-description { font-size: 11px; }
            .price-row { font-size: 11px; }
            .price-value { font-size: 12px; }
            .price-panel { gap: 7px; margin-top: 10px; padding: 9px 0; }

            .quantity-control {
                grid-template-columns: 34px minmax(0, 1fr) 34px;
                gap: 4px;
            }

            .quantity-button { min-height: 44px; }
            .quantity-control .field {
                min-height: 44px;
                padding: 6px 3px;
                font-size: 16px;
            }

            .add-cart-panel .btn { min-height: 46px; }
            .empty .btn { max-width: 100%; }
        }

        @media (max-width: 370px) {
            .product-card { grid-template-columns: 1fr; }
            .product-visual { height: 170px; min-height: 170px; }
            .nav-actions > * { min-height: 42px; }
            .product-card button,
            .product-card .btn { min-height: 44px; }
        }
    </style>
</head>

<body>
<header>
    <div class="container nav">
        <a class="brand" href="{{ route('home') }}" aria-label="H&amp;K Consumer Goods Store home">
            <img class="brand-logo"
                 src="{{ asset('images/HK_Consumer_Goods_Store_logo.png') }}"
                 alt="H&amp;K Consumer Goods Store logo">
            <span>
                <span class="brand-name">H&amp;K Consumer Goods Store</span>
                <span class="brand-caption">Quality rice for every home</span>
            </span>
        </a>

        <nav class="nav-actions" aria-label="Main navigation">
            <a class="nav-link" href="#products">Menu</a>
            <a class="nav-link" href="#contact">Contact</a>

            @guest
                <a class="nav-link" href="{{ route('login') }}">Log In</a>
                <a class="btn-outline" href="{{ route('register') }}">Sign Up</a>
            @else
                @include('components.notification-center')

                @if (auth()->user()->isAdmin())
                    <a class="nav-link" href="{{ route('admin.dashboard') }}">
                        Owner Dashboard
                    </a>
                @else
                    <a class="nav-link" href="{{ route('customer.orders.index') }}">
                        My Orders
                    </a>
                @endif

                <div class="admin-profile-menu" id="profile-menu">
                    <a href="{{ auth()->user()->isAdmin()
                        ? route('admin.profile.edit')
                        : route('customer.profile.edit') }}"
                       class="admin-profile-header"
                       id="profile-menu-toggle"
                       aria-haspopup="true"
                       aria-expanded="false"
                       aria-controls="profile-dropdown">

                        <span class="admin-profile-picture">
                            @if (auth()->user()->profile_picture)
                                <img
                                    src="{{ asset('storage/' . auth()->user()->profile_picture) }}"
                                    alt="Profile picture">
                            @else
                                <span class="profile-placeholder" aria-hidden="true">👤</span>
                            @endif
                        </span>

                        <span class="admin-profile-identity">
                            <strong>{{ auth()->user()->name }}</strong>
                            <small>
                                {{ auth()->user()->isAdmin() ? 'Administrator' : 'Customer' }}
                            </small>
                        </span>

                        <span class="profile-chevron" aria-hidden="true">▼</span>
                    </a>

                    <div class="admin-profile-dropdown" id="profile-dropdown">
                        <div class="admin-profile-info">
                            <strong>
                                {{ auth()->user()->isAdmin() ? 'Admin Profile' : 'My Profile' }}
                            </strong>
                            <span>{{ auth()->user()->name }}</span>
                            <small>{{ auth()->user()->email }}</small>
                        </div>

                        <a href="{{ auth()->user()->isAdmin()
                            ? route('admin.profile.edit')
                            : route('customer.profile.edit') }}"
                           class="admin-profile-action">
                            <span aria-hidden="true">👤</span>
                            <span>Edit Profile</span>
                        </a>

                        @unless (auth()->user()->isAdmin())
                            <a href="{{ route('customer.orders.index') }}"
                               class="admin-profile-action">
                                <span aria-hidden="true">📦</span>
                                <span>My Orders</span>
                            </a>
                        @endunless

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="admin-signout">
                                <span aria-hidden="true">↪</span>
                                <span>Sign Out</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endguest
        </nav>
    </div>
</header>

<section class="hero">
    <div class="container">
        <div class="hero-banner-wrap">
            <img class="hero-banner"
                 src="{{ asset('images/HK_Consumer_Goods_Store_banner.png') }}"
                 alt="H&amp;K Consumer Goods Store banner"
                 fetchpriority="high">
            <a class="btn hero-order-btn" href="#products">Order Now</a>
        </div>

        <div class="benefits">
            <div class="benefit">
                <span class="benefit-icon" aria-hidden="true">🌾</span>
                <div>
                    <strong>Quality Rice</strong>
                    <small>Explore our available rice varieties</small>
                </div>
            </div>

            <div class="benefit">
                <span class="benefit-icon" aria-hidden="true">🚚</span>
                <div>
                    <strong>Reliable Delivery</strong>
                    <small>Delivery fees shown at checkout</small>
                </div>
            </div>

            <div class="benefit">
                <span class="benefit-icon" aria-hidden="true">🏡</span>
                <div>
                    <strong>For Every Home</strong>
                    <small>Good rice for everyday meals</small>
                </div>
            </div>
        </div>
    </div>
</section>

<main class="container" id="products">
    @if (session('success'))
        <div class="success-message" role="status">
            {{ session('success') }}
        </div>
    @endif

    <div class="section-heading">
        <div>
            <h2>Our Products</h2>
            <p>Search and filter our products instantly.</p>
        </div>

        <span class="count" id="product-count" aria-live="polite">
            {{ $products->count() }}
            {{ $products->count() === 1 ? 'product' : 'products' }}
        </span>
        @if (auth()->check() && auth()->user()->isCustomer())
            <a class="favorites-link" href="{{ route('customer.favorites.index') }}">Favorites</a>
        @endif
        <a class="favorites-link" href="{{ route('bundles.index') }}">Bundle Packs</a>
    </div>

    <form class="filters" id="product-filters" method="GET" action="{{ route('home') }}">
        <input class="field"
               id="product-search"
               type="search"
               name="search"
               value="{{ request('search') }}"
               placeholder="Search products, brand, or type..."
               aria-label="Search products"
               maxlength="100"
               autocomplete="off">

        <select class="field" id="rice-type-filter" name="type" aria-label="Filter by product type">
            <option value="">All product types</option>
            @foreach ($productTypes as $productType)
                <option value="{{ $productType }}" @selected(request('type') === $productType)>
                    {{ $productType }}
                </option>
            @endforeach
        </select>

        <select class="field" id="product-sort" name="sort" aria-label="Sort products">
            <option value="name" @selected($sort === 'name')>Name: A to Z</option>
            <option value="price_asc" @selected($sort === 'price_asc')>Price: Low to high</option>
            <option value="price_desc" @selected($sort === 'price_desc')>Price: High to low</option>
        </select>

        <a class="reset-link" id="reset-filters" href="{{ route('home') }}">Reset filters</a>
    </form>

    <p class="results-summary" id="results-summary" aria-live="polite">
        Showing {{ $products->count() }} products.
    </p>

    <div class="product-grid" id="product-grid">
        @foreach ($products as $product)
            @php
                $isGenericProduct = ($product->inventory_type ?? 'rice') === 'generic';
                $productName = strtolower(trim($product->name ?? ''));
                $productBrand = strtolower(trim($product->brand ?? ''));
                $searchableText = $productName . ' ' . $productBrand . ' ' . strtolower(trim($product->rice_type ?? '')) . ' ' . strtolower(trim($product->category ?? ''));
                $productType = $isGenericProduct
                    ? ($product->category ?: 'General product')
                    : ($product->rice_type ?: 'Rice');

                // Use the uploaded product image first.
                $uploadedImageUrl = !empty($product->image)
                    ? asset('storage/' . $product->image)
                    : null;

                // Keep the existing images for products that do not have an uploaded image.
                $productImages = [
                    'bon ramon' => 'bon ramon.jpg',
                    'carlos imported' => 'carlos imported.jpg',
                    'carlos uno imported' => 'carlos uno imported.jpg',
                    'carlos uno local' => 'carlos uno local.jpg',
                    'jasmine 10 kg' => 'Jasmine 10 kg.jpg',
                    'jasmine 5 kg' => 'Jasmine 5 kg.jpg',
                    'marina' => 'marina.jpg',
                    'red rice' => 'Red Rice 50 kg.jpg',
                    'senior roberto' => 'Senior Roberto 50 kg.png',
                    'senior reberto' => 'Senior Reberto 25 kg.jpg',
                ];

                $imageFile = null;
                foreach ($productImages as $keyword => $filename) {
                    if (str_contains($searchableText, $keyword)) {
                        $imageFile = $filename;
                        break;
                    }
                }

                $imagePath = $imageFile
                    ? public_path('images/products/' . $imageFile)
                    : null;

                $mappedImageExists = $imagePath && file_exists($imagePath);

                $hasKgPrice = !$isGenericProduct
                    && (bool) $product->price_confirmed
                    && $product->price_per_kg !== null
                    && (float) $product->price_per_kg > 0;

                $hasSackPrice = !$isGenericProduct
                    && (bool) $product->price_confirmed
                    && $product->price_per_sack !== null
                    && (float) $product->price_per_sack > 0
                    && (float) $product->sack_weight_kg >= 5;

                $genericSellingUnits = $isGenericProduct
                    ? $product->sellingUnits->filter(fn ($unit) =>
                        (bool) $product->price_confirmed
                        && (float) $unit->selling_price > 0
                    )->values()
                    : collect();
                $genericBaseUnit = strtolower(trim(
                    (string) ($product->base_unit ?: $product->stock_unit ?: 'piece')
                ));
                $stock = (float) ($isGenericProduct
                    ? ($product->stock_base_quantity ?? 0)
                    : ($product->stock_kg ?? 0));
                $lowThreshold = (float) ($isGenericProduct
                    ? ($product->low_stock_threshold_quantity ?? 0)
                    : ($product->low_stock_threshold ?? 5));
                $sackWeight = (float) ($product->sack_weight_kg ?? 0);
                $stockSacks = !$isGenericProduct && $sackWeight > 0
                    ? $stock / $sackWeight
                    : null;
                $stockUnitLabel = $isGenericProduct
                    ? \Illuminate\Support\Str::plural($genericBaseUnit, $stock)
                    : 'kg';
                $genericBaseSellingUnit = $genericSellingUnits->first(
                    fn ($unit) => strtolower($unit->unit_name) === $genericBaseUnit
                );
                $sortPrice = $isGenericProduct
                    ? ($genericBaseSellingUnit
                        ? (float) $genericBaseSellingUnit->selling_price
                        : null)
                    : ($hasKgPrice ? (float) $product->price_per_kg : null);
                $canAddToCart = $stock > 0 && (
                    $isGenericProduct
                        ? $genericSellingUnits->isNotEmpty()
                        : ($hasKgPrice || $hasSackPrice)
                );
            @endphp

            <article class="product-card"
                     data-name="{{ strtolower($product->name ?? '') }}"
                     data-brand="{{ strtolower($product->brand ?? '') }}"
                     data-type="{{ strtolower($productType) }}"
                     data-price-unit="{{ $sortPrice ?? '' }}"
                     data-price-sack="{{ $hasSackPrice ? (float) $product->price_per_sack : '' }}"
                     data-original-order="{{ $loop->index }}">
                <div class="product-visual">
                    @if ($uploadedImageUrl)
                        <img src="{{ $uploadedImageUrl }}"
                             alt="{{ $product->name }}"
                             loading="lazy"
                             onerror="this.style.display='none';this.nextElementSibling.hidden=false;">
                        <div class="image-fallback" hidden>
                            <span aria-hidden="true">🌾</span>
                            <small>{{ $productType }}</small>
                        </div>
                    @elseif ($mappedImageExists)
                        <img src="{{ asset('images/products/' . $imageFile) }}"
                             alt="{{ $product->name }}"
                             loading="lazy"
                             onerror="this.style.display='none';this.nextElementSibling.hidden=false;">
                        <div class="image-fallback" hidden>
                            <span aria-hidden="true">🌾</span>
                            <small>{{ $productType }}</small>
                        </div>
                    @else
                        <div class="image-fallback">
                            <span aria-hidden="true">🌾</span>
                            <small>{{ $productType }}</small>
                        </div>
                    @endif
                </div>

                <div class="product-body">
                    <div class="product-type">{{ $productType }}</div>
                    <h3>{{ $product->name }}</h3>

                    <div class="product-brand">
                        {{ $product->brand ?: 'H&K Consumer Goods Store' }}
                    </div>

                    @if ($product->variant_size || $product->variant_color)
                        <p class="product-description">
                            @if ($product->variant_size) Size: {{ $product->variant_size }} @endif
                            @if ($product->variant_color) · Color: {{ $product->variant_color }} @endif
                        </p>
                    @endif

                    @if ($product->approved_reviews_count > 0)
                        <p class="product-rating" aria-label="Rated {{ number_format((float) $product->approved_reviews_avg, 1) }} out of 5">
                            <span aria-hidden="true">&#9733;</span>
                            {{ number_format((float) $product->approved_reviews_avg, 1) }}
                            <span class="product-brand">({{ $product->approved_reviews_count }} reviews)</span>
                        </p>
                        @foreach ($product->reviews as $review)
                            @if ($review->review || $review->photo_path)
                                <div class="product-review-snippet">
                                    <strong>{{ $review->user?->name ?? 'Customer' }}</strong>
                                    <span aria-label="{{ $review->rating }} out of 5 stars">{{ str_repeat('★', $review->rating) }}</span>
                                    @if ($review->review)<p>{{ $review->review }}</p>@endif
                                    @if ($review->photo_path)
                                        <img src="{{ asset('storage/' . $review->photo_path) }}" alt="Customer review photo for {{ $product->name }}" loading="lazy" width="96" height="72">
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    @endif

                    @if ($product->description)
                        <p class="product-description">{{ $product->description }}</p>
                    @endif

                    <div class="price-panel">
                        @if ($isGenericProduct)
                            @forelse ($product->sellingUnits as $sellingUnit)
                                <div class="price-row">
                                    <span class="price-label">Per {{ $sellingUnit->unit_name }}</span>
                                    <span class="price-value">
                                        @if ((bool) $product->price_confirmed && (float) $sellingUnit->selling_price > 0)
                                            ₱{{ number_format((float) $sellingUnit->selling_price, 2) }}
                                        @else
                                            <span class="price-label">Not confirmed</span>
                                        @endif
                                    </span>
                                </div>
                            @empty
                                <div class="price-row">
                                    <span class="price-label">Price</span>
                                    <span class="price-value"><span class="price-label">Not confirmed</span></span>
                                </div>
                            @endforelse
                        @else
                            <div class="price-row">
                                <span class="price-label">Per kilogram</span>
                                <span class="price-value">
                                    @if ($hasKgPrice)
                                        ₱{{ number_format((float) $product->price_per_kg, 2) }}
                                    @else
                                        <span class="price-label">Not confirmed</span>
                                    @endif
                                </span>
                            </div>

                            <div class="price-row">
                                <span class="price-label">
                                    @if ($product->sack_weight_kg)
                                        {{ $product->sack_weight_kg }} kg sack/pack
                                    @else
                                        Per sack/pack
                                    @endif
                                </span>
                                <span class="price-value">
                                    @if ($hasSackPrice)
                                        ₱{{ number_format((float) $product->price_per_sack, 2) }}
                                    @else
                                        <span class="price-label">Not confirmed</span>
                                    @endif
                                </span>
                            </div>
                        @endif

                        @if (!$isGenericProduct && !$hasKgPrice && !$hasSackPrice)
                            <div class="price-pending">Price to be confirmed</div>
                        @endif
                    </div>

                    @if ($stock <= 0)
                        <div class="stock out">
                            <span class="stock-dot"></span>
                            Currently out of stock
                        </div>
                    @elseif ($stock <= $lowThreshold)
                        <div class="stock low">
                            <span class="stock-dot"></span>
                            Limited stock ·
                            @if ($isGenericProduct)
                                {{ number_format($stock, $stock == floor($stock) ? 0 : 2) }} {{ $stockUnitLabel }}
                            @elseif ($stockSacks !== null)
                                {{ number_format($stockSacks, 2) }} sacks
                            @else
                                {{ number_format($stock, 2) }} kg
                            @endif
                        </div>
                    @else
                        <div class="stock">
                            <span class="stock-dot"></span>
                            In stock ·
                            @if ($isGenericProduct)
                                {{ number_format($stock, $stock == floor($stock) ? 0 : 2) }} {{ $stockUnitLabel }}
                            @elseif ($stockSacks !== null)
                                {{ number_format($stockSacks, 2) }} sacks
                            @else
                                {{ number_format($stock, 2) }} kg
                            @endif
                        </div>
                    @endif

                    <div class="add-cart-panel">
                        @auth
                            @if ($canAddToCart)
                                <form method="POST"
                                      action="{{ route('cart.add', $product) }}"
                                      class="add-cart-form">
                                    @csrf

                                    <label for="unit-{{ $product->id }}">{{ $isGenericProduct ? 'Choose unit' : 'Choose package' }}</label>
                                    <select class="field package-select"
                                            id="unit-{{ $product->id }}"
                                            name="unit"
                                            data-generic="{{ $isGenericProduct ? '1' : '0' }}"
                                            required>
                                        @if ($isGenericProduct)
                                            @foreach ($genericSellingUnits as $sellingUnit)
                                                <option value="{{ $sellingUnit->unit_name }}" @selected(strtolower($sellingUnit->unit_name) === $genericBaseUnit)>
                                                    {{ ucfirst($sellingUnit->unit_name) }} · ₱{{ number_format((float) $sellingUnit->selling_price, 2) }}
                                                </option>
                                            @endforeach
                                        @elseif ($hasKgPrice)
                                            <option value="kg">Per kilogram</option>
                                        @endif
                                        @if (!$isGenericProduct && $hasSackPrice)
                                            <option value="sack">{{ $product->sack_weight_kg }} kg sack/pack</option>
                                        @endif
                                    </select>

                                    <label for="quantity-{{ $product->id }}">
                                        Quantity <span class="quantity-unit">({{ $isGenericProduct ? $genericBaseUnit : 'kg' }})</span>
                                    </label>

                                    <div class="quantity-control">
                                        <button class="quantity-button quantity-minus"
                                                type="button"
                                                aria-label="Decrease quantity">&minus;</button>
                                        <input class="field quantity-input"
                                               id="quantity-{{ $product->id }}"
                                               type="number"
                                               name="quantity"
                                               min="{{ $isGenericProduct ? '1' : '5' }}"
                                               max="1000"
                                               step="1"
                                               value="{{ $isGenericProduct ? '1' : '5' }}"
                                               required
                                               inputmode="numeric">
                                        <button class="quantity-button quantity-plus"
                                                type="button"
                                                aria-label="Increase quantity">+</button>
                                    </div>

                                    <small class="quantity-hint">{{ $isGenericProduct ? 'Enter the quantity in the selected unit.' : 'Minimum order: 5 kg.' }}</small>
                                    <button class="btn" type="submit">Add to Cart</button>
                                </form>
                            @elseif (!$product->price_confirmed || ($isGenericProduct && $genericSellingUnits->isEmpty()))
                                <p class="price-pending">Price has not yet been confirmed.</p>
                            @else
                                <p class="price-pending">This product is currently unavailable.</p>
                            @endif
                        @else
                            @if (!$product->price_confirmed || ($isGenericProduct && $genericSellingUnits->isEmpty()))
                                <p class="price-pending">Price has not yet been confirmed.</p>
                            @elseif ($stock <= 0)
                                <p class="price-pending">This product is currently out of stock.</p>
                            @else
                                <a class="btn" href="{{ route('login') }}">Log in to order</a>
                            @endif
                        @endauth
                    </div>

                    @if (auth()->check() && auth()->user()->isCustomer())
                        @php
                            $isFavorite = in_array($product->id, $favoriteProductIds ?? [], true);
                        @endphp
                        <form class="favorite-form" method="POST" action="{{ $isFavorite ? route('customer.favorites.destroy', $product) : route('customer.favorites.store', $product) }}">
                            @csrf
                            @if ($isFavorite)
                                @method('DELETE')
                            @endif
                            <button
                                class="favorite-button"
                                type="submit"
                                aria-pressed="{{ $isFavorite ? 'true' : 'false' }}"
                                aria-label="{{ $isFavorite ? 'Remove ' . $product->name . ' from favorites' : 'Save ' . $product->name . ' to favorites' }}"
                            >
                                <span aria-hidden="true">{{ $isFavorite ? '&#9829;' : '&#9825;' }}</span>
                                {{ $isFavorite ? 'Saved' : 'Save for later' }}
                            </button>
                        </form>
                    @endif
                </div>
            </article>
        @endforeach

        <div class="empty" id="no-products" hidden>
            <h3>No matching products found</h3>
            <p>Try another search term or select a different product type.</p>
            <button class="btn" type="button" id="clear-search">Clear search and filters</button>
        </div>
    </div>

    <div class="info-strip">
        <strong>Price Notice:</strong>
        Prices that are not confirmed are not available for purchase.
        Please check the displayed package size, price, and stock before ordering.
    </div>
</main>

<footer id="contact">
    <div class="container footer-inner">
        <div>
            <h2>Contact H&amp;K Consumer Goods Store</h2>
            <p>Order assistance and delivery information</p>
        </div>
        <div class="contact-details">
            <div class="contact-item">
                <strong>Order status</strong>
                <p>Check your order details and updates in your account.</p>
                @auth
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}">Open dashboard</a>
                    @else
                        <a href="{{ route('customer.orders.index') }}">View my orders</a>
                    @endif
                @else
                    <a href="{{ route('login') }}">Log in for order help</a>
                @endauth
            </div>
            <div class="contact-item">
                <strong>Browse products</strong>
                <p>See available rice varieties, prices, and stock.</p>
                <a href="#products">View products</a>
            </div>
            <div class="contact-item">
                <strong>Contact details</strong>
                <p>Phone: <a href="tel:09287269237">09287269237</a></p>
                <p>Address: Real St., Brgy. Loyonsawang, Abuyog, Leyte</p>
            </div>
        </div>
    </div>
</footer>

<script>
(() => {
    const form = document.getElementById('product-filters');
    const searchInput = document.getElementById('product-search');
    const typeSelect = document.getElementById('rice-type-filter');
    const sortSelect = document.getElementById('product-sort');
    const grid = document.getElementById('product-grid');
    const countLabel = document.getElementById('product-count');
    const summary = document.getElementById('results-summary');
    const emptyMessage = document.getElementById('no-products');
    const resetLink = document.getElementById('reset-filters');
    const clearButton = document.getElementById('clear-search');
    const cards = Array.from(grid.querySelectorAll('.product-card'));

    function normalize(value) {
        return (value || '')
            .toLocaleLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
    }

    function updateProducts(updateUrl = true) {
        const searchTerm = normalize(searchInput.value);
        const selectedType = normalize(typeSelect.value);
        const sortValue = sortSelect.value;
        const visibleCards = [];

        cards.forEach(card => {
            const name = normalize(card.dataset.name);
            const brand = normalize(card.dataset.brand);
            const type = normalize(card.dataset.type);

            const matchesSearch = !searchTerm ||
                name.includes(searchTerm) ||
                brand.includes(searchTerm) ||
                type.includes(searchTerm);

            const matchesType = !selectedType || type === selectedType;
            const matches = matchesSearch && matchesType;

            card.hidden = !matches;
            if (matches) visibleCards.push(card);
        });

        visibleCards.sort((a, b) => {
            if (sortValue === 'price_asc' || sortValue === 'price_desc') {
                const priceA = a.dataset.priceUnit === ''
                    ? null : Number(a.dataset.priceUnit);
                const priceB = b.dataset.priceUnit === ''
                    ? null : Number(b.dataset.priceUnit);

                if (priceA === null && priceB !== null) return 1;
                if (priceA !== null && priceB === null) return -1;

                if (priceA !== null && priceB !== null && priceA !== priceB) {
                    return sortValue === 'price_asc'
                        ? priceA - priceB
                        : priceB - priceA;
                }
            }

            if (sortValue === 'name') {
                const comparison = normalize(a.dataset.name)
                    .localeCompare(normalize(b.dataset.name));

                if (comparison !== 0) return comparison;
            }

            return Number(a.dataset.originalOrder) -
                Number(b.dataset.originalOrder);
        });

        visibleCards.forEach(card => grid.insertBefore(card, emptyMessage));

        const total = visibleCards.length;
        countLabel.textContent = total + (total === 1 ? ' product' : ' products');
        summary.textContent = 'Showing ' + total +
            (total === 1 ? ' product' : ' products') + ' matching your filters.';
        emptyMessage.hidden = total !== 0;

        if (updateUrl) {
            const params = new URLSearchParams();

            if (searchInput.value.trim()) {
                params.set('search', searchInput.value.trim());
            }

            if (typeSelect.value) params.set('type', typeSelect.value);
            if (sortValue && sortValue !== 'name') params.set('sort', sortValue);

            const queryString = params.toString();
            const newUrl = window.location.pathname +
                (queryString ? '?' + queryString : '') +
                window.location.hash;

            window.history.replaceState({}, '', newUrl);
        }
    }

    form.addEventListener('submit', event => {
        event.preventDefault();
        updateProducts();
    });

    searchInput.addEventListener('input', () => updateProducts());
    typeSelect.addEventListener('change', () => updateProducts());
    sortSelect.addEventListener('change', () => updateProducts());

    function clearFilters() {
        searchInput.value = '';
        typeSelect.value = '';
        sortSelect.value = 'name';
        updateProducts();
        searchInput.focus();
    }

    resetLink.addEventListener('click', event => {
        event.preventDefault();
        clearFilters();
    });

    clearButton.addEventListener('click', clearFilters);

    document.querySelectorAll('.add-cart-form').forEach(cartForm => {
        const packageSelect = cartForm.querySelector('.package-select');
        const quantityInput = cartForm.querySelector('.quantity-input');
        const quantityUnit = cartForm.querySelector('.quantity-unit');
        const quantityHint = cartForm.querySelector('.quantity-hint');
        const minusButton = cartForm.querySelector('.quantity-minus');
        const plusButton = cartForm.querySelector('.quantity-plus');

        function updateQuantityRules(resetToDefault = false) {
            const isGeneric = packageSelect.dataset.generic === '1';
            const isSack = !isGeneric && packageSelect.value === 'sack';
            const defaultQuantity = isGeneric || isSack ? 1 : 5;

            quantityInput.min = isGeneric || isSack ? '1' : '5';
            quantityInput.step = '1';
            quantityUnit.textContent = isGeneric
                ? '(' + packageSelect.options[packageSelect.selectedIndex].text.split(' · ')[0].toLowerCase() + ')'
                : (isSack ? '(sacks)' : '(kg)');
            quantityHint.textContent = isGeneric
                ? 'Enter the quantity in the selected unit.'
                : (isSack
                ? 'Enter the number of sacks.'
                : 'Minimum order: 5 kg.');

            const minimum = Number(quantityInput.min);
            const maximum = Number(quantityInput.max) || 1000;
            const current = Number(quantityInput.value);

            if (resetToDefault || !Number.isFinite(current) || current < minimum) {
                quantityInput.value = String(defaultQuantity);
            } else if (current > maximum) {
                quantityInput.value = String(maximum);
            }
        }

        function changeQuantity(amount) {
            const minimum = Number(quantityInput.min);
            const maximum = Number(quantityInput.max) || 1000;
            let current = Number(quantityInput.value);

            if (!Number.isFinite(current)) current = minimum;

            quantityInput.value = String(
                Math.min(maximum, Math.max(minimum, Math.floor(current) + amount))
            );
        }

        packageSelect.addEventListener('change', () => updateQuantityRules(true));
        minusButton.addEventListener('click', () => changeQuantity(-1));
        plusButton.addEventListener('click', () => changeQuantity(1));

        quantityInput.addEventListener('change', () => {
            const minimum = Number(quantityInput.min);
            const maximum = Number(quantityInput.max) || 1000;
            let value = Number(quantityInput.value);

            if (!Number.isFinite(value)) value = minimum;

            quantityInput.value = String(
                Math.min(maximum, Math.max(minimum, Math.floor(value)))
            );
        });

        updateQuantityRules(true);
    });

    // Dropdown: click support for touch devices; hover/focus still works on desktop.
    const profileMenu = document.getElementById('profile-menu');
    const profileToggle = document.getElementById('profile-menu-toggle');

    if (profileMenu && profileToggle) {
        profileToggle.addEventListener('click', event => {
            event.preventDefault();

            const isOpen = profileMenu.classList.toggle('is-open');
            profileToggle.setAttribute('aria-expanded', String(isOpen));
        });

        document.addEventListener('click', event => {
            if (!profileMenu.contains(event.target)) {
                profileMenu.classList.remove('is-open');
                profileToggle.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('keydown', event => {
            if (event.key === 'Escape') {
                profileMenu.classList.remove('is-open');
                profileToggle.setAttribute('aria-expanded', 'false');
                profileToggle.focus();
            }
        });
    }

    updateProducts(false);
})();
</script>
    @include('components.smart-assistant')
</body>
</html>
