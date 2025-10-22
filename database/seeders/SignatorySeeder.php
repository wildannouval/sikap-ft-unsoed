<?php

namespace Database\Seeders;

use App\Models\Signatory;
use Illuminate\Database\Seeder;

class SignatorySeeder extends Seeder
{
    public function run(): void
    {
        Signatory::firstOrCreate(
            ['name' => 'Dr. Ir. NOR INTANG SETYO HERMANTO, S.T., M.T.'],
            [
                'position' => 'Wakil Dekan Bidang Akademik',
                'nip'      => '19716022003221001',
            ]
        );
    }
}
