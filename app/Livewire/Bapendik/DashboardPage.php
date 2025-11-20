<?php

namespace App\Livewire\Bapendik;

use App\Models\KpSeminar;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use Livewire\Attributes\Computed;
use Livewire\Component;

class DashboardPage extends Component
{
    #[Computed]
    public function stats(): array
    {
        return [
            'menungguJadwal' => KpSeminar::where('status', 'disetujui_pembimbing')->count(),
            'dijadwalkan'    => KpSeminar::where('status', 'dijadwalkan')->count(),
            'baTerbit'       => KpSeminar::where('status', 'ba_terbit')->count(),
            'dinilai'        => KpSeminar::where('status', 'dinilai')->count(),
            'mhs'            => Mahasiswa::count(),
            'dosen'          => Dosen::count(),
        ];
    }

    #[Computed]
    public function recent()
    {
        return KpSeminar::query()
            ->latest('updated_at')
            ->limit(8)
            ->get();
    }

    public function render()
    {
        return view('livewire.bapendik.dashboard-page');
    }
}
