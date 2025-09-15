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

    public function test_list_attendees_paginated()
    {
        // Create an event
        $event = Event::create([
            'id' => \Illuminate\Support\Str::uuid(),
            'name' => 'Attendee List Test',
            'location' => 'X',
            'start_time' => Carbon::now('UTC')->addDay(),
            'end_time' => Carbon::now('UTC')->addDay()->addHours(2),
            'max_capacity' => 5
        ]);

        // Register 3 attendees
        $attendees = [
            ['name' => 'User 1', 'email' => 'user1@example.com'],
            ['name' => 'User 2', 'email' => 'user2@example.com'],
            ['name' => 'User 3', 'email' => 'user3@example.com'],
        ];

        foreach ($attendees as $attendee) {
            $this->postJson("/api/v1/events/{$event->id}/register", $attendee)->assertStatus(201);
        }

        // Fetch attendees paginated with per_page = 2
        $resp = $this->getJson("/api/v1/events/{$event->id}/attendees?per_page=2");

        $resp->assertStatus(200);
        
        $json = $resp->json();

        // Assert pagination meta fields exist
        $this->assertArrayHasKey('current_page', $json);
        $this->assertArrayHasKey('per_page', $json);
        $this->assertArrayHasKey('total', $json);
        $this->assertArrayHasKey('last_page', $json);
        $this->assertArrayHasKey('data', $json);

        // Assert per_page respected
        $this->assertEquals(2, $json['per_page']);
        
        // Assert total is correct
        $this->assertEquals(3, $json['total']);

        // Assert data count is per_page or less
        $this->assertLessThanOrEqual(2, count($json['data']));

        // Fetch second page and verify the next attendees
        $resp2 = $this->getJson("/api/v1/events/{$event->id}/attendees?per_page=2&page=2");
        $resp2->assertStatus(200);
        $json2 = $resp2->json();
        $this->assertEquals(2, $json2['current_page']);
        $this->assertEquals(1, count($json2['data'])); // last remaining attendee
    }

}