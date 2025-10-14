<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CorrectionRequestTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function 出勤時間が退勤時間より後の場合、エラーメッセージを表示()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 9, 9, 0, 0));

        /** @var User $user */
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->post(route('correction.store', ['attendance' => $attendance->id]), [
            'work_date' => $attendance->work_date,
            'clock_in' => now()->setHour(19),
            'clock_out' => now()->setHour(18),
            'status' => '承認待ち',
        ]);

        // 機能要件FN029のメッセージ文で確認
        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 休憩開始時間が退勤時間より後の場合、エラーメッセージを表示()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 9, 9, 0, 0));

        /** @var User $user */
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->post(route('correction.store', ['attendance' => $attendance->id]), [
            'work_date' => $attendance->work_date,
            'clock_out' => now()->setHour(18),
            'break_start_1' => now()->setHour(19),
            'status' => '承認待ち',
        ]);

        $response->assertSessionHasErrors([
            'break_start_1' => '休憩時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 休憩終了時間が退勤時間より後の場合、エラーメッセージを表示()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 9, 9, 0, 0));

        /** @var User $user */
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->post(route('correction.store', ['attendance' => $attendance->id]), [
            'work_date' => $attendance->work_date,
            'clock_out' => now()->setHour(18),
            'break_start_1' => now()->setHour(17),
            'break_end_1' => now()->setHour(19),
            'status' => '承認待ち',
        ]);

        $response->assertSessionHasErrors([
            'break_end_1' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 備考欄が未入力の場合、エラーメッセージを表示()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 9, 9, 0, 0));

        /** @var User $user */
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->post(route('correction.store', ['attendance' => $attendance->id]), [
            'work_date' => $attendance->work_date,
            'note' => '',
            'status' => '承認待ち',
        ]);

        $response->assertSessionHasErrors([
            'note' => '備考を記入してください',
        ]);
    }

    /** @test */
    public function 修正申請処理が実行される()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 14, 9, 0, 0));

        /** @var User $user */
        $user = User::factory()->create();

        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-10-14',
        ]);

        $this->actingAs($user)->post(route('correction.store', $attendance->id), [
            'work_date' => $attendance->work_date,
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
            'note'      => 'テスト申請',
        ]);

        $attendance->refresh();
        $correctionRequest = $attendance->correctionRequests()->first();

        $this->assertDatabaseHas('correction_requests', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
        ]);

        $response = $this->actingAs($admin)
            ->get('admin/stamp_correction_request/list?tab=waiting');

        $response->assertSee($user->name)
            ->assertSee('2025/10/14')
            ->assertSee('テスト申請')
            ->assertSee('承認待ち');

        $this->actingAs($admin)
            ->get("admin/stamp_correction_request/approve/{$correctionRequest->id}")
            ->assertSee($user->full_name)
            ->assertSee('2025年')
            ->assertSee('10月14日')
            ->assertSee('テスト申請')
            ->assertSee('承認');
    }
}
