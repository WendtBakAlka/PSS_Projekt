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

        $anyUserGame = UserGame::where('rawg_game_id', $this->rawgGameId)->exists();

        if (!$anyUserGame) {
            GameStat::where('rawg_game_id', $this->rawgGameId)->delete();
            Log::info("Brak wpisów w user_games, usunięto GameStat dla gry: {$this->rawgGameId}");
            return;
        }

        $ratings = UserGame::where('rawg_game_id', $this->rawgGameId)
            ->whereNotNull('rating')
            ->pluck('rating');

        $count = $ratings->count();
        Log::info("Liczba ocen w user_games: " . $count);

        $sample = UserGame::where('rawg_game_id', $this->rawgGameId)->first();

        $avg = $count > 0 ? round($ratings->avg(), 2) : null;

        GameStat::updateOrCreate(
            ['rawg_game_id' => $this->rawgGameId],
            [
                'title'          => $sample->title,
                'cover_url'      => $sample->cover_url,
                'average_rating' => $avg,
                'ratings_count'  => $count,
            ]
        );

        Log::info("Zaktualizowano statystyki dla gry RAWG ID: {$this->rawgGameId}");
    }
}
