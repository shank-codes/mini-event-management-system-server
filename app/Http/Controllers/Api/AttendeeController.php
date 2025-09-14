<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterAttendeeRequest;
use App\Services\EventService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AttendeeController extends Controller
{
    protected EventService $service;

    public function __construct(EventService $service)
    {
        $this->service = $service;
    }

    // POST /api/v1/events/{event}/register
    public function register(RegisterAttendeeRequest $request, $event): JsonResponse
    {
        $payload = $request->validated();

        try {
            $attendee = $this->service->registerAttendee($event, $payload);
            // optionally dispatch a job to send confirmation email (async)
            return response()->json($attendee, 201);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Event not found.'], 404);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Registration failed.'], 500);
        }
    }

    // GET /api/v1/events/{event}/attendees?per_page=20
    public function index(Request $request, $event): JsonResponse
    {
        $perPage = (int) $request->get('per_page', 10);
        try {
            $page = $this->service->listAttendees($event, $perPage);
            return response()->json($page);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Event not found.'], 404);
        }
    }
}