<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\User;
use App\Models\Jurusan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DosenSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Dr. Ir. Suryanto, M.T.',
            'Dr. Andini Pramesti, S.T., M.Eng.',
            'Ir. Bambang Kurnia, M.T.',
            'Dra. Retno Lestari, M.Kom.',
            'Ahmad Fauzi, S.T., M.T.',
            'Dwi Rahayu, S.T., M.T.',
            'M. Rizky Maulana, S.T., M.T.',
            'Nurul Hidayati, S.T., M.Kom.',
            'Fajar Pratama, S.T., M.T.',
            'Hendra Wijaya, S.T., M.Eng.',
            'Larasati Putri, S.T., M.T.',
            'Yudi Saputra, S.T., M.T.',
        ];

        $jurusanId = Jurusan::query()->inRandomOrder()->value('id');

        foreach ($names as $name) {
            // buat user dosen
            $email = Str::of($name)->lower()->replaceMatches('/[^a-z]+/', '.') . '@unsoed.ac.id';
            $user  = User::firstOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => 'password']
            );
            $user->syncRoles('Dosen Pembimbing');

            // buat dosen
            Dosen::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'dosen_name'   => $name,
                    'dosen_nip'    => null,
                    'jurusan_id'   => $jurusanId,
                    'is_komisi_kp' => false,
                ]
            );
        }
    }
}
