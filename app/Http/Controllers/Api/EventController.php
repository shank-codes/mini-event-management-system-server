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
    /**
     * @OA\Post(
     *     path="/v1/events",
     *     operationId="createEvent",
     *     tags={"Events"},
     *     summary="Create a new event",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name","start_time","end_time","max_capacity"},
     *                 @OA\Property(property="name", type="string", maxLength=255),
     *                 @OA\Property(property="location", type="string", maxLength=255, nullable=true),
     *                 @OA\Property(property="start_time", type="string", format="date-time"),
     *                 @OA\Property(property="end_time", type="string", format="date-time"),
     *                 @OA\Property(property="max_capacity", type="integer", minimum=1)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Event created",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Event created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Event")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation errors", @OA\JsonContent(ref="#/components/schemas/ValidationError")),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function store(StoreEventRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $event = $this->service->createEvent($payload);

        return response()->json($event->toArrayWithTimezone($request->get('timezone', 'UTC')), 201);
    }

    // GET /api/v1/events?timezone=Asia/Kolkata
    /**
     * @OA\Get(
     *     path="/v1/events",
     *     operationId="getEventsList",
     *     tags={"Events"},
     *     summary="Retrieve list of events",
     *     @OA\Response(
     *         response=200,
     *         description="A list of events",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Event")),
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $tz = $request->get('timezone', 'UTC');
        $events = $this->service->listUpcomingEvents($tz);
        return response()->json(['data' => $events]);

    }
}