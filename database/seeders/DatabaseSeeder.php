<?php

namespace Database\Seeders;

use App\Enums\UserRole;
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
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Conduttore',
            'email' => 'c@beliven.com',
            'role' => UserRole::Admin->value,
        ]);

        User::factory()->create([
            'name' => 'Player 1',
            'email' => 'p1@beliven.com',
            'role' => UserRole::Player->value,
            'score' => 0,
            'banned' => false,
        ]);

        User::factory()->create([
            'name' => 'Player 2',
            'email' => 'p2@beliven.com',
            'role' => UserRole::Player->value,
            'score' => 0,
            'banned' => false,
        ]);
    }
}
