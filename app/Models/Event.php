<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Event extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    protected $fillable = [
        'id', 'name', 'location', 'start_time', 'end_time', 'max_capacity'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
            // ensure stored times are in UTC
            if ($model->start_time) {
                $model->start_time = Carbon::parse($model->start_time)->setTimezone('UTC');
            }
            if ($model->end_time) {
                $model->end_time = Carbon::parse($model->end_time)->setTimezone('UTC');
            }
        });
    }

    public function attendees()
    {
        return $this->hasMany(Attendee::class);
    }

    // Helper to get remaining capacity (atomic logic handled in service)
    public function remainingCapacity(): int
    {
        return max(0, $this->max_capacity - $this->attendees()->count());
    }

    // Return times converted to a timezone (e.g., request-provided)
    public function toArrayWithTimezone(string $tz = 'UTC'): array
    {
        $arr = $this->toArray();
        $arr['start_time'] = $this->start_time ? $this->start_time->setTimezone($tz)->toIso8601String() : null;
        $arr['end_time'] = $this->end_time ? $this->end_time->setTimezone($tz)->toIso8601String() : null;
        $arr['remaining_capacity'] = $this->remainingCapacity();
        return $arr;
    }
}