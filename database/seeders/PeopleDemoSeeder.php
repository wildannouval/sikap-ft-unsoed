<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\Jurusan;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Database\Seeder;

class PeopleDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Jurusan demo
        $ti = Jurusan::firstOrCreate(['nama_jurusan' => 'Teknik Informatika']);

        // Mahasiswa
        $mhsUser = User::firstOrCreate(
            ['email' => 'mhs@example.com'],
            ['name' => 'Mahasiswa Satu', 'password' => 'password']
        );
        $mhsUser->syncRoles('Mahasiswa');

        Mahasiswa::firstOrCreate(
            ['user_id' => $mhsUser->id],
            [
                'jurusan_id'               => $ti->id,
                'mahasiswa_name'           => $mhsUser->name,
                'mahasiswa_nim'            => 'F1E0000001',
                'mahasiswa_tahun_angkatan' => 2022,
            ]
        );

        // Bapendik
        $bap = User::firstOrCreate(
            ['email' => 'bap@example.com'],
            ['name' => 'Bapendik', 'password' => 'password']
        );
        $bap->syncRoles('Bapendik');

        // Dosen Pembimbing (user + dosen)
        $dspUser = User::firstOrCreate(
            ['email' => 'dsp@example.com'],
            ['name' => 'Dosen Pembimbing', 'password' => 'password']
        );
        $dspUser->syncRoles('Dosen Pembimbing');

        Dosen::firstOrCreate(
            ['user_id' => $dspUser->id],
            [
                'dosen_name'   => $dspUser->name,
                'dosen_nip'    => null,
                'jurusan_id'   => $ti->id,
                'is_komisi_kp' => false,
            ]
        );

        // Dosen Komisi (user + dosen)
        $komUser = User::firstOrCreate(
            ['email' => 'kom@example.com'],
            ['name' => 'Dosen Komisi', 'password' => 'password']
        );
        $komUser->syncRoles('Dosen Komisi');

        Dosen::firstOrCreate(
            ['user_id' => $komUser->id],
            [
                'dosen_name'   => $komUser->name,
                'dosen_nip'    => null,
                'jurusan_id'   => $ti->id,
                'is_komisi_kp' => true,
            ]
        );
    }
}
