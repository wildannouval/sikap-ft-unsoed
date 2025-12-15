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
use App\Models\User;
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
            ]);
        }

        Signatory::firstOrCreate(
            ['nip' => '19716022003221001'],
            ['name' => 'Dr. Ir. NOR INTANG SETYO HERMANTO, S.T., M.T.', 'position' => 'Wakil Dekan Bidang Akademik']
        );

        Room::firstOrCreate(['room_number' => 'R.201', 'building' => 'Gedung F']);
        Room::firstOrCreate(['room_number' => 'R.202', 'building' => 'Gedung F']);

        // 3. User Bapendik
        $bap = User::firstOrCreate(
            ['email' => 'bapendik@example.com'],
            ['name' => 'Bapendik Admin', 'password' => 'password']
        );
        $bap->syncRoles('Bapendik');

        // 4. Dosen (Komisi & Pembimbing)
        // Dosen Komisi
        $komisiUser = User::firstOrCreate(
            ['email' => 'komisi@example.com'],
            ['name' => 'Dosen Komisi', 'password' => 'password']
        );
        $komisiUser->syncRoles(['Dosen Pembimbing', 'Dosen Komisi']);
        $dosenKomisi = Dosen::factory()->create([
            'user_id' => $komisiUser->id,
            'dosen_name' => $komisiUser->name,
            'is_komisi_kp' => true,
            'jurusan_id' => 1
        ]);

        // Dosen Pembimbing Biasa
        $dspUser = User::firstOrCreate(
            ['email' => 'dospem@example.com'],
            ['name' => 'Dosen Pembimbing', 'password' => 'password']
        );
        $dspUser->syncRoles('Dosen Pembimbing');
        $dosenPembimbing = Dosen::factory()->create([
            'user_id' => $dspUser->id,
            'dosen_name' => $dspUser->name,
            'is_komisi_kp' => false,
            'jurusan_id' => 1
        ]);

        // Tambah beberapa dosen dummy lain
        Dosen::factory(5)->create()->each(function ($d) {
            $d->user->assignRole('Dosen Pembimbing');
        });

        // 5. Mahasiswa & Skenario KP

        // A. Mahasiswa Baru (Belum ada KP)
        $mhsBaru = User::firstOrCreate(
            ['email' => 'mhs_baru@example.com'],
            ['name' => 'Mahasiswa Baru', 'password' => 'password']
        );
        $mhsBaru->syncRoles('Mahasiswa');
        Mahasiswa::factory()->create(['user_id' => $mhsBaru->id, 'jurusan_id' => 1]);

        // B. Mahasiswa Mengajukan KP (Review Komisi)
        $mhsSubmit = User::firstOrCreate(
            ['email' => 'mhs_submit@example.com'],
            ['name' => 'Mahasiswa Submit', 'password' => 'password']
        );
        $mhsSubmit->syncRoles('Mahasiswa');
        $mhsSubmitModel = Mahasiswa::factory()->create(['user_id' => $mhsSubmit->id, 'jurusan_id' => 1]);

        KerjaPraktik::factory()->create([
            'mahasiswa_id' => $mhsSubmitModel->mahasiswa_id,
            'status' => 'review_komisi'
        ]);

        // C. Mahasiswa KP Berjalan (Sudah SPK, Sedang Bimbingan)
        $mhsActive = User::firstOrCreate(
            ['email' => 'mhs_active@example.com'],
            ['name' => 'Mahasiswa Aktif', 'password' => 'password']
        );
        $mhsActive->syncRoles('Mahasiswa');
        $mhsActiveModel = Mahasiswa::factory()->create(['user_id' => $mhsActive->id, 'jurusan_id' => 1]);

        $kpActive = KerjaPraktik::factory()->berjalan()->create([
            'mahasiswa_id' => $mhsActiveModel->mahasiswa_id,
            'dosen_pembimbing_id' => $dosenPembimbing->dosen_id, // Bimbingan dospem@example.com
            'judul_kp' => 'Analisis Performa Jaringan 5G'
        ]);

        // Buat konsultasi (3 terverifikasi, 2 belum)
        KpConsultation::factory(3)->verified()->create([
            'kerja_praktik_id' => $kpActive->id,
            'mahasiswa_id' => $mhsActiveModel->mahasiswa_id,
            'dosen_pembimbing_id' => $dosenPembimbing->dosen_id,
            'verified_by_dosen_id' => $dosenPembimbing->dosen_id,
        ]);
        KpConsultation::factory(2)->create([
            'kerja_praktik_id' => $kpActive->id,
            'mahasiswa_id' => $mhsActiveModel->mahasiswa_id,
            'dosen_pembimbing_id' => $dosenPembimbing->dosen_id,
        ]);

        // D. Mahasiswa Siap Seminar (Konsultasi > 6)
        $mhsSeminar = User::firstOrCreate(
            ['email' => 'mhs_seminar@example.com'],
            ['name' => 'Mahasiswa Seminar', 'password' => 'password']
        );
        $mhsSeminar->syncRoles('Mahasiswa');
        $mhsSeminarModel = Mahasiswa::factory()->create(['user_id' => $mhsSeminar->id, 'jurusan_id' => 1]);

        $kpSeminar = KerjaPraktik::factory()->berjalan()->create([
            'mahasiswa_id' => $mhsSeminarModel->mahasiswa_id,
            'dosen_pembimbing_id' => $dosenPembimbing->dosen_id,
            'judul_kp' => 'Pengembangan Sistem AI Sederhana'
        ]);

        KpConsultation::factory(8)->verified()->create([
            'kerja_praktik_id' => $kpSeminar->id,
            'mahasiswa_id' => $mhsSeminarModel->mahasiswa_id,
            'dosen_pembimbing_id' => $dosenPembimbing->dosen_id,
            'verified_by_dosen_id' => $dosenPembimbing->dosen_id,
        ]);

        // Buat pengajuan seminar (status diajukan)
        KpSeminar::factory()->create([
            'kerja_praktik_id' => $kpSeminar->id,
            'mahasiswa_id' => $mhsSeminarModel->mahasiswa_id,
            'dosen_pembimbing_id' => $dosenPembimbing->dosen_id,
            'judul_laporan' => $kpSeminar->judul_kp,
            'status' => 'diajukan',
        ]);

        // E. Mahasiswa Selesai (Sudah Nilai)
        $mhsDone = User::firstOrCreate(
            ['email' => 'mhs_done@example.com'],
            ['name' => 'Mahasiswa Selesai', 'password' => 'password']
        );
        $mhsDone->syncRoles('Mahasiswa');
        $mhsDoneModel = Mahasiswa::factory()->create(['user_id' => $mhsDone->id, 'jurusan_id' => 1]);

        $kpDone = KerjaPraktik::factory()->berjalan()->create([
            'mahasiswa_id' => $mhsDoneModel->mahasiswa_id,
            'dosen_pembimbing_id' => $dosenPembimbing->dosen_id,
            'judul_kp' => 'Implementasi IoT Smart Home'
        ]);

        KpConsultation::factory(10)->verified()->create([
            'kerja_praktik_id' => $kpDone->id,
            'mahasiswa_id' => $mhsDoneModel->mahasiswa_id,
            'dosen_pembimbing_id' => $dosenPembimbing->dosen_id,
            'verified_by_dosen_id' => $dosenPembimbing->dosen_id,
        ]);

        $seminarDone = KpSeminar::factory()->create([
            'kerja_praktik_id' => $kpDone->id,
            'mahasiswa_id' => $mhsDoneModel->mahasiswa_id,
            'dosen_pembimbing_id' => $dosenPembimbing->dosen_id,
            'judul_laporan' => $kpDone->judul_kp,
            'status' => 'dinilai', // atau selesai
            'tanggal_seminar' => now()->subDays(5),
            'ruangan_id' => 1,
            'ruangan_nama' => 'R.201',
            'ba_scan_path' => 'dummy/ba_scan.pdf',
        ]);

        KpGrade::create([
            'kp_seminar_id' => $seminarDone->id,
            'score_dospem' => 85,
            'score_pl' => 90,
            'final_score' => 87,
            'final_letter' => 'A',
            'graded_by_user_id' => $dspUser->id,
            'graded_at' => now(),
        ]);
    }
}
