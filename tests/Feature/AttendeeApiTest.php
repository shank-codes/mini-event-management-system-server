<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use App\Models\Event;

class AttendeeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_attendee_and_prevent_duplicate()
    {
        // create event
        $event = Event::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'name' => 'Test',
            'location' => 'X',
            'start_time' => Carbon::now('UTC')->addDay(),
            'end_time' => Carbon::now('UTC')->addDay()->addHours(2),
            'max_capacity' => 1
        ]);

        $payload = ['name' => 'John', 'email' => 'john@example.com'];
        $resp = $this->postJson("/api/v1/events/{$event->id}/register", $payload);
        $resp->assertStatus(201);

        // duplicate registration
        $resp2 = $this->postJson("/api/v1/events/{$event->id}/register", $payload);
        $resp2->assertStatus(422);
    }

    public function test_prevent_overbooking()
    {
        $event = Event::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'name' => 'Capacity Test',
            'location' => 'X',
            'start_time' => Carbon::now('UTC')->addDay(),
            'end_time' => Carbon::now('UTC')->addDay()->addHours(2),
            'max_capacity' => 1
        ]);

        $this->postJson("/api/v1/events/{$event->id}/register", ['name' => 'A', 'email' => 'a@example.com'])->assertStatus(201);
        $this->postJson("/api/v1/events/{$event->id}/register", ['name' => 'B', 'email' => 'b@example.com'])->assertStatus(422);
    }
}