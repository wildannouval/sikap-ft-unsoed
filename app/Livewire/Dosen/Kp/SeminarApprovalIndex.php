<?php

namespace App\Livewire\Dosen\Kp;

use App\Models\Dosen;
use App\Models\KpSeminar;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use App\Services\Notifier;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SeminarApprovalIndex extends Component
{
    use WithPagination;

    public string $q = '';
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 10;

    public string $tab = 'pending'; // pending | approved | history

    public ?int $rejectId = null;
    public string $rejectReason = '';

    public ?int $detailId = null;

    public function updatingQ()
    {
        $this->resetPage();
    }
    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function setTab(string $tab): void
    {
        $allowed = ['pending', 'approved', 'history'];
        if (! in_array($tab, $allowed, true)) return;

        $this->tab = $tab;
        $this->resetPage();
    }

    public function sort(string $field): void
    {
        $allowed = ['created_at', 'tanggal_seminar', 'status', 'judul_laporan'];
        if (! in_array($field, $allowed, true)) {
            $field = 'created_at';
        }

        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    #[Computed]
    public function dosenId(): int
    {
        $userId = Auth::id();
        if (! $userId) return 0;

        $rel = Auth::user()?->dosen?->dosen_id ?? null;
        if ($rel) return (int) $rel;

        return (int) Dosen::where('user_id', $userId)->value('dosen_id') ?: 0;
    }

    protected function baseQuery()
    {
        $dosenId = $this->dosenId;

        if ($dosenId <= 0) {
            return KpSeminar::query()->whereRaw('1=0');
        }

        return KpSeminar::query()
            ->with(['kp.mahasiswa.user'])
            ->where(function ($q) use ($dosenId) {
                $q->where('dosen_pembimbing_id', $dosenId)
                    // fallback bila ada data lama yang nyantol di kerja_praktiks
                    ->orWhereHas('kp', fn($kp) => $kp->where('dosen_pembimbing_id', $dosenId));
            });
    }

    #[Computed]
    public function items(): LengthAwarePaginator
    {
        $term = '%' . $this->q . '%';
        $q = $this->baseQuery();

        // TAB FILTER
        $q->when($this->tab === 'pending', fn($qq) => $qq->where('status', KpSeminar::ST_DIAJUKAN));

        $q->when($this->tab === 'approved', function ($qq) {
            // dukung status legacy bila ada
            $qq->whereIn('status', [KpSeminar::ST_DISETUJUI_PEMBIMBING, 'dijadwalkan']);
        });

        $q->when($this->tab === 'history', function ($qq) {
            $qq->whereIn('status', [
                KpSeminar::ST_BA_TERBIT,
                KpSeminar::ST_DINILAI,
                KpSeminar::ST_SELESAI,
                KpSeminar::ST_DITOLAK,
                KpSeminar::ST_GAGAL,
                KpSeminar::ST_REVISI,
            ]);
        });

        // SEARCH
        $q->when($this->q !== '', function ($qq) use ($term) {
            $qq->where(function ($w) use ($term) {
                $w->where('judul_laporan', 'like', $term)
                    ->orWhereHas('kp.mahasiswa.user', fn($u) => $u->where('name', 'like', $term))
                    ->orWhereHas('kp.mahasiswa', fn($m) => $m->where('mahasiswa_nim', 'like', $term));
            });
        });

        // SORT whitelist
        $allowedSort = ['created_at', 'tanggal_seminar', 'status', 'judul_laporan'];
        $sortBy = in_array($this->sortBy, $allowedSort, true) ? $this->sortBy : 'created_at';
        $dir = $this->sortDirection === 'asc' ? 'asc' : 'desc';

        return $q->orderBy($sortBy, $dir)
            ->paginate($this->perPage)
            ->withQueryString();
    }

    #[Computed]
    public function stats(): array
    {
        $base = $this->baseQuery();

        return [
            'pending'  => (clone $base)->where('status', KpSeminar::ST_DIAJUKAN)->count(),
            'approved' => (clone $base)->whereIn('status', [KpSeminar::ST_DISETUJUI_PEMBIMBING, 'dijadwalkan'])->count(),
            'history'  => (clone $base)->whereIn('status', [
                KpSeminar::ST_BA_TERBIT,
                KpSeminar::ST_DINILAI,
                KpSeminar::ST_SELESAI,
                KpSeminar::ST_DITOLAK,
                KpSeminar::ST_GAGAL,
                KpSeminar::ST_REVISI,
            ])->count(),
        ];
    }

    #[Computed]
    public function selectedItem(): ?KpSeminar
    {
        if (! $this->detailId) return null;
        return KpSeminar::with(['kp.mahasiswa.user'])->find($this->detailId);
    }

    public function badgeColor(string $status): string
    {
        return KpSeminar::badgeColor($status);
    }

    public function badgeIcon(string $status): string
    {
        return match ($status) {
            KpSeminar::ST_DIAJUKAN => 'clock',
            KpSeminar::ST_DISETUJUI_PEMBIMBING => 'check-circle',
            'dijadwalkan' => 'calendar',
            KpSeminar::ST_BA_TERBIT => 'document-text',
            KpSeminar::ST_DITOLAK => 'x-circle',
            default => 'minus',
        };
    }

    public function statusLabel(string $status): string
    {
        if ($status === 'dijadwalkan') return 'Menunggu Jadwal';
        return KpSeminar::statusLabel($status);
    }

    public function openDetail(int $id): void
    {
        $this->detailId = $id;
        Flux::modal('detail-seminar')->show();
    }

    public function closeDetail(): void
    {
        $this->detailId = null;
        Flux::modal('detail-seminar')->close();
    }

    public function approve(int $id): void
    {
        $row = $this->baseQuery()->where('id', $id)->firstOrFail();
        if ($row->status !== KpSeminar::ST_DIAJUKAN) return;

        $row->update([
            'status'                => KpSeminar::ST_DISETUJUI_PEMBIMBING,
            'approved_by_dospem_at' => now(),
            'rejected_by_dospem_at' => null,
            'rejected_reason'       => null,
        ]);

        $mhsUser = $row->kp?->mahasiswa?->user;
        if ($mhsUser) {
            Notifier::toUser(
                $mhsUser,
                'Seminar Disetujui Dospem',
                'Pengajuan seminar disetujui, menunggu penjadwalan.',
                route('mhs.kp.seminar', $row->kerja_praktik_id)
            );
        }

        Notifier::toRole(
            'Bapendik',
            'Jadwalkan Seminar KP',
            "Seminar {$row->kp?->mahasiswa?->user?->name} perlu dijadwalkan.",
            route('bap.kp.seminar.jadwal')
        );

        Flux::toast(heading: 'Disetujui', text: 'Pengajuan disetujui.', variant: 'success');
        $this->resetPage();
    }

    public function triggerReject(int $id): void
    {
        $this->rejectId = $id;
        $this->rejectReason = '';
        Flux::modal('reject-seminar')->show();
    }

    public function confirmReject(): void
    {
        $this->validate(['rejectReason' => 'required|string|min:5|max:1000']);
        if (! $this->rejectId) return;

        $row = $this->baseQuery()->where('id', $this->rejectId)->firstOrFail();
        if ($row->status !== KpSeminar::ST_DIAJUKAN) return;

        $row->update([
            'status'                => KpSeminar::ST_DITOLAK,
            'rejected_by_dospem_at' => now(),
            'rejected_reason'       => $this->rejectReason,
        ]);

        $mhsUser = $row->kp?->mahasiswa?->user;
        if ($mhsUser) {
            Notifier::toUser(
                $mhsUser,
                'Seminar Ditolak',
                'Alasan: ' . $this->rejectReason,
                route('mhs.kp.seminar', $row->kerja_praktik_id)
            );
        }

        Flux::modal('reject-seminar')->close();
        Flux::toast(heading: 'Ditolak', text: 'Pengajuan ditolak.', variant: 'warning');

        $this->rejectId = null;
        $this->rejectReason = '';
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.dosen.kp.seminar-approval-index');
    }
}
