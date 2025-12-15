<?php

namespace App\Livewire\Mahasiswa;

use App\Models\KerjaPraktik;
use App\Models\KpSeminar;
use App\Models\Mahasiswa;
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
            ->whereIn('status', [
                KerjaPraktik::ST_SPK_TERBIT,
                KerjaPraktik::ST_KP_BERJALAN,
            ])
            ->latest('updated_at')
            ->first();
    }

    #[Computed]
    public function seminarStats(): array
    {
        if (!$this->mahasiswaId) {
            return [
                'total'       => 0,
                'diajukan'    => 0,
                'disetujui'   => 0,
                'dijadwalkan' => 0,
                'ba_terbit'   => 0,
                'dinilai'     => 0,
                'selesai'     => 0,
            ];
        }

        $base = KpSeminar::query()->whereHas('kp', fn($q) => $q->where('mahasiswa_id', $this->mahasiswaId));

        return [
            'total'       => (clone $base)->count(),
            'diajukan'    => (clone $base)->where('status', KpSeminar::ST_DIAJUKAN)->count(),
            'disetujui'   => (clone $base)->where('status', KpSeminar::ST_DISETUJUI_PEMBIMBING)->count(),
            'dijadwalkan' => (clone $base)->where('status', KpSeminar::ST_DIJADWALKAN)->count(),
            'ba_terbit'   => (clone $base)->where('status', KpSeminar::ST_BA_TERBIT)->count(),
            'dinilai'     => (clone $base)->where('status', KpSeminar::ST_DINILAI)->count(),
            'selesai'     => (clone $base)->where('status', KpSeminar::ST_SELESAI)->count(),
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

    public function render()
    {
        return view('livewire.mahasiswa.dashboard-page');
    }
}
