<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AttendanceListTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function 自分が行った勤怠情報が全て表示されている()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 6, 9, 0, 0));

        /** @var User $user */
        $user = User::factory()->create();

        $clockInTimes = [
            Carbon::create(2025, 10, 4, 8, 0, 0),
            Carbon::create(2025, 10, 5, 9, 0, 0),
            Carbon::create(2025, 10, 6, 10, 0, 0),
        ];

        foreach ($clockInTimes as $clockIn) {
            Attendance::create([
                'user_id' => $user->id,
                'work_date' => $clockIn->toDateString(),
                'clock_in' => $clockIn,
            ]);
        }

        $response = $this->actingAs($user)->get(route('attendance.list'));

        $response->assertStatus(200);

        // 出勤時刻が全て表示されているかで確認
        foreach ($clockInTimes as $clockIn) {
            $response->assertSee($clockIn->format('H:i'));
        }
    }

    /** @test */
    public function 勤怠一覧画面に遷移した際に現在の月が表示される()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 6, 9, 0, 0));

        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('attendance.list'));

        $response->assertStatus(200)
            ->assertSee('2025/10');
    }

    /** @test */
    public function 「前月」を押下した時に表示月の前月の情報が表示される()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 6, 9, 0, 0));

        /** @var User $user */
        $user = User::factory()->create();

        // 9月分データ
        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2025-09-15',
            'clock_in' => now()->subMonth()->setTime(9, 0),
            'clock_out' => now()->subMonth()->setTime(18, 0),
        ]);

        $response = $this->actingAs($user)
            ->get(route('attendance.list', ['month' => '2025-09']));

        $response->assertStatus(200)
            ->assertSee('2025/09')
            ->assertSee('09/15')
            ->assertSee('09:00')
            ->assertSee('18:00');
    }

    /** @test */
    public function 「翌月」を押下した時に表示月の前月の情報が表示される()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 6, 9, 0, 0));

        /** @var User $user */
        $user = User::factory()->create();

        // 11月分データ
        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2025-11-03',
            'clock_in' => now()->addMonth()->setTime(9, 0),
            'clock_out' => now()->addMonth()->setTime(18, 0),
        ]);

        $response = $this->actingAs($user)
            ->get(route('attendance.list', ['month' => '2025-11']));

        $response->assertStatus(200)
            ->assertSee('2025/11')
            ->assertSee('11/03')
            ->assertSee('09:00')
            ->assertSee('18:00');
    }

    /** @test */
    public function 「詳細」を押下すると、その日の勤怠詳細画面に遷移する()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 6, 9, 0, 0));

        /** @var User $user */
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2025-10-06',
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(18, 0),
        ]);

        $response = $this->actingAs($user)
            ->get(route('attendance.list'));

        $response->assertSee('詳細');

        $this->actingAs($user)
            ->get(route('attendance.detail', $attendance->id))
            ->assertStatus(200)
            ->assertSee('勤怠詳細')
            ->assertSee('10月6日');
    }
}
