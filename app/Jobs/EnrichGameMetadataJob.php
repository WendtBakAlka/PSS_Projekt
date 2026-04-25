<?php

namespace App\Jobs;

use App\Models\Game;
use App\Services\RawgService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EnrichGameMetadataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $gameId;

    public function __construct(int $gameId)
    {
        $this->gameId = $gameId;
    }

    public function handle(RawgService $rawg): void
    {
        $game = Game::find($this->gameId);

        if (!$game) {
            Log::warning("Metadata job: nie znaleziono gry ID {$this->gameId}");
            return;
        }

        $data = $rawg->fetchGame((int) $game->rawg_game_id);

        if (!$data) {
            Log::warning("Metadata job: brak danych z RAWG dla {$game->rawg_game_id}");
            return;
        }

        $genres = collect($data['genres'] ?? [])
            ->pluck('name')
            ->filter()
            ->values()
            ->toArray();

        $platforms = collect($data['platforms'] ?? [])
            ->map(fn ($item) => $item['platform']['name'] ?? null)
            ->filter()
            ->values()
            ->toArray();

        $game->update([
            'title' => $data['name'] ?? $game->title,
            'description' => $data['description_raw'] ?? $data['description'] ?? $game->description,
            'genres' => $genres,
            'platforms' => $platforms,
            'rawg_rating' => isset($data['rating']) ? round($data['rating'] * 2, 1) : $game->rawg_rating,
            'cover_url' => $data['background_image'] ?? $game->cover_url,
        ]);

        Log::info("Metadata job OK dla {$game->title}", [
            'genres' => $genres,
            'platforms' => $platforms,
            'description' => !empty($game->description),
        ]);
    }
}
