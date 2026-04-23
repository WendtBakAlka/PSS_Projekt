<?php

namespace App\Http\Controllers;

use App\Models\GameStat;
use Illuminate\Http\Request;

class RankingController extends Controller
{
    public function topRated()
    {
        // Ładujemy powiązany model Game, aby mieć dostęp do tytułu, okładki i rawg_rating
        $games = GameStat::with('game')
            ->whereNotNull('average_rating')
            ->orderBy('average_rating', 'desc')
            ->orderBy('ratings_count', 'desc')
            ->paginate(20);

        $activeTab = 'rated';
        return view('rankings', compact('games', 'activeTab'));
    }

    public function mostPopular()
    {
        $games = GameStat::with('game')
            ->where('ratings_count', '>', 0)
            ->orderBy('ratings_count', 'desc')
            ->orderBy('average_rating', 'desc')
            ->paginate(20);

        $activeTab = 'popular';
        return view('rankings', compact('games', 'activeTab'));
    }
}
