<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ClockInTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function 出勤ボタンが正しく機能する()
    {
        /** @var User $user */
        $user = User::factory()->create();

        // 出勤ボタン確認
        $this->actingAs($user)
            ->get('/attendance')
            ->assertSee('出勤');

        $response = $this->actingAs($user)
            ->post(route('attendance.store'), [
            'clock_in' => now()->format('H:i'),
            ]);

        $response->assertRedirect(route('attendance.index'));

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
        ]);

        // ステータス表示確認
        $this->actingAs($user)
            ->get('/attendance')
            ->assertSee('出勤中');
    }

    /** @test */
    public function 出勤は一日一回のみできる()
    {
        /** @var User $user */
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHours(8),
        ]);

        $this->actingAs($user)
            ->get('/attendance')
            ->assertDontSee('出勤');
    }

    /** @test */
    public function 出勤時刻が勤怠一覧画面で確認できる()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('attendance.store'), [
                'clock_in' => now()->format('H:i'),
            ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertSee(now()->format('Y年m月'));
        $response->assertSee(now()->format('d'));
        $response->assertSee(now()->format('H:i'));
    }
}
