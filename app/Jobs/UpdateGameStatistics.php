<?php

namespace App\Jobs;

use App\Models\UserGame;
use App\Models\GameStat;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
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

        // Pobierz przykładowy wpis, żeby mieć tytuł i okładkę (jeśli istnieje)
        $sample = UserGame::where('rawg_game_id', $this->rawgGameId)->first();

        // Oblicz średnią (zaokrągloną do 2 miejsc po przecinku)
        $avg = round($ratings->avg(), 2);

        // Aktualizuj lub utwórz rekord w game_stats
        GameStat::updateOrCreate(
            ['rawg_game_id' => $this->rawgGameId],
            [
                'title'          => $sample->title ?? null,
                'cover_url'      => $sample->cover_url ?? null,
                'average_rating' => $avg,
                'ratings_count'  => $count,
            ]
        );

        Log::info("Zaktualizowano statystyki dla gry RAWG ID: {$this->rawgGameId}");
    }
}
