<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AdminUser::firstOrCreate(
            ['email' => 'admin@fabriku.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );

        $this->command->info('Super Admin created:');
        $this->command->info('Email: admin@fabriku.com');
        $this->command->info('Password: password');
    }
}
