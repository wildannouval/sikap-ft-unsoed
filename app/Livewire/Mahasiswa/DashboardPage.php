<?php

namespace App\Livewire\Mahasiswa;

use App\Models\KerjaPraktik;
use App\Models\KpSeminar;
use App\Models\Mahasiswa;
use App\Models\SuratPengantar;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class DashboardPage extends Component
{
    #[Computed]
    public function mahasiswaId(): ?int
    {
        return Mahasiswa::where('user_id', Auth::id())->value('mahasiswa_id');
    }

    #[Computed]
    public function activeKp()
    {
        if (!$this->mahasiswaId) return null;

        return KerjaPraktik::query()
            ->withCount([
                'consultations as verified_consultations_count' => fn($q) => $q->whereNotNull('verified_at'),
            ])
            ->where('mahasiswa_id', $this->mahasiswaId)
            ->latest('updated_at')
            ->first();
    }

    /**
     * Statistik Utama untuk Card Tiles
     */
    #[Computed]
    public function mainStats(): array
    {
        if (!$this->mahasiswaId) {
            return [
                'surat_pengantar' => '0 Terbit',
                'kp_status'       => 'Belum Ada',
                'konsultasi'      => '0 Terverifikasi',
                'seminar_selesai' => '0 Seminar',
            ];
        }

        // 1. Surat Pengantar (Total Diterbitkan)
        $spCount = SuratPengantar::where('mahasiswa_id', $this->mahasiswaId)
            ->where('status_surat_pengantar', 'Diterbitkan')
            ->count();

        // 2. Status KP Terakhir
        $kp = $this->activeKp;
        // Pastikan status label ada, jika null default 'Belum Ada'
        $kpStatusLabel = $kp ? $kp::statusLabel($kp->status) : 'Belum Ada';

        // 3. Konsultasi
        $konsultasiCount = $kp ? $kp->verified_consultations_count : 0;

        // 4. Seminar Selesai / BA
        $seminarSelesai = KpSeminar::whereHas('kp', fn($q) => $q->where('mahasiswa_id', $this->mahasiswaId))
            ->whereIn('status', [KpSeminar::ST_BA_TERBIT, KpSeminar::ST_DINILAI, KpSeminar::ST_SELESAI])
            ->count();

        return [
            'surat_pengantar' => $spCount . ' Diterbitkan',
            'kp_status'       => $kpStatusLabel,
            'konsultasi'      => $konsultasiCount . ' Terverifikasi',
            'seminar_selesai' => $seminarSelesai . ' Seminar',
        ];
    }

    #[Computed]
    public function needDistribusi(): int
    {
        if (!$this->mahasiswaId) return 0;

        return KpSeminar::query()
            ->whereHas('kp', fn($q) => $q->where('mahasiswa_id', $this->mahasiswaId))
            ->whereIn('status', [KpSeminar::ST_BA_TERBIT, KpSeminar::ST_DINILAI])
            ->whereNull('distribusi_proof_path')
            ->count();
    }

    #[Computed]
    public function recentSeminars()
    {
        if (!$this->mahasiswaId) return collect();

        return KpSeminar::query()
            ->with(['grade'])
            ->whereHas('kp', fn($q) => $q->where('mahasiswa_id', $this->mahasiswaId))
            ->latest('updated_at')
            ->limit(5)
            ->get();
    }

    /**
     * Shortcut Download Dokumen
     */
    #[Computed]
    public function downloadLinks(): array
    {
        if (!$this->mahasiswaId) return [];

        $links = [];
        $kp = $this->activeKp;

        // 1. Download SPK (Jika sudah terbit)
        if ($kp && in_array($kp->status, [KerjaPraktik::ST_SPK_TERBIT, KerjaPraktik::ST_KP_BERJALAN, 'lulus'])) {
            $links[] = [
                'label' => 'Download SPK',
                'url'   => route('mhs.kp.download.spk', $kp->id),
                'icon'  => 'document-text',
                'color' => 'emerald',
            ];
        }

        // 2. Berita Acara (Jika seminar selesai)
        $seminar = KpSeminar::whereHas('kp', fn($q) => $q->where('mahasiswa_id', $this->mahasiswaId))
            ->whereIn('status', [KpSeminar::ST_BA_TERBIT, KpSeminar::ST_DINILAI, KpSeminar::ST_SELESAI])
            ->latest()
            ->first();

        if ($seminar) {
            $links[] = [
                'label' => 'Berita Acara',
                'url'   => route('mhs.kp.seminar.download.ba', [$seminar->kerja_praktik_id, $seminar->id]),
                'icon'  => 'clipboard-document-check',
                'color' => 'violet',
            ];
        }

        // 3. Surat Pengantar Terakhir (Diterbitkan)
        $sp = SuratPengantar::where('mahasiswa_id', $this->mahasiswaId)
            ->where('status_surat_pengantar', 'Diterbitkan')
            ->latest()
            ->first();

        if ($sp) {
            $links[] = [
                'label' => 'Surat Pengantar',
                'url'   => route('mhs.sp.download.docx', $sp->id),
                'icon'  => 'envelope',
                'color' => 'sky',
            ];
        }

        return $links;
    }

    public function render()
    {
        return view('livewire.mahasiswa.dashboard-page');
    }
}
