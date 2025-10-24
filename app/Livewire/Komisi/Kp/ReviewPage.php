<?php

namespace App\Livewire\Komisi\Kp;

use App\Models\KerjaPraktik;
use App\Models\Dosen; // pastikan model ini ada dan tabelnya "dosens" dengan kolom "id", "nama"
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ReviewPage extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $q = '';

    #[Url(as: 'status')]
    public string $statusFilter = 'review_komisi';

    #[Url(as: 'sortBy')]
    public string $sortBy = 'created_at';

    #[Url(as: 'dir')]
    public string $sortDirection = 'desc';

    // tetap 10 per halaman (kontrol UI dihilangkan)
    public int $perPage = 10;

    // modal/detail
    public ?int $detailId   = null;
    public ?int $approveId  = null;
    public ?int $rejectId   = null;
    public string $rejectNote = '';

    // pilih pembimbing
    public ?int $assignId = null;   // kerja_praktik id
    public ?int $dosen_id = null;   // dosen terpilih

    public function updatingQ(){ $this->resetPage(); }
    public function updatingStatusFilter(){ $this->resetPage(); }
    public function updatingSortBy(){ $this->resetPage(); }
    public function updatingSortDirection(){ $this->resetPage(); }

    #[Computed]
    public function orders()
    {
        $keyword = trim($this->q);

        return KerjaPraktik::query()
            ->with(['mahasiswa.user','dosenPembimbing'])
            ->when($keyword !== '', function ($q) use ($keyword) {
                $q->where(function ($qq) use ($keyword) {
                    $qq->where('judul_kp', 'like', "%{$keyword}%")
                       ->orWhere('lokasi_kp', 'like', "%{$keyword}%")
                       ->orWhereHas('mahasiswa', function ($m) use ($keyword) {
                           $m->where('nim', 'like', "%{$keyword}%");
                       })
                       ->orWhereHas('mahasiswa.user', function ($u) use ($keyword) {
                           $u->where('name', 'like', "%{$keyword}%");
                       })
                       ->orWhereHas('dosenPembimbing', function ($d) use ($keyword) {
                           $d->where('nama', 'like', "%{$keyword}%");
                       });
                });
            })
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('status', $this->statusFilter))
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

        return KerjaPraktik::with(['mahasiswa.user','dosenPembimbing'])->find($this->detailId);
    }

    #[Computed]
    public function dosenOptions(): array
    {
        // >>> pakai kolom "nama" saja
        return Dosen::query()
            ->orderBy('nama')
            ->get(['id','nama'])
            ->map(fn($d)=>[
                'id' => $d->id,
                'nama' => $d->nama ?? 'Tanpa Nama',
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

    public function openDetail(int $id): void { $this->detailId = $id; }
    public function closeDetail(): void { $this->detailId = null; }

    public function triggerApprove(int $id): void { $this->approveId = $id; }

    public function confirmApprove(): void
    {
        if (!$this->approveId) return;

        $kp = KerjaPraktik::find($this->approveId);
        if (!$kp) return;

        // Wajib ada pembimbing sebelum diteruskan
        if (is_null($kp->dosen_pembimbing_id)) {
            session()->flash('err', 'Tetapkan dosen pembimbing terlebih dahulu sebelum menyetujui.');
            $this->approveId = null;
            return;
        }

        if ($kp->status === KerjaPraktik::ST_REVIEW_KOMISI) {
            $kp->update(['status' => KerjaPraktik::ST_REVIEW_BAPENDIK]);
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

        $kp = KerjaPraktik::find($this->rejectId);
        if ($kp && $kp->status === KerjaPraktik::ST_REVIEW_KOMISI) {
            $kp->update([
                'status'  => KerjaPraktik::ST_DITOLAK,
                'catatan' => $this->rejectNote,
            ]);
            session()->flash('ok', 'Pengajuan ditolak oleh Komisi.');
        } else {
            session()->flash('err', 'Pengajuan tidak bisa ditolak (status tidak valid).');
        }

        $this->rejectId = null;
        $this->rejectNote = '';
    }

    // ==== TETAPKAN / GANTI PEMBIMBING ====

    public function openAssign(int $id): void
    {
        $kp = KerjaPraktik::findOrFail($id);

        if ($kp->status !== KerjaPraktik::ST_REVIEW_KOMISI) {
            session()->flash('err', 'Pembimbing hanya dapat ditetapkan saat menunggu review komisi.');
            return;
        }

        $this->assignId = $kp->id;
        $this->dosen_id = $kp->dosen_pembimbing_id; // isi jika sudah ada
        // buka modal via <flux:modal.trigger> di blade
    }

    public function saveAssign(): void
    {
        $this->validate([
            'assignId' => ['required', 'exists:kerja_praktiks,id'],
            // >>> validasi pakai tabel "dosens" dan kolom id
            'dosen_id' => ['required', 'exists:dosens,id'],
        ]);

        $kp = KerjaPraktik::findOrFail($this->assignId);

        if ($kp->status !== KerjaPraktik::ST_REVIEW_KOMISI) {
            session()->flash('err', 'Pembimbing hanya dapat ditetapkan saat menunggu review komisi.');
            return;
        }

        $kp->update(['dosen_pembimbing_id' => $this->dosen_id]);

        session()->flash('ok','Dosen pembimbing berhasil ditetapkan.');
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
