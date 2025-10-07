<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AttendanceDetailTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function 勤怠詳細画面の名前がログインユーザーの氏名になっている()
    {
        /** @var User $user */
        $user = User::factory()->create(['name' => '山田太郎']);

        Carbon::setTestNow(Carbon::create(2025, 10, 7, 9, 0, 0));
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
        ]);

        $this->actingAs($user)
            ->get("/attendance/detail/{$attendance->id}")
            ->assertStatus(200)
            ->assertSee('山田　太郎');
    }

    /** @test */
    public function 勤怠詳細画面の「日付」が選択した日付になっている()
    {
        /** @var User $user */
        $user = User::factory()->create();

        Carbon::setTestNow(Carbon::create(2025, 10, 7, 9, 0, 0));
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
        ]);

        $this->actingAs($user)
            ->get("/attendance/detail/{$attendance->id}")
            ->assertStatus(200)
            ->assertSee('2025年')
            ->assertSee('10月7日');
    }

    /** @test */
    public function 出勤・退勤の時間がログインユーザーの打刻と一致している()
    {
        /** @var User $user */
        $user = User::factory()->create();

        Carbon::setTestNow(Carbon::create(2025, 10, 7, 9, 0, 0));
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->setHour(9),
            'clock_out' => now()->setHour(18),
        ]);

        $this->actingAs($user)
            ->get("/attendance/detail/{$attendance->id}")
            ->assertStatus(200)
            ->assertSee('09:00')
            ->assertSee('18:00');
    }

    /** @test */
    public function 休憩の時間がログインユーザーの打刻と一致している()
    {
        /** @var User $user */
        $user = User::factory()->create();

        Carbon::setTestNow(Carbon::create(2025, 10, 7, 9, 0, 0));
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->setHour(9),
            'clock_out' => now()->setHour(18),
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => now()->setHour(12),
            'break_end' => now()->setHour(13),
        ]);


        $this->actingAs($user)
            ->get("/attendance/detail/{$attendance->id}")
            ->assertStatus(200)
            ->assertSee('12:00')
            ->assertSee('13:00');
    }
}
