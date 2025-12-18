<?php

namespace App\Livewire\Dosen;

use App\Models\KpSeminar;
use App\Models\KerjaPraktik;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class DashboardPage extends Component
{
    protected function dosenId(): ?int
    {
        return optional(Auth::user()->dosen)->dosen_id ?? optional(Auth::user()->dosen)->id ?? null;
    }

    #[Computed]
    public function stats(): array
    {
        $dosenId = $this->dosenId();
        // Hapus 'dijadwalkan' dari default return
        if (!$dosenId) {
            return ['menungguAcc' => 0, 'perluNilai' => 0, 'baTerbit' => 0];
        }

        $base = KpSeminar::where('dosen_pembimbing_id', $dosenId);

        return [
            'menungguAcc' => (clone $base)->where('status', KpSeminar::ST_DIAJUKAN)->count(),
            // 'dijadwalkan' => ... // HAPUS
            'baTerbit'    => (clone $base)->where('status', KpSeminar::ST_BA_TERBIT)->count(),
            // Perlu nilai: Sudah BA terbit tapi belum ada nilai (grade)
            'perluNilai'  => (clone $base)->where('status', KpSeminar::ST_BA_TERBIT)
                ->whereDoesntHave('grade')->count(),
        ];
    }

    #[Computed]
    public function recent()
    {
        $dosenId = $this->dosenId();
        if (!$dosenId) return collect();

        return KpSeminar::query()
            ->with(['grade', 'kp.mahasiswa.user'])
            ->where('dosen_pembimbing_id', $dosenId)
            ->latest('updated_at')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function bimbinganAktif(): int
    {
        $dosenId = $this->dosenId();
        if (!$dosenId) return 0;

        return KerjaPraktik::where('dosen_pembimbing_id', $dosenId)
            ->whereIn('status', [KerjaPraktik::ST_SPK_TERBIT, KerjaPraktik::ST_KP_BERJALAN])
            ->count();
    }

    public function render()
    {
        return view('livewire.dosen.dashboard-page');
    }
}
