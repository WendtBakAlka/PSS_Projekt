<?php

namespace App\Jobs;

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

    protected $rawgGameId;

    public function __construct($rawgGameId)
    {
        $this->rawgGameId = $rawgGameId;
    }

    public function handle()
    {
        Log::info("=== UpdateGameStatistics START dla gry: {$this->rawgGameId} ===");

        $ratings = UserGame::where('rawg_game_id', $this->rawgGameId)
            ->whereNotNull('rating')
            ->pluck('rating');

        $count = $ratings->count();
        Log::info("Liczba ocen w user_games: " . $count);

        $sample = UserGame::where('rawg_game_id', $this->rawgGameId)->first();

        if ($count === 0) {
            // Brak ocen – zerujemy statystyki, ale zachowujemy istniejące dane (jeśli są)
            $stat = GameStat::where('rawg_game_id', $this->rawgGameId)->first();
            if ($stat) {
                $stat->update([
                    'average_rating' => null,
                    'ratings_count'  => 0,
                    // rawg_rating pozostaje bez zmian
                ]);
            } else {
                GameStat::create([
                    'rawg_game_id'    => $this->rawgGameId,
                    'title'           => null,
                    'cover_url'       => null,
                    'average_rating'  => null,
                    'ratings_count'   => 0,
                    'rawg_rating'     => null,
                ]);
            }
            Log::info("Zaktualizowano statystyki (brak ocen) dla gry: {$this->rawgGameId}");
            return;
        }

        $avg = round($ratings->avg(), 2);

        // Pobierz ocenę z RAWG API (jeśli jeszcze nie mamy lub odświeżamy)
        $rawgRating = null;
        if ($sample && $sample->rawg_game_id) {
            $rawgRating = $this->fetchRawgRating($sample->rawg_game_id);
        }

        // Aktualizuj lub utwórz rekord
        GameStat::updateOrCreate(
            ['rawg_game_id' => $this->rawgGameId],
            [
                'title'          => $sample->title ?? null,
                'cover_url'      => $sample->cover_url ?? null,
                'average_rating' => $avg,
                'ratings_count'  => $count,
                'rawg_rating'    => $rawgRating,
            ]
        );

        Log::info("Zaktualizowano statystyki dla gry RAWG ID: {$this->rawgGameId}");
    }

    private function fetchRawgRating($rawgGameId)
    {
        try {
            $apiKey = env('RAWG_API_KEY');
            $baseUrl = env('RAWG_BASE_URL', 'https://api.rawg.io/api');

            // Dla pewności zaloguj, czy klucz istnieje
            Log::info("Próba pobrania RAWG, klucz: " . ($apiKey ? 'ustawiony' : 'BRAK'));

            $response = \Illuminate\Support\Facades\Http::get("{$baseUrl}/games/{$rawgGameId}", [
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
                    Log::warning("Brak pola 'rating' w odpowiedzi RAWG dla gry $rawgGameId");
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
