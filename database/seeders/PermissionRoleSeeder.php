<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        $perms = [
            'sp.create','sp.view','sp.validate',
            'kp.create','kp.view','kp.approve',
            'bimbingan.create','bimbingan.view','bimbingan.verify',
            'seminar.register','seminar.schedule','seminar.view',
            'nilai.input','nilai.view','masterdata.manage',
        ];
        foreach ($perms as $p) { Permission::firstOrCreate(['name'=>$p,'guard_name'=>'web']); }

        $roleMhs  = Role::firstOrCreate(['name'=>'Mahasiswa', 'guard_name'=>'web']);
        $roleBap  = Role::firstOrCreate(['name'=>'Bapendik', 'guard_name'=>'web']);
        $roleDsp  = Role::firstOrCreate(['name'=>'Dosen Pembimbing', 'guard_name'=>'web']);
        $roleKom  = Role::firstOrCreate(['name'=>'Dosen Komisi', 'guard_name'=>'web']);

        $roleMhs->givePermissionTo(['sp.create','sp.view','kp.create','kp.view','bimbingan.create','bimbingan.view','seminar.register','seminar.view','nilai.view']);
        $roleBap->givePermissionTo(['sp.validate','kp.approve','seminar.schedule','seminar.view','masterdata.manage']);
        $roleDsp->givePermissionTo(['bimbingan.verify','nilai.input','seminar.view']);
        $roleKom->givePermissionTo(['kp.approve','nilai.input','seminar.view']);
    }
}
