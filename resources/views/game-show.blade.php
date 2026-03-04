<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $game['name'] ?? 'Szczegóły gry' }} - GAMELIST</title>

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
        .card-custom { background-color: #111; border: 1px solid #1f1f1f; border-radius: 12px; box-shadow: 0 0 25px rgba(255, 0, 0, 0.05); }
        .btn-red { background-color: #dc3545; border: none; color: white; }
        .btn-red:hover { background-color: #b52a37; color: white; }
        .search-input { background-color: #1a1a1a; border: 1px solid #333; color: white; }
        .search-input:focus { background-color: #1a1a1a; color: white; border-color: #dc3545; box-shadow: none; }

        /* Szczegóły */
        .game-img-big { width: 100%; height: 320px; object-fit: cover; border-radius: 12px; border: 1px solid #1f1f1f; }
        .tag-badge { background: rgba(220, 53, 69, 0.12); color: #ff7a87; border: 1px solid rgba(220, 53, 69, 0.35); }
        .muted { color: #9aa0a6; }
        .desc { white-space: pre-wrap; color: #d9d9d9; }
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
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="bi bi-person"></i> Twój Profil
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
                            <button type="submit" class="dropdown-item text-danger">Wyloguj się</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    @php
        $img   = $game['background_image'] ?? null;
        $title = $game['name'] ?? 'Gra';
        $desc  = $game['description_raw'] ?? '';
        $released = $game['released'] ?? null;
        $metacritic = $game['metacritic'] ?? null;
    @endphp

    <div class="container mt-5">

        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-4">
            <div>
                <h1 class="mb-1">{{ $title }}</h1>
                <div class="muted small">
                    RAWG ID: {{ $game['id'] }}
                    @if($released) • Premiera: {{ $released }} @endif
                    @if($metacritic) • Metacritic: {{ $metacritic }} @endif
                </div>
            </div>

            <div class="d-flex gap-2">
                <a class="btn btn-outline-secondary text-white" href="{{ route('games') }}">
                    <i class="bi bi-arrow-left"></i> Wróć
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
            </div>
        @endif

        {{-- SEKCJA SZCZEGÓŁÓW --}}
        <div class="card card-custom mb-4">
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-12 col-lg-5">
                        @if($img)
                            <img src="{{ $img }}" class="game-img-big" alt="{{ $title }}">
                        @else
                            <div class="game-img-big d-flex align-items-center justify-content-center muted">Brak obrazka</div>
                        @endif

                        @if(!empty($game['genres']))
                            <div class="mt-3 d-flex flex-wrap gap-2">
                                @foreach($game['genres'] as $g)
                                    <span class="badge tag-badge">{{ $g['name'] }}</span>
                                @endforeach
                            </div>
                        @endif

                        @if(!empty($game['platforms']))
                            <div class="mt-2 small muted">
                                Platformy:
                                @foreach($game['platforms'] as $p)
                                    <span class="me-2">{{ $p['platform']['name'] ?? '' }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="col-12 col-lg-7">
                        <h4 class="text-danger mb-3">Opis</h4>
                        @if($desc)
                            <div class="desc">{{ $desc }}</div>
                        @else
                            <div class="muted">Brak opisu.</div>
                        @endif

                        @if(!empty($game['website']))
                            <div class="mt-3">
                                <a class="btn btn-outline-light btn-sm" href="{{ $game['website'] }}" target="_blank" rel="noopener">
                                    <i class="bi bi-box-arrow-up-right"></i> Strona gry
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- SEKCJA: DODAJ DO BIBLIOTEKI --}}
        <div class="card card-custom mb-5">
            <div class="card-body">
                <h3 class="mb-4">Twoja biblioteka</h3>

                {{-- GŁÓWNY FORMULARZ --}}
                @if($inLibrary)
                    {{-- FORMULARZ AKTUALIZACJI --}}
                    <form method="POST" action="{{ route('library.update', $inLibrary->id) }}" class="row g-3" id="libraryForm">
                        @csrf
                        @method('PUT')
                @else
                    {{-- FORMULARZ DODAWANIA --}}
                    <form method="POST" action="{{ route('library.store') }}" class="row g-3" id="libraryForm">
                        @csrf
                @endif

                        <input type="hidden" name="rawg_game_id" value="{{ $game['id'] }}">
                        <input type="hidden" name="title" value="{{ $title }}">
                        <input type="hidden" name="cover_url" value="{{ $img }}">

                        <div class="col-md-5">
                            <label class="form-label muted">Status</label>
                            <select name="status" class="form-select search-input" required>
                                @foreach($statuses as $k => $label)
                                    <option value="{{ $k }}" @selected(($inLibrary->status ?? 'to_play') === $k)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label muted">Ocena (1–10)</label>
                            <input type="number" min="1" max="10" name="rating"
                                   class="form-control search-input"
                                   value="{{ old('rating', $inLibrary->rating ?? '') }}"
                                   placeholder="np. 8">
                        </div>

                        <div class="col-md-4 d-flex align-items-end gap-2">
                            <button class="btn btn-red w-100" type="submit">
                                <i class="bi bi-bookmark-plus"></i>
                                {{ $inLibrary ? 'Zapisz zmiany' : 'Dodaj do biblioteki' }}
                            </button>

                            @if($inLibrary)
                                <button type="button" class="btn btn-outline-danger" onclick="deleteGame()">
                                    <i class="bi bi-trash"></i>
                                </button>
                            @endif
                        </div>
                    </form>

                {{-- OSOBNY FORMULARZ DO USUWANIA --}}
                @if($inLibrary)
                    <form method="POST" action="{{ route('library.destroy', $inLibrary->id) }}" id="deleteForm" style="display: none;">
                        @csrf
                        @method('DELETE')
                    </form>
                @endif

                @if($inLibrary)
                    <div class="mt-3 muted small">
                        Masz tę grę w bibliotece: status <b class="text-light">{{ $statuses[$inLibrary->status] ?? $inLibrary->status }}</b>
                        @if($inLibrary->rating) • ocena <b class="text-light">{{ $inLibrary->rating }}/10</b> @endif
                    </div>
                @endif
            </div>
        </div>

    </div>

@else
    <div class="container mt-5">
        <div class="alert alert-danger">Zaloguj się!</div>
    </div>
@endif

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function deleteGame() {
    if(confirm('Czy na pewno chcesz usunąć grę "{{ $title }}" z biblioteki?')) {
        document.getElementById('deleteForm').submit();
    }
}
</script>
</body>
</html>