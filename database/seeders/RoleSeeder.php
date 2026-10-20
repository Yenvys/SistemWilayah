<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // create roles
        Role::create(['name' => 'superadmin']);
        Role::create(['name' => 'desa']);
        Role::create(['name' => 'rw']);
        Role::create(['name' => 'rt']);
        Role::create(['name' => 'warga']);
        Role::create(['name' => 'ai']);
    }
}
