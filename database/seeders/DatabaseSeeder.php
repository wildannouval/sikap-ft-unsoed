<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\Jurusan;
use App\Models\KerjaPraktik;
use App\Models\KpConsultation;
use App\Models\KpGrade;
use App\Models\KpSeminar;
use App\Models\Mahasiswa;
use App\Models\Room;
use App\Models\Signatory;
use App\Models\SuratPengantar;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles & Permissions
        $this->call(PermissionRoleSeeder::class);

        // 2. Master Data
        if (Jurusan::count() === 0) {
            Jurusan::factory()->createMany([
                ['nama_jurusan' => 'Teknik Informatika'],
                ['nama_jurusan' => 'Teknik Elektro'],
                ['nama_jurusan' => 'Teknik Sipil'],
                ['nama_jurusan' => 'Teknik Industri'],
                ['nama_jurusan' => 'Teknik Geologi'],
            ]);
        }

        // Pastikan Ruangan & Penandatangan ada
        $rooms = collect([
            Room::firstOrCreate(['room_number' => 'R.201', 'building' => 'Gedung F']),
            Room::firstOrCreate(['room_number' => 'R.202', 'building' => 'Gedung F']),
            Room::firstOrCreate(['room_number' => 'R.Sidang', 'building' => 'Gedung A']),
        ]);

        $signatories = collect([
            Signatory::firstOrCreate(
                ['nip' => '19716022003221001'],
                ['name' => 'Dr. Ir. NOR INTANG SETYO HERMANTO, S.T., M.T.', 'position' => 'Wakil Dekan Bidang Akademik']
            ),
            Signatory::firstOrCreate(
                ['nip' => '198001012005011002'],
                ['name' => 'Prof. Dr. Eng. Retno Supriyanti, S.T., M.T.', 'position' => 'Dekan Fakultas Teknik']
            )
        ]);

        // 3. User Admin
        $bap = User::firstOrCreate(
            ['email' => 'bapendik@example.com'],
            ['name' => 'Bapendik Admin', 'password' => 'password']
        );
        $bap->syncRoles('Bapendik');

        // 4. Dosen Bulk (10 orang)
        $dosens = Dosen::factory(10)->create()->each(function ($dosen) {
            $dosen->user->assignRole('Dosen Pembimbing');
        });

        // Komisi
        $komisi = $dosens->first();
        $komisi->update(['is_komisi_kp' => true]);
        $komisi->user->assignRole(['Dosen Pembimbing', 'Dosen Komisi']);

        // 5. Mahasiswa & KP Bulk (Random Data 30 orang)
        Mahasiswa::factory(30)->create()->each(function ($mhs) use ($dosens, $rooms, $signatories) {
            $mhs->user->assignRole('Mahasiswa');
            $rand = rand(1, 100);

            // A. 10% Baru
            if ($rand <= 10) return;

            // B. 20% SP Diajukan
            if ($rand <= 30) {
                SuratPengantar::factory()->create(['mahasiswa_id' => $mhs->mahasiswa_id, 'status_surat_pengantar' => 'Diajukan']);
                return;
            }

            // --- Base: SP Diterbitkan ---
            $lokasi = fake()->company();
            SuratPengantar::factory()->diterbitkan()->create(['mahasiswa_id' => $mhs->mahasiswa_id, 'lokasi_surat_pengantar' => $lokasi, 'signatory_id' => $signatories->random()->id]);

            // C. 20% KP Review Komisi
            if ($rand <= 50) {
                KerjaPraktik::factory()->create([
                    'mahasiswa_id' => $mhs->mahasiswa_id,
                    'status' => 'review_komisi',
                    'lokasi_kp' => $lokasi,
                ]);
                return;
            }

            // Sisanya: KP Berjalan/Selesai
            $dospem = $dosens->random();
            $kp = KerjaPraktik::factory()->berjalan()->create([
                'mahasiswa_id' => $mhs->mahasiswa_id,
                'dosen_pembimbing_id' => $dospem->dosen_id,
                'lokasi_kp' => $lokasi,
                'signatory_id' => $signatories->random()->id,
            ]);

            // D. 20% Sedang Bimbingan
            if ($rand <= 70) {
                KpConsultation::factory(rand(1, 5))->create(['kerja_praktik_id' => $kp->id, 'mahasiswa_id' => $mhs->mahasiswa_id, 'dosen_pembimbing_id' => $dospem->dosen_id]);
                return;
            }

            // E. Sisanya: Seminar/Selesai (Bimbingan Penuh)
            KpConsultation::factory(8)->verified()->create(['kerja_praktik_id' => $kp->id, 'mahasiswa_id' => $mhs->mahasiswa_id, 'dosen_pembimbing_id' => $dospem->dosen_id, 'verified_by_dosen_id' => $dospem->dosen_id]);

            if ($rand <= 85) {
                // REVISI: Menggunakan disetujuiPembimbing() bukan dijadwalkan()
                KpSeminar::factory()->disetujuiPembimbing()->create([
                    'kerja_praktik_id' => $kp->id,
                    'mahasiswa_id' => $mhs->mahasiswa_id,
                    'dosen_pembimbing_id' => $dospem->dosen_id,
                    'judul_laporan' => $kp->judul_kp,
                    'ruangan_id' => $rooms->random()->id,
                    'ruangan_nama' => $rooms->random()->room_number,
                ]);
            } else {
                // Selesai / BA Terbit
                $sem = KpSeminar::factory()->baTerbit()->create([
                    'kerja_praktik_id' => $kp->id,
                    'mahasiswa_id' => $mhs->mahasiswa_id,
                    'dosen_pembimbing_id' => $dospem->dosen_id,
                    'judul_laporan' => $kp->judul_kp,
                    'ruangan_id' => $rooms->random()->id,
                    'ruangan_nama' => $rooms->random()->room_number,
                    'signatory_id' => $signatories->random()->id,
                ]);

                // 50% chance sudah dinilai
                if (rand(0, 1)) {
                    $sem->update(['status' => 'dinilai']);
                    KpGrade::create([
                        'kp_seminar_id' => $sem->id,
                        'score_dospem' => 85,
                        'score_pl' => 88,
                        'final_score' => 86.5,
                        'final_letter' => 'A',
                        'graded_by_user_id' => $dospem->user_id,
                        'graded_at' => now(),
                    ]);
                }
            }
        });

        // 6. Panggil PeopleDemoSeeder untuk akun fix & scenario spesifik
        $this->call(PeopleDemoSeeder::class);
    }

    private function createMhs($email, $name, $jurusanId)
    {
        $u = User::firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => 'password']
        );
        $u->syncRoles('Mahasiswa');

        return Mahasiswa::firstOrCreate(
            ['user_id' => $u->id],
            [
                'mahasiswa_name' => $name,
                'mahasiswa_nim' => 'H1D0' . rand(10000, 99999),
                'jurusan_id' => $jurusanId,
                'mahasiswa_tahun_angkatan' => 2021
            ]
        );
    }
}
