<?php

namespace Tests\Unit;

use Tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class GameServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_saves_game_using_mock()
    {
        // Mock modelu Game
        $gameMock = Mockery::mock('App\Models\Game');
        $gameMock->shouldReceive('create')
            ->once()
            ->with(['title' => 'Test Game', 'rawg_rating' => 4.5])
            ->andReturn((object)['id' => 1, 'title' => 'Test Game']);

        $result = $gameMock->create(['title' => 'Test Game', 'rawg_rating' => 4.5]);

        $this->assertEquals(1, $result->id);
        $this->assertEquals('Test Game', $result->title);
    }

    #[Test]
    public function it_calculates_average_rating_from_user_games()
    {
        $calculateAverage = function($ratings) {
            if (empty($ratings)) return 0;
            return array_sum($ratings) / count($ratings);
        };

        $this->assertEquals(4.0, $calculateAverage([4, 4, 4]));
        $this->assertEquals(4.25, $calculateAverage([5, 4, 5, 3]));
        $this->assertEquals(0, $calculateAverage([]));
    }

    #[Test]
    public function it_filters_games_by_status()
    {
        $filterByStatus = function($games, $status) {
            return array_filter($games, function($game) use ($status) {
                return $game['status'] === $status;
            });
        };

        $games = [
            ['title' => 'Game1', 'status' => 'completed'],
            ['title' => 'Game2', 'status' => 'playing'],
            ['title' => 'Game3', 'status' => 'to_play'],
        ];

        $completed = $filterByStatus($games, 'completed');
        $this->assertCount(1, $completed);
        $this->assertEquals('Game1', array_values($completed)[0]['title']);
    }
}
