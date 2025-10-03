<?php

namespace Database\Factories;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class BreakFactory extends Factory
{
    public function definition()
    {
        $start = $this->faker->dateTimeBetween('12:00:00', '13:00:00');
        $end = (clone $start)->modify('+1 hour');

        return [
            'attendance_id' => Attendance::factory(),
            'break_start' => $start,
            'break_end' => $end,
        ];
    }
}
