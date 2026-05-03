<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\TestDatabaseSeeder;
use PHPUnit\Framework\Attributes\Test;
use App\Models\User;
use App\Models\Game;
use App\Models\UserGame;

class DatabaseInteractionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TestDatabaseSeeder::class);
    }

    #[Test]
    public function it_saves_user_rating_for_game()
    {
        $user = User::first();
        $game = Game::first();

        UserGame::updateOrCreate(
            ['user_id' => $user->id, 'game_id' => $game->id],
            ['rating' => 5, 'status' => 'completed']
        );

        $this->assertDatabaseHas('user_games', [
            'user_id' => $user->id,
            'game_id' => $game->id,
            'rating' => 5,
        ]);
    }

    #[Test]
    public function it_updates_existing_user_game()
    {
        $user = User::first();
        $game = Game::first();

        // Najpierw usuń jeśli istnieje (z seedera)
        UserGame::where('user_id', $user->id)
            ->where('game_id', $game->id)
            ->delete();

        // Dodaj nowy wpis
        $userGame = UserGame::create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'status' => 'to_play',
            'rating' => null
        ]);

        // Zaktualizuj
        $userGame->update(['status' => 'completed', 'rating' => 5]);

        $this->assertDatabaseHas('user_games', [
            'user_id' => $user->id,
            'game_id' => $game->id,
            'status' => 'completed',
            'rating' => 5,
        ]);
    }
}
