<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionRoleSeeder extends Seeder
{
    /**
     * Buat permission & role dasar aplikasi.
     */
    public function run(): void
    {
        // Bersihkan cache permission agar perubahan terdeteksi
        app('cache')->forget('spatie.permission.cache');

        $permissions = [
            // Surat Pengantar
            'sp.create', 'sp.view', 'sp.validate',

            // KP
            'kp.create', 'kp.view', 'kp.approve',

            // Bimbingan
            'bimbingan.create', 'bimbingan.view', 'bimbingan.verify',

            // Seminar
            'seminar.register', 'seminar.schedule', 'seminar.view',

            // Nilai
            'nilai.input', 'nilai.view',

            // Master data (hak admin/bapendik)
            'masterdata.manage',

            // (opsional) Penandatangan
            'signatory.manage',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Roles
        $roleMhs = Role::firstOrCreate(['name' => 'Mahasiswa',        'guard_name' => 'web']);
        $roleBap = Role::firstOrCreate(['name' => 'Bapendik',         'guard_name' => 'web']);
        $roleDsp = Role::firstOrCreate(['name' => 'Dosen Pembimbing', 'guard_name' => 'web']);
        $roleKom = Role::firstOrCreate(['name' => 'Dosen Komisi',     'guard_name' => 'web']);

        // Assign permissions per role
        $roleMhs->syncPermissions([
            'sp.create','sp.view',
            'kp.create','kp.view',
            'bimbingan.create','bimbingan.view',
            'seminar.register','seminar.view',
            'nilai.view',
        ]);

        $roleBap->syncPermissions([
            'sp.validate',
            'kp.approve',
            'seminar.schedule','seminar.view',
            'masterdata.manage',
            'signatory.manage', // boleh kelola penandatangan
        ]);

        $roleDsp->syncPermissions([
            'bimbingan.verify',
            'nilai.input',
            'seminar.view',
        ]);

        $roleKom->syncPermissions([
            'kp.approve',
            'nilai.input',
            'seminar.view',
        ]);
    }
}
