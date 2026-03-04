<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Wyszukiwarka gier - GAMELIST</title>

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
        .btn-red { background-color: #dc3545; border: none; color: white; }
        .btn-red:hover { background-color: #b52a37; color: white; }
        .mb-1, .mb-0{ color: white; }

        /* Styl dla inputa */
        .search-input { background-color: #1a1a1a; border: 1px solid #333; color: white; }
        .search-input:focus { background-color: #1a1a1a; color: white; border-color: #dc3545; box-shadow: none; }

        /* Obrazek gry */
        .game-img { height: 200px; object-fit: cover; border-top-left-radius: 12px; border-top-right-radius: 12px; }

        /* POPRAWIONY BADGE OCENY */
        .badge-rating {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: rgba(0,0,0,0.8);
            border: 1px solid #dc3545;
            color: #dc3545;
            padding: 5px 10px; /* Dodany padding dla lepszego wyglądu */
        }

        #searchInput::placeholder{ color: #8a8a8a; }
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

    <div class="container mt-5">

        {{-- SEKCJA WYSZUKIWANIA --}}
        <div class="row mb-5 justify-content-center">
            <div class="col-md-8 text-center">
                <h1 class="mb-4">Znajdź swoją grę</h1>
                <div class="input-group mb-3">
                    <input type="text" id="searchInput" class="form-control form-control-lg search-input" placeholder="Wpisz nazwę gry...">
                    <button class="btn btn-red px-4" type="button" onclick="searchGames(1)">
                        <i class="bi bi-search"></i> Szukaj
                    </button>
                </div>
            </div>
        </div>

        {{-- SEKCJA WYNIKÓW --}}
        <div id="loading" class="text-center d-none">
            <div class="spinner-border text-danger" role="status"></div>
        </div>

        <div class="row g-4" id="games-container">
            {{-- Tutaj JavaScript wrzuci wyniki --}}
        </div>

        {{-- PAGINACJA --}}
        <div class="row mt-5 mb-5 d-none" id="pagination-container">
            <div class="col-12 d-flex justify-content-between">
                <button id="prevBtn" class="btn btn-outline-secondary text-white" onclick="changePage(-1)" disabled>
                    <i class="bi bi-arrow-left"></i> Poprzednia
                </button>
                <span class="align-self-center text-secondary" id="pageInfo">Strona 1</span>
                <button id="nextBtn" class="btn btn-red" onclick="changePage(1)">
                    Następna <i class="bi bi-arrow-right"></i>
                </button>
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
    let currentPage = 1;
    let currentQuery = '';

    document.getElementById('searchInput').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            searchGames(1);
        }
    });

    async function searchGames(page) {
        const query = document.getElementById('searchInput').value;
        const container = document.getElementById('games-container');
        const loading = document.getElementById('loading');
        const pagination = document.getElementById('pagination-container');

        if (!query) return;

        if(page === 1) {
            currentPage = 1;
            currentQuery = query;
        }

        loading.classList.remove('d-none');
        container.innerHTML = '';
        pagination.classList.add('d-none');

        try {
            const response = await fetch(`/api/games/search?search=${encodeURIComponent(currentQuery)}&page=${page}`);
            const data = await response.json();

            loading.classList.add('d-none');

            if (data.results && data.results.length > 0) {
                renderGames(data.results);
                updatePagination(data.next, data.previous, page);
            } else {
                container.innerHTML = '<div class="col-12 text-center text-muted"><h3>Nie znaleziono gier :(</h3></div>';
            }

        } catch (error) {
            console.error(error);
            loading.classList.add('d-none');
            container.innerHTML = '<div class="alert alert-danger">Wystąpił błąd podczas pobierania danych.</div>';
        }
    }

    // POPRAWIONA FUNKCJA RENDERUJĄCA
    function renderGames(games) {
        const container = document.getElementById('games-container');

        games.forEach(game => {
            // Przeliczanie oceny z 5 na 10
            const rating10 = (game.rating * 2).toFixed(1);

            const image = game.background_image ? game.background_image : 'https://via.placeholder.com/400x200?text=No+Image';
            const year = game.released ? game.released.substring(0, 4) : 'TBA';
            const detailsUrl = `/games/${game.id}`;
            const html = `
                <div class="col-md-6 col-lg-4">
                    <div class="card card-custom h-100">
                        <div style="position: relative;">
                            <img src="${image}" class="card-img-top game-img" alt="${game.name}">
                            <span class="badge rounded-pill badge-rating">⭐ ${rating10}</span>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title text-danger text-truncate">${game.name}</h5>
                            <p class="card-text text-secondary mb-4">
                                <small>Data wydania: ${year}</small>
                            </p>
                            <a href="${detailsUrl}" class="btn btn-outline-light btn-sm mt-auto stretched-link">Szczegóły</a>
                        </div>
                    </div>
                </div>
            `;
            container.innerHTML += html;
        });
    }

    function updatePagination(nextUrl, prevUrl, page) {
        const pagination = document.getElementById('pagination-container');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const pageInfo = document.getElementById('pageInfo');

        pagination.classList.remove('d-none');
        pageInfo.innerText = `Strona ${page}`;

        prevBtn.disabled = !prevUrl;
        nextBtn.disabled = !nextUrl;

        if(!prevUrl) {
            prevBtn.classList.replace('btn-outline-danger', 'btn-outline-secondary');
        } else {
            prevBtn.classList.replace('btn-outline-secondary', 'btn-outline-danger');
        }
    }

    function changePage(direction) {
        currentPage += direction;
        if(currentPage < 1) currentPage = 1;
        searchGames(currentPage);
    }
</script>

</body>
</html>
