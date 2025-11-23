<?php

namespace App\Livewire\Komisi\Kp;

use App\Models\KerjaPraktik;
use App\Models\Dosen;
use App\Services\Notifier;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ReviewPage extends Component
{
    use WithPagination;

    #[Url(as: 'q')]      public string $q = '';
    #[Url(as: 'status')] public string $statusFilter = 'review_komisi';
    #[Url(as: 'sortBy')] public string $sortBy = 'created_at';
    #[Url(as: 'dir')]    public string $sortDirection = 'desc';

    public int $perPage = 10;

    public ?int $detailId   = null;
    public ?int $approveId  = null;
    public ?int $rejectId   = null;
    public string $rejectNote = '';

    public ?int $assignId = null;
    public ?int $dosen_id = null;

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
    public function updatingSortDirection()
    {
        $this->resetPage();
    }

    #[Computed]
    public function orders()
    {
        $keyword = trim($this->q);

        return KerjaPraktik::query()
            ->with(['mahasiswa.user', 'dosenPembimbing'])
            ->when($keyword !== '', function ($q) use ($keyword) {
                $q->where(function ($qq) use ($keyword) {
                    $qq->where('judul_kp', 'like', "%{$keyword}%")
                        ->orWhere('lokasi_kp', 'like', "%{$keyword}%")
                        ->orWhereHas('mahasiswa', function ($m) use ($keyword) {
                            $m->where('mahasiswa_nim', 'like', "%{$keyword}%");
                        })
                        ->orWhereHas('mahasiswa.user', function ($u) use ($keyword) {
                            $u->where('name', 'like', "%{$keyword}%");
                        })
                        ->orWhereHas('dosenPembimbing', function ($d) use ($keyword) {
                            $d->where('dosen_name', 'like', "%{$keyword}%");
                        });
                });
            })
            ->when($this->statusFilter !== 'all', fn($q) => $q->where('status', $this->statusFilter))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage)
            ->withQueryString();
    }

    #[Computed]
    public function stats(): array
    {
        $base = KerjaPraktik::query();

        return [
            'review_komisi'   => (clone $base)->where('status', KerjaPraktik::ST_REVIEW_KOMISI)->count(),
            'review_bapendik' => (clone $base)->where('status', KerjaPraktik::ST_REVIEW_BAPENDIK)->count(),
            'spk_terbit'      => (clone $base)->where('status', KerjaPraktik::ST_SPK_TERBIT)->count(),
            'ditolak'         => (clone $base)->where('status', KerjaPraktik::ST_DITOLAK)->count(),
        ];
    }

    #[Computed]
    public function selectedItem(): ?KerjaPraktik
    {
        if (!$this->detailId) return null;
        return KerjaPraktik::with(['mahasiswa.user', 'dosenPembimbing'])->find($this->detailId);
    }

    #[Computed]
    public function dosenOptions(): array
    {
        return Dosen::query()
            ->orderBy('dosen_name')
            ->get(['dosen_id', 'dosen_name'])
            ->map(fn($d) => [
                'id'   => $d->getKey(),
                'nama' => $d->dosen_name ?? 'Tanpa Nama',
            ])
            ->all();
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

    public function openDetail(int $id): void
    {
        $this->detailId = $id;
    }
    public function closeDetail(): void
    {
        $this->detailId = null;
    }

    public function triggerApprove(int $id): void
    {
        $this->approveId = $id;
    }

    public function confirmApprove(): void
    {
        if (!$this->approveId) return;

        $kp = KerjaPraktik::with(['mahasiswa.user', 'dosenPembimbing'])->find($this->approveId);
        if (!$kp) return;

        if (is_null($kp->dosen_pembimbing_id)) {
            session()->flash('err', 'Tetapkan dosen pembimbing terlebih dahulu sebelum menyetujui.');
            $this->approveId = null;
            return;
        }

        if ($kp->status === KerjaPraktik::ST_REVIEW_KOMISI) {
            $kp->update(['status' => KerjaPraktik::ST_REVIEW_BAPENDIK]);

            // 1) Ke Bapendik
            Notifier::toRole(
                'Bapendik',
                'KP Disetujui Komisi',
                "Pengajuan KP oleh {$kp->mahasiswa?->user?->name} telah disetujui Komisi dan menunggu penerbitan SPK.",
                route('bap.kp.spk'),
                ['type' => 'kp_approved_by_komisi', 'kp_id' => $kp->id]
            );

            // 2) Ke Mahasiswa
            Notifier::toUser(
                $kp->mahasiswa?->user_id,
                'Pengajuan KP Disetujui Komisi',
                'Pengajuan telah disetujui Komisi dan diteruskan ke Bapendik untuk penerbitan SPK.',
                route('mhs.kp.index'),
                ['type' => 'kp_forwarded_to_bapendik', 'kp_id' => $kp->id]
            );

            session()->flash('ok', 'Pengajuan diteruskan ke Bapendik untuk penerbitan SPK.');
        } else {
            session()->flash('err', 'Pengajuan tidak bisa disetujui (status tidak valid).');
        }

        $this->approveId = null;
    }

    public function triggerReject(int $id): void
    {
        $this->rejectId = $id;
        $this->rejectNote = '';
    }

    public function confirmReject(): void
    {
        $this->validate([
            'rejectNote' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        if (!$this->rejectId) return;

        $kp = KerjaPraktik::with('mahasiswa.user')->find($this->rejectId);
        if ($kp && $kp->status === KerjaPraktik::ST_REVIEW_KOMISI) {
            $kp->update([
                'status'  => KerjaPraktik::ST_DITOLAK,
                'catatan' => $this->rejectNote,
            ]);

            Notifier::toUser(
                $kp->mahasiswa?->user_id,
                'Pengajuan KP Ditolak',
                "Pengajuan KP ditolak oleh Komisi. Catatan: {$this->rejectNote}",
                route('mhs.kp.index'),
                ['type' => 'kp_rejected', 'kp_id' => $kp->id]
            );

            session()->flash('ok', 'Pengajuan ditolak oleh Komisi.');
        } else {
            session()->flash('err', 'Pengajuan tidak bisa ditolak (status tidak valid).');
        }

        $this->rejectId = null;
        $this->rejectNote = '';
    }

    public function openAssign(int $id): void
    {
        $kp = KerjaPraktik::findOrFail($id);

        if ($kp->status !== KerjaPraktik::ST_REVIEW_KOMISI) {
            session()->flash('err', 'Pembimbing hanya dapat ditetapkan saat menunggu review komisi.');
            return;
        }

        $this->assignId = $kp->id;
        $this->dosen_id = $kp->dosen_pembimbing_id;
    }

    public function saveAssign(): void
    {
        $this->validate([
            'assignId' => ['required', 'exists:kerja_praktiks,id'],
            'dosen_id' => ['required', 'exists:dosens,dosen_id'],
        ]);

        $kp = KerjaPraktik::with(['mahasiswa.user'])->findOrFail($this->assignId);
        if ($kp->status !== KerjaPraktik::ST_REVIEW_KOMISI) {
            session()->flash('err', 'Pembimbing hanya dapat ditetapkan saat menunggu review komisi.');
            return;
        }

        $kp->update(['dosen_pembimbing_id' => $this->dosen_id]);

        $dosen = Dosen::find($this->dosen_id);

        // 1) Ke Mahasiswa
        Notifier::toUser(
            $kp->mahasiswa?->user_id,
            'Dosen Pembimbing Ditugaskan',
            "Komisi menetapkan {$dosen?->dosen_name} sebagai dosen pembimbing KP-mu.",
            route('mhs.kp.index'),
            ['type' => 'kp_supervisor_assigned', 'kp_id' => $kp->id, 'dosen_id' => $this->dosen_id]
        );

        // 2) Ke Dosen
        if ($dosen && !empty($dosen->user_id)) {
            Notifier::toUser(
                $dosen->user_id,
                'Penetapan Dosen Pembimbing KP',
                "Anda ditetapkan sebagai pembimbing KP untuk {$kp->mahasiswa?->user?->name}.",
                route('dsp.kp.konsultasi'),
                ['type' => 'kp_supervisor_assigned_to_lecturer', 'kp_id' => $kp->id]
            );
        }

        session()->flash('ok', 'Dosen pembimbing berhasil ditetapkan.');
        $this->assignId = null;
        $this->dosen_id = null;
        $this->resetPage();
    }

    public function badgeColor(string $status): string
    {
        return KerjaPraktik::badgeColor($status);
    }
    public function statusLabel(string $status): string
    {
        return KerjaPraktik::statusLabel($status);
    }

    public function render()
    {
        return view('livewire.komisi.kp.review-page');
    }
}
