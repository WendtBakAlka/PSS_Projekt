<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use OpenApi\Attributes as OA;

class GameApiController extends Controller
{
    #[OA\Get(
        path: "/api/games/search",
        summary: "Wyszukaj gry w RAWG API",
        tags: ["Games"],
        parameters: [
            new OA\Parameter(
                name: "search",
                description: "Fraza do wyszukania gier",
                in: "query",
                required: true,
                schema: new OA\Schema(type: "string")
            ),
            new OA\Parameter(
                name: "page",
                description: "Numer strony wyników",
                in: "query",
                required: false,
                schema: new OA\Schema(type: "integer", default: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista znalezionych gier z RAWG API",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "count", type: "integer", example: 100),
                        new OA\Property(property: "next", type: "string", nullable: true, example: "https://api.rawg.io/api/games?search=witcher&page=2"),
                        new OA\Property(property: "previous", type: "string", nullable: true),
                        new OA\Property(
                            property: "results",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "integer", example: 3328),
                                    new OA\Property(property: "name", type: "string", example: "The Witcher 3: Wild Hunt"),
                                    new OA\Property(property: "released", type: "string", example: "2015-05-18"),
                                    new OA\Property(property: "background_image", type: "string", example: "https://media.rawg.io/media/games/618/618c2031a07bbff6b4f611f10b6bcdbc.jpg"),
                                ]
                            )
                        )
                    ]
                )
            ),
            new OA\Response(response: 500, description: "Błąd połączenia z RAWG API"),
        ]
    )]
    public function search(Request $request)
    {
        $query = $request->input('search');
        $page = $request->input('page', 1);

        // Używamy config() zamiast env(), bo tak masz w api.php
        $response = Http::get(config('rawg.base_url') . '/games', [
            'key' => config('rawg.key'),
            'search' => $query,
            'page_size' => 9,
            'page' => $page,
        ]);

        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json(['error' => 'Błąd API RAWG'], 500);
    }
}