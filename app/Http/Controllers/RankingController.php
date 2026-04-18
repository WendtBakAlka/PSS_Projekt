<?php

namespace App\Http\Controllers;

use App\Models\GameStat;
use Illuminate\Http\Request;

class RankingController extends Controller
{
    public function topRated()
    {
        $games = GameStat::whereNotNull('average_rating')
            ->orderBy('average_rating', 'desc')
            ->orderBy('ratings_count', 'desc')
            ->paginate(20);
        $activeTab = 'rated';
        return view('rankings', compact('games', 'activeTab'));
    }

    public function mostPopular()
    {
        $games = GameStat::where('ratings_count', '>', 0)
            ->orderBy('ratings_count', 'desc')
            ->orderBy('average_rating', 'desc')
            ->paginate(20);
        $activeTab = 'popular';
        return view('rankings', compact('games', 'activeTab'));
    }
}
