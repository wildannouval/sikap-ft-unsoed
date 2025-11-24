<?php

namespace App\Livewire\Bapendik\Kp;

use App\Models\KpSeminar;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class NilaiIndex extends Component
{
    use WithPagination;

    /** Pencarian & filter */
    public string $search = '';
    public string $statusFilter = 'all'; // all | ba_terbit | dinilai
    public int $perPage = 10;

    /** Reset pagination saat filter berubah */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    #[Computed]
    public function items()
    {
        $term = '%' . $this->search . '%';

        return KpSeminar::query()
            ->with(['grade', 'kp.mahasiswa.user'])
            // fokus ke seminar yang sudah ada BA / sudah dinilai
            ->whereIn('status', ['ba_terbit', 'dinilai'])
            ->when($this->statusFilter !== 'all', fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->search !== '', function ($q) use ($term) {
                $q->where(function ($qq) use ($term) {
                    $qq->where('judul_laporan', 'like', $term)
                        ->orWhereHas('kp.mahasiswa.user', fn($w) => $w->where('name', 'like', $term))
                        ->orWhereHas('kp.mahasiswa', function ($w) use ($term) {
                            // DB hanya punya kolom `mahasiswa_nim`
                            $w->where('mahasiswa_nim', 'like', $term);
                        });
                });
            })
            ->orderByDesc('updated_at')
            ->paginate($this->perPage)
            ->withQueryString();
    }

    public function render()
    {
        return view('livewire.bapendik.kp.nilai-index');
    }
}
