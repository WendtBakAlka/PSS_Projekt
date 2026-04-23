<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = [
        'rawg_game_id',
        'title',
        'cover_url',
        'rawg_rating',
    ];
}
