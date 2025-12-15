<?php

namespace App\Livewire\Dosen\Kp;

use App\Models\KpSeminar;
use App\Models\KerjaPraktik;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use App\Services\Notifier;
use Flux\Flux;

class SeminarApprovalIndex extends Component
{
    use WithPagination;

    public string $q = '';
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 10;
    public string $statusFilter = 'diajukan';

    public ?int $rejectId = null;
    public string $rejectReason = '';

    public function updatingQ()
    {
        $this->resetPage();
    }
    public function updatingStatusFilter()
    {
        $this->resetPage();
    }
    public function updatingSortBy()
    {
        $this->resetPage();
    }
    public function updatingPerPage()
    {
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
            ->with(['kp.mahasiswa.user'])
            ->where('dosen_pembimbing_id', $this->dosenId)
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

    // --- Helper Badge ---
    public function badgeColor(string $status): string
    {
        return KpSeminar::badgeColor($status);
    }

    public function badgeIcon(string $status): string
    {
        return match ($status) {
            KpSeminar::ST_DIAJUKAN => 'clock',
            KpSeminar::ST_DISETUJUI_PEMBIMBING => 'check-circle',
            KpSeminar::ST_DIJADWALKAN => 'calendar',
            KpSeminar::ST_BA_TERBIT => 'document-text',
            KpSeminar::ST_DITOLAK => 'x-circle',
            default => 'minus',
        };
    }

    public function statusLabel(string $status): string
    {
        return KpSeminar::statusLabel($status);
    }

    public function approve(int $id): void
    {
        $row = KpSeminar::where('id', $id)
            ->where('dosen_pembimbing_id', $this->dosenId)
            ->firstOrFail();

        if ($row->status !== KpSeminar::ST_DIAJUKAN) return;

        $row->update([
            'status'                => KpSeminar::ST_DISETUJUI_PEMBIMBING,
            'approved_by_dospem_at' => now(),
            'rejected_by_dospem_at' => null,
            'rejected_reason'       => null,
        ]);

        // Notif Mahasiswa & Bapendik
        $mhsUser = $row->kp?->mahasiswa?->user;
        if ($mhsUser) {
            Notifier::toUser($mhsUser, 'Seminar Disetujui Dospem', 'Pengajuan seminar disetujui, menunggu penjadwalan.', route('mhs.kp.seminar', $row->kerja_praktik_id));
        }
        Notifier::toRole('Bapendik', 'Jadwalkan Seminar KP', "Seminar {$row->kp?->mahasiswa?->user?->name} perlu dijadwalkan.", route('bap.kp.seminar.jadwal'));

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

        $row = KpSeminar::where('id', $this->rejectId)
            ->where('dosen_pembimbing_id', $this->dosenId)
            ->firstOrFail();

        if ($row->status !== KpSeminar::ST_DIAJUKAN) return;

        $row->update([
            'status'                => KpSeminar::ST_DITOLAK,
            'rejected_by_dospem_at' => now(),
            'rejected_reason'       => $this->rejectReason,
        ]);

        $mhsUser = $row->kp?->mahasiswa?->user;
        if ($mhsUser) {
            Notifier::toUser($mhsUser, 'Seminar Ditolak', 'Alasan: ' . $this->rejectReason, route('mhs.kp.seminar', $row->kerja_praktik_id));
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
