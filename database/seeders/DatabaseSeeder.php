<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Test User',  'email' => 'test@example.com'],
            ['name' => 'Admin User', 'email' => 'admin@example.com'],
        ];

        foreach ($users as $data) {
            $user = User::query()->firstOrNew(['email' => $data['email']]);
            $user->name = $data['name'];
            $user->password = 'password';
            $user->verified_at = now();
            $user->save();
        }
    }
}
