<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameStat extends Model
{
    protected $table = 'game_stats';
    protected $fillable = [
        'rawg_game_id',
        'title',
        'cover_url',
        'average_rating',
        'ratings_count'
    ];
}
