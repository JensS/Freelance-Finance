<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed the users table with an initial owner account.
     */
    public function run(): void
    {
        // Create initial owner account if no users exist
        if (User::count() === 0) {
            User::create([
                'name' => 'Administrator',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => Role::Owner,
            ]);

            $this->command->info('Initial owner account created:');
            $this->command->info('  Email: admin@example.com');
            $this->command->info('  Password: password');
            $this->command->warn('  Please change these credentials after first login!');
        } else {
            $this->command->info('Users already exist, skipping initial owner creation.');
        }
    }
}
