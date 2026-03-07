<?php

namespace App\Http\Controllers;

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
                                    new OA\Property(property: "rawg_game_id", type: "integer"),
                                    new OA\Property(property: "title", type: "string"),
                                    new OA\Property(property: "status", type: "string"),
                                    new OA\Property(property: "rating", type: "integer", nullable: true),
                                    new OA\Property(property: "cover_url", type: "string", nullable: true),
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
        $q = UserGame::query()->where('user_id', Auth::id());

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

        $game = UserGame::create([
            'user_id' => Auth::id(),
            'rawg_game_id' => $data['rawg_game_id'],
            'title' => $data['title'],
            'cover_url' => $data['cover_url'] ?? null,
            'status' => $data['status'],
            'rating' => $data['rating'] ?? null,
        ]);

        // Jeśli dodano z oceną, wysyłamy job
        if (!is_null($data['rating'])) {
            dispatch(new UpdateGameStatistics($data['rawg_game_id']))->onQueue('statistics');
        }

        return response()->json(['game' => $game], 201);
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
        $game = UserGame::findOrFail($id);

        if ($game->user_id !== Auth::id()) {
            return response()->json(['message' => 'To nie twoja gra'], 403);
        }

        $data = $req->validate([
            'status' => ['required', 'in:to_play,playing,finished'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        $oldRating = $game->rating;
        $game->update($data);

        if ($oldRating != $game->rating) {
            dispatch(new UpdateGameStatistics($game->rawg_game_id))->onQueue('statistics');
        }

        return response()->json(['game' => $game]);
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
        $game = UserGame::findOrFail($id);

        if ($game->user_id !== Auth::id()) {
            return response()->json(['message' => 'To nie twoja gra'], 403);
        }

        $rawgGameId = $game->rawg_game_id;
        $hadRating = !is_null($game->rating);

        $game->delete();

        if ($hadRating) {
            Log::info("Destroy: usuwam grę {$rawgGameId}, hadRating=" . ($hadRating ? 'tak' : 'nie'));
            dispatch(new UpdateGameStatistics($rawgGameId))->onQueue('statistics');
        }

        return response()->json(null, 204);
    }

    // =============================================
    // WEB ENDPOINTS (dla przeglądarki)
    // =============================================

    public function index(Request $req)
    {
        $q = UserGame::query()->where('user_id', Auth::id());

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

        return view('library.index', [
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

        // Znajdź istniejący wpis, aby porównać starą ocenę
        $existing = UserGame::where('user_id', Auth::id())
            ->where('rawg_game_id', (int)$data['rawg_game_id'])
            ->first();

        $oldRating = $existing ? $existing->rating : null;

        // Aktualizacja lub utworzenie
        $game = UserGame::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'rawg_game_id' => (int)$data['rawg_game_id'],
            ],
            [
                'title' => $data['title'],
                'cover_url' => $data['cover_url'] ?? null,
                'status' => $data['status'],
                'rating' => $data['rating'] ?? null,
            ]
        );

        // Jeśli ocena się zmieniła (nawet na null), wysyłamy job
        if ($oldRating != $game->rating) {
            dispatch(new UpdateGameStatistics($game->rawg_game_id))->onQueue('statistics');
        }

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
            dispatch(new UpdateGameStatistics($userGame->rawg_game_id))->onQueue('statistics');
        }

        return redirect()->back()->with('success', 'Zmieniono wpis.');
    }

    public function destroy(UserGame $userGame)
    {
        abort_unless($userGame->user_id === Auth::id(), 403);

        $rawgGameId = $userGame->rawg_game_id;
        $hadRating = !is_null($userGame->rating);

        $userGame->delete();

        // Dopiero po usunięciu przelicz statystyki
        if ($hadRating) {
            Log::info("Destroy: usuwam grę {$rawgGameId}, hadRating=" . ($hadRating ? 'tak' : 'nie'));
            dispatch(new UpdateGameStatistics($rawgGameId))->onQueue('statistics');
        }

        return redirect()->back()->with('success', 'Usunięto z biblioteki.');
    }
}
