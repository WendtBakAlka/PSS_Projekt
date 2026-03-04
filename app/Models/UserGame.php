<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="UserGame",
 *     type="object",
 *     title="Gra w bibliotece użytkownika",
 *     description="Model reprezentujący grę dodaną do biblioteki użytkownika",
 *     required={"id", "user_id", "rawg_game_id", "title", "status", "created_at", "updated_at"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         example=1,
 *         description="Unikalny identyfikator wpisu w bibliotece"
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         example=1,
 *         description="ID użytkownika, do którego należy gra"
 *     ),
 *     @OA\Property(
 *         property="rawg_game_id",
 *         type="integer",
 *         example=3328,
 *         description="ID gry w zewnętrznym API RAWG"
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         maxLength=255,
 *         example="The Witcher 3: Wild Hunt",
 *         description="Tytuł gry"
 *     ),
 *     @OA\Property(
 *         property="cover_url",
 *         type="string",
 *         maxLength=2048,
 *         nullable=true,
 *         example="https://media.rawg.io/media/games/618/618c2031a07bbff6b4f611f10b6bcdbc.jpg",
 *         description="URL obrazka okładki gry"
 *     ),
 *     @OA\Property(
 *         property="rating",
 *         type="integer",
 *         minimum=1,
 *         maximum=10,
 *         nullable=true,
 *         example=9,
 *         description="Ocena gry nadana przez użytkownika (1-10)"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={"to_play", "playing", "finished"},
 *         example="playing",
 *         description="Status gry w bibliotece użytkownika"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2024-01-15T10:30:00Z",
 *         description="Data i czas dodania gry do biblioteki"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         example="2024-01-20T14:45:00Z",
 *         description="Data i czas ostatniej aktualizacji wpisu"
 *     ),
 *     @OA\Property(
 *         property="user",
 *         ref="#/components/schemas/User",
 *         description="Obiekt użytkownika (tylko przy eager loading)"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="UserGameCreate",
 *     type="object",
 *     title="Tworzenie wpisu w bibliotece",
 *     description="Dane wymagane do dodania gry do biblioteki użytkownika",
 *     required={"rawg_game_id", "title", "status"},
 *     @OA\Property(
 *         property="rawg_game_id",
 *         type="integer",
 *         example=3328,
 *         description="ID gry z API RAWG"
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         maxLength=255,
 *         example="The Witcher 3: Wild Hunt"
 *     ),
 *     @OA\Property(
 *         property="cover_url",
 *         type="string",
 *         maxLength=2048,
 *         nullable=true,
 *         example="https://example.com/cover.jpg"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={"to_play", "playing", "finished"},
 *         example="to_play"
 *     ),
 *     @OA\Property(
 *         property="rating",
 *         type="integer",
 *         minimum=1,
 *         maximum=10,
 *         nullable=true,
 *         example=9
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="UserGameUpdate",
 *     type="object",
 *     title="Aktualizacja wpisu w bibliotece",
 *     description="Dane do aktualizacji gry w bibliotece użytkownika",
 *     required={"status"},
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={"to_play", "playing", "finished"},
 *         example="finished"
 *     ),
 *     @OA\Property(
 *         property="rating",
 *         type="integer",
 *         minimum=1,
 *         maximum=10,
 *         nullable=true,
 *         example=10
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="UserGameStatuses",
 *     type="object",
 *     title="Dostępne statusy gier",
 *     description="Mapa dostępnych statusów gier w bibliotece z tłumaczeniami",
 *     @OA\Property(
 *         property="to_play",
 *         type="string",
 *         example="Do zagrania",
 *         description="Gra do zagrania w przyszłości"
 *     ),
 *     @OA\Property(
 *         property="playing",
 *         type="string",
 *         example="W trakcie",
 *         description="Gra aktualnie rozgrywana"
 *     ),
 *     @OA\Property(
 *         property="finished",
 *         type="string",
 *         example="Ukończona",
 *         description="Gra już ukończona"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="LibraryResponse",
 *     type="object",
 *     title="Odpowiedź z biblioteki gier",
 *     description="Pełna odpowiedź z paginacją dla endpointów biblioteki",
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         description="Lista gier na bieżącej stronie",
 *         @OA\Items(ref="#/components/schemas/UserGame")
 *     ),
 *     @OA\Property(
 *         property="links",
 *         type="object",
 *         description="Linki paginacji",
 *         @OA\Property(property="first", type="string", example="http://localhost/library?page=1"),
 *         @OA\Property(property="last", type="string", example="http://localhost/library?page=5"),
 *         @OA\Property(property="prev", type="string", nullable=true),
 *         @OA\Property(property="next", type="string", nullable=true)
 *     ),
 *     @OA\Property(
 *         property="meta",
 *         type="object",
 *         description="Metadane paginacji",
 *         @OA\Property(property="current_page", type="integer", example=1),
 *         @OA\Property(property="from", type="integer", example=1),
 *         @OA\Property(property="last_page", type="integer", example=5),
 *         @OA\Property(property="path", type="string", example="http://localhost/library"),
 *         @OA\Property(property="per_page", type="integer", example=24),
 *         @OA\Property(property="to", type="integer", example=24),
 *         @OA\Property(property="total", type="integer", example=100)
 *     ),
 *     @OA\Property(
 *         property="statuses",
 *         ref="#/components/schemas/UserGameStatuses",
 *         description="Dostępne statusy gier"
 *     ),
 *     @OA\Property(
 *         property="filters",
 *         type="object",
 *         description="Aktywne filtry",
 *         @OA\Property(property="status", type="string", nullable=true),
 *         @OA\Property(property="min_rating", type="integer", nullable=true),
 *         @OA\Property(property="sort", type="string", example="updated")
 *     )
 * )
 */
class UserGame extends Model
{
    protected $fillable = [
        'user_id',
        'rawg_game_id',
        'title',
        'cover_url',
        'rating',
        'status',
    ];

    public const STATUS_TO_PLAY = 'to_play';
    public const STATUS_PLAYING = 'playing';
    public const STATUS_FINISHED = 'finished';

    /**
     * Zwraca mapę dostępnych statusów z tłumaczeniami.
     *
     * @return array<string, string>
     * 
     * @OA\Property(
     *     property="available_statuses",
     *     type="object",
     *     description="Dostępne statusy gier",
     *     @OA\AdditionalProperties(type="string")
     * )
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_TO_PLAY => 'Do zagrania',
            self::STATUS_PLAYING => 'W trakcie',
            self::STATUS_FINISHED => 'Ukończona',
        ];
    }

    /**
     * Relacja z użytkownikiem.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope do filtrowania po statusie.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope do filtrowania po minimalnej ocenie.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $minRating
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithMinRating($query, $minRating)
    {
        return $query->whereNotNull('rating')
                    ->where('rating', '>=', $minRating);
    }

    /**
     * Scope do sortowania.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $sort
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortedBy($query, $sort)
    {
        switch ($sort) {
            case 'rating_desc':
                return $query->orderByDesc('rating');
            case 'rating_asc':
                return $query->orderBy('rating');
            default: // 'updated'
                return $query->orderByDesc('updated_at');
        }
    }
}