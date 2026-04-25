<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\EnrichGameMetadataJob;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GameApiController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('search');
        $page = (int) $request->input('page', 1);
        $source = $request->input('source', 'rawg');

        if (!$query) {
            return response()->json([
                'results' => [],
                'next' => null,
                'previous' => null,
            ]);
        }

        if ($source === 'local') {
            $games = Game::where('title', 'like', '%' . $query . '%')
                ->orderBy('title')
                ->paginate(9, ['*'], 'page', $page);

            return response()->json([
                'results' => $games->getCollection()->map(function ($game) {
                    return [
                        'id' => $game->rawg_game_id,
                        'name' => $game->title,
                        'released' => null,
                        'background_image' => $game->cover_url,
                        'rating' => $game->rawg_rating ? $game->rawg_rating / 2 : 0,
                        'from_local' => true,
                    ];
                })->values(),
                'next' => $games->hasMorePages(),
                'previous' => $games->currentPage() > 1,
            ]);
        }

        $response = Http::timeout(10)->get(config('rawg.base_url') . '/games', [
            'key' => config('rawg.key'),
            'search' => $query,
            'page_size' => 9,
            'page' => $page,
        ]);

        if (!$response->successful()) {
            return response()->json(['error' => 'Błąd API RAWG'], 500);
        }

        $data = $response->json();

        foreach (($data['results'] ?? []) as $item) {
            if (empty($item['id']) || empty($item['name'])) {
                continue;
            }

            $game = Game::updateOrCreate(
                ['rawg_game_id' => $item['id']],
                [
                    'title' => $item['name'],
                    'cover_url' => $item['background_image'] ?? null,
                    'rawg_rating' => isset($item['rating']) ? round($item['rating'] * 2, 1) : null,
                ]
            );

            if (empty($game->description) || empty($game->genres) || empty($game->platforms)) {
                EnrichGameMetadataJob::dispatch($game->id)->onQueue('metadata');
            }
        }

        $data['results'] = collect($data['results'] ?? [])->map(function ($game) {
            $game['from_local'] = false;
            return $game;
        })->values();

        return response()->json($data);
    }
}
