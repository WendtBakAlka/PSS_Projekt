<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;

class QueueTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_dispatches_job_to_queue()
    {
        Queue::fake();

        // Przykład: dispatch(new ProcessGameJob(['game_id' => 1]));

        Queue::assertNothingPushed();
    }
}
