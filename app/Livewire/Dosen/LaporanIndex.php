<?php

namespace App\Livewire\Dosen;

use App\Models\KpSeminar;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class LaporanIndex extends Component
{
    use WithPagination;

    public string $q = '';
    public int $perPage = 10;
    public string $sortBy = 'updated_at';
    public string $sortDirection = 'desc';

    public function updatingQ()
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
    public function dosenId(): int
    {
        return Auth::user()->dosen?->dosen_id ?? 0;
    }

    #[Computed]
    public function items()
    {
        $term = '%' . $this->q . '%';

        return KpSeminar::query()
            ->with(['kp.mahasiswa.user', 'grade'])
            ->where('dosen_pembimbing_id', $this->dosenId)
            // Menghapus ST_DIJADWALKAN (sesuai catatan kamu)
            ->whereIn('status', [
                KpSeminar::ST_BA_TERBIT,
                KpSeminar::ST_DINILAI,
                KpSeminar::ST_SELESAI
            ])
            ->when($this->q !== '', function ($q) use ($term) {
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
        $base = KpSeminar::query()
            ->where('dosen_pembimbing_id', $this->dosenId)
            ->whereIn('status', [KpSeminar::ST_BA_TERBIT, KpSeminar::ST_DINILAI, KpSeminar::ST_SELESAI]);

        return [
            'total_laporan' => (clone $base)->whereNotNull('berkas_laporan_path')->count(),

            // BA Scan tersimpan di relasi grade->ba_scan_path, bukan di kp_seminars
            'total_ba'      => (clone $base)->whereHas('grade', fn($g) => $g->whereNotNull('ba_scan_path'))->count(),

            'total_selesai' => (clone $base)->whereNotNull('distribusi_proof_path')->count(),
        ];
    }

    public function render()
    {
        return view('livewire.dosen.laporan-index');
    }
}
