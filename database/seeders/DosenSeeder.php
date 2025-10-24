<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DosenSeeder extends Seeder
{
    public function run(): void
    {
        // Hanya kolom 'nama' agar aman untuk berbagai skema (kolom lain diharapkan nullable)
        $data = [
            ['nama' => 'Dr. Ir. Suryanto, M.T.'],
            ['nama' => 'Dr. Andini Pramesti, S.T., M.Eng.'],
            ['nama' => 'Ir. Bambang Kurnia, M.T.'],
            ['nama' => 'Dra. Retno Lestari, M.Kom.'],
            ['nama' => 'Ahmad Fauzi, S.T., M.T.'],
            ['nama' => 'Dwi Rahayu, S.T., M.T.'],
            ['nama' => 'M. Rizky Maulana, S.T., M.T.'],
            ['nama' => 'Nurul Hidayati, S.T., M.Kom.'],
            ['nama' => 'Fajar Pratama, S.T., M.T.'],
            ['nama' => 'Hendra Wijaya, S.T., M.Eng.'],
            ['nama' => 'Larasati Putri, S.T., M.T.'],
            ['nama' => 'Yudi Saputra, S.T., M.T.'],
        ];

        // pakai upsert agar tidak dobel kalau seeder dijalankan ulang
        DB::table('dosens')->upsert($data, ['nama']);
    }
}
