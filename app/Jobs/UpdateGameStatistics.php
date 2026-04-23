<?php

namespace App\Jobs;

use App\Models\Game;
use App\Models\UserGame;
use App\Models\GameStat;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateGameStatistics implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $gameId; // zmieniamy nazwę i typ

    public function __construct($gameId)
    {
        $this->gameId = $gameId;
    }

    public function handle()
    {
        Log::info("=== UpdateGameStatistics START dla game_id: {$this->gameId} ===");

        // Pobierz wszystkie oceny dla tej gry przez game_id
        $ratings = UserGame::where('game_id', $this->gameId)
            ->whereNotNull('rating')
            ->pluck('rating');

        $count = $ratings->count();
        Log::info("Liczba ocen w user_games: " . $count);

        // Pobierz grę z tabeli games
        $game = Game::find($this->gameId);
        if (!$game) {
            Log::error("Gra o ID {$this->gameId} nie istnieje w tabeli games");
            return;
        }

        // Sprawdź, czy istnieje już rekord w game_stats
        $stat = GameStat::where('game_id', $this->gameId)->first();

        if ($count === 0) {
            // Brak ocen – usuń rekord statystyk (jeśli istnieje)
            if ($stat) {
                $stat->delete();
                Log::info("Usunięto statystyki (brak ocen) dla game_id: {$this->gameId}");
            } else {
                Log::info("Brak ocen i brak rekordu w game_stats dla game_id: {$this->gameId}");
            }
            return;
        }

        // Są oceny – oblicz średnią
        $avg = round($ratings->avg(), 2);

        // Pobierz ocenę z RAWG przez rawg_game_id (z modelu Game)
        $rawgRating = $this->fetchRawgRating($game->rawg_game_id);
        if ($rawgRating !== null && $game->rawg_rating != $rawgRating) {
            $game->rawg_rating = $rawgRating;
            $game->save();
            Log::info("Zaktualizowano rawg_rating dla gry {$game->title} na {$rawgRating}");
        }

        // Aktualizuj lub utwórz rekord w game_stats
        GameStat::updateOrCreate(
            ['game_id' => $this->gameId],
            [
                'average_rating' => $avg,
                'ratings_count'  => $count,
            ]
        );

        Log::info("Zaktualizowano statystyki dla game_id: {$this->gameId}");
    }

    private function fetchRawgRating($rawgGameId)
    {
        try {
            $apiKey = env('RAWG_API_KEY');
            $baseUrl = env('RAWG_BASE_URL', 'https://api.rawg.io/api');

            Log::info("Próba pobrania RAWG dla rawg_game_id: {$rawgGameId}, klucz: " . ($apiKey ? 'ustawiony' : 'BRAK'));

            $response = Http::get("{$baseUrl}/games/{$rawgGameId}", [
                'key' => $apiKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $rating = $data['rating'] ?? null;
                if ($rating !== null) {
                    $converted = round($rating * 2, 1);
                    Log::info("Pobrano rating RAWG: $rating -> $converted");
                    return $converted;
                } else {
                    Log::warning("Brak pola 'rating' w odpowiedzi RAWG dla gry {$rawgGameId}");
                }
            } else {
                Log::warning("Błąd HTTP przy pobieraniu RAWG: " . $response->status());
            }
        } catch (\Exception $e) {
            Log::error("Wyjątek w fetchRawgRating: " . $e->getMessage());
        }
        return null;
    }
}
