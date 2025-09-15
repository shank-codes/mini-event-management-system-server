<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     title="Event Management API",
 *     version="1.0.0",
 *     description="API for managing events and attendee registrations",
 *     @OA\Contact(email="admin@example.com")
 * )
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Local server"
 * )
 * @OA\Tag(name="Events", description="Operations about events")
 * @OA\Tag(name="Attendees", description="Operations about attendees")
 *
 * @OA\Schema(
 *     schema="Event",
 *     type="object",
 *     required={"id","name","start_time","end_time","max_capacity"},
 *     @OA\Property(property="id", type="string", format="uuid", example="3fa85f64-5717-4562-b3fc-2c963f66afa6"),
 *     @OA\Property(property="name", type="string", example="Laravel Conference 2025"),
 *     @OA\Property(property="location", type="string", nullable=true, example="San Francisco"),
 *     @OA\Property(property="start_time", type="string", format="date-time", example="2025-12-01T09:00:00+05:30"),
 *     @OA\Property(property="end_time", type="string", format="date-time", example="2025-12-01T17:00:00+05:30"),
 *     @OA\Property(property="max_capacity", type="integer", minimum=1, example=500),
 *     @OA\Property(property="remaining_capacity", type="integer", example=450, description="Computed as max_capacity minus current attendees"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 *
 * @OA\Schema(
 *     schema="Attendee",
 *     type="object",
 *     required={"id","event_id","name","email"},
 *     @OA\Property(property="id", type="string", format="uuid", example="d5f2e7f1-9c9a-4a28-8f2c-1234567890ab"),
 *     @OA\Property(property="event_id", type="string", format="uuid", example="3fa85f64-5717-4562-b3fc-2c963f66afa6"),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
 *     @OA\Property(property="registered_at", type="string", format="date-time", example="2025-09-14T15:30:00+05:30")
 * )
 *
 * @OA\Schema(
 *     schema="ValidationError",
 *     type="object",
 *     @OA\Property(property="message", type="string", example="The given data was invalid."),
 *     @OA\Property(
 *         property="errors",
 *         type="object",
 *         @OA\AdditionalProperties(
 *             type="array",
 *             @OA\Items(type="string")
 *         )
 *     )
 * )
 */

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}