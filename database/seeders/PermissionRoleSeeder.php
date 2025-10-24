<?php

namespace Database\Seeders;

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
        // Pastikan cache permission direset agar perubahan langsung terpakai
        app('cache')->forget('spatie.permission.cache');

        // Definisikan seluruh permission yang dipakai aplikasi
        $permissions = [
            // Surat Pengantar (SP)
            'sp.create',        // Mahasiswa membuat pengajuan SP
            'sp.view',          // Melihat daftar SP milik sendiri (Mhs) / semua (role tertentu)
            'sp.validate',      // Bapendik memvalidasi & menerbitkan SP

            // Kerja Praktik (KP)
            'kp.create',        // Mahasiswa membuat pengajuan KP
            'kp.view',          // Melihat KP (milik sendiri / semua tergantung role)
            'kp.review',        // Komisi (dan/atau Dosen Pembimbing) me-review KP
            'kp.approve',       // Komisi menyetujui & menetapkan pembimbing
                                // (Jika kamu ingin pisah review vs approve, biarkan keduanya)

            // Bimbingan
            'bimbingan.create',
            'bimbingan.view',
            'bimbingan.verify',

            // Seminar
            'seminar.register',
            'seminar.schedule',
            'seminar.view',

            // Nilai
            'nilai.input',
            'nilai.view',

            // Master data
            'masterdata.manage',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate([
                'name'       => $p,
                'guard_name' => 'web',
            ]);
        }

        // Definisikan roles
        $roleMhs = Role::firstOrCreate(['name' => 'Mahasiswa',        'guard_name' => 'web']);
        $roleBap = Role::firstOrCreate(['name' => 'Bapendik',          'guard_name' => 'web']);
        $roleDsp = Role::firstOrCreate(['name' => 'Dosen Pembimbing',  'guard_name' => 'web']);
        $roleKom = Role::firstOrCreate(['name' => 'Dosen Komisi',      'guard_name' => 'web']);

        // Berikan permission ke masing-masing role
        $roleMhs->givePermissionTo([
            'sp.create','sp.view',
            'kp.create','kp.view',
            'bimbingan.create','bimbingan.view',
            'seminar.register','seminar.view',
            'nilai.view',
        ]);

        $roleBap->givePermissionTo([
            'sp.validate',
            'kp.approve',              // jika Bapendik ikut approve SPK, biarkan. Kalau tidak, boleh dihapus.
            'seminar.schedule','seminar.view',
            'masterdata.manage',
        ]);

        // Dosen Pembimbing (opsional diberi akses review KP jika kebijakan kampus mengizinkan)
        $roleDsp->givePermissionTo([
            'bimbingan.verify',
            'nilai.input',
            'seminar.view',
            'kp.review',               // opsional: jika DSP boleh mengakses menu review KP
        ]);

        // Dosen Komisi — aktor utama untuk review/approve KP
        $roleKom->givePermissionTo([
            'kp.review',
            'kp.approve',
            'nilai.input',
            'seminar.view',
        ]);
    }
}
