<?php

namespace Tests\Feature;


use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AdminAttendanceListTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function その日になされた全ユーザーの勤怠情報が正確に確認できる()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 11, 9, 0, 0));

        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        /** @var User $user */
        $users = User::factory(3)->create();

        Attendance::create([
            'user_id' => $users[0]->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now(),
            'clock_out' => now()->setTime(17, 0),
            'total_break_time' => 60,
            'total_work_time' => 480,
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list');

        // 全ユーザー名前表示確認
        foreach ($users as $user) {
            $response->assertSee($user->full_name);
        }

        // 出勤したユーザーの勤怠情報確認
        $response->assertSee('09:00');
        $response->assertSee('17:00');
        $response->assertSee('1:00');
        $response->assertSee('8:00');
        // 出勤してないユーザーの勤怠情報非表示確認
        $response->assertDontSee('10:00');
        $response->assertDontSee('18:00');
    }

    /** @test */
    public function 遷移した際に現在の日付が表示される()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 11, 9, 0, 0));

        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/attendance/list');

        $response->assertSee('2025年10月11日');
        $response->assertSee('2025/10/11');
    }

    /** @test */
    public function 「前日」を押下した時に前の日の勤怠情報が表示される()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 11, 9, 0, 0));

        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        /** @var User $user */
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2025-10-10',
            'clock_in' => now()->subDay()->setTime(9, 0),
            'clock_out' => now()->subDay()->setTime(17, 0),
            'total_break_time' => 60,
            'total_work_time' => 480,
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/list?date=2025-10-10');

        $response->assertSee('2025/10/10');
        $response->assertSee('09:00');
        $response->assertSee('17:00');
        $response->assertSee('1:00');
        $response->assertSee('8:00');
    }

    /** @test */
    public function 「翌日」を押下した時に次の日の勤怠情報が表示される()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 11, 9, 0, 0));

        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        /** @var User $user */
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2025-10-12',
            'clock_in' => now()->addDay()->setTime(9, 0),
            'clock_out' => now()->addDay()->setTime(18, 0),
            'total_break_time' => 90,
            'total_work_time' => 450,
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/list?date=2025-10-12');

        $response->assertSee('2025/10/12');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('1:30');
        $response->assertSee('7:30');
    }
}
