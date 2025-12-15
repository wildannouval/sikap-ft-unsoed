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
    public string $sortBy = 'updated_at';
    public string $sortDirection = 'desc';

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

    #[Computed]
    public function items()
    {
        $term = '%' . $this->search . '%';

        return KpSeminar::query()
            ->with(['grade', 'kp.mahasiswa.user'])
            // fokus ke seminar yang sudah ada BA / sudah dinilai / selesai
            ->whereIn('status', [KpSeminar::ST_BA_TERBIT, KpSeminar::ST_DINILAI, KpSeminar::ST_SELESAI])
            ->when($this->statusFilter !== 'all', fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->search !== '', function ($q) use ($term) {
                $q->where(function ($qq) use ($term) {
                    $qq->where('judul_laporan', 'like', $term)
                        ->orWhereHas('kp.mahasiswa.user', fn($w) => $w->where('name', 'like', $term))
                        ->orWhereHas('kp.mahasiswa', function ($w) use ($term) {
                            $w->where('mahasiswa_nim', 'like', $term);
                        });
                });
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage)
            ->withQueryString();
    }

    #[Computed]
    public function stats(): array
    {
        $base = KpSeminar::whereIn('status', [KpSeminar::ST_BA_TERBIT, KpSeminar::ST_DINILAI, KpSeminar::ST_SELESAI]);

        return [
            'ba_terbit' => (clone $base)->where('status', KpSeminar::ST_BA_TERBIT)->count(),
            'dinilai'   => (clone $base)->where('status', KpSeminar::ST_DINILAI)->count(),
            'selesai'   => (clone $base)->where('status', KpSeminar::ST_SELESAI)->count(),
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
