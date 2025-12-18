<?php

namespace App\Livewire\Bapendik\Kp;

use App\Models\KpSeminar;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Flux\Flux;

class NilaiIndex extends Component
{
    use WithPagination;

    #[Url] public string $search = '';
    #[Url] public string $tab = 'ba_terbit'; // ba_terbit | dinilai | selesai
    #[Url] public int $perPage = 10;
    #[Url] public string $sortBy = 'updated_at';
    #[Url] public string $sortDirection = 'desc';

    /** Reset pagination hooks */
    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatingTab()
    {
        $this->resetPage();
    }
    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    protected function baseQuery()
    {
        $term = '%' . $this->search . '%';

        return KpSeminar::query()
            ->with(['grade', 'kp.mahasiswa.user'])
            ->when($this->search !== '', function ($q) use ($term) {
                $q->where(function ($qq) use ($term) {
                    $qq->where('judul_laporan', 'like', $term)
                        ->orWhereHas('kp.mahasiswa.user', fn($w) => $w->where('name', 'like', $term))
                        ->orWhereHas('kp.mahasiswa', function ($w) use ($term) {
                            $w->where('mahasiswa_nim', 'like', $term);
                        });
                });
            })
            ->orderBy($this->sortBy, $this->sortDirection);
    }

    #[Computed]
    public function itemsBATerbit()
    {
        return $this->baseQuery()
            ->where('status', KpSeminar::ST_BA_TERBIT)
            ->paginate($this->perPage, ['*'], 'baPage');
    }

    #[Computed]
    public function itemsDinilai()
    {
        return $this->baseQuery()
            ->where('status', KpSeminar::ST_DINILAI)
            ->paginate($this->perPage, ['*'], 'nilaiPage');
    }

    #[Computed]
    public function itemsSelesai()
    {
        return $this->baseQuery()
            ->where('status', KpSeminar::ST_SELESAI)
            ->paginate($this->perPage, ['*'], 'selesaiPage');
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'ba_terbit' => KpSeminar::where('status', KpSeminar::ST_BA_TERBIT)->count(),
            'dinilai'   => KpSeminar::where('status', KpSeminar::ST_DINILAI)->count(),
            'selesai'   => KpSeminar::where('status', KpSeminar::ST_SELESAI)->count(),
        ];
    }

    public function badgeColor(string $st): string
    {
        return KpSeminar::badgeColor($st);
    }

    public function statusLabel(string $st): string
    {
        return KpSeminar::statusLabel($st);
    }

    public function render()
    {
        return view('livewire.bapendik.kp.nilai-index');
    }
}
