<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AttendanceStatusTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function 勤務外の場合、勤怠ステータスが正しく表示される()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/attendance')
            ->assertSee('勤務外');
    }

    /** @test */
    public function 出勤中の場合、勤怠ステータスが正しく表示される()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => now()->subHours(2),
            'clock_out' => null,
        ]);

        $this->actingAs($user)
            ->get('/attendance')
            ->assertSee('出勤中');
    }

    /** @test */
    public function 休憩中の場合、勤怠ステータスが正しく表示される()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => now()->subHours(3),
            'clock_out' => null,
        ]);

        session(['on_break' => true]);

        $this->actingAs($user)
            ->get('/attendance')
            ->assertSee('休憩中');
    }

    /** @test */
    public function 退勤済の場合、勤怠ステータスが正しく表示される()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
        ]);

        $this->actingAs($user)
            ->get('/attendance')
            ->assertSee('退勤済');
    }
}
