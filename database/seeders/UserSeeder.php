<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'talabiyatec@gmail.com'],
            [
                'name' => 'Admin_Farried',
                'password' => Hash::make('talabiyatec123***'),
            ]
        );

        $admin->assignRole('admin');

        $this->command->info('✅ Admin user created successfully!');
    }
}
