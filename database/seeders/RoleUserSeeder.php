<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleUserSeeder extends Seeder
{
    public function run(): void
    {
        // Buat role
        $owner  = Role::firstOrCreate(['name' => 'owner']);
        $cashier = Role::firstOrCreate(['name' => 'cashier']);

        // Buat user admin/owner
        $admin = User::firstOrCreate(
            ['email' => 'admin@pos.local'],
            ['name' => 'Owner Admin', 'password' => bcrypt('password')]
        );
        $admin->assignRole($owner);

        // Contoh user kasir
        $kasir = User::firstOrCreate(
            ['email' => 'kasir@pos.local'],
            ['name' => 'Kasir 1', 'password' => bcrypt('password')]
        );
        $kasir->assignRole($cashier);
    }
}
