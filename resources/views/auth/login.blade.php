<!DOCTYPE html>
<html lang="en">
<head>
   
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Login | H&K Consumer Goods Store</title>
     <link rel="icon" type="image/png" href="{{ asset('images/HK_Consumer_Goods_Store_logo.png') }}">

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 20px;
            background: #f4f5ec;
            color: #243b2b;
            font-family: Arial, sans-serif;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            padding: 32px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, .08);
        }

        .brand {
            margin-bottom: 8px;
            color: #2f6b42;
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        h1 { margin: 0 0 8px; }
        .subtitle { margin: 0 0 24px; color: #667267; }

        label {
            display: block;
            margin: 16px 0 7px;
            font-weight: bold;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5ca;
            border-radius: 8px;
            font-size: 16px;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 16px 0;
            font-size: 14px;
        }

        button {
            width: 100%;
            padding: 13px;
            border: 0;
            border-radius: 8px;
            background: #2f6b42;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover { background: #245333; }

        .error {
            margin: 14px 0;
            padding: 12px;
            border-radius: 8px;
            background: #fff0ee;
            color: #9c2920;
            font-size: 14px;
        }

        .back {
            display: block;
            margin-top: 20px;
            color: #2f6b42;
            text-align: center;
            text-decoration: none;
        }

        @media (max-width: 480px) {
            body {
                padding: 14px;
            }

            .login-card {
                padding: 24px 18px;
                border-radius: 12px;
            }

            h1 {
                font-size: 25px;
            }

            input[type="email"],
            input[type="password"] {
                min-height: 46px;
                font-size: 16px;
            }

            button {
                min-height: 46px;
            }
        }
    </style>
</head>
<body>
    <main class="login-card">
        <div class="brand">H&K Consumer Goods Store</div>
        <h1>Owner Login</h1>
        <p class="subtitle">Sign in to manage rice prices and inventory.</p>

        @if ($errors->any())
            <div class="error" role="alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}">
            @csrf

            <label for="email">Email address</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                autocomplete="username"
                required
                autofocus
            >

            <label for="password">Password</label>
            <input
                id="password"
                type="password"
                name="password"
                autocomplete="current-password"
                required
            >

            <label class="remember">
                <input type="checkbox" name="remember" value="1">
                Remember me
            </label>

            <button type="submit">Sign In</button>
        </form>

        <a class="back" href="{{ route('home') }}">Back to Store</a>
    </main>
</body>
</html>
