<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Strona startowa</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #0b0b0b;
            color: #f5f5f5;
        }

        .start-card {
            background-color: #111;
            border: 1px solid #1f1f1f;
            border-radius: 12px;
            box-shadow: 0 0 25px rgba(255, 0, 0, 0.15);
        }

        .btn-red {
            background-color: #dc3545;
            border: none;
        }

        .btn-red:hover {
            background-color: #b52a37;
        }

        a.btn-outline-red {
            border: 1px solid #dc3545;
            color: #dc3545;
            text-decoration: none;
        }

        a.btn-outline-red:hover {
            background-color: #dc3545;
            color: #fff;
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
        <div class="start-card p-4 text-center">

            <div class="mb-4">
                <div class="logo">GAME<span class="text-light">LIST</span></div>
                <small class="text-white">Wybierz opcję, aby rozpocząć</small>
            </div>

            <div class="d-grid gap-3">
                <!-- Zawsze pokazuj logowanie i rejestrację, bez sprawdzania stanu logowania -->
                <a href="{{ route('login') }}" class="btn btn-red py-2">Logowanie</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn btn-outline-red py-2">Rejestracja</a>
                @endif
            </div>

        </div>
    </div>
</div>

</body>
</html>