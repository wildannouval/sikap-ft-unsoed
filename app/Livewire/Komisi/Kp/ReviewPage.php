<?php

namespace App\Livewire\Komisi\Kp;

use App\Models\Dosen;
use App\Models\KerjaPraktik;
use App\Services\Notifier;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ReviewPage extends Component
{
    use WithPagination;

    #[Url(as: 'q')]       public string $q = '';
    #[Url(as: 'status')]  public string $statusFilter = 'review_komisi';
    #[Url(as: 'sortBy')]  public string $sortBy = 'created_at';
    #[Url(as: 'dir')]     public string $sortDirection = 'desc';

    public int $perPage = 10;

    public ?int $detailId   = null;
    public ?int $approveId  = null;
    public ?int $rejectId   = null;
    public string $rejectNote = '';

    public ?int $assignId = null;
    public ?int $dosen_id = null;

    /**
     * Kalau user klik "Setujui" tapi pembimbing belum ada,
     * kita paksa pilih pembimbing dulu dan auto-approve setelah simpan.
     */
    public bool $approveAfterAssign = false;

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
        return [
            'review_komisi'   => KerjaPraktik::where('status', KerjaPraktik::ST_REVIEW_KOMISI)->count(),
            'review_bapendik' => KerjaPraktik::where('status', KerjaPraktik::ST_REVIEW_BAPENDIK)->count(),
            'spk_terbit'      => KerjaPraktik::where('status', KerjaPraktik::ST_SPK_TERBIT)->count(),
            'ditolak'         => KerjaPraktik::where('status', KerjaPraktik::ST_DITOLAK)->count(),
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
        Flux::modal('detail-kp')->show();
    }

    public function closeDetail(): void
    {
        $this->detailId = null;
        Flux::modal('detail-kp')->close();
    }

    /**
     * Klik "Setujui" dari menu:
     * - jika pembimbing belum ada → buka modal assign dan set approveAfterAssign = true
     * - jika sudah ada → buka modal approve biasa
     */
    public function triggerApprove(int $id): void
    {
        $kp = KerjaPraktik::find($id);
        if (!$kp) return;

        if ($kp->status !== KerjaPraktik::ST_REVIEW_KOMISI) {
            Flux::toast(heading: 'Gagal', text: 'Status tidak valid untuk disetujui.', variant: 'danger');
            return;
        }

        if (is_null($kp->dosen_pembimbing_id)) {
            $this->approveAfterAssign = true;
            $this->assignId = $kp->id;
            $this->dosen_id = null;

            Flux::modal('assign-mentor')->show();
            Flux::toast(heading: 'Pilih pembimbing', text: 'Pilih dosen pembimbing lalu Simpan untuk langsung menyetujui.', variant: 'warning');
            return;
        }

        $this->approveAfterAssign = false;
        $this->approveId = $kp->id;
        Flux::modal('approve-kp')->show();
    }

    /**
     * Approve lewat modal approve.
     */
    public function confirmApprove(): void
    {
        if (!$this->approveId) return;

        try {
            DB::transaction(function () {
                $kp = KerjaPraktik::with(['mahasiswa.user', 'dosenPembimbing'])
                    ->lockForUpdate()
                    ->findOrFail($this->approveId);

                $this->approveKp($kp);
            });

            Flux::toast(heading: 'Disetujui', text: 'Pengajuan diteruskan ke Bapendik.', variant: 'success');
        } catch (\Throwable $e) {
            Flux::toast(heading: 'Gagal', text: $e->getMessage(), variant: 'danger');
        }

        $this->approveId = null;
        Flux::modal('approve-kp')->close();
        $this->resetPage();
    }

    public function triggerReject(int $id): void
    {
        $this->rejectId = $id;
        $this->rejectNote = '';
        Flux::modal('reject-kp')->show();
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

            Flux::toast(heading: 'Ditolak', text: 'Pengajuan ditolak.', variant: 'warning');
        } else {
            Flux::toast(heading: 'Gagal', text: 'Status tidak valid untuk ditolak.', variant: 'danger');
        }

        $this->rejectId = null;
        $this->rejectNote = '';
        Flux::modal('reject-kp')->close();
        $this->resetPage();
    }

    public function openAssign(int $id): void
    {
        $kp = KerjaPraktik::findOrFail($id);

        if ($kp->status !== KerjaPraktik::ST_REVIEW_KOMISI) {
            Flux::toast(heading: 'Gagal', text: 'Hanya bisa saat status Review Komisi.', variant: 'danger');
            return;
        }

        $this->approveAfterAssign = false;
        $this->assignId = $kp->id;
        $this->dosen_id = $kp->dosen_pembimbing_id;

        Flux::modal('assign-mentor')->show();
    }

    /**
     * Save assign:
     * - default (Simpan) → hanya set pembimbing
     * - saveAssign(true) (Simpan & Setujui) → set pembimbing lalu approve
     * - atau jika approveAfterAssign = true → auto approve
     */
    public function saveAssign(bool $alsoApprove = false): void
    {
        $this->validate([
            'assignId' => ['required', 'exists:kerja_praktiks,id'],
            'dosen_id' => ['required', 'exists:dosens,dosen_id'],
        ]);

        $approved = false;

        try {
            DB::transaction(function () use (&$approved, $alsoApprove) {
                $kp = KerjaPraktik::with(['mahasiswa.user'])
                    ->lockForUpdate()
                    ->findOrFail($this->assignId);

                if ($kp->status !== KerjaPraktik::ST_REVIEW_KOMISI) {
                    throw new \RuntimeException('Hanya bisa saat status Review Komisi.');
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

                // Auto approve
                if ($alsoApprove || $this->approveAfterAssign) {
                    $this->approveKp($kp);
                    $approved = true;
                }
            });

            Flux::toast(
                heading: 'Berhasil',
                text: $approved ? 'Pembimbing ditetapkan & pengajuan diteruskan ke Bapendik.' : 'Dosen pembimbing ditetapkan.',
                variant: 'success'
            );
        } catch (\Throwable $e) {
            Flux::toast(heading: 'Gagal', text: $e->getMessage(), variant: 'danger');
        }

        $this->assignId = null;
        $this->dosen_id = null;
        $this->approveAfterAssign = false;
        $this->approveId = null;

        Flux::modal('assign-mentor')->close();
        Flux::modal('approve-kp')->close();

        $this->resetPage();
    }

    /**
     * Helper approve agar bisa dipanggil dari confirmApprove() maupun saveAssign().
     */
    protected function approveKp(KerjaPraktik $kp): void
    {
        $kp->loadMissing(['mahasiswa.user', 'dosenPembimbing']);

        if (is_null($kp->dosen_pembimbing_id)) {
            throw new \RuntimeException('Tetapkan dosen pembimbing terlebih dahulu.');
        }

        if ($kp->status !== KerjaPraktik::ST_REVIEW_KOMISI) {
            throw new \RuntimeException('Status tidak valid untuk disetujui.');
        }

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
    }

    public function badgeColor(string $status): string
    {
        return KerjaPraktik::badgeColor($status);
    }

    public function badgeIcon(string $status): string
    {
        return match ($status) {
            'review_komisi'   => 'clock',
            'review_bapendik' => 'clock',
            'spk_terbit'      => 'check-circle',
            'ditolak'         => 'x-circle',
            default           => 'minus',
        };
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
