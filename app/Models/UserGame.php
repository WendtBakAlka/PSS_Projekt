<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserGame extends Model
{
    protected $fillable = [
        'user_id',
        'game_id',
        'rating',
        'status',
    ];

    public const STATUS_TO_PLAY = 'to_play';
    public const STATUS_PLAYING = 'playing';
    public const STATUS_FINISHED = 'finished';

    public static function statuses(): array
    {
        return [
            self::STATUS_TO_PLAY => 'Do zagrania',
            self::STATUS_PLAYING => 'W trakcie',
            self::STATUS_FINISHED => 'Ukończona',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    // Scopes – nie zmieniają się, bo odnoszą się do pól rating, status, updated_at
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeWithMinRating($query, $minRating)
    {
        return $query->whereNotNull('rating')
            ->where('rating', '>=', $minRating);
    }

    public function scopeSortedBy($query, $sort)
    {
        switch ($sort) {
            case 'rating_desc':
                return $query->orderByDesc('rating');
            case 'rating_asc':
                return $query->orderBy('rating');
            default:
                return $query->orderByDesc('updated_at');
        }
    }
}
