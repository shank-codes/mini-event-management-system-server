<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use App\Models\Event;

class EventApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_event_success()
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

    public function test_create_event_validation_requires_name()
    {
        $payload = [
            'location' => 'Bangalore',
            'start_time' => Carbon::now('Asia/Kolkata')->addDay()->toIso8601String(),
            'end_time' => Carbon::now('Asia/Kolkata')->addDay()->addHours(2)->toIso8601String(),
            'max_capacity' => 5,
        ];

        $resp = $this->postJson('/api/v1/events', $payload);
        $resp->assertStatus(422);
        $resp->assertJsonValidationErrors('name');
        $this->assertEquals('Event name is required.', $resp->json('errors.name.0'));
    }

    public function test_create_event_validation_requires_start_time()
    {
        $payload = [
            'name' => 'Demo Event',
            'location' => 'Bangalore',
            'end_time' => Carbon::now('Asia/Kolkata')->addDay()->addHours(2)->toIso8601String(),
            'max_capacity' => 5,
        ];

        $resp = $this->postJson('/api/v1/events', $payload);
        $resp->assertStatus(422);
        $resp->assertJsonValidationErrors('start_time');
        $this->assertEquals('Start time is required.', $resp->json('errors.start_time.0'));
    }

    public function test_create_event_validation_end_time_after_start_time()
    {
        $payload = [
            'name' => 'Demo Event',
            'location' => 'Bangalore',
            'start_time' => Carbon::now('Asia/Kolkata')->addDay()->addHours(3)->toIso8601String(),
            'end_time' => Carbon::now('Asia/Kolkata')->addDay()->addHours(2)->toIso8601String(),
            'max_capacity' => 5,
        ];

        $resp = $this->postJson('/api/v1/events', $payload);
        $resp->assertStatus(422);
        $resp->assertJsonValidationErrors('end_time');
        $this->assertEquals('End time must be after start time.', $resp->json('errors.end_time.0'));
    }

    public function test_create_event_validation_max_capacity_minimum()
    {
        $payload = [
            'name' => 'Demo Event',
            'location' => 'Bangalore',
            'start_time' => Carbon::now('Asia/Kolkata')->addDay()->toIso8601String(),
            'end_time' => Carbon::now('Asia/Kolkata')->addDay()->addHours(2)->toIso8601String(),
            'max_capacity' => 0,
        ];

        $resp = $this->postJson('/api/v1/events', $payload);
        $resp->assertStatus(422);
        // $resp->assertJsonValidationErrors(['max_capacity']);
        $resp->assertJsonValidationErrors('max_capacity');
        $this->assertEquals('Max capacity must be at least 1.', $resp->json('errors.max_capacity.0'));
    }

    public function test_list_upcoming_events_returns_only_future_events()
    {
        // Create a past event (should NOT be listed)
        Event::factory()->create([
            'start_time' => Carbon::now('UTC')->subDay(),
            'end_time' => Carbon::now('UTC')->subDay()->addHours(2),
        ]);

        // Create a future event (should be listed)
        $futureEvent = Event::factory()->create([
            'start_time' => Carbon::now('UTC')->addDay(),
            'end_time' => Carbon::now('UTC')->addDay()->addHours(2),
            'name' => 'Future Event',
        ]);

        $resp = $this->getJson('/api/v1/events');

        $resp->assertStatus(200);
        $resp->assertJsonCount(1);
        $resp->assertJsonFragment(['name' => 'Future Event']);
    }

}
