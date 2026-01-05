<?php

namespace App\Livewire\Komisi\Kp;

use App\Models\KpSeminar;
use App\Exports\KpGradeExport;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

class NilaiIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q')]      public string $q = '';
    #[Url(as: 'status')] public string $statusFilter = 'all';
    #[Url(as: 'start')]  public string $startDate = '';
    #[Url(as: 'end')]    public string $endDate = '';

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
    public function updatingStartDate()
    {
        $this->resetPage();
    }
    public function updatingEndDate()
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

    /**
     * Logic Query Inti untuk Tabel & Export
     */
    protected function baseFilterQuery()
    {
        $term = '%' . trim($this->q) . '%';

        return KpSeminar::query()
            ->with(['grade', 'kp.mahasiswa.user', 'kp.dosenPembimbing.user'])
            ->whereIn('status', [KpSeminar::ST_BA_TERBIT, KpSeminar::ST_DINILAI, KpSeminar::ST_SELESAI])

            // Filter Status
            ->when($this->statusFilter !== 'all', fn($q) => $q->where('status', $this->statusFilter))

            // Filter Tanggal (Berdasarkan Update Nilai Terakhir)
            ->when($this->startDate, fn($q) => $q->whereDate('updated_at', '>=', $this->startDate))
            ->when($this->endDate, fn($q) => $q->whereDate('updated_at', '<=', $this->endDate))

            // Pencarian Global
            ->when($this->q !== '', function ($q) use ($term) {
                $q->where(function ($qq) use ($term) {
                    $qq->where('judul_laporan', 'like', $term)
                        ->orWhereHas('kp.mahasiswa.user', fn($w) => $w->where('name', 'like', $term))
                        ->orWhereHas('kp.mahasiswa', fn($w) => $w->where('mahasiswa_nim', 'like', $term));
                });
            });
    }

    #[Computed]
    public function items()
    {
        return $this->baseFilterQuery()
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

    /**
     * Method Export Excel
     */
    public function export()
    {
        $filename = 'Rekap_Nilai_KP_' . now()->format('Y-m-d_His') . '.xlsx';
        return Excel::download(new KpGradeExport($this->baseFilterQuery()), $filename);
    }

    public function badgeColor(string $status): string
    {
        return KpSeminar::badgeColor($status);
    }
    public function badgeIcon(string $status): string
    {
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
