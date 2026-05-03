<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class TestDatabaseSeeder extends Seeder
{
    public function run()
    {
        // 1. Użytkownik testowy
        DB::table('users')->insert([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Gry testowe (dopasowane do Twoich kolumn)
        DB::table('games')->insert([
            [
                'rawg_game_id' => 12345,
                'title' => 'The Witcher 3: Wild Hunt',
                'cover_url' => 'https://example.com/witcher3.jpg',
                'rawg_rating' => 4.9,
                'description' => 'Fantastyczne RPG od CD Projekt Red',
                'genres' => json_encode(['RPG', 'Action']),
                'platforms' => json_encode(['PC', 'PS4', 'Xbox One']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'rawg_game_id' => 12346,
                'title' => 'Cyberpunk 2077',
                'cover_url' => 'https://example.com/cyberpunk.jpg',
                'rawg_rating' => 3.5,
                'description' => 'Futurystyczne RPG od CD Projekt Red',
                'genres' => json_encode(['RPG', 'Sci-Fi']),
                'platforms' => json_encode(['PC', 'PS5', 'Xbox Series X']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'rawg_game_id' => 12347,
                'title' => 'Mario Odyssey',
                'cover_url' => 'https://example.com/mario.jpg',
                'rawg_rating' => 4.8,
                'description' => 'Platformówka od Nintendo.',
                'genres' => json_encode(['Platformer', 'Adventure']),
                'platforms' => json_encode(['Nintendo Switch']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'rawg_game_id' => 12348,
                'title' => 'Baldur\'s Gate 3',
                'cover_url' => 'https://example.com/bg3.jpg',
                'rawg_rating' => 5.0,
                'description' => 'RPG roku 2023',
                'genres' => json_encode(['RPG', 'Strategy']),
                'platforms' => json_encode(['PC', 'PS5']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        // 3. Gry w bibliotece użytkownika (user_games)
        DB::table('user_games')->insert([
            [
                'user_id' => 1,
                'game_id' => 1,
                'status' => 'completed',
                'rating' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 1,
                'game_id' => 2,
                'status' => 'playing',
                'rating' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
