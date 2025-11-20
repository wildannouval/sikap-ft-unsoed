<?php

namespace App\Livewire\Dosen\Kp;

use App\Models\KpSeminar;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use App\Services\Notifier;

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
    public function updatingSortBy()
    {
        $this->resetPage();
    }
    public function updatingSortDirection()
    {
        $this->resetPage();
    }
    public function updatingPerPage()
    {
        $this->resetPage();
    }
    public function updatingStatusFilter()
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
                        // FIX NIM: dukung nim atau mahasiswa_nim
                        ->orWhereHas('kp.mahasiswa', function ($w) use ($term) {
                            $w->where(function ($x) use ($term) {
                                $x->where('nim', 'like', $term)
                                    ->orWhere('mahasiswa_nim', 'like', $term);
                            });
                        });
                });
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage)
            ->withQueryString();
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

        // === NOTIFIKASI
        // 1) ke Mahasiswa
        $mhsUser = $row->kp?->mahasiswa?->user;
        if ($mhsUser) {
            Notifier::toUser(
                $mhsUser,
                'Pengajuan Seminar disetujui Dosen Pembimbing',
                'Pengajuan seminar kamu sudah disetujui dan akan diteruskan ke Bapendik untuk penjadwalan.',
                route('mhs.kp.seminar', ['kp' => $row->kerja_praktik_id]),
                [
                    'type' => 'kp_seminar_approved_by_advisor',
                    'kp_id' => $row->kerja_praktik_id,
                    'seminar_id' => $row->id,
                ]
            );
        }
        // 2) broadcast ke Bapendik
        Notifier::toRole(
            'Bapendik',
            'Perlu Penjadwalan Seminar KP',
            sprintf('Seminar KP untuk %s perlu dijadwalkan.', $row->kp?->mahasiswa?->user?->name ?? 'mahasiswa'),
            route('bap.kp.seminar.jadwal'),
            [
                'type' => 'kp_seminar_need_schedule',
                'kp_id' => $row->kerja_praktik_id,
                'seminar_id' => $row->id,
            ]
        );

        session()->flash('ok', 'Pengajuan seminar disetujui.');
        $this->resetPage();
    }

    public function triggerReject(int $id): void
    {
        $this->rejectId = $id;
        $this->rejectReason = '';
    }

    public function confirmReject(): void
    {
        $this->validate([
            'rejectReason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $row = KpSeminar::where('id', $this->rejectId)
            ->where('dosen_pembimbing_id', $this->dosenId)
            ->firstOrFail();

        if ($row->status !== KpSeminar::ST_DIAJUKAN) return;

        $row->update([
            'status'                 => KpSeminar::ST_DITOLAK,
            'rejected_by_dospem_at'  => now(),
            'rejected_reason'        => $this->rejectReason,
        ]);

        // === NOTIFIKASI → Mahasiswa
        $mhsUser = $row->kp?->mahasiswa?->user;
        if ($mhsUser) {
            Notifier::toUser(
                $mhsUser,
                'Pengajuan Seminar ditolak Dosen Pembimbing',
                'Alasan: ' . $this->rejectReason,
                route('mhs.kp.seminar', ['kp' => $row->kerja_praktik_id]),
                [
                    'type' => 'kp_seminar_rejected_by_advisor',
                    'kp_id' => $row->kerja_praktik_id,
                    'seminar_id' => $row->id,
                    'reason' => $this->rejectReason,
                ]
            );
        }

        session()->flash('ok', 'Pengajuan seminar ditolak.');
        $this->rejectId = null;
        $this->rejectReason = '';
        $this->resetPage();
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
        return view('livewire.dosen.kp.seminar-approval-index');
    }
}
