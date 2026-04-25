<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RawgService
{
    public function fetchGame(int $id): ?array
    {
        $resp = Http::get(config('rawg.base_url') . "/games/{$id}", [
            'key' => config('rawg.key')
        ]);

        if (!$resp->ok()) return null;

        return $resp->json();
    }
}
