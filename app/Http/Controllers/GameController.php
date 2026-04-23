<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\UserGame;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class GameController extends Controller
{
    public function show(string $id)
    {
        $key = config('services.rawg.key') ?? env('RAWG_API_KEY');
        $resp = Http::timeout(10)->get("https://api.rawg.io/api/games/{$id}", [
            'key' => $key,
        ]);

        abort_unless($resp->ok(), 404);

        $gameData = $resp->json();

        // Znajdź lub utwórz Game w naszej bazie
        $game = Game::firstOrCreate(
            ['rawg_game_id' => (int)$id],
            [
                'title' => $gameData['name'],
                'cover_url' => $gameData['background_image'] ?? null,
                'rawg_rating' => isset($gameData['rating']) ? round($gameData['rating'] * 2, 1) : null,
            ]
        );

        // Sprawdź, czy użytkownik ma tę grę w bibliotece
        $inLibrary = UserGame::where('user_id', Auth::id())
            ->where('game_id', $game->id)
            ->first();

        return view('game-show', [
            'game' => $gameData, // dane z RAWG do widoku (niezmienione)
            'inLibrary' => $inLibrary,
            'statuses' => UserGame::statuses(),
        ]);
    }
}
