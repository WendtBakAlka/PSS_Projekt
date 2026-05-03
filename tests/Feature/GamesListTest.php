<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\User;

class GamesListTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function games_search_page_loads_correctly()
    {
        // Tworzymy użytkownika i logujemy go
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/games');

        // Sprawdzamy czy strona działa
        $response->assertStatus(200);
    }
}
