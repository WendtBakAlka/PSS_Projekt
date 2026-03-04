<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - GAMELIST</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #0b0b0b; color: #f5f5f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        .sidebar {
            min-width: 250px;
            max-width: 250px;
            min-height: 100vh;
            background-color: #111;
            border-right: 1px solid #1f1f1f;
        }
        
        .sidebar-brand { padding: 20px; font-weight: bold; color: #dc3545; font-size: 1.25rem; text-decoration: none; display: block; }
        
        .nav-link { color: #ccc; padding: 12px 20px; transition: 0.3s; }
        .nav-link:hover { color: #fff; background: rgba(220, 53, 69, 0.1); border-left: 4px solid #dc3545; }
        .nav-link i { margin-right: 10px; color: #dc3545; }

        .content-area { flex-grow: 1; padding: 30px; }
        
        .admin-card { background: #161616; border: 1px solid #222; border-radius: 8px; }
    </style>
</head>
<body>

<div class="d-flex">
    <div class="sidebar">
        <a href="{{ route('admin.index') }}" class="sidebar-brand">GAME<span class="text-light">LIST</span> <small class="text-muted">ADMIN</small></a>
        <nav class="nav flex-column mt-4">
            <a class="nav-link" href="{{ route('admin.index') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
            <a class="nav-link" href="{{ route('admin.users') }}"><i class="bi bi-people"></i> Użytkownicy</a>
            
            <hr class="mx-3 border-secondary my-3">
            
            <a class="nav-link" href="{{ route('profile.edit') }}">
                <i class="bi bi-person"></i> Twój Profil
            </a>
            <a class="nav-link" href="{{ route('library.index') }}">
                <i class="bi bi-collection"></i> Biblioteka
            </a>

            <a class="nav-link" href="{{ route('games') }}">
                <i class="bi bi-search"></i> Wyszukiwarka
            </a>

            <hr class="mx-3 border-secondary my-3">
            
            <form action="{{ route('logout') }}" method="POST" class="mt-2 px-3">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm w-100">Wyloguj</button>
            </form>
        </nav>
    </div>

    <div class="content-area">
        @if(session('success'))
            <div class="alert alert-success bg-dark text-success border-success mb-4">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger bg-dark text-danger border-danger mb-4">{{ session('error') }}</div>
        @endif

        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>