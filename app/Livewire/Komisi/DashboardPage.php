<?php

namespace App\Livewire\Komisi;

use App\Models\KpSeminar;
use Livewire\Attributes\Computed;
use Livewire\Component;

class DashboardPage extends Component
{
    #[Computed]
    public function stats(): array
    {
        return [
            'menungguReview' => KpSeminar::where('status', 'diajukan')->count(),
            'disetujuiPemb'  => KpSeminar::where('status', 'disetujui_pembimbing')->count(),
            'dijadwalkan'    => KpSeminar::where('status', 'dijadwalkan')->count(),
            'baTerbit'       => KpSeminar::where('status', 'ba_terbit')->count(),
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
        return view('livewire.komisi.dashboard-page');
    }
}
