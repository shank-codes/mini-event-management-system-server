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
    /**
     * @OA\Post(
     *     path="/v1/events/{event}/register",
     *     operationId="registerAttendee",
     *     tags={"Attendees"},
     *     summary="Register an attendee to an event",
     *     @OA\Parameter(name="event", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name","email"},
     *                 @OA\Property(property="name", type="string", maxLength=255),
     *                 @OA\Property(property="email", type="string", format="email", maxLength=255)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Registered successfully", 
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Successfully registered"),
     *             @OA\Property(property="data", ref="#/components/schemas/Attendee")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Event not found"),
     *     @OA\Response(response=409, description="Conflict: already registered or full"),
     *     @OA\Response(response=422, description="Validation errors", @OA\JsonContent(ref="#/components/schemas/ValidationError")),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
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
    /**
     * @OA\Get(
     *     path="/v1/events/{event}/attendees",
     *     operationId="getAttendees",
     *     tags={"Attendees"},
     *     summary="Get attendees for an event",
     *     @OA\Parameter(name="event", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", example=15)),
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="List of attendees",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Attendee")),
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="per_page", type="integer"),
     *             @OA\Property(property="total", type="integer"),
     *             @OA\Property(property="last_page", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Event not found"),
     *     @OA\Response(response=500, description="Server error")
     * )
     */
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