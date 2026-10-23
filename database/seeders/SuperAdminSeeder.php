<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('INITIAL_ADMIN_EMAIL');
        $password = env('INITIAL_ADMIN_PASSWORD');

        if (! $email || ! $password) {
            $this->command?->warn('Skipping admin creation: INITIAL_ADMIN_EMAIL/PASSWORD not configured.');

            return;
        }

        if (strlen($password) < 12) {
            throw new RuntimeException('INITIAL_ADMIN_PASSWORD must be at least 12 characters.');
        }

        if (User::where('email', $email)->exists()) {
            $this->command?->warn('Admin account already exists; no credentials were changed.');

            return;
        }

        User::create([
            'name' => 'السوبر أدمن',
            'email' => $email,
            'phone' => env('INITIAL_ADMIN_PHONE', '01000000000'),
            'role' => 'super_admin',
            'is_active' => true,
            'password' => Hash::make($password),
        ]);
    }
}
