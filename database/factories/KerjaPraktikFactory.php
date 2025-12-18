<?php

namespace Database\Factories;

use App\Models\KerjaPraktik;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\Signatory;
use Illuminate\Database\Eloquent\Factories\Factory;

class KerjaPraktikFactory extends Factory
{
    protected $model = KerjaPraktik::class;

    public function definition(): array
    {
        return [
            'mahasiswa_id' => Mahasiswa::factory(),
            'judul_kp' => 'Rancang Bangun Sistem ' . $this->faker->words(3, true) . ' Berbasis ' . $this->faker->randomElement(['Web', 'Mobile', 'IoT', 'AI']),
            'lokasi_kp' => $this->faker->company(),
            'proposal_path' => 'dummy/proposal.pdf',
            'surat_keterangan_path' => 'dummy/suket.pdf',
            'status' => KerjaPraktik::ST_REVIEW_KOMISI,
            'created_at' => $this->faker->dateTimeBetween('-1 year', '-6 months'),
        ];
    }

    public function accKomisi()
    {
        return $this->state(fn(array $attributes) => [
            'status' => KerjaPraktik::ST_REVIEW_BAPENDIK,
            'dosen_pembimbing_id' => Dosen::inRandomOrder()->first()?->dosen_id ?? Dosen::factory(),
        ]);
    }

    public function spkTerbit()
    {
        return $this->state(fn(array $attributes) => [
            'status' => KerjaPraktik::ST_SPK_TERBIT,
            'dosen_pembimbing_id' => Dosen::inRandomOrder()->first()?->dosen_id ?? Dosen::factory(),
            'nomor_spk' => $this->faker->unique()->numerify('###/UNSOED/FT/SPK/2025'),
            'tanggal_terbit_spk' => $this->faker->dateTimeBetween('-5 months', '-4 months'),
            'signatory_id' => Signatory::inRandomOrder()->first()?->id ?? Signatory::factory(),
            'ttd_signed_at' => now(),
            'ttd_signed_by_name' => 'Dr. Pejabat Dummy',
            'spk_qr_token' => $this->faker->uuid(),
        ]);
    }

    public function berjalan()
    {
        return $this->state(fn(array $attributes) => [
            'status' => KerjaPraktik::ST_KP_BERJALAN,
            'dosen_pembimbing_id' => Dosen::inRandomOrder()->first()?->dosen_id ?? Dosen::factory(),
            'nomor_spk' => $this->faker->unique()->numerify('###/UNSOED/FT/SPK/2025'),
            'tanggal_terbit_spk' => $this->faker->dateTimeBetween('-5 months', '-4 months'),
            'spk_qr_token' => $this->faker->uuid(),
        ]);
    }
}
