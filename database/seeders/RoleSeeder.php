<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin_gudang',
                'display_name' => 'Admin Gudang',
                'description' => 'Administrator gudang dengan akses penuh',
            ],
            [
                'name' => 'spv',
                'display_name' => 'Supervisor',
                'description' => 'Supervisor dengan akses approval',
            ],
            [
                'name' => 'technician',
                'display_name' => 'Technician',
                'description' => 'Teknisi yang dapat membuat request',
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
