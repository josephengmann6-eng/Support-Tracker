<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /** Demo accounts. All share the password "password" (documented in the README). */
    public const ACCOUNTS = [
        ['Ama Serwaa Boateng', 'STF-0001', 'admin@supporttracker.test', '0244000001', 'IT Operations', User::ROLE_ADMIN],
        ['Kofi Owusu', 'STF-1042', 'kofi.owusu@supporttracker.test', '0244000042', 'Application Support', User::ROLE_MEMBER],
        ['Efua Quaye', 'STF-1057', 'efua.quaye@supporttracker.test', '0244000057', 'Application Support', User::ROLE_MEMBER],
        ['Yaw Darko', 'STF-1063', 'yaw.darko@supporttracker.test', '0244000063', 'Infrastructure', User::ROLE_MEMBER],
    ];

    public function run(): void
    {
        foreach (self::ACCOUNTS as [$name, $staffId, $email, $phone, $department, $role]) {
            User::updateOrCreate(['staff_id' => $staffId], [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'department' => $department,
                'role' => $role,
                'is_active' => true,
                'password' => 'password',
            ]);
        }
    }
}
