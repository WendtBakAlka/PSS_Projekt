<?php

namespace App\Http\Controllers;

use App\Models\UserGame;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class GameController extends Controller
{
    public function show(string $id)
    {
        // RAWG: key masz już w .env bo używasz search endpointu
        $key = config('services.rawg.key') ?? env('RAWG_API_KEY');

        $resp = Http::timeout(10)->get("https://api.rawg.io/api/games/{$id}", [
            'key' => $key,
        ]);

        abort_unless($resp->ok(), 404);

        $game = $resp->json();

        $inLibrary = UserGame::where('user_id', Auth::id())
            ->where('rawg_game_id', (int)$id)
            ->first();

        return view('game-show', [
            'game' => $game,
            'inLibrary' => $inLibrary,
            'statuses' => UserGame::statuses(),
        ]);
    }
}
