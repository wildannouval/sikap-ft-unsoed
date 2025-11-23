<?php

namespace App\Livewire\Komisi\Kp;

use App\Models\KpSeminar;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

class NilaiIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q')]      public string $q = '';
    #[Url(as: 'status')] public string $statusFilter = 'all'; // all | ba_terbit | dinilai
    public int $perPage = 10;

    public function updatingQ()
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
        $term = '%' . $this->q . '%';

        return KpSeminar::query()
            ->with(['grade', 'kp.mahasiswa.user'])
            ->whereIn('status', ['ba_terbit', 'dinilai'])
            ->when($this->statusFilter !== 'all', fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->q !== '', function ($q) use ($term) {
                $q->where(function ($qq) use ($term) {
                    $qq->where('judul_laporan', 'like', $term)
                        ->orWhereHas('kp.mahasiswa.user', fn($w) => $w->where('name', 'like', $term))
                        ->orWhereHas('kp.mahasiswa', function ($w) use ($term) {
                            $w->where(function ($x) use ($term) {
                                $x->where('nim', 'like', $term)
                                    ->orWhere('mahasiswa_nim', 'like', $term);
                            });
                        });
                });
            })
            ->orderByDesc('updated_at')
            ->paginate($this->perPage)
            ->withQueryString();
    }

    public function render()
    {
        return view('livewire.komisi.kp.nilai-index');
    }
}
