<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Logowanie</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #0b0b0b;
            color: #f5f5f5;
        }

        .login-card {
            background-color: #111;
            border: 1px solid #1f1f1f;
            border-radius: 12px;
            box-shadow: 0 0 25px rgba(255, 0, 0, 0.15);
        }

        .form-control {
            background-color: #0f0f0f;
            border: 1px solid #333;
            color: #fff;
        }

        .form-control:focus {
            background-color: #0f0f0f;
            color: #fff;
            border-color: #dc3545;
            box-shadow: 0 0 0 0.15rem rgba(220, 53, 69, 0.25);
        }

        .btn-red {
            background-color: #dc3545;
            border: none;
        }

        .btn-red:hover {
            background-color: #b52a37;
        }

        a {
            color: #dc3545;
            text-decoration: none;
        }

        a:hover {
            color: #ff5c5c;
            text-decoration: underline;
        }

        .logo {
            font-size: 2rem;
            font-weight: bold;
            color: #dc3545;
        }
    </style>
</head>
<body>

<div class="container vh-100 d-flex justify-content-center align-items-center">
    <div class="col-md-4">
        <div class="login-card p-4">

            <div class="text-center mb-4">
                <div class="logo">GAME<span class="text-light">LIST</span></div>
                <small class="color:#ffffff">Zaloguj się do panelu</small>
            </div>

            <!-- NORMALNY FORMULARZ SESYJNY -->
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email"
                           name="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}"
                           required
                           autofocus>

                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Hasło</label>
                    <input type="password"
                           name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           required>

                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" name="remember" class="form-check-input">
                    <label class="form-check-label">Zapamiętaj mnie</label>
                </div>

                <button type="submit" class="btn btn-red w-100 py-2">
                    Zaloguj się
                </button>

                @if (Route::has('password.request'))
                    <div class="text-center mt-3">
                        <a href="{{ route('password.request') }}">Nie pamiętasz hasła?</a>
                    </div>
                @endif
            </form>
            <!-- KONIEC FORMULARZA -->

        </div>
    </div>
</div>

</body>
</html>