<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class AdminStaffTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function 管理者は全一般ユーザーの「氏名」「メールアドレス」を確認できる()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 15, 9, 0, 0));

        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        /** @var User $user */
        $users = User::factory(3)->create();

        $response = $this->actingAs($admin)
            ->get(route('admin.staff.list'));

        $response->assertStatus(200);

            foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }

    /** @test */
    public function ユーザーの勤怠情報が正しく表示される()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 15, 9, 0, 0));

        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        /** @var User $user */
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2025-10-15',
            'clock_in' => now(),
            'clock_out' => now()->addHours(8),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.staff.list', ['id' => $user->id]));

        $response->assertStatus(200)
            ->assertSee($user->display_name)
            ->assertSee('10/15')
            ->assertSee('09:00')
            ->assertSee('17:00');
    }

    /** @test */
    public function 「前月」を押下した時に表示月の前月の情報が表示される()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 15, 9, 0, 0));

        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        /** @var User $user */
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2025-09-15',
            'clock_in' => now()->subMonth()->setTime(9, 0),
            'clock_out' => now()->subMonth()->setTime(18, 0),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.staff.list', ['id' => $user->id, 'month' => '2025-09']));

        $response->assertStatus(200)
            ->assertSee('2025/09')
            ->assertSee('09/15')
            ->assertSee('09:00')
            ->assertSee('18:00');
    }

    /** @test */
    public function 「翌月」を押下した時に表示月の翌月の情報が表示される()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 15, 9, 0, 0));

        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        /** @var User $user */
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2025-11-15',
            'clock_in' => now()->subMonth()->setTime(9, 0),
            'clock_out' => now()->subMonth()->setTime(18, 0),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.staff.list', ['id' => $user->id, 'month' => '2025-11']));

        $response->assertStatus(200)
            ->assertSee('2025/11')
            ->assertSee('11/15')
            ->assertSee('09:00')
            ->assertSee('18:00');
    }

    /** @test */
    public function 「詳細」を押下すると、その日の勤怠詳細画面に遷移する()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 15, 9, 0, 0));

        /** @var User $admin */
        $admin = User::factory()->create(['role' => 'admin']);

        /** @var User $user */
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2025-10-15',
            'clock_in' => now(),
            'clock_out' => now()->setTime(18, 0),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.staff.list', ['id' => $user->id]
        ));

        $response->assertSee('詳細');

        $this->actingAs($admin)
            ->get(route('attendance.detail', $attendance->id))
            ->assertStatus(200)
            ->assertSee('勤怠詳細')
            ->assertSee('10月15日')
            ->assertSee('09:00')
            ->assertSee('18:00');
    }
}
