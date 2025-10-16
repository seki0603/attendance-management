<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
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
        $faker = Faker::create(config('app.faker_locale', 'ja_JP'));

        // 管理者ユーザー
        User::create([
            'name' => $faker->name(),
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // 勤怠履歴付き一般ユーザー5名
        foreach (range(1, 5) as $index) {
            User::create([
                'name' => $faker->name(),
                'email' => "test{$index}@example.com",
                'password' => Hash::make('password123'),
                'role' => 'user',
                'email_verified_at' => now(),
            ]);
        }
    }
}
