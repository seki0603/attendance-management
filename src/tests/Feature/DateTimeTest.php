<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class DateTimeTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function 現在の日時情報がUIと同じ形式で出力されている()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 5, 10, 30, 0));

        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSeeInOrder([
            now()->format('Y年m月d日'),
            '（' . ['日', '月', '火', '水', '木', '金', '土'][now()->dayOfWeek] . '）',
        ]);
    }
}
