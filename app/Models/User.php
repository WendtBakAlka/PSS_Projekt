<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     title="Użytkownik",
 *     description="Model użytkownika systemu z biblioteką gier",
 *     required={"id", "name", "email", "created_at", "updated_at"},
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         example=1,
 *         description="Unikalny identyfikator użytkownika"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         maxLength=255,
 *         example="Jan Kowalski",
 *         description="Imię i nazwisko użytkownika"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         maxLength=255,
 *         example="jan.kowalski@example.com",
 *         description="Adres email użytkownika (unikalny)"
 *     ),
 *     @OA\Property(
 *         property="email_verified_at",
 *         type="string",
 *         format="date-time",
 *         nullable=true,
 *         example="2024-01-15T10:30:00Z",
 *         description="Data i czas weryfikacji adresu email"
 *     ),
 *     @OA\Property(
 *         property="is_admin",
 *         type="boolean",
 *         example=false,
 *         default=false,
 *         description="Czy użytkownik ma uprawnienia administratora"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         example="2024-01-15T10:30:00Z",
 *         description="Data i czas utworzenia konta"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         example="2024-01-20T14:45:00Z",
 *         description="Data i czas ostatniej aktualizacji konta"
 *     ),
 *     @OA\Property(
 *         property="library_games",
 *         type="array",
 *         description="Gry w bibliotece użytkownika (relacja)",
 *         @OA\Items(ref="#/components/schemas/UserGame")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="UserCreate",
 *     type="object",
 *     title="Tworzenie użytkownika",
 *     description="Dane wymagane do utworzenia nowego użytkownika",
 *     required={"name", "email", "password"},
 *     @OA\Property(property="name", type="string", maxLength=255, example="Jan Kowalski"),
 *     @OA\Property(property="email", type="string", format="email", maxLength=255, example="jan@example.com"),
 *     @OA\Property(property="password", type="string", format="password", minLength=8, example="haslo123"),
 *     @OA\Property(
 *         property="is_admin",
 *         type="boolean",
 *         example=false,
 *         default=false,
 *         description="Opcjonalnie - czy użytkownik ma być administratorem"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="UserUpdate",
 *     type="object",
 *     title="Aktualizacja użytkownika",
 *     description="Dane do aktualizacji użytkownika (wszystkie pola opcjonalne)",
 *     @OA\Property(property="name", type="string", maxLength=255, example="Nowe Imię"),
 *     @OA\Property(property="email", type="string", format="email", maxLength=255, example="nowy@example.com"),
 *     @OA\Property(property="password", type="string", format="password", minLength=8, example="nowe_haslo123")
 * )
 *
 * @OA\Schema(
 *     schema="UserLogin",
 *     type="object",
 *     title="Logowanie użytkownika",
 *     description="Dane wymagane do logowania",
 *     required={"email", "password"},
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         example="jan@example.com",
 *         description="Adres email użytkownika"
 *     ),
 *     @OA\Property(
 *         property="password",
 *         type="string",
 *         format="password",
 *         example="haslo123",
 *         description="Hasło użytkownika"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="UserWithToken",
 *     type="object",
 *     title="Użytkownik z tokenem",
 *     description="Odpowiedź po udanym logowaniu/rejestracji",
 *     @OA\Property(property="user", ref="#/components/schemas/User"),
 *     @OA\Property(
 *         property="access_token",
 *         type="string",
 *         example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
 *         description="Token dostępu JWT/Sanctum"
 *     ),
 *     @OA\Property(
 *         property="token_type",
 *         type="string",
 *         example="Bearer",
 *         description="Typ tokenu"
 *     )
 * )
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Atrybuty, które można masowo przypisywać.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    /**
     * Atrybuty ukryte przy serializacji.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Rzutowania atrybutów.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Relacja z grami w bibliotece użytkownika.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function libraryGames()
    {
        return $this->hasMany(\App\Models\UserGame::class);
    }
}