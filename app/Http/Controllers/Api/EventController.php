<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Services\EventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    protected EventService $service;

    public function __construct(EventService $service)
    {
        $this->service = $service;
    }

    // POST /api/v1/events
    public function store(StoreEventRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $event = $this->service->createEvent($payload);

        return response()->json($event->toArrayWithTimezone($request->get('timezone', 'UTC')), 201);
    }

    // GET /api/v1/events?timezone=Asia/Kolkata
    public function index(Request $request): JsonResponse
    {
        $tz = $request->get('timezone', 'UTC');
        $events = $this->service->listUpcomingEvents($tz);
        return response()->json($events);
    }
}