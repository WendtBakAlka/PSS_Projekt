<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Resetowanie hasła</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #0b0b0b;
            color: #f5f5f5;
        }

        .reset-card {
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

        .info-text {
            font-size: 0.875rem;
            color: #aaa;
        }
    </style>
</head>
<body>

<div class="container vh-100 d-flex justify-content-center align-items-center">
    <div class="col-md-4">
        <div class="reset-card p-4">

            <div class="text-center mb-4">
                <div class="logo">GAME<span class="text-light">LIST</span></div>
                <small class="info-text">Podaj swój email, aby zresetować hasło</small>
            </div>

            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
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

                <button type="submit" class="btn btn-red w-100 py-2">
                    Wyślij link resetujący hasło
                </button>

                <div class="text-center mt-3">
                    <a href="{{ route('login') }}">Wróć do logowania</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
