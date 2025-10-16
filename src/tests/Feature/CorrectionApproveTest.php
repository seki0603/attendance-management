<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\CorrectionRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CorrectionApproveTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function 承認待ちの修正申請が全て表示されている()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 14, 9, 0, 0));

        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        /** @var User $user */
        $users = User::factory(3)->create();

        foreach ($users as $user) {
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'work_date' => '2025-10-14',
            ]);

            CorrectionRequest::create([
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
                'work_date' => $attendance->work_date,
                'status' => '承認待ち',
            ]);
        }

        $response = $this->actingAs($admin)
            ->get('admin/stamp_correction_request/list?tab=waiting');

        foreach ($users as $user) {
            $response->assertSee($user->name);
        }

        $response->assertStatus(200);
        $response->assertSee('承認待ち');
    }

    /** @test */
    public function 承認済みの修正申請が全て表示されている()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 14, 9, 0, 0));

        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        $users = User::factory(3)->create();

        foreach ($users as $user) {
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'work_date' => '2025-10-14',
            ]);

            CorrectionRequest::create([
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
                'work_date' => $attendance->work_date,
                'status' => '承認済み',
            ]);
        }

        $response = $this->actingAs($admin)
            ->get('admin/stamp_correction_request/list?tab=completed');

        foreach ($users as $user) {
            $response->assertSee($user->name);
        }

        $response->assertStatus(200);
        $response->assertSee('承認済み');
    }

    /** @test */
    public function 修正申請の詳細内容が正しく表示されている()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 14, 9, 0, 0));

        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        /** @var User $user */
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2025-10-14',
        ]);

        $request = CorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'work_date' => $attendance->work_date,
            'note' => 'テスト申請',
            'status' => '承認待ち',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/stamp_correction_request/list?tab=waiting');

        $response->assertSee('詳細');

        $this->actingAs($admin)
            ->get(route('admin.correction.approve', $request->id))
            ->assertStatus(200)
            ->assertSee('勤怠詳細')
            ->assertSee($user->full_name)
            ->assertSee('10月14日')
            ->assertSee('テスト申請');
    }

    /** @test */
    public function 修正申請の承認処理が正しく行われる()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 14, 9, 0, 0));

        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        /** @var User $user */
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2025-10-13',
            'clock_in' => now()->subDay()->setTime(9, 0),
            'clock_out' => now()->subDay()->setTime(17, 0),
            'total_break_time' => 60,
            'total_work_time' => 480,
        ]);

        $request = CorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'work_date' => $attendance->work_date,
            'clock_in' => now()->subDay()->setTime(9, 0),
            'clock_out' => now()->subDay()->setTime(18, 0),
            'status' => '承認待ち',
        ]);

        $response = $this->actingAs($admin)
            ->get("admin/stamp_correction_request/approve/{$request->id}");

        $response->assertSee('承認');

        $this->actingAs($admin)->put(route('admin.correction.approve.update', $request->id));

        // 修正申請のstatus確認
            $this->assertDatabaseHas('correction_requests', [
            'id' => $request->id,
            'status' => '承認済み',
        ]);

        // 勤怠テーブルの更新確認
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_out' => now()->subDay()->setTime(18, 0)->format('Y-m-d H:i:s'),
            'total_work_time' => 540,
        ]);
    }
}
