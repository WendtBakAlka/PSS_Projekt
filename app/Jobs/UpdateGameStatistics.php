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

    protected $gameId;

    public function __construct($gameId)
    {
        $this->gameId = $gameId;
    }

    public function handle()
    {
        Log::info("=== UpdateGameStatistics START dla game_id: {$this->gameId} ===");

        // Pobierz wszystkie wpisy dla tej gry (nie tylko z ocenami)
        $totalCount = UserGame::where('game_id', $this->gameId)->count();
        // Pobierz tylko oceny (nie null)
        $ratings = UserGame::where('game_id', $this->gameId)
            ->whereNotNull('rating')
            ->pluck('rating');
        $ratingsCount = $ratings->count();
        $avg = $ratingsCount > 0 ? round($ratings->avg(), 2) : null;

        Log::info("Liczba wszystkich wpisów w user_games: {$totalCount}, w tym z ocenami: {$ratingsCount}, średnia: {$avg}");

        $game = Game::find($this->gameId);
        if (!$game) {
            Log::error("Gra o ID {$this->gameId} nie istnieje w tabeli games");
            return;
        }

        if ($totalCount === 0) {
            // Brak jakichkolwiek wpisów dla tej gry – usuń rekord z game_stats
            GameStat::where('game_id', $this->gameId)->delete();
            Log::info("Usunięto statystyki (brak wpisów w user_games) dla game_id: {$this->gameId}");
            return;
        }

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
                'ratings_count'  => $totalCount,  // liczba wszystkich wpisów, nie tylko z oceną
            ]
        );

        Log::info("Zaktualizowano statystyki dla game_id: {$this->gameId} (ratings_count = {$totalCount}, average_rating = {$avg})");
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
