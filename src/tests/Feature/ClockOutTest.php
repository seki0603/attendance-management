<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ClockOutTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function 退勤ボタンが正しく機能する()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 6, 18, 0, 0));

        /** @var User $user */
        $user = User::factory()->create();
        $today = now()->toDateString();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $today,
            'clock_in' => now()->subHours(8),
            'clock_out' => null,
        ]);

        // 退勤ボタン確認
        $this->actingAs($user)
            ->get('/attendance')
            ->assertSee('退勤');

        $response = $this->actingAs($user)->post(route('attendance.store'), [
            'clock_out' => now()->format('H:i'),
        ]);

        $response->assertRedirect(route('attendance.index'));

        $this->assertNotNull(Attendance::first()->clock_out);

        // ステータス表示確認
        $this->actingAs($user)
            ->get('/attendance')
            ->assertSee('退勤済');
    }

    /** @test */
    public function 退勤時刻が勤怠一覧画面で確認できる()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 6, 9, 0, 0));

        /** @var User $user */
        $user = User::factory()->create();
        $today = now()->toDateString();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $today,
            'clock_in' => now(),
        ]);

        Carbon::setTestNow(Carbon::create(2025, 10, 6, 17, 30, 0));
        $this->actingAs($user)->post(route('attendance.store'), [
            'clock_out' => now()->format('H:i'),
        ]);

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->first();

        $expectedTime = $attendance->clock_out->format('H:i');

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertSee($expectedTime);
    }
}
