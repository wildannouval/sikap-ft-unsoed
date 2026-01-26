<?php

namespace App\Livewire\Calendar;

use App\Models\KpSeminar;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class SeminarIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q')]      public string $q = '';
    #[Url(as: 'room')]   public string $room = 'all';
    #[Url(as: 'month')]  public string $month; // format "Y-m"
    #[Url(as: 'sortBy')] public string $sortBy = 'tanggal_seminar';
    #[Url(as: 'dir')]    public string $sortDirection = 'asc';

    public int $perPage = 25;

    public function mount(): void
    {
        if (empty($this->month)) {
            $this->month = now()->format('Y-m');
        }
    }

    public function updatingQ()
    {
        $this->resetPage();
    }
    public function updatingRoom()
    {
        $this->resetPage();
    }
    public function updatingPerPage()
    {
        $this->resetPage();
    }
    public function updatingMonth()
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
    }

    public function prevMonth(): void
    {
        $dt = Carbon::createFromFormat('Y-m', $this->month)->startOfMonth()->subMonth();
        $this->month = $dt->format('Y-m');
        $this->resetPage();
    }

    public function nextMonth(): void
    {
        $dt = Carbon::createFromFormat('Y-m', $this->month)->startOfMonth()->addMonth();
        $this->month = $dt->format('Y-m');
        $this->resetPage();
    }

    public function setToday(): void
    {
        $this->month = now()->format('Y-m');
        $this->resetPage();
    }

    #[Computed]
    public function dateRange(): array
    {
        $start = Carbon::createFromFormat('Y-m', $this->month)->startOfMonth();
        $end   = (clone $start)->endOfMonth();
        return [$start, $end];
    }

    #[Computed]
    public function roomOptions(): array
    {
        return KpSeminar::query()
            ->select('ruangan_nama')
            ->whereNotNull('ruangan_nama')
            ->distinct()
            ->orderBy('ruangan_nama')
            ->pluck('ruangan_nama')
            ->filter(fn($v) => trim((string)$v) !== '')
            ->values()
            ->all();
    }

    #[Computed]
    public function items()
    {
        [$start, $end] = $this->dateRange();
        $term = '%' . $this->q . '%';

        // Menggunakan string 'dijadwalkan' secara langsung karena konstanta tidak ada di model
        $visibleStatuses = [
            'dijadwalkan',
            KpSeminar::ST_BA_TERBIT,
            KpSeminar::ST_DINILAI,
            KpSeminar::ST_SELESAI,
        ];

        return KpSeminar::query()
            ->with(['kp.mahasiswa.user'])
            ->whereIn('status', $visibleStatuses)
            ->whereBetween('tanggal_seminar', [$start->toDateString(), $end->toDateString()])
            ->when($this->room !== 'all', fn($q) => $q->where('ruangan_nama', $this->room))
            ->when($this->q !== '', function ($q) use ($term) {
                $q->where(function ($qq) use ($term) {
                    $qq->where('judul_laporan', 'like', $term)
                        ->orWhereHas('kp.mahasiswa.user', fn($w) => $w->where('name', 'like', $term))
                        ->orWhereHas('kp.mahasiswa', function ($w) use ($term) {
                            $w->where(function ($x) use ($term) {
                                $x->orWhere('nim', 'like', $term)
                                    ->orWhere('mahasiswa_nim', 'like', $term);
                            });
                        })
                        ->orWhere('ruangan_nama', 'like', $term);
                });
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->orderBy('jam_mulai', 'asc') // secondary sort
            ->paginate($this->perPage)
            ->withQueryString();
    }

    public function badgeColor(string $status): string
    {
        return KpSeminar::badgeColor($status);
    }

    public function statusLabel(string $status): string
    {
        return KpSeminar::statusLabel($status);
    }

    public function render()
    {
        return view('livewire.calendar.seminar-index');
    }
}
