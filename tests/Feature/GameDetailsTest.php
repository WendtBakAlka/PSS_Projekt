<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use App\Models\User;

class GameDetailsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_shows_game_details_from_rawg()
    {
        // Mockujemy odpowiedź RAWG API (nie idziemy na prawdziwe API)
        Http::fake([
            'api.rawg.io/api/games/*' => Http::response([
                'id' => 12345,
                'name' => 'The Witcher 3: Wild Hunt',
                'rating' => 4.9,
                'description' => 'Fantastyczne RPG',
            ], 200)
        ]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/games/12345');

        $response->assertStatus(200);
        $response->assertSee('The Witcher 3: Wild Hunt');
    }
}
