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

        // Jedno zapytanie SQL zamiast trzech
        $stats = UserGame::where('game_id', $this->gameId)
            ->selectRaw('
            COUNT(*) as total,
            COUNT(rating) as ratings_count,
            AVG(rating) as avg_rating
        ')
            ->first();

        $totalCount = $stats->total;
        $ratingsCount = $stats->ratings_count;
        $avg = $ratingsCount > 0 ? round($stats->avg_rating, 2) : null;

        Log::info("Liczba wpisów: {$totalCount}, ocen: {$ratingsCount}, średnia: {$avg}");

        if ($totalCount === 0) {
            GameStat::where('game_id', $this->gameId)->delete();
            Log::info("Usunięto statystyki (brak wpisów) dla game_id: {$this->gameId}");
            return;
        }

        GameStat::updateOrCreate(
            ['game_id' => $this->gameId],
            [
                'average_rating' => $avg,
                'ratings_count'  => $totalCount,
            ]
        );

        Log::info("Zaktualizowano statystyki dla game_id: {$this->gameId}");
    }


}
