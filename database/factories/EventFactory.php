<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition()
    {
        $start = $this->faker->dateTimeBetween('now', '+1 month');
        $end = (clone $start)->modify('+2 hours');

        return [
            'id' => (string) Str::uuid(),
            'name' => $this->faker->sentence(3),
            'location' => $this->faker->city(),
            'start_time' => Carbon::instance($start)->toDateTimeString(),
            'end_time' => Carbon::instance($end)->toDateTimeString(),
            'max_capacity' => $this->faker->numberBetween(1, 100),
        ];
    }
}
