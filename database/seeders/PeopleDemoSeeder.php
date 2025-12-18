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

class PeopleDemoSeeder extends Seeder
{
    public function run(): void
    {
        $ti = Jurusan::where('nama_jurusan', 'Teknik Informatika')->first() ?? Jurusan::factory()->create();

        // ==========================================
        // 1. SETUP DOSEN (FIXED ACCOUNTS)
        // ==========================================

        $dspUser = User::firstOrCreate(
            ['email' => 'dsp@example.com'],
            ['name' => 'Dosen Pembimbing Demo', 'password' => 'password']
        );
        $dspUser->syncRoles('Dosen Pembimbing');
        $dosen = Dosen::firstOrCreate(
            ['user_id' => $dspUser->id],
            ['dosen_name' => $dspUser->name, 'jurusan_id' => $ti->id, 'is_komisi_kp' => false]
        );

        $komUser = User::firstOrCreate(
            ['email' => 'kom@example.com'],
            ['name' => 'Dosen Komisi Demo', 'password' => 'password']
        );
        $komUser->syncRoles(['Dosen Pembimbing', 'Dosen Komisi']);
        $dosenKomisi = Dosen::firstOrCreate(
            ['user_id' => $komUser->id],
            ['dosen_name' => $komUser->name, 'jurusan_id' => $ti->id, 'is_komisi_kp' => true]
        );

        // ==========================================
        // 2. SKENARIO MAHASISWA UTAMA (Loginable)
        // ==========================================

        // A. Mhs Baru
        $this->createMhs('mhs_baru@example.com', 'Mhs Baru', $ti->id);

        // B. Mhs Mengajukan Surat Pengantar
        $mhsSp = $this->createMhs('mhs_sp@example.com', 'Mhs Ajukan SP', $ti->id);
        SuratPengantar::factory()->create(['mahasiswa_id' => $mhsSp->mahasiswa_id, 'status_surat_pengantar' => 'Diajukan']);

        // C. Mhs Mengajukan KP
        $mhsSubmit = $this->createMhs('mhs_submit@example.com', 'Mhs Ajukan KP', $ti->id);
        SuratPengantar::factory()->diterbitkan()->create(['mahasiswa_id' => $mhsSubmit->mahasiswa_id]);
        KerjaPraktik::factory()->create(['mahasiswa_id' => $mhsSubmit->mahasiswa_id, 'status' => 'review_komisi']);

        // D. Mhs KP Berjalan
        $mhsActive = $this->createMhs('mhs_active@example.com', 'Mhs Bimbingan', $ti->id);
        SuratPengantar::factory()->diterbitkan()->create(['mahasiswa_id' => $mhsActive->mahasiswa_id]);
        $kpActive = KerjaPraktik::factory()->berjalan()->create(['mahasiswa_id' => $mhsActive->mahasiswa_id, 'dosen_pembimbing_id' => $dosen->dosen_id]);
        KpConsultation::factory(3)->verified()->create(['kerja_praktik_id' => $kpActive->id, 'mahasiswa_id' => $mhsActive->mahasiswa_id, 'dosen_pembimbing_id' => $dosen->dosen_id, 'verified_by_dosen_id' => $dosen->dosen_id]);

        // E. Mhs Siap Seminar
        $mhsSeminar = $this->createMhs('mhs_seminar@example.com', 'Mhs Siap Seminar', $ti->id);
        SuratPengantar::factory()->diterbitkan()->create(['mahasiswa_id' => $mhsSeminar->mahasiswa_id]);
        $kpSeminar = KerjaPraktik::factory()->berjalan()->create(['mahasiswa_id' => $mhsSeminar->mahasiswa_id, 'dosen_pembimbing_id' => $dosen->dosen_id]);
        KpConsultation::factory(8)->verified()->create(['kerja_praktik_id' => $kpSeminar->id, 'mahasiswa_id' => $mhsSeminar->mahasiswa_id, 'dosen_pembimbing_id' => $dosen->dosen_id, 'verified_by_dosen_id' => $dosen->dosen_id]);

        // Mhs ini mengajukan seminar
        KpSeminar::factory()->create([
            'kerja_praktik_id' => $kpSeminar->id,
            'mahasiswa_id' => $mhsSeminar->mahasiswa_id,
            'dosen_pembimbing_id' => $dosen->dosen_id,
            'status' => KpSeminar::ST_DIAJUKAN
        ]);

        // F. Mhs Selesai
        $mhsDone = $this->createMhs('mhs_nilai@example.com', 'Mhs Selesai', $ti->id);
        SuratPengantar::factory()->diterbitkan()->create(['mahasiswa_id' => $mhsDone->mahasiswa_id]);
        $kpDone = KerjaPraktik::factory()->berjalan()->create(['mahasiswa_id' => $mhsDone->mahasiswa_id, 'dosen_pembimbing_id' => $dosen->dosen_id]);
        KpConsultation::factory(10)->verified()->create(['kerja_praktik_id' => $kpDone->id, 'mahasiswa_id' => $mhsDone->mahasiswa_id, 'dosen_pembimbing_id' => $dosen->dosen_id, 'verified_by_dosen_id' => $dosen->dosen_id]);

        $semDone = KpSeminar::factory()->dinilai()->create([
            'kerja_praktik_id' => $kpDone->id,
            'mahasiswa_id' => $mhsDone->mahasiswa_id,
            'dosen_pembimbing_id' => $dosen->dosen_id
        ]);

        KpGrade::create([
            'kp_seminar_id' => $semDone->id,
            'score_dospem' => 88,
            'score_pl' => 90,
            'final_score' => 89,
            'final_letter' => 'A',
            'graded_by_user_id' => $dspUser->id,
            'graded_at' => now()
        ]);

        // ==========================================
        // 3. SCENARIO BAPENDIK: DATA DUMMY ADMIN (Agar Tabel Penuh)
        // ==========================================

        $rooms = Room::all();
        $signatories = Signatory::all();

        // A. Data ANTREAN PENERBITAN SPK (5 Data)
        Mahasiswa::factory(5)->create(['jurusan_id' => $ti->id])->each(function ($m) use ($dosenKomisi) {
            $m->user->assignRole('Mahasiswa');
            SuratPengantar::factory()->diterbitkan()->create(['mahasiswa_id' => $m->mahasiswa_id]);

            KerjaPraktik::factory()->create([
                'mahasiswa_id' => $m->mahasiswa_id,
                'status' => 'review_bapendik',
                'dosen_pembimbing_id' => Dosen::inRandomOrder()->value('dosen_id'),
            ]);
        });

        // B. Data RIWAYAT SPK TERBIT (5 Data)
        Mahasiswa::factory(5)->create(['jurusan_id' => $ti->id])->each(function ($m) use ($signatories) {
            $m->user->assignRole('Mahasiswa');
            SuratPengantar::factory()->diterbitkan()->create(['mahasiswa_id' => $m->mahasiswa_id]);

            KerjaPraktik::factory()->spkTerbit()->create([
                'mahasiswa_id' => $m->mahasiswa_id,
                'signatory_id' => $signatories->random()->id,
            ]);
        });

        // C. Data ANTREAN PENJADWALAN SEMINAR (5 Data)
        // Flow: Mhs Daftar Seminar -> Dospem ACC -> Status: disetujui_pembimbing -> Bapendik Proses
        Mahasiswa::factory(5)->create(['jurusan_id' => $ti->id])->each(function ($m) use ($dosen) {
            $m->user->assignRole('Mahasiswa');
            SuratPengantar::factory()->diterbitkan()->create(['mahasiswa_id' => $m->mahasiswa_id]);
            $kp = KerjaPraktik::factory()->berjalan()->create(['mahasiswa_id' => $m->mahasiswa_id, 'dosen_pembimbing_id' => $dosen->dosen_id]);
            KpConsultation::factory(8)->verified()->create(['kerja_praktik_id' => $kp->id, 'mahasiswa_id' => $m->mahasiswa_id, 'dosen_pembimbing_id' => $dosen->dosen_id, 'verified_by_dosen_id' => $dosen->dosen_id]);

            KpSeminar::factory()->create([
                'kerja_praktik_id' => $kp->id,
                'mahasiswa_id' => $m->mahasiswa_id,
                'dosen_pembimbing_id' => $dosen->dosen_id,
                'status' => KpSeminar::ST_DISETUJUI_PEMBIMBING, // Menunggu Proses Bapendik
                'judul_laporan' => 'Analisis ' . fake()->bs(),
            ]);
        });

        // D. Data ANTREAN TERBIT BA (5 Data)
        // Flow: Sudah Disetujui Dospem -> Menunggu Proses Bapendik (BA Terbit)
        Mahasiswa::factory(5)->create(['jurusan_id' => $ti->id])->each(function ($m) use ($dosen, $rooms) {
            $m->user->assignRole('Mahasiswa');
            SuratPengantar::factory()->diterbitkan()->create(['mahasiswa_id' => $m->mahasiswa_id]);
            $kp = KerjaPraktik::factory()->berjalan()->create(['mahasiswa_id' => $m->mahasiswa_id, 'dosen_pembimbing_id' => $dosen->dosen_id]);
            KpConsultation::factory(8)->verified()->create(['kerja_praktik_id' => $kp->id, 'mahasiswa_id' => $m->mahasiswa_id, 'dosen_pembimbing_id' => $dosen->dosen_id, 'verified_by_dosen_id' => $dosen->dosen_id]);

            // REVISI: Ganti dijadwalkan() dengan disetujuiPembimbing()
            // Ini mensimulasikan data yang siap diproses Bapendik untuk menjadi BA Terbit
            KpSeminar::factory()->disetujuiPembimbing()->create([
                'kerja_praktik_id' => $kp->id,
                'mahasiswa_id' => $m->mahasiswa_id,
                'dosen_pembimbing_id' => $dosen->dosen_id,
                'ruangan_id' => $rooms->random()->id ?? null,
                'judul_laporan' => 'Implementasi ' . fake()->catchPhrase(),
            ]);
        });
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
