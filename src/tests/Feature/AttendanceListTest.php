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
        /** @var User $user */
        $user = User::factory()->create();

        Carbon::setTestNow(Carbon::create(2025, 10, 6, 9, 0, 0));

        Attendance::factory()->count(3)->sequence(
            ['work_date' => Carbon::now()->subDays(2)->toDateString()],
            ['work_date' => Carbon::now()->subDay()->toDateString()],
            ['work_date' => Carbon::now()->toDateString()],
        )->create([
            'user_id' => $user->id,
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('attendance.list'));

        $response->assertStatus(200)
            ->assertSee('10/04')
            ->assertSee('10/05')
            ->assertSee('10/06');
    }

    /** @test */
    public function 勤怠一覧画面に遷移した際に現在の月が表示される()
    {
        /** @var User $user */
        $user = User::factory()->create();
        Carbon::setTestNow(Carbon::create(2025, 10, 6, 9, 0, 0));

        $response = $this->actingAs($user)->get(route('attendance.list'));

        $response->assertStatus(200)
            ->assertSee('2025/10');
    }

    /** @test */
    public function 「前月」を押下した時に表示月の前月の情報が表示される()
    {
        /** @var User $user */
        $user = User::factory()->create();

        Carbon::setTestNow(Carbon::create(2025, 10, 6, 9, 0, 0));

        // 9月分データ
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-09-15',
            'clock_in' => now()->subMonth()->setTime(9, 0),
            'clock_out' => now()->subMonth()->setTime(18, 0),
        ]);

        $response = $this->actingAs($user)
            ->get(route('attendance.list', ['month' => '2025-09']));

        $response->assertStatus(200)
            ->assertSee('2025/09')
            ->assertSee('09/15');
    }

    /** @test */
    public function 「翌月」を押下した時に表示月の前月の情報が表示される()
    {
        /** @var User $user */
        $user = User::factory()->create(['email_verified_at' => now()]);

        Carbon::setTestNow(Carbon::create(2025, 10, 6, 9, 0, 0));

        // 11月分データ
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-11-03',
            'clock_in' => now()->addMonth()->setTime(9, 0),
            'clock_out' => now()->addMonth()->setTime(18, 0),
        ]);

        // act: 「翌月」ボタン押下イメージ
        $response = $this->actingAs($user)
            ->get(route('attendance.list', ['month' => '2025-11']));

        $response->assertStatus(200)
            ->assertSee('2025/11')
            ->assertSee('11/03');
    }

    /** @test */
    public function 「詳細」を押下すると、その日の勤怠詳細画面に遷移する()
    {
        /** @var User $user */
        $user = User::factory()->create(['email_verified_at' => now()]);

        Carbon::setTestNow(Carbon::create(2025, 10, 6, 9, 0, 0));

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-10-06',
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(18, 0),
        ]);

        // act
        $response = $this->actingAs($user)->get(route('attendance.detail', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee('10月6日');
    }
}
