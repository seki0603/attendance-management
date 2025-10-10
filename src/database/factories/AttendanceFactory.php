<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    public function definition()
    {
        $clockIn = $this->faker->dateTimeBetween('08:00:00', '10:00:00');
        $clockOut = (clone $clockIn)->modify('+8 hours');

        $breakMinutes = $this->faker->numberBetween(30, 120);

        $workMinutes = (8 * 60) - $breakMinutes;

        return [
            'user_id' => User::factory(),
            'work_date' => $clockIn->format('Y-m-d'),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'total_break_time' => $breakMinutes,
            'total_work_time' => $workMinutes,
        ];
    }
}
