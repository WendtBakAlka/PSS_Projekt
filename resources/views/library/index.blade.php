<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Biblioteka - GAMELIST</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Ikony Bootstrap --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        body { background-color: #0b0b0b; color: #f5f5f5; }
        .navbar-custom { background-color: #111; border-bottom: 1px solid #1f1f1f; padding: 0.75rem 1rem; }
        .logo { font-size: 1.5rem; font-weight: bold; color: #dc3545; text-decoration: none; transition: 0.3s; }
        .logo:hover { color: #ff5c5c; }
        .nav-link-user { color: #f5f5f5 !important; font-weight: 500; }
        .dropdown-menu-dark { background-color: #111; border: 1px solid #1f1f1f; box-shadow: 0 5px 15px rgba(220, 53, 69, 0.1); }
        .dropdown-item:hover { color: #fff; background: rgba(220, 53, 69, 0.1); border-left: 4px solid #dc3545; transition: 0.3s; }

        /* Karty i UI */
        .card-custom { background-color: #111; border: 1px solid #1f1f1f; border-radius: 12px; box-shadow: 0 0 25px rgba(255, 0, 0, 0.05); transition: transform 0.2s; }
        .card-custom:hover { transform: translateY(-5px); border-color: #dc3545; }
        .card-custom-search { background-color: #111; border: 1px solid #1f1f1f; border-radius: 12px; box-shadow: 0 0 25px rgba(255, 0, 0, 0.05); transition: transform 0.2s; }
        .card-custom-search:hover{ border-color: #dc3545; }
        .btn-red { background-color: #dc3545; border: none; color: white; }
        .btn-red:hover { background-color: #b52a37; color: white; }

        .search-input { background-color: #1a1a1a; border: 1px solid #333; color: white; }
        .search-input:focus { background-color: #1a1a1a; color: white; border-color: #dc3545; box-shadow: none; }
        .example::placeholder { color: #878787 }

        .game-img { height: 200px; object-fit: cover; border-top-left-radius: 12px; border-top-right-radius: 12px; }
        .badge-rating {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: rgba(0,0,0,0.8);
            border: 1px solid #dc3545;
            color: #dc3545;
            padding: 5px 10px;
        }
        .muted { color: #9aa0a6; }
        h3 { color: #c4c4c4 }
    </style>
</head>
<body>

@if(auth()->check())
    {{-- Panel Nawigacyjny --}}
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a href="{{ route('games') }}" class="logo">GAME<span class="text-light">LIST</span></a>
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
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="bi bi-person"></i> Twój Profil
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
                            <button type="submit" class="dropdown-item text-danger">Wyloguj się</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">

        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-4">
            <div>
                <h1 class="mb-1">Biblioteka</h1>
                <div class="muted small">Filtruj po statusie i ocenach, zmieniaj status gry.</div>
            </div>

            <a class="btn btn-outline-light" href="{{ route('games') }}">
                <i class="bi bi-search"></i> Wyszukiwarka
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- FILTRY --}}
        <div class="card card-custom-search mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label muted">Status</label>
                        <select name="status" class="form-select search-input">
                            <option value="">(wszystkie)</option>
                            @foreach($statuses as $k => $label)
                                <option value="{{ $k }}" @selected(($filters['status'] ?? '') === $k)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label muted">Min ocena</label>
                        <input name="min_rating" type="number" min="1" max="10"
                               class="example form-control search-input"
                               value="{{ $filters['min_rating'] ?? '' }}" placeholder="np. 7">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label muted">Sortowanie</label>
                        <select name="sort" class="form-select search-input">
                            <option value="updated" @selected(($filters['sort'] ?? 'updated')==='updated')>Ostatnio zmieniane</option>
                            <option value="rating_desc" @selected(($filters['sort'] ?? '')==='rating_desc')>Ocena: malejąco</option>
                            <option value="rating_asc" @selected(($filters['sort'] ?? '')==='rating_asc')>Ocena: rosnąco</option>
                        </select>
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <button class="btn btn-red px-4" type="submit">
                            <i class="bi bi-funnel"></i> Filtruj
                        </button>
                        <a class="btn btn-outline-secondary text-white" href="{{ route('library.index') }}">Wyczyść</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- LISTA --}}
        <div class="row g-4">
            @forelse($items as $it)
                <div class="col-md-6 col-lg-4">
                    <div class="card card-custom h-100">
                        <div style="position: relative;">
                            @php
                                $img = $it->cover_url ?: 'https://via.placeholder.com/400x200?text=No+Image';
                            @endphp
                            <img src="{{ $img }}" class="card-img-top game-img" alt="{{ $it->title }}">
                            @if($it->rating)
                                <span class="badge rounded-pill badge-rating">⭐ {{ $it->rating }}/10</span>
                            @endif
                        </div>

                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title text-danger text-truncate">{{ $it->title }}</h5>

                            <div class="muted small mb-3">
                                Status: <span class="text-light">{{ $statuses[$it->status] ?? $it->status }}</span>
                            </div>

                            <a href="{{ route('games.show', $it->rawg_game_id) }}" class="btn btn-outline-light btn-sm mb-3">
                                <i class="bi bi-info-circle"></i> Szczegóły
                            </a>

                            {{-- ZMIANA STATUSU/OCENY --}}
                            <form method="POST" action="{{ route('library.update', $it->id) }}" class="row g-2 mt-auto">
                                @csrf
                                @method('PATCH')

                                <div class="col-7">
                                    <select name="status" class="form-select form-select-sm search-input" required>
                                        @foreach($statuses as $k => $label)
                                            <option value="{{ $k }}" @selected($it->status === $k)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-5">
                                    <input type="number" min="1" max="10" name="rating"
                                           class="form-control form-control-sm search-input"
                                           value="{{ $it->rating ?? '' }}" placeholder="ocena">
                                </div>

                                <div class="col-12 d-flex gap-2">
                                    <button class="btn btn-red btn-sm w-100" type="submit">
                                        <i class="bi bi-save"></i> Zapisz
                                    </button>
                                </div>
                            </form>

                            <form method="POST" action="{{ route('library.destroy', $it->id) }}" class="mt-2">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm w-100" type="submit">
                                    <i class="bi bi-trash"></i> Usuń
                                </button>
                            </form>

                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center text-muted">
                    <h3>Biblioteka pusta :(</h3>
                    <div class="mt-2">
                        <a class="btn btn-red" href="{{ route('games') }}"><i class="bi bi-search"></i> Idź wyszukać gry</a>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="row mt-5 mb-5">
            <div class="col-12 d-flex justify-content-center">
                {{ $items->links() }}
            </div>
        </div>

    </div>

@else
    <div class="container mt-5">
       <div class="alert alert-danger">Zaloguj się!</div>
    </div>
@endif

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
