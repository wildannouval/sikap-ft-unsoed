<?php

namespace Database\Factories;

use App\Models\KerjaPraktik;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use Illuminate\Database\Eloquent\Factories\Factory;

class KerjaPraktikFactory extends Factory
{
    protected $model = KerjaPraktik::class;

    public function definition(): array
    {
        return [
            'mahasiswa_id' => Mahasiswa::factory(),
            'judul_kp' => 'Rancang Bangun Sistem ' . $this->faker->words(3, true),
            'lokasi_kp' => $this->faker->company(),
            'proposal_path' => 'dummy/proposal.pdf',
            'surat_keterangan_path' => 'dummy/suket.pdf',
            'status' => 'review_komisi',
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }

    // State untuk berbagai status
    public function accKomisi()
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'review_bapendik',
            'dosen_pembimbing_id' => Dosen::inRandomOrder()->first()?->dosen_id ?? Dosen::factory(),
        ]);
    }

    public function spkTerbit()
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'spk_terbit',
            'dosen_pembimbing_id' => Dosen::inRandomOrder()->first()?->dosen_id ?? Dosen::factory(),
            'nomor_spk' => $this->faker->unique()->numerify('###/UNSOED/FT/SPK/2025'),
            'tanggal_terbit_spk' => now(),
            'spk_qr_token' => $this->faker->uuid(),
        ]);
    }

    public function berjalan()
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'kp_sedang_berjalan',
            'dosen_pembimbing_id' => Dosen::inRandomOrder()->first()?->dosen_id ?? Dosen::factory(),
            'nomor_spk' => $this->faker->unique()->numerify('###/UNSOED/FT/SPK/2025'),
            'spk_qr_token' => $this->faker->uuid(),
        ]);
    }
}
