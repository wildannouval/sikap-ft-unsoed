<?php

namespace Database\Factories;

use App\Models\KpSeminar;
use App\Models\KerjaPraktik;
use App\Models\Signatory;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class KpSeminarFactory extends Factory
{
    protected $model = KpSeminar::class;

    public function definition(): array
    {
        return [
            'kerja_praktik_id' => KerjaPraktik::factory(),
            // mahasiswa_id & dosen_pembimbing_id sebaiknya diambil dari relation kerja_praktik saat create
            'judul_laporan' => $this->faker->sentence(6),
            'abstrak' => $this->faker->paragraph(3),
            'berkas_laporan_path' => 'dummy/laporan.pdf',
            'status' => KpSeminar::ST_DIAJUKAN,
            'created_at' => now(),
        ];
    }

    public function disetujuiPembimbing()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => KpSeminar::ST_DISETUJUI_PEMBIMBING,
                'approved_by_dospem_at' => now()->subDays(2),
            ];
        });
    }

    public function baTerbit()
    {
        return $this->state(function (array $attributes) {
            $signatory = Signatory::inRandomOrder()->first();
            $room = Room::inRandomOrder()->first();

            return [
                'status' => KpSeminar::ST_BA_TERBIT,
                'approved_by_dospem_at' => now()->subMonth(),
                'tanggal_seminar' => now()->subDays(5),
                'jam_mulai' => '09:00',
                'jam_selesai' => '10:00',
                'ruangan_id' => $room?->id,
                'ruangan_nama' => $room?->room_number ?? 'Ruang Sidang',
                'nomor_ba' => $this->faker->unique()->numerify('BA/###/FT/2025'),
                'tanggal_ba' => now(),
                'signatory_id' => $signatory?->id,
                'ttd_signed_by_name' => $signatory?->name,
                'ba_qr_token' => Str::uuid(),
            ];
        });
    }

    public function dinilai()
    {
        return $this->baTerbit()->state(fn(array $attributes) => [
            'status' => KpSeminar::ST_DINILAI,
            'ba_scan_path' => 'dummy/ba_scan.pdf',
        ]);
    }

    public function selesai()
    {
        return $this->dinilai()->state(fn(array $attributes) => [
            'status' => KpSeminar::ST_SELESAI,
            'distribusi_proof_path' => 'dummy/distribusi.pdf',
            'distribusi_uploaded_at' => now(),
        ]);
    }
}
