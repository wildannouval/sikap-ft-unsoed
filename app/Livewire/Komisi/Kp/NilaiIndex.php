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
    public string $sortBy = 'updated_at';
    public string $sortDirection = 'desc';

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

    public function sort(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    #[Computed]
    public function items()
    {
        $term = '%' . $this->q . '%';

        return KpSeminar::query()
            ->with(['grade', 'kp.mahasiswa.user'])
            // Komisi memantau mulai dari BA terbit hingga selesai
            ->whereIn('status', [KpSeminar::ST_BA_TERBIT, KpSeminar::ST_DINILAI, KpSeminar::ST_SELESAI])
            ->when($this->statusFilter !== 'all', fn($q) => $q->where('status', $this->statusFilter))
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
        // Hitung statistik global (tanpa filter pencarian) untuk sidebar
        $base = KpSeminar::whereIn('status', [KpSeminar::ST_BA_TERBIT, KpSeminar::ST_DINILAI, KpSeminar::ST_SELESAI]);

        return [
            'ba_terbit' => (clone $base)->where('status', KpSeminar::ST_BA_TERBIT)->count(),
            'dinilai'   => (clone $base)->where('status', KpSeminar::ST_DINILAI)->count(),
            'selesai'   => (clone $base)->where('status', KpSeminar::ST_SELESAI)->count(),
        ];
    }

    // Helper proxy ke Model
    public function badgeColor(string $status): string
    {
        return KpSeminar::badgeColor($status);
    }

    public function badgeIcon(string $status): string
    {
        // Mapping manual icon jika model belum support, atau panggil dari model jika ada
        return match ($status) {
            'ba_terbit' => 'document-text',
            'dinilai'   => 'star',
            'selesai'   => 'check-badge',
            default     => 'minus',
        };
    }

    public function statusLabel(string $status): string
    {
        return KpSeminar::statusLabel($status);
    }

    public function render()
    {
        return view('livewire.komisi.kp.nilai-index');
    }
}
