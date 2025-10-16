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
        Carbon::setTestNow(Carbon::create(2025, 10, 6, 9, 0, 0));

        /** @var User $user */
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2025-10-06',
            'clock_in' => now(),
            'clock_out' => null,
        ]);

        // 休憩入ボタン確認
        $this->actingAs($user)
            ->get('/attendance')
            ->assertSee('休憩入');

        Carbon::setTestNow(Carbon::create(2025, 10, 6, 9, 45, 0));
            $response = $this->actingAs($user)
            ->post(route('attendance.store'), [
                'break_start' => now()->format('H:i'),
            ]);

        $response->assertRedirect(route('attendance.show_form'));

        $this->assertDatabaseHas('breaks', [
            'attendance_id' => $attendance->id,
        ]);

        // ステータス表示確認
        $this->actingAs($user)
            ->get('/attendance')
            ->assertSee('休憩中');
    }

    /** @test */
    public function 休憩は一日に何回でもできる()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 6, 9, 0, 0));

        /** @var User $user */
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2025-10-06',
            'clock_in' => now(),
            'clock_out' => null,
        ]);

        // 1回目の休憩
        Carbon::setTestNow(Carbon::create(2025, 10, 6, 9, 15, 0));
        $this->actingAs($user)->post(route('attendance.store'), [
            'break_start' => now()->format('H:i'),
        ]);
        Carbon::setTestNow(Carbon::create(2025, 10, 6, 9, 45, 0));
        $this->actingAs($user)->post(route('attendance.store'), [
            'break_end' => now()->format('H:i'),
        ]);

        // 休憩入ボタン再表示確認
        $this->actingAs($user)
            ->get('/attendance')
            ->assertSee('休憩入');
    }

    /** @test */
    public function 休憩戻ボタンが正しく機能する()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 6, 9, 0, 0));

        /** @var User $user */
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2025-10-06',
            'clock_in' => now(),
            'clock_out' => null,
        ]);

        Carbon::setTestNow(Carbon::create(2025, 10, 6, 9, 15, 0));
        $this->actingAs($user)->post(route('attendance.store'), [
            'break_start' => now()->format('H:i'),
        ]);

        // 休憩戻ボタン確認
        $this->actingAs($user)
            ->get('/attendance')
            ->assertSee('休憩戻');

        Carbon::setTestNow(Carbon::create(2025, 10, 6, 9, 45, 0));
            $this->actingAs($user)->post(route('attendance.store'), [
            'break_end' => now()->format('H:i'),
        ]);

        $this->assertNotNull(BreakTime::first()->break_end);

        // ステータス表示確認
        $this->actingAs($user)
            ->get('/attendance')
            ->assertSee('出勤中');
    }

    /** @test */
    public function 休憩戻は一日に何回でもできる()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 6, 9, 0, 0));

        /** @var User $user */
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2025-10-06',
            'clock_in' => now(),
            'clock_out' => null,
        ]);

        // 1回目の休憩
        Carbon::setTestNow(Carbon::create(2025, 10, 6, 10, 0, 0));
        $this->actingAs($user)->post(route('attendance.store'), [
            'break_start' => now()->format('H:i'),
        ]);
        Carbon::setTestNow(Carbon::create(2025, 10, 6, 10, 15, 0));
        $this->actingAs($user)->post(route('attendance.store'), [
            'break_end' => now()->format('H:i'),
        ]);

        // 2回目の休憩開始
        Carbon::setTestNow(Carbon::create(2025, 10, 6, 12, 0, 0));
        $this->actingAs($user)->post(route('attendance.store'), [
            'break_start' => now()->format('H:i'),
        ]);

        // 休憩戻ボタン確認
        $this->actingAs($user)
            ->get('/attendance')
            ->assertSee('休憩戻');
    }

    /** @test */
    public function 休憩時刻が勤怠一覧画面で確認できる()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 6, 9, 0, 0));

        /** @var User $user */
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2025-10-06',
            'clock_in' => now(),
            'clock_out' => null,
        ]);

        Carbon::setTestNow(Carbon::create(2025, 10, 6, 10, 0, 0));
        $this->actingAs($user)->post(route('attendance.store'), [
            'break_start' => now()->format('H:i'),
        ]);
        Carbon::setTestNow(Carbon::create(2025, 10, 6, 10, 15, 0));
        $this->actingAs($user)->post(route('attendance.store'), [
            'break_end' => now()->format('H:i'),
        ]);

        $attendance->refresh();

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertSee('0:15');
        // Figmaによる画面設計に従い、時刻ではなく合計休憩時間表示で確認。
    }
}
