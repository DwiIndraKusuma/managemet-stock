<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin_gudang')->first();
        $spvRole = Role::where('name', 'spv')->first();
        $technicianRole = Role::where('name', 'technician')->first();

        $users = [
            [
                'name' => 'Admin Gudang',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role_id' => $adminRole->id,
                'status' => 'active',
            ],
            [
                'name' => 'Supervisor',
                'email' => 'spv@example.com',
                'password' => Hash::make('password'),
                'role_id' => $spvRole->id,
                'status' => 'active',
            ],
            [
                'name' => 'Technician 1',
                'email' => 'technician1@example.com',
                'password' => Hash::make('password'),
                'role_id' => $technicianRole->id,
                'status' => 'active',
            ],
            [
                'name' => 'Technician 2',
                'email' => 'technician2@example.com',
                'password' => Hash::make('password'),
                'role_id' => $technicianRole->id,
                'status' => 'active',
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
