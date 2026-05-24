<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Rankingi gier - GAMELIST</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        /* Twoje style (bez zmian) */
        body { background-color: #0b0b0b; color: #f5f5f5; }
        .navbar-custom { background-color: #111; border-bottom: 1px solid #1f1f1f; padding: 0.75rem 1rem; }
        .logo { font-size: 1.5rem; font-weight: bold; color: #dc3545; text-decoration: none; }
        .logo:hover { color: #ff5c5c; }
        .nav-link-user { color: #f5f5f5 !important; }
        .dropdown-menu-dark { background-color: #111; border: 1px solid #1f1f1f; box-shadow: 0 5px 15px rgba(220, 53, 69, 0.1); }
        .dropdown-item:hover { color: #fff; background: rgba(220, 53, 69, 0.1); border-left: 4px solid #dc3545; transition: 0.3s; }
        .btn-red { background-color: #dc3545; border: none; color: white; }
        .btn-red:hover { background-color: #b52a37; color: white; }
        .nav-tabs .nav-link.active { background-color: #dc3545; color: white; border-color: #dc3545; }
        .nav-tabs .nav-link { color: #ccc; background-color: #111; border-color: #333; }

        /* Tabela */
        .table-gamelist {
            width: 100%;
            background-color: #111;
            color: #f5f5f5;
            border-collapse: collapse;
        }
        .table-gamelist th, .table-gamelist td {
            padding: 12px 8px;
            border: 1px solid #333;
            vertical-align: middle;
        }
        .table-gamelist thead th {
            background-color: #1a1a1a;
            color: #dc3545;
            border-bottom: 2px solid #dc3545;
        }
        .table-gamelist tbody tr:hover {
            background-color: #1f1f1f;
        }
        .table-gamelist a:not(.btn) {
            color: #ff7a87;
            text-decoration: none;
        }
        .table-gamelist a:not(.btn):hover {
            color: #dc3545;
        }

        .pagination .page-link {
            background-color: #111;
            border-color: #333;
            color: #f5f5f5;
        }
        .pagination .page-link:hover {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
        }
        .pagination .active .page-link {
            background-color: #dc3545;
            border-color: #dc3545;
        }
    </style>
</head>
<body>
@if(auth()->check())
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a href="{{ route('games') }}" class="logo">GAME<span class="text-light">LIST</span></a>
            <a href="{{ route('rankings.topRated') }}" class="text-warning text-decoration-none ms-5">Rankingi <i class="bi bi-trophy"></i></a>
            <div class="ms-auto dropdown">
                <a class="nav-link nav-link-user dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                    {{ auth()->user()->name ?? auth()->user()->email }}
                </a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark">
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person"></i> Twój Profil</a></li>
                    <li><a class="dropdown-item" href="{{ route('library.index') }}"><i class="bi bi-collection me-2"></i> Biblioteka</a></li>
                    @if(auth()->user()->is_admin)
                        <li><a class="dropdown-item" href="{{ route('admin.index') }}"><i class="bi bi-person"></i> Panel admina</a></li>
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
    <div class="container mt-4">
        <h1 class="mb-4">Rankingi gier</h1>
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item"><a class="nav-link {{ $activeTab == 'rated' ? 'active' : '' }}" href="{{ route('rankings.topRated') }}">🏆 Najlepiej oceniane</a></li>
            <li class="nav-item"><a class="nav-link {{ $activeTab == 'popular' ? 'active' : '' }}" href="{{ route('rankings.mostPopular') }}">🔥 Najpopularniejsze</a></li>
        </ul>
        <div class="table-responsive">
            <table class="table-gamelist">
                <thead>
                <tr><th>#</th><th>Tytuł</th><th>Okładka</th>
                    @if($activeTab == 'rated')
                        <th>Średnia ocen</th>
                        <th>Liczba ocen</th>
                    @else
                        <th>Liczba dodań</th>
                        <th>Średnia ocen</th>
                    @endif
                    <th>Ocena RAWG</th>
                    <th>Akcje</th>
                </tr>
                </thead>
                <tbody>
                @forelse($games as $index => $stat)
                    @php
                        $game = $stat->game;
                        $title = $game->title ?? 'Brak tytułu';
                        $cover = $game->cover_url ?? 'https://via.placeholder.com/50x50?text=No+Img';
                        $rawgRating = $game->rawg_rating ?? null;
                    @endphp
                    <tr>
                        <td>{{ $games->firstItem() + $index }}</td>
                        <td class="fw-bold">{{ $title }}</td>
                        <td>
                            <img src="{{ $cover }}" width="50" height="50" style="object-fit: cover; border-radius: 8px;">
                        </td>
                        @if($activeTab == 'rated')
                            <td>
                                @if($stat->average_rating !== null)
                                    {{ number_format($stat->average_rating, 1) }}/10
                                @else
                                    --/10
                                @endif
                            </td>
                            <td>{{ $stat->ratings_count }}</td>
                        @else
                            <td>{{ $stat->ratings_count }}</td>
                            <td>
                                @if($stat->average_rating !== null)
                                    {{ number_format($stat->average_rating, 1) }}/10
                                @else
                                    --/10
                                @endif
                            </td>
                        @endif
                        <td>
                            @if($rawgRating)
                                {{ $rawgRating }}/10
                            @else
                                --
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('games.show', $game->rawg_game_id) }}" class="btn btn-sm btn-outline-light">
                                <i class="bi bi-info-circle"></i> Szczegóły
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center">Brak gier.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($games->hasPages())
            <div class="d-flex justify-content-center mt-4">{{ $games->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
@else
    <div class="container mt-5"><div class="alert alert-danger">Zaloguj się.</div></div>
@endif
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
