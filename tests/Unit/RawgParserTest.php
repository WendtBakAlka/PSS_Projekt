<?php

namespace Tests\Unit;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RawgParserTest extends TestCase
{
    private function parseRawgGames($response)
    {
        if (!isset($response['results'])) {
            return [];
        }

        return array_map(function($game) {
            return [
                'rawg_id' => $game['id'],
                'title' => $game['name'],
                'rating' => $game['rating'] ?? 0,
                'released' => $game['released'] ?? null,
                'cover_url' => $game['background_image'] ?? null,
                'genres' => isset($game['genres'])
                    ? array_column($game['genres'], 'name')
                    : [],
            ];
        }, $response['results']);
    }

    #[Test]
    public function it_parses_rawg_games_correctly()
    {
        $mockResponse = [
            'results' => [
                [
                    'id' => 123,
                    'name' => 'The Witcher 3',
                    'rating' => 4.9,
                    'released' => '2015-05-18',
                    'background_image' => 'https://image.com/witcher.jpg',
                    'genres' => [
                        ['name' => 'RPG'],
                        ['name' => 'Action']
                    ]
                ],
                [
                    'id' => 456,
                    'name' => 'Cyberpunk',
                    'rating' => null,
                    'released' => null,
                    'background_image' => null,
                    'genres' => null
                ]
            ]
        ];

        $result = $this->parseRawgGames($mockResponse);

        $this->assertCount(2, $result);
        $this->assertEquals(123, $result[0]['rawg_id']);
        $this->assertEquals('The Witcher 3', $result[0]['title']);
        $this->assertEquals(4.9, $result[0]['rating']);
        $this->assertEquals(['RPG', 'Action'], $result[0]['genres']);
        $this->assertEquals(0, $result[1]['rating']);
        $this->assertEquals([], $result[1]['genres']);
    }

    #[Test]
    public function it_handles_empty_rawg_response()
    {
        $result = $this->parseRawgGames([]);
        $this->assertEmpty($result);
    }

    #[Test]
    public function it_handles_missing_fields_in_rawg_response()
    {
        $mockResponse = [
            'results' => [
                ['id' => 789, 'name' => 'Game Only']
            ]
        ];

        $result = $this->parseRawgGames($mockResponse);

        $this->assertEquals(789, $result[0]['rawg_id']);
        $this->assertEquals('Game Only', $result[0]['title']);
        $this->assertEquals(0, $result[0]['rating']);
        $this->assertNull($result[0]['released']);
        $this->assertEquals([], $result[0]['genres']);
    }
}
