<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\UserGame;
use App\Jobs\EnrichGameMetadataJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class GameController extends Controller
{
    public function show(string $id)
    {
        $game = Game::where('rawg_game_id', (int) $id)->first();

        if (!$game) {
            $key = config('rawg.key') ?? env('RAWG_API_KEY');

            $resp = Http::timeout(10)->get(config('rawg.base_url') . "/games/{$id}", [
                'key' => $key,
            ]);

            abort_unless($resp->ok(), 404);

            $data = $resp->json();

            $game = Game::create([
                'rawg_game_id' => (int) $id,
                'title' => $data['name'] ?? 'Brak tytułu',
                'cover_url' => $data['background_image'] ?? null,
                'rawg_rating' => isset($data['rating']) ? round($data['rating'] * 2, 1) : null,
                'description' => $data['description_raw'] ?? null,
                'genres' => collect($data['genres'] ?? [])->pluck('name')->values()->toArray(),
                'platforms' => collect($data['platforms'] ?? [])
                    ->map(fn ($item) => $item['platform']['name'] ?? null)
                    ->filter()
                    ->values()
                    ->toArray(),
            ]);
        }

        if (empty($game->description) || empty($game->genres) || empty($game->platforms)) {
            EnrichGameMetadataJob::dispatch($game->id)->onQueue('metadata');
        }

        $inLibrary = UserGame::where('user_id', Auth::id())
            ->where('game_id', $game->id)
            ->first();

        return view('game-show', [
            'game' => [
                'id' => $game->rawg_game_id,
                'name' => $game->title,
                'background_image' => $game->cover_url,
                'rating' => $game->rawg_rating ? $game->rawg_rating / 2 : null,
                'description_raw' => $game->description,
                'genres' => collect($game->genres ?? [])->map(fn ($name) => ['name' => $name])->toArray(),
                'platforms' => collect($game->platforms ?? [])->map(fn ($name) => [
                    'platform' => ['name' => $name],
                ])->toArray(),
            ],
            'inLibrary' => $inLibrary,
            'statuses' => UserGame::statuses(),
        ]);
    }
}
