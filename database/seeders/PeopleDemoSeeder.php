<?php

namespace Database\Seeders;

use App\Models\Jurusan;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PeopleDemoSeeder extends Seeder
{
    /**
     * Seed contoh user: Mahasiswa, Bapendik, Dosen Pembimbing, Dosen Komisi.
     * Catatan: Seeder ini mengasumsikan PermissionRoleSeeder sudah dijalankan.
     */
    public function run(): void
    {
        // Jurusan demo
        $ti = Jurusan::firstOrCreate(['nama_jurusan' => 'Teknik Informatika']);

        // Mahasiswa
        $mhs = User::firstOrCreate(
            ['email' => 'mhs@example.com'],
            ['name' => 'Mahasiswa Satu', 'password' => Hash::make('password')]
        );
        $mhs->syncRoles('Mahasiswa');

        Mahasiswa::firstOrCreate(
            ['user_id' => $mhs->id],
            [
                'jurusan_id'     => $ti->id,
                'nama_mahasiswa' => $mhs->name,
                'nim'            => 'H1A001',
                'tahun_angkatan' => 2022,
            ]
        );

        // Bapendik
        $bap = User::firstOrCreate(
            ['email' => 'bap@example.com'],
            ['name' => 'Bapendik', 'password' => Hash::make('password')]
        );
        $bap->syncRoles('Bapendik');

        // Dosen Pembimbing
        $dsp = User::firstOrCreate(
            ['email' => 'dsp@example.com'],
            ['name' => 'Dosen Pembimbing', 'password' => Hash::make('password')]
        );
        $dsp->syncRoles('Dosen Pembimbing');

        // Dosen Komisi
        $kom = User::firstOrCreate(
            ['email' => 'kom@example.com'],
            ['name' => 'Dosen Komisi', 'password' => Hash::make('password')]
        );
        $kom->syncRoles('Dosen Komisi');
    }
}
