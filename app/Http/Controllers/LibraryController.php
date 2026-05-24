<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\UserGame;
use App\Models\GameStat;
use App\Jobs\UpdateGameStatistics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;
use Illuminate\Support\Facades\Log;

class LibraryController extends Controller
{
    // =============================================
    // API ENDPOINTS (dla Swaggera)
    // =============================================

    #[OA\Get(
        path: "/api/library",
        summary: "Pobierz listę gier z biblioteki (API)",
        tags: ["Library"],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista gier w formacie JSON",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: "games",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer"),
                                    new OA\Property(property: "game_id", type: "integer"),
                                    new OA\Property(property: "title", type: "string"),
                                    new OA\Property(property: "cover_url", type: "string", nullable: true),
                                    new OA\Property(property: "rawg_rating", type: "number", nullable: true),
                                    new OA\Property(property: "status", type: "string"),
                                    new OA\Property(property: "rating", type: "integer", nullable: true),
                                ]
                            )
                        )
                    ]
                )
            ),
        ]
    )]
    public function apiIndex(Request $req)
    {
        $q = UserGame::with('game')->where('user_id', Auth::id());

        if ($req->filled('status')) {
            $q->where('status', $req->string('status'));
        }

        if ($req->filled('min_rating')) {
            $q->whereNotNull('rating')
                ->where('rating', '>=', (int)$req->input('min_rating'));
        }

        $sort = $req->input('sort', 'updated');
        if ($sort === 'rating_desc') $q->orderByDesc('rating');
        elseif ($sort === 'rating_asc') $q->orderBy('rating');
        else $q->orderByDesc('updated_at');

        $games = $q->get();

        // Transformacja do formatu zawierającego dane gry
        $games = $games->map(function ($userGame) {
            return [
                'id' => $userGame->id,
                'game_id' => $userGame->game_id,
                'title' => $userGame->game->title ?? null,
                'cover_url' => $userGame->game->cover_url ?? null,
                'rawg_rating' => $userGame->game->rawg_rating ?? null,
                'status' => $userGame->status,
                'rating' => $userGame->rating,
            ];
        });

        return response()->json(['games' => $games]);
    }

    #[OA\Post(
        path: "/api/library",
        summary: "Dodaj grę do biblioteki (API)",
        tags: ["Library"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["rawg_game_id", "title", "status"],
                properties: [
                    new OA\Property(property: "rawg_game_id", type: "integer", example: 3498),
                    new OA\Property(property: "title", type: "string", example: "The Witcher 3: Wild Hunt"),
                    new OA\Property(property: "cover_url", type: "string", nullable: true, example: "https://image.url"),
                    new OA\Property(property: "status", type: "string", enum: ["to_play", "playing", "finished"], example: "playing"),
                    new OA\Property(property: "rating", type: "integer", minimum: 1, maximum: 10, nullable: true, example: 9),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Gra dodana"),
            new OA\Response(response: 422, description: "Błąd walidacji"),
        ]
    )]
    public function apiStore(Request $req)
    {
        $data = $req->validate([
            'rawg_game_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'cover_url' => ['nullable', 'string', 'max:2048'],
            'status' => ['required', 'in:to_play,playing,finished'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        $game = Game::firstOrCreate(
            ['rawg_game_id' => $data['rawg_game_id']],
            [
                'title' => $data['title'],
                'cover_url' => $data['cover_url'] ?? null,
                'rawg_rating' => null,
            ]
        );

        dispatch(new \App\Jobs\EnrichGameMetadataJob($game->id))->onQueue('metadata');

        $userGame = UserGame::create([
            'user_id' => Auth::id(),
            'game_id' => $game->id,
            'status' => $data['status'],
            'rating' => $data['rating'] ?? null,
        ]);

        dispatch(new UpdateGameStatistics($game->id))->onQueue('statistics');

        return response()->json([
            'game' => [
                'id' => $userGame->id,
                'game_id' => $game->id,
                'title' => $game->title,
                'cover_url' => $game->cover_url,
                'rawg_rating' => $game->rawg_rating,
                'status' => $userGame->status,
                'rating' => $userGame->rating,
            ]
        ], 201);
    }

    #[OA\Put(
        path: "/api/library/{id}",
        summary: "Zaktualizuj grę w bibliotece (API)",
        tags: ["Library"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "status", type: "string", enum: ["to_play", "playing", "finished"]),
                    new OA\Property(property: "rating", type: "integer", minimum: 1, maximum: 10, nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Gra zaktualizowana"),
            new OA\Response(response: 403, description: "Nie twoja gra"),
            new OA\Response(response: 404, description: "Gra nie znaleziona"),
        ]
    )]
    public function apiUpdate(Request $req, $id)
    {
        $userGame = UserGame::findOrFail($id);

        if ($userGame->user_id !== Auth::id()) {
            return response()->json(['message' => 'To nie twoja gra'], 403);
        }

        $data = $req->validate([
            'status' => ['required', 'in:to_play,playing,finished'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        $oldRating = $userGame->rating;
        $userGame->update($data);

        if ($oldRating != $userGame->rating) {
            dispatch(new UpdateGameStatistics($userGame->game_id))->onQueue('statistics');
        }

        return response()->json(['game' => $userGame]);
    }

    #[OA\Delete(
        path: "/api/library/{id}",
        summary: "Usuń grę z biblioteki (API)",
        tags: ["Library"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 204, description: "Gra usunięta"),
            new OA\Response(response: 403, description: "Nie twoja gra"),
            new OA\Response(response: 404, description: "Gra nie znaleziona"),
        ]
    )]
    public function apiDestroy($id)
    {
        $userGame = UserGame::findOrFail($id);
        if ($userGame->user_id !== Auth::id()) {
            return response()->json(['message' => 'To nie twoja gra'], 403);
        }

        $gameId = $userGame->game_id;
        $hadRating = !is_null($userGame->rating);

        $userGame->delete();

        // Sprawdzamy, czy istnieją jeszcze inne wpisy tej gry w bibliotekach innych użytkowników
        $anyLeft = UserGame::where('game_id', $gameId)->exists();

        // Jeśli usunięto ocenę lub nie ma już żadnych wpisów, aktualizujemy statystyki
        if ($hadRating || !$anyLeft) {
            dispatch(new UpdateGameStatistics($gameId))->onQueue('statistics');
        }

        return response()->json(null, 204);
    }

    // =============================================
    // WEB ENDPOINTS (dla przeglądarki)
    // =============================================

    public function index(Request $req)
    {
        $q = UserGame::with('game')->where('user_id', Auth::id());

        if ($req->filled('status')) {
            $q->where('status', $req->string('status'));
        }

        if ($req->filled('min_rating')) {
            $q->whereNotNull('rating')
                ->where('rating', '>=', (int)$req->input('min_rating'));
        }

        $sort = $req->input('sort', 'updated');
        if ($sort === 'rating_desc') $q->orderByDesc('rating');
        elseif ($sort === 'rating_asc') $q->orderBy('rating');
        else $q->orderByDesc('updated_at');

        $items = $q->paginate(24)->withQueryString();

        return view('library', [
            'items' => $items,
            'statuses' => UserGame::statuses(),
            'filters' => [
                'status' => $req->input('status', ''),
                'min_rating' => $req->input('min_rating', ''),
                'sort' => $sort,
            ],
        ]);
    }
    public function store(Request $req)
    {
        $data = $req->validate([
            'rawg_game_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'cover_url' => ['nullable', 'string', 'max:2048'],
            'status' => ['required', 'in:to_play,playing,finished'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        $game = Game::firstOrCreate(
            ['rawg_game_id' => $data['rawg_game_id']],
            [
                'title' => $data['title'],
                'cover_url' => $data['cover_url'] ?? null,
                'rawg_rating' => null,
            ]
        );

        $existing = UserGame::where('user_id', Auth::id())
            ->where('game_id', $game->id)
            ->first();

        $oldRating = $existing ? $existing->rating : null;

        $userGame = UserGame::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'game_id' => $game->id,
            ],
            [
                'status' => $data['status'],
                'rating' => $data['rating'] ?? null,
            ]
        );

        dispatch(new UpdateGameStatistics($game->id))->onQueue('statistics');

        return redirect()->route('library.index')->with('success', 'Dodano / zaktualizowano w bibliotece.');
    }

    public function update(Request $req, UserGame $userGame)
    {
        abort_unless($userGame->user_id === Auth::id(), 403);

        $data = $req->validate([
            'status' => ['required', 'in:to_play,playing,finished'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        $oldRating = $userGame->rating;

        $userGame->update([
            'status' => $data['status'],
            'rating' => $data['rating'] ?? null,
        ]);

        if ($oldRating != $userGame->rating) {
            dispatch(new UpdateGameStatistics($userGame->game_id))->onQueue('statistics');
        }

        return redirect()->back()->with('success', 'Zmieniono wpis.');
    }

    public function destroy(UserGame $userGame)
    {
        abort_unless($userGame->user_id === Auth::id(), 403);

        $gameId = $userGame->game_id;
        $hadRating = !is_null($userGame->rating);

        $userGame->delete();

        $anyLeft = UserGame::where('game_id', $gameId)->exists();

        if ($hadRating || !$anyLeft) {
            dispatch(new UpdateGameStatistics($gameId))->onQueue('statistics');
        }

        return redirect()->back()->with('success', 'Usunięto z biblioteki.');
    }
}
