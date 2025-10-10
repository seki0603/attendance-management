<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceStatus;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::where('role', 'user')
            ->where('email', '!=', 'test@example.com')
            ->get();

        foreach ($users as $user) {
            foreach (range(0, 1) as $monthOffset) {
                $baseDate = Carbon::now()->startOfMonth()->subMonths($monthOffset);
                $daysInMonth = $baseDate->daysInMonth;

                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $workDate = $baseDate->copy()->addDays($day - 1);

                    $clockIn = $workDate->copy()->setTime(rand(8, 9), rand(0, 59));
                    $clockOut = $clockIn->copy()->addHours(8)->addMinutes(rand(0, 30));

                    $attendance = Attendance::create([
                        'user_id' => $user->id,
                        'work_date' => $workDate->toDateString(),
                        'clock_in' => $clockIn,
                        'clock_out' => $clockOut,
                        'total_break_time' => 0,
                        'total_work_time' => 8 * 60 + rand(0, 30),
                    ]);

                    // 休憩1~2回
                    $totalBreakMinutes = 0;

                    for ($breakIndex = 0; $breakIndex < rand(1, 2); $breakIndex++) {
                        $breakStart = $clockIn->copy()->addHours(3 + $breakIndex * 2)->setMinutes(0);
                        $breakEnd = $breakStart->copy()->addMinutes(rand(15, 60));

                        BreakTime::create([
                            'attendance_id' => $attendance->id,
                            'break_start' => $breakStart,
                            'break_end' => $breakEnd,
                        ]);

                        $totalBreakMinutes += $breakEnd->diffInMinutes($breakStart);
                    }

                    $attendance->update([
                        'total_break_time' => $totalBreakMinutes,
                    ]);

                    // 勤怠ステータスを退勤済に固定
                    AttendanceStatus::create([
                        'attendance_id' => $attendance->id,
                        'status' => '退勤済',
                    ]);
                }
            }
        }
    }
}
