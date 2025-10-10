<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class BreakTimeTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function 休憩ボタンが正しく機能する()
    {

        /** @var User $user */
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => now()->subHours(2),
            'clock_out' => null,
        ]);

        // 休憩入ボタン確認
        $this->actingAs($user)
            ->get('/attendance')
            ->assertSee('休憩入');

        $response = $this->actingAs($user)
            ->post(route('attendance.store'), [
                'break_start' => now()->format('H:i'),
            ]);

        $response->assertRedirect(route('attendance.index'));

        $this->assertDatabaseHas('breaks', [
            'attendance_id' => $attendance->id,
        ]);

        // ステータス表示確認
        $this->actingAs($user)
            ->get('/attendance')
            ->assertSee('休憩中');
    }

    /** @test */
    public function 休憩戻ボタンが正しく機能する()
    {
        /** @var User $user */
        $user = User::factory()->create();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => now()->subHours(3),
            'clock_out' => null,
        ]);

        $this->actingAs($user)->post(route('attendance.store'), [
            'break_start' => now()->format('H:i'),
        ]);

        // 休憩戻ボタン確認
        $this->actingAs($user)
            ->get('/attendance')
            ->assertSee('休憩戻');

        $this->actingAs($user)->post(route('attendance.store'), [
            'break_end' => now()->addMinutes(20)->format('H:i'),
        ]);

        $this->assertNotNull(BreakTime::first()->break_end);

        // ステータス表示確認
        $this->actingAs($user)
            ->get('/attendance')
            ->assertSee('出勤中');
    }

    /** @test */
    public function 休憩は一日に何回でもできる()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => now()->subHours(3),
            'clock_out' => null,
        ]);

        // 1回目の休憩
        $this->actingAs($user)->post(route('attendance.store'), [
            'break_start' => now()->format('H:i'),
        ]);
        $this->actingAs($user)->post(route('attendance.store'), [
            'break_end' => now()->addMinutes(15)->format('H:i'),
        ]);

        // 2回目の休憩開始
        $this->actingAs($user)->post(route('attendance.store'), [
            'break_start' => now()->format('H:i'),
        ]);

        $this->assertEquals(2, BreakTime::where('attendance_id', $attendance->id)->count());

        // 休憩戻ボタン確認
        $this->actingAs($user)
            ->get('/attendance')
            ->assertSee('休憩戻');
    }

    /** @test */
    public function 休憩時刻が勤怠一覧画面で確認できる()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $today = '2025-10-06';

        Carbon::setTestNow(Carbon::create(2025, 10, 6, 9, 0, 0));
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $today,
            'clock_in' => now()->subHours(4),
            'clock_out' => null,
        ]);

        Carbon::setTestNow(Carbon::create(2025, 10, 6, 10, 0, 0));
        $this->actingAs($user)->post(route('attendance.store'), [
            'break_start' => now()->subHour()->format('H:i'),
        ]);

        Carbon::setTestNow(Carbon::create(2025, 10, 6, 10, 30, 0));
        $this->actingAs($user)->post(route('attendance.store'), [
            'break_end' => now()->subMinutes(30)->format('H:i'),
        ]);

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->first();

        $minutes = $attendance->total_break_time;
        $hours = intdiv($minutes, 60);
        $mins  = $minutes % 60;
        $expectedFormatted = sprintf('%d:%02d', $hours, $mins);

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertSee($expectedFormatted);
        // Figmaによる画面設計に従い、時刻ではなく合計休憩時間表示で確認。
    }
}
