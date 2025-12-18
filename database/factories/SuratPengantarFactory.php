<?php

namespace Database\Factories;

use App\Models\SuratPengantar;
use App\Models\Mahasiswa;
use App\Models\Signatory;
use Illuminate\Database\Eloquent\Factories\Factory;

class SuratPengantarFactory extends Factory
{
    protected $model = SuratPengantar::class;

    public function definition(): array
    {
        return [
            'mahasiswa_id' => Mahasiswa::factory(),
            'lokasi_surat_pengantar' => $this->faker->company(),
            'penerima_surat_pengantar' => $this->faker->name(),
            'alamat_surat_pengantar' => $this->faker->address(),
            'tembusan_surat_pengantar' => 'Arsip',
            'status_surat_pengantar' => 'Diajukan',
            'tanggal_pengajuan_surat_pengantar' => now(),
            'created_at' => now(),
        ];
    }

    public function diterbitkan()
    {
        return $this->state(fn(array $attributes) => [
            'status_surat_pengantar' => 'Diterbitkan',
            'nomor_surat' => $this->faker->unique()->numerify('###/UNSOED/FT/SP/2025'),
            'tanggal_disetujui_surat_pengantar' => now(),
            'signatory_id' => Signatory::inRandomOrder()->first()?->id ?? Signatory::factory(),
            'ttd_signed_at' => now(),
            'ttd_signed_by_name' => 'Dr. Pejabat Dummy',
            'ttd_signed_by_nip' => '1987654321',
            'ttd_signed_by_position' => 'Wakil Dekan',
        ]);
    }

    public function ditolak()
    {
        return $this->state(fn(array $attributes) => [
            'status_surat_pengantar' => 'Ditolak',
            'catatan_surat' => 'Alamat perusahaan kurang lengkap.',
        ]);
    }
}
