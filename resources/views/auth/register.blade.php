 
<!DOCTYPE html>
<html lang="en">
<head>
  
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#176b42">
    <title>Sign Up | H&K Consumer Goods Store</title>
     <link rel="icon" type="image/png" href="{{ asset('images/HK_Consumer_Goods_Store_logo.png') }}">

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background: linear-gradient(135deg, #e8f4eb, #fffdf5);
            color: #203329;
            font-family: Arial, Helvetica, sans-serif;
        }

        .card {
            width: min(100%, 460px);
            padding: 32px;
            border: 1px solid #e5eae5;
            border-radius: 20px;
            background: #fff;
            box-shadow: 0 18px 45px rgba(16, 75, 48, .09);
        }

        .brand {
            margin-bottom: 22px;
            color: #104b30;
            font-size: 22px;
            font-weight: 800;
        }

        .brand span {
            display: block;
            margin-top: 4px;
            color: #6d7c72;
            font-size: 13px;
            font-weight: 400;
        }

        h1 {
            margin: 0 0 8px;
            color: #104b30;
            font-size: 28px;
        }

        .intro {
            margin: 0 0 24px;
            color: #6d7c72;
            font-size: 14px;
        }

        label {
            display: block;
            margin: 15px 0 6px;
            font-size: 14px;
            font-weight: 700;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #dfe6df;
            border-radius: 10px;
            background: white;
            font: inherit;
        }

        input:focus {
            outline: 2px solid #176b42;
            outline-offset: 1px;
        }

        .errors {
            margin-bottom: 18px;
            padding: 12px 16px;
            border-radius: 10px;
            background: #fff0ee;
            color: #a33a34;
            font-size: 13px;
        }

        .errors ul { margin: 0; padding-left: 18px; }

        .btn {
            width: 100%;
            margin-top: 23px;
            padding: 13px;
            border: 0;
            border-radius: 10px;
            background: #176b42;
            color: white;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
        }

        .btn:hover { background: #104b30; }

        .links {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
        }

        a {
            color: #176b42;
            font-weight: 700;
            text-decoration: none;
        }

        a:hover { text-decoration: underline; }

        .back { margin-top: 14px; }

        .hint {
            margin-top: 7px;
            color: #6d7c72;
            font-size: 12px;
        }

        @media (max-width: 480px) {
            body {
                padding: 14px;
            }

            .card {
                padding: 24px 18px;
                border-radius: 14px;
            }

            .brand {
                font-size: 20px;
            }

            h1 {
                font-size: 25px;
            }

            input {
                min-height: 46px;
                font-size: 16px;
            }

            .btn {
                min-height: 46px;
            }
        }
    </style>
</head>
<body>
    <main class="card">
        <div class="brand">
            H+K Consumer Goods
            <span>Quality rice for every home</span>
        </div>

        <h1>Create your account</h1>
        <p class="intro">
            Sign up to become a customer of H&K Consumer Goods Store.
        </p>

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

        <form method="POST" action="{{ route('register.submit') }}">
            @csrf

            <label for="name">Full name</label>
            <input
                id="name"
                name="name"
                type="text"
                value="{{ old('name') }}"
                autocomplete="name"
                maxlength="255"
                required
                autofocus
            >

            <label for="email">Email address</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                autocomplete="email"
                maxlength="255"
                required
            >

            <label for="password">Password</label>
            <input
                id="password"
                name="password"
                type="password"
                autocomplete="new-password"
                inputmode="numeric"
                pattern="[0-9]{6}"
                minlength="6"
                maxlength="6"
                title="Enter exactly 6 digits."
                required
            >
            <div class="hint">Use exactly 6 digits.</div>

            <label for="password_confirmation">Confirm password</label>
            <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                autocomplete="new-password"
                inputmode="numeric"
                pattern="[0-9]{6}"
                minlength="6"
                maxlength="6"
                title="Enter exactly 6 digits."
                required
            >

            <button class="btn" type="submit">Create Account</button>
        </form>

        <div class="links">
            Already have an account?
            <a href="{{ route('login') }}">Log In</a>
            <div class="back">
                <a href="{{ route('home') }}">â† Back to store</a>
            </div>
        </div>
    </main>
</body>
</html>
