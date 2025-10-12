<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Attendance;
use App\Models\CorrectionRequest;
use App\Models\CorrectionBreak;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;

class CorrectionRequestSeeder extends Seeder
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

        // ユーザーごとに新しい勤怠を10件取得
        foreach ($users as $user) {
            $attendances = Attendance::where('user_id', $user->id)
                ->orderByDesc('work_date')
                ->take(10)
                ->get();

            // 承認待ち3件作成
            foreach ($attendances->take(3) as $attendance) {
                $request = CorrectionRequest::create([
                    'attendance_id' => $attendance->id,
                    'user_id'       => $user->id,
                    'work_date'     => $attendance->work_date,
                    'clock_in'      => Carbon::parse($attendance->clock_in)->subMinutes(rand(5, 10)),
                    'clock_out'     => $attendance->clock_out,
                    'note'          => '遅延のため',
                    'status'        => '承認待ち',
                    'created_at'    => Carbon::now()->subDays(rand(1, 3)),
                ]);

                // 休憩時間の申請を1〜2件作成
                foreach (range(1, rand(1, 2)) as $i) {
                    $breakStart = Carbon::parse($attendance->clock_in)->addHours(3 + $i);
                    $breakEnd   = $breakStart->copy()->addMinutes(rand(15, 45));

                    CorrectionBreak::create([
                        'correction_request_id' => $request->id,
                        'break_start'           => $breakStart,
                        'break_end'             => $breakEnd,
                    ]);
                }
            }

            // 承認済み3件作成
            foreach ($attendances->skip(3)->take(3) as $attendance) {
                $request = CorrectionRequest::create([
                    'attendance_id' => $attendance->id,
                    'user_id'       => $user->id,
                    'work_date'     => $attendance->work_date,
                    'clock_in'      => Carbon::parse($attendance->clock_in)->subMinutes(rand(5, 10)),
                    'clock_out'     => $attendance->clock_out,
                    'note'          => '遅延のため',
                    'status'        => '承認済み',
                    'created_at'    => Carbon::now()->subDays(rand(4, 7)),
                    'updated_at'    => Carbon::now()->subDays(rand(1, 2)),
                ]);

                // 休憩時間の申請を1〜2件作成
                foreach (range(1, rand(1, 2)) as $i) {
                    $breakStart = Carbon::parse($attendance->clock_in)->addHours(3 + $i);
                    $breakEnd   = $breakStart->copy()->addMinutes(rand(15, 45));

                    CorrectionBreak::create([
                        'correction_request_id' => $request->id,
                        'break_start'           => $breakStart,
                        'break_end'             => $breakEnd,
                    ]);
                }
            }
        }
    }
}
