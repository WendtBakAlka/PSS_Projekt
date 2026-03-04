<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Potwierdzenie hasła</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #0b0b0b;
            color: #f5f5f5;
        }

        .confirm-card {
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

        .logo {
            font-size: 2rem;
            font-weight: bold;
            color: #dc3545;
        }

        a {
            color: #dc3545;
            text-decoration: none;
        }

        a:hover {
            color: #ff5c5c;
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container vh-100 d-flex justify-content-center align-items-center">
    <div class="col-md-4">
        <div class="confirm-card p-4">

            <div class="text-center mb-4">
                <div class="logo">GAME<span class="text-light">LIST</span></div>
                <small class="text-white">Potwierdź swoje hasło, aby kontynuować</small>
            </div>

            <div class="mb-3 text-gray-400">
                To jest bezpieczny obszar aplikacji. Proszę potwierdź swoje hasło, aby kontynuować.
            </div>

            <form method="POST" action="{{ route('password.confirm') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Hasło</label>
                    <input type="password"
                           name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           required
                           autocomplete="current-password">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-red py-2">Potwierdź</button>
                </div>

            </form>

        </div>
    </div>
</div>

</body>
</html>
