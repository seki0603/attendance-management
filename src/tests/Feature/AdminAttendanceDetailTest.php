<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AdminAttendanceDetailTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function 勤怠詳細画面に表示されるデータが選択したものになっている()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 12, 9, 0, 0));

        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        /** @var User $user */
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
        ]);

        $this->actingAs($admin)
            ->get("admin/attendance/{$attendance->id}")
            ->assertStatus(200)
            ->assertSee($user->full_name)
            ->assertSee('2025年')
            ->assertSee('10月12日');
    }

    /** @test */
    public function 出勤時間が退勤時間より後になっている場合、エラーメッセージを表示()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 12, 9, 0, 0));

        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        /** @var User $user */
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($admin)->put(route('admin.attendance.update', ['id' => $attendance->id]), [
            'work_date' => $attendance->work_date,
            'clock_in' => now()->setHour(19),
            'clock_out' => now()->setHour(18),
        ]);

        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 休憩開始時間が退勤時間より後になっている場合、エラーメッセージを表示()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 12, 9, 0, 0));

        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        /** @var User $user */
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($admin)->put(route('admin.attendance.update', ['id' => $attendance->id]), [
            'work_date' => $attendance->work_date,
            'clock_out' => now()->setHour(18),
            'break_start_1' => now()->setHour(19),
        ]);

        $response->assertSessionHasErrors([
            'break_start_1' => '休憩時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 休憩終了時間が退勤時間より後になっている場合、エラーメッセージを表示()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 12, 9, 0, 0));

        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        /** @var User $user */
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($admin)->put(route('admin.attendance.update', ['id' => $attendance->id]), [
            'work_date' => $attendance->work_date,
            'clock_out' => now()->setHour(18),
            'break_start_1' => now()->setHour(17),
            'break_end_1' => now()->setHour(19),
        ]);

        $response->assertSessionHasErrors([
            'break_end_1' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 備考欄が未入力の場合のエラーメッセージを表示()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 12, 9, 0, 0));

        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        /** @var User $user */
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($admin)->put(route('admin.attendance.update', ['id' => $attendance->id]), [
            'work_date' => $attendance->work_date,
            'note' => '',
            'status' => '承認待ち',
        ]);

        $response->assertSessionHasErrors([
            'note' => '備考を記入してください',
        ]);
    }
}
