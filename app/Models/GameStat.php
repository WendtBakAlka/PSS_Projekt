<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameStat extends Model
{
    protected $table = 'game_stats';
    protected $fillable = [
        'game_id',
        'average_rating',
        'ratings_count',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
