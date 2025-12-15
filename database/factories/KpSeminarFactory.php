<?php

namespace Database\Factories;

use App\Models\KpSeminar;
use App\Models\KerjaPraktik;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class KpSeminarFactory extends Factory
{
    protected $model = KpSeminar::class;

    public function definition(): array
    {
        $mulai = $this->faker->time('H:i');
        // jam selesai +1-2 jam (simple, aman)
        $selesai = $this->faker->time('H:i');

        // Ambil random dosen/mahasiswa yang sudah ada, fallback bikin via factory kalau ada
        $dosenId = Dosen::query()->inRandomOrder()->value('dosen_id');
        $mhsId   = Mahasiswa::query()->inRandomOrder()->value('mahasiswa_id');

        return [
            // Wajib & FK utama
            'kerja_praktik_id' => KerjaPraktik::factory(),
            'mahasiswa_id' => $mhsId, // boleh null kalau migration mengizinkan; kalau NOT NULL pastikan seeder mhs ada
            'dosen_pembimbing_id' => $dosenId,

            // Konten seminar
            'judul_laporan' => $this->faker->sentence(6),
            'abstrak' => $this->faker->paragraph(3),
            'tanggal_seminar' => $this->faker->dateTimeBetween('-30 days', '+30 days'),
            'jam_mulai' => $mulai,
            'jam_selesai' => $selesai,

            // Ruangan (diset null dulu biar aman kalau tabel rooms beda PK)
            'ruangan_id' => null,
            'ruangan_nama' => $this->faker->randomElement(['Ruang A1', 'Ruang B2', 'Ruang C3', 'Lab 1', 'Lab 2']),

            // Status (pakai konstanta yang valid dari model)
            'status' => $this->faker->randomElement([
                KpSeminar::ST_DIAJUKAN,
                KpSeminar::ST_DISETUJUI_PEMBIMBING,
                KpSeminar::ST_DIJADWALKAN,
                KpSeminar::ST_SELESAI,
                KpSeminar::ST_REVISI,
                KpSeminar::ST_BA_TERBIT,
                KpSeminar::ST_DINILAI,
                KpSeminar::ST_DITOLAK,
            ]),

            // BA & TTD (nullable aman)
            'nomor_ba' => null,
            'tanggal_ba' => null,
            'signatory_id' => null,
            'ttd_signed_by_name' => null,
            'ttd_signed_by_position' => null,
            'ttd_signed_by_nip' => null,

            // QR BA (nullable)
            'ba_qr_token' => null,
            'ba_qr_expires_at' => null,
            'ba_scan_path' => null,

            // Approval dospem
            'approved_by_dospem_at' => null,
            'rejected_by_dospem_at' => null,
            'rejected_reason' => null,

            // Distribusi
            'distribusi_proof_path' => null,
            'distribusi_uploaded_at' => null,

            // Berkas laporan
            'berkas_laporan_path' => null,
        ];
    }

    /**
     * State: sudah dijadwalkan (punya tanggal/jam/ruangan)
     */
    public function dijadwalkan(): static
    {
        return $this->state(function () {
            return [
                'status' => KpSeminar::ST_DIJADWALKAN,
                'tanggal_seminar' => $this->faker->dateTimeBetween('+1 days', '+14 days'),
                'jam_mulai' => '09:00',
                'jam_selesai' => '10:00',
                'ruangan_nama' => $this->faker->randomElement(['Ruang Sidang 1', 'Ruang Sidang 2']),
            ];
        });
    }

    /**
     * State: BA terbit (punya nomor BA + token)
     */
    public function baTerbit(): static
    {
        return $this->state(function () {
            return [
                'status' => KpSeminar::ST_BA_TERBIT,
                'nomor_ba' => 'BA-' . strtoupper(Str::random(8)),
                'tanggal_ba' => now(),
                'ba_qr_token' => Str::uuid()->toString(),
                'ba_qr_expires_at' => now()->addDays(7),
            ];
        });
    }
}
