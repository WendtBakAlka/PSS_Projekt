<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Użytkownika - GAMELIST</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Ikony Bootstrap --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        body { background-color: #0b0b0b; color: #f5f5f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar-custom { background-color: #111; border-bottom: 1px solid #1f1f1f; padding: 0.75rem 1rem; }
        .logo { font-size: 1.5rem; font-weight: bold; color: #dc3545; text-decoration: none; transition: 0.3s; }
        .logo:hover { color: #ff5c5c; }
        .profile-card { background-color: #111; border: 1px solid #1f1f1f; border-radius: 12px; box-shadow: 0 0 25px rgba(255, 0, 0, 0.1); margin-bottom: 2rem; }
        h3 { color: #dc3545; font-size: 1.25rem; margin-bottom: 1.5rem; text-transform: uppercase; letter-spacing: 1px; }

        /* Stylizacja dropdowna */
        .nav-link-user { color: #f5f5f5 !important; font-weight: 500; }
        .dropdown-menu-dark { background-color: #111; border: 1px solid #1f1f1f; box-shadow: 0 5px 15px rgba(220, 53, 69, 0.2); }
        .dropdown-item:hover { color: #fff; background: rgba(220, 53, 69, 0.1); border-left: 4px solid #dc3545; transition: 0.3s; }

        /* Formularze */
        .form-control, .form-select { background-color: #0f0f0f !important; border: 1px solid #333 !important; color: #fff !important;}
        .form-control:focus { background-color: #0f0f0f; border-color: #dc3545; box-shadow: 0 0 0 0.15rem rgba(220, 53, 69, 0.25); color: #fff; }
        .btn-red { background-color: #dc3545; border: none; color: white; padding: 0.5rem 1.5rem; transition: 0.3s; }
        .btn-red:hover { background-color: #b52a37; color: white; }
        label { margin-bottom: 0.5rem; color: #ccc; }
        .dropdown-item.text-danger:hover { color: #e99191 !important;  background: rgba(220, 53, 69, 0.1);  border-left: 4px solid #dc3545; transition: 0.3s; }
    </style>
</head>
<body>

{{-- Navbar zintegrowany z Twoim systemem --}}
<nav class="navbar navbar-expand-lg navbar-custom mb-5">
    <div class="container">
        {{-- Logo kieruje do strony games --}}
        <a href="{{ route('games') }}" class="logo">GAME<span class="text-light">LIST</span></a>

        {{-- Dropdown z profilem i wylogowaniem --}}
        <div class="ms-auto dropdown">
            <a class="nav-link nav-link-user dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                {{ auth()->user()->name ?? auth()->user()->email }}
            </a>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark">
                <li>
                    <a class="dropdown-item" href="{{ route('games') }}">
                        <i class="bi bi-controller"></i> Lista Gier
                    </a>
                </li>
                <li class="nav-item">
                    <a class="dropdown-item" href="{{ route('library.index') }}">
                        <i class="bi bi-collection me-2"></i> Biblioteka
                    </a>
                </li>
                @if(auth()->user()->is_admin)
                    <a class="dropdown-item" href="{{ route('admin.index') }}">
                        <i class="bi bi-person"></i> Panel admina
                    </a>
                @endif
                <li><hr class="dropdown-divider border-secondary"></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            Wyloguj się
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Ustawienia profilu</h2>
                <a href="{{ route('games') }}" class="btn btn-outline-light btn-sm">← Powrót do gier</a>
            </div>

            {{-- Sekcja 1: Informacje o profilu --}}
            <div class="profile-card p-4 shadow-sm">
                <h3>Informacje o profilu</h3>
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            {{-- Sekcja 2: Zmiana hasła --}}
            <div class="profile-card p-4 shadow-sm">
                <h3>Aktualizacja hasła</h3>
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            {{-- Sekcja 3: Usuwanie konta --}}
            <div class="profile-card p-4 border-danger shadow-sm">
                <h3 class="text-danger">Strefa Niebezpieczna</h3>
                <div class="max-w-xl">
                    {{-- PROSTY FORMULARZ BEZ MODALA --}}
                    <form method="POST" action="{{ route('profile.destroy') }}" id="deleteAccountForm">
                        @csrf
                        @method('DELETE')

                        <h4 class="text-danger mb-3">{{ __('Czy na pewno chcesz usunąć konto?') }}</h4>
                        <p class="text-secondary small mb-4">
                            {{ __('Wprowadź hasło, aby potwierdzić trwałe usunięcie konta.') }}
                        </p>

                        <div class="mb-4">
                            <label for="password" class="form-label">{{ __('Hasło') }}</label>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                class="form-control @error('password', 'userDeletion') is-invalid @enderror"
                                placeholder="{{ __('Hasło') }}"
                                style="background-color: #0f0f0f; border: 1px solid #333; color: #fff;"
                                required
                            />
                            @error('password', 'userDeletion')
                                <div class="text-danger small mt-2">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-red" onclick="return confirm('Na pewno usunąć konto? Tej operacji nie można cofnąć.')">
                                {{ __('Usuń konto na zawsze') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Skrypt JS - niezbędny do działania dropdowna --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>