<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class EventApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_event()
    {
        $payload = [
            'name' => 'Demo Event',
            'location' => 'Bangalore',
            'start_time' => Carbon::now('Asia/Kolkata')->addDay()->toIso8601String(),
            'end_time' => Carbon::now('Asia/Kolkata')->addDay()->addHours(2)->toIso8601String(),
            'max_capacity' => 5
        ];

        $resp = $this->postJson('/api/v1/events', $payload);
        $resp->assertStatus(201);
        $resp->assertJsonFragment(['name' => 'Demo Event']);
    }
}