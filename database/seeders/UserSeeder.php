<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'ahmedamrmedo58@gmail.com'],
            [
                'name' => 'Ahmed Amr',
                'password' => Hash::make('Aassdd102030'),
            ]
        );
    }
}
