<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\CorrectionRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CorrectionRequestListTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function 「承認待ち」にログインユーザーが行った申請が全て表示されている()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 10, 9, 0, 0));

        /** @var User $user */
        $user = User::factory()->create();

        $attendances = Attendance::factory()->count(3)->sequence(
            ['work_date' => Carbon::now()->subDays(2)->toDateString()],
            ['work_date' => Carbon::now()->subDay()->toDateString()],
            ['work_date' => Carbon::now()->toDateString()],
        )->create(['user_id' => $user->id]);

        foreach ($attendances as $attendance) {
            CorrectionRequest::create([
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
                'work_date' => $attendance->work_date,
                'status' => '承認待ち',
                'created_at' => Carbon::now(),
            ]);
        }

        $response = $this->actingAs($user)
            ->get('/stamp_correction_request/list?tab=waiting');

        $response->assertStatus(200)
            ->assertSee('承認待ち')
            ->assertSee($user->name)
            ->assertSee('2025/10/08')
            ->assertSee('2025/10/09')
            ->assertSee('2025/10/10');
    }

    /** @test */
    public function 「承認済み」に管理者が承認した修正申請が全て表示されている()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 10, 9, 0, 0));

        /** @var User $user */
        $user = User::factory()->create();

        $attendances = Attendance::factory()->count(3)->sequence(
            ['work_date' => Carbon::now()->subDays(2)->toDateString()],
            ['work_date' => Carbon::now()->subDay()->toDateString()],
            ['work_date' => Carbon::now()->toDateString()],
        )->create(['user_id' => $user->id]);

        foreach ($attendances as $attendance) {
            CorrectionRequest::create([
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
                'work_date' => $attendance->work_date,
                'status' => '承認済み',
                'created_at' => Carbon::now(),
            ]);
        }

        $response = $this->actingAs($user)
            ->get('/stamp_correction_request/list?tab=completed');

        $response->assertStatus(200)
            ->assertSee('承認済み')
            ->assertSee($user->name)
            ->assertSee('2025/10/08')
            ->assertSee('2025/10/09')
            ->assertSee('2025/10/10');
    }

    /** @test */
    public function 各申請の「詳細」を押下すると勤怠詳細画面に遷移する()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 10, 9, 0, 0));

        /** @var User $user */
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-10-10',
        ]);

        $request = CorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'work_date' => $attendance->work_date,
            'status' => '承認待ち',
            'created_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($user)
            ->get('/stamp_correction_request/list?tab=waiting');

        $response->assertSee('詳細');

        $this->actingAs($user)
            ->get(route('attendance.detail', $request->attendance_id))
            ->assertStatus(200)
            ->assertSee('勤怠詳細')
            ->assertSee('10月10日');
    }
}
