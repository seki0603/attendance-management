<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 管理者ユーザー
        User::create([
            'name' => '管理者ユーザー',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // 勤怠履歴付き一般ユーザー5名
        User::factory(5)->create([
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        // 勤怠履歴なし打刻機能確認用ユーザー
        User::create([
            'name' => '打刻用ユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'email_verified_at' => now(),
        ]);
    }
}
