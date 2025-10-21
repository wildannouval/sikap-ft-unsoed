<?php

namespace Database\Seeders;

use App\Models\Jurusan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Mahasiswa;
use Illuminate\Support\Facades\Hash;

class PeopleDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ti = Jurusan::firstOrCreate(['nama_jurusan'=>'Teknik Informatika']);

        // Mahasiswa
        $mhs = User::firstOrCreate(
            ['email'=>'mhs@example.com'],
            ['name'=>'Mahasiswa Satu','password'=>Hash::make('password')]
        );
        $mhs->assignRole('Mahasiswa');
        Mahasiswa::firstOrCreate(
            ['user_id'=>$mhs->id],
            ['jurusan_id'=>$ti->id,'nama_mahasiswa'=>$mhs->name,'nim'=>'H1A001','tahun_angkatan'=>2022]
        );

        // Bapendik
        $bap = User::firstOrCreate(
            ['email'=>'bap@example.com'],
            ['name'=>'Bapendik','password'=>Hash::make('password')]
        );
        $bap->assignRole('Bapendik');

        // Dosen Pembimbing
        $dsp = User::firstOrCreate(
            ['email'=>'dsp@example.com'],
            ['name'=>'Dosen Pembimbing','password'=>Hash::make('password')]
        );
        $dsp->assignRole('Dosen Pembimbing');

        // Dosen Komisi
        $kom = User::firstOrCreate(
            ['email'=>'kom@example.com'],
            ['name'=>'Dosen Komisi','password'=>Hash::make('password')]
        );
        $kom->assignRole('Dosen Komisi');
    }
}
