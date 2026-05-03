<?php

namespace Tests\Unit;

use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ValidationTest extends TestCase
{
    #[Test]
    public function rating_must_be_between_0_and_5()
    {
        $validateRating = function($rating) {
            return is_numeric($rating) && $rating >= 0 && $rating <= 5;
        };

        // Prawidłowe
        $this->assertTrue($validateRating(5));
        $this->assertTrue($validateRating(0));
        $this->assertTrue($validateRating(3.7));

        // Nieprawidłowe
        $this->assertFalse($validateRating(6));
        $this->assertFalse($validateRating(-1));
        $this->assertFalse($validateRating('abc'));
        $this->assertFalse($validateRating(null));
    }

    #[Test]
    public function game_status_must_be_valid()
    {
        $validStatuses = ['to_play', 'playing', 'completed', 'dropped'];

        $validateStatus = function($status) use ($validStatuses) {
            return in_array($status, $validStatuses);
        };

        $this->assertTrue($validateStatus('to_play'));
        $this->assertTrue($validateStatus('playing'));
        $this->assertTrue($validateStatus('completed'));
        $this->assertTrue($validateStatus('dropped'));
        $this->assertFalse($validateStatus('invalid'));
        $this->assertFalse($validateStatus(''));
    }

    #[Test]
    public function search_query_cannot_be_empty()
    {
        $validateSearch = function($query) {
            return is_string($query) && strlen(trim($query)) >= 2;
        };

        $this->assertTrue($validateSearch('witcher'));
        $this->assertTrue($validateSearch('12'));
        $this->assertFalse($validateSearch(''));
        $this->assertFalse($validateSearch('a'));
        $this->assertFalse($validateSearch(null));
    }
}
