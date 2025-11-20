<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Jurusan;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Roles & Permissions lengkap
        $this->call(PermissionRoleSeeder::class);

        // 2) Jurusan minimal
        if (Jurusan::count() === 0) {
            Jurusan::factory()->count(4)->create();
        }

        // 3) Akun Bapendik (admin operasional)
        $bap = User::firstOrCreate(
            ['email' => 'bapendik@example.com'],
            ['name' => 'Bapendik Admin', 'password' => 'password']
        );
        if (method_exists($bap, 'assignRole')) {
            $bap->syncRoles('Bapendik');
        }

        // 4) Dosen sample
        Dosen::factory()->count(5)->create()->each(function (Dosen $dosen) {
            if ($dosen->user && method_exists($dosen->user, 'assignRole')) {
                $dosen->user->syncRoles('Dosen Pembimbing');
            }
        });

        // 5) Mahasiswa sample
        Mahasiswa::factory()->count(20)->create()->each(function (Mahasiswa $mhs) {
            if ($mhs->user && method_exists($mhs->user, 'assignRole')) {
                $mhs->user->syncRoles('Mahasiswa');
            }
        });

        // 6) TTD/Signatory defaults
        $this->call(SignatorySeeder::class);

        // 7) (Opsional) People demo accounts
        $this->call(PeopleDemoSeeder::class);
    }
}
