<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Attendee;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EventService
{
    /**
     * Create an event, storing times in UTC.
     */
    public function createEvent(array $data): Event
    {
        // Accept start_time/end_time in any timezone; parse and convert to UTC before saving.
        $start = Carbon::parse($data['start_time'])->setTimezone('UTC');
        $end = Carbon::parse($data['end_time'])->setTimezone('UTC');

        $event = Event::create([
            'name' => $data['name'],
            'location' => $data['location'] ?? null,
            'start_time' => $start,
            'end_time' => $end,
            'max_capacity' => (int)$data['max_capacity'],
        ]);

        return $event;
    }

    /**
     * Return upcoming events (start_time >= now)
     * Accept optional timezone param to convert times in response.
     */
    public function listUpcomingEvents(string $tz = 'UTC')
    {
        $now = Carbon::now('UTC');
        $events = Event::where('start_time', '>=', $now)->orderBy('start_time', 'asc')->get();

        return $events->map(fn($e) => $e->toArrayWithTimezone($tz));
    }

    /**
     * Register an attendee with concurrency-safe capacity check.
     * Returns Attendee model on success; throws exceptions for errors.
     */
    public function registerAttendee(string $eventId, array $attendeeData)
    {
        return DB::transaction(function () use ($eventId, $attendeeData) {
            // Lock the event row FOR UPDATE to prevent race conditions
            $event = Event::where('id', $eventId)->lockForUpdate()->first();
            if (!$event) {
                throw new ModelNotFoundException("Event not found");
            }

            // Combined query to check duplicate email existence and count attendees
            $result = Attendee::where('event_id', $eventId)
                ->selectRaw('COUNT(*) as total, SUM(CASE WHEN email = ? THEN 1 ELSE 0 END) as email_exists', [$attendeeData['email']])
                ->first();

            if ($result->email_exists) {
                throw new \RuntimeException('This email is already registered for the event.');
            }

            if ($result->total >= $event->max_capacity) {
                throw new \RuntimeException('Event is fully booked.');
            }

            // Create attendee
            $attendee = Attendee::create([
                'event_id' => $eventId,
                'name' => $attendeeData['name'],
                'email' => $attendeeData['email'],
            ]);

            return $attendee;
        }, 5); // retry attempts
    }

    public function listAttendees(string $eventId, int $perPage = 10)
    {
        $paginated =  Attendee::where('event_id', $eventId)->paginate($perPage);

        return [
            'current_page' => $paginated->currentPage(),
            'per_page' => $paginated->perPage(),
            'total' => $paginated->total(),
            'last_page' => $paginated->lastPage(),
            'data' => $paginated->items(),
        ];
    }
}