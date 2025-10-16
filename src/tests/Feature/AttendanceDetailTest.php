<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AttendanceDetailTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function 勤怠詳細画面の名前がログインユーザーの氏名になっている()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 7, 9, 0, 0));

        /** @var User $user */
        $user = User::factory()->create(['name' => '山田太郎']);

        $attendance = Attendance::create([
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
        Carbon::setTestNow(Carbon::create(2025, 10, 7, 9, 0, 0));

        /** @var User $user */
        $user = User::factory()->create();

        $attendance = Attendance::create([
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
        Carbon::setTestNow(Carbon::create(2025, 10, 7, 9, 0, 0));

        /** @var User $user */
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
        ]);

        // 出退勤打刻
        $this->actingAs($user)->post(route('attendance.store'), [
            'clock_in' => now()->format('H:i'),
        ]);
        Carbon::setTestNow(Carbon::create(2025, 10, 7, 18, 0, 0));
        $this->actingAs($user)->post(route('attendance.store'), [
            'clock_out' => now()->format('H:i'),
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
        Carbon::setTestNow(Carbon::create(2025, 10, 7, 9, 0, 0));

        /** @var User $user */
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->setTime(9, 0),
        ]);

        // 休憩打刻
        Carbon::setTestNow(Carbon::create(2025, 10, 7, 12, 0, 0));
        $this->actingAs($user)->post(route('attendance.store'), [
            'break_start' => now()->format('H:i'),
        ]);
        Carbon::setTestNow(Carbon::create(2025, 10, 7, 13, 0, 0));
        $this->actingAs($user)->post(route('attendance.store'), [
            'break_end' => now()->format('H:i'),
        ]);

        $this->actingAs($user)
            ->get("/attendance/detail/{$attendance->id}")
            ->assertStatus(200)
            ->assertSee('12:00')
            ->assertSee('13:00');
    }
}
