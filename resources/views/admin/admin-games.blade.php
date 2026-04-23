@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <h2 class="mb-4 text-light">Lista <span class="text-danger">Gier</span></h2>

        <div class="admin-card" style="background: #111; border: 1px solid #1f1f1f; border-radius: 12px; overflow: hidden;">
            <table class="table table-dark table-hover mb-0">
                <thead>
                <tr style="background: #1a1a1a;">
                    <th class="p-3">ID</th>
                    <th class="p-3">RAWG ID</th>
                    <th class="p-3">Tytuł</th>
                    <th class="p-3">Okładka</th>
                    <th class="p-3">Ocena RAWG</th>
                    <th class="p-3">Data utworzenia</th>
                    <th class="p-3">Data aktualizacji</th>
                    <th class="p-3 text-end">Akcje</th>  <!-- NOWA KOLUMNA -->
                </tr>
                </thead>
                <tbody>
                @forelse($games as $game)
                    <tr style="border-bottom: 1px solid #1f1f1f;">
                        <td class="p-3 text-secondary">#{{ $game->id }}</td>
                        <td class="p-3">{{ $game->rawg_game_id }}</td>
                        <td class="p-3">{{ $game->title }}</td>
                        <td class="p-3">
                            @if($game->cover_url)
                                <img src="{{ $game->cover_url }}" width="40" height="40" style="object-fit: cover; border-radius: 6px;">
                            @else
                                <span class="text-muted">brak</span>
                            @endif
                        </td>
                        <td class="p-3">
                            @if($game->rawg_rating)
                                {{ $game->rawg_rating }}/10
                            @else
                                <span class="text-muted">brak</span>
                            @endif
                        </td>
                        <td class="p-3 text-secondary">{{ $game->created_at ? $game->created_at->format('Y-m-d H:i') : '-' }}</td>
                        <td class="p-3 text-secondary">{{ $game->updated_at ? $game->updated_at->format('Y-m-d H:i') : '-' }}</td>
                        <td class="p-3 text-end">
                            <a href="{{ route('games.show', $game->rawg_game_id) }}" class="btn btn-sm btn-outline-light">
                                <i class="bi bi-info-circle"></i> Szczegóły
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center p-3 text-muted">Brak gier w tabeli `games`.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
