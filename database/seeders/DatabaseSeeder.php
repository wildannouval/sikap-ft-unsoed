<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Entry point seeding.
     */
    public function run(): void
    {
        // Urutan penting: permission/role → people (assign role) → signatory
        $this->call([
            PermissionRoleSeeder::class,
            PeopleDemoSeeder::class,
            SignatorySeeder::class,
        ]);
    }
}
