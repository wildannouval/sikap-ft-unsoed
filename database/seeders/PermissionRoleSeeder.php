<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionRoleSeeder extends Seeder
{
    public function run(): void
    {
        app('cache')->forget('spatie.permission.cache');

        $permissions = [
            'sp.create',
            'sp.view',
            'sp.validate',
            'kp.create',
            'kp.view',
            'kp.review',
            'kp.approve',
            'bimbingan.create',
            'bimbingan.view',
            'bimbingan.verify',
            'seminar.register',
            'seminar.schedule',
            'seminar.view',
            'nilai.input',
            'nilai.view',
            'masterdata.manage',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $roleMhs = Role::firstOrCreate(['name' => 'Mahasiswa',       'guard_name' => 'web']);
        $roleBap = Role::firstOrCreate(['name' => 'Bapendik',         'guard_name' => 'web']);
        $roleDsp = Role::firstOrCreate(['name' => 'Dosen Pembimbing', 'guard_name' => 'web']);
        $roleKom = Role::firstOrCreate(['name' => 'Dosen Komisi',     'guard_name' => 'web']);

        $roleMhs->syncPermissions([
            'sp.create',
            'sp.view',
            'kp.create',
            'kp.view',
            'bimbingan.create',
            'bimbingan.view',
            'seminar.register',
            'seminar.view',
            'nilai.view',
        ]);

        $roleBap->syncPermissions([
            'sp.validate',
            'kp.approve',
            'seminar.schedule',
            'seminar.view',
            'masterdata.manage',
        ]);

        $roleDsp->syncPermissions([
            'bimbingan.verify',
            'nilai.input',
            'seminar.view',
            'kp.review',
        ]);

        $roleKom->syncPermissions([
            'kp.review',
            'kp.approve',
            'nilai.input',
            'seminar.view',
        ]);
    }
}
