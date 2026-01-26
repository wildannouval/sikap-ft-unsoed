<?php

namespace App\Livewire\Bapendik\SuratPengantar;

use App\Models\Signatory;
use App\Models\SuratPengantar;
use App\Services\Notifier;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ValidasiPage extends Component
{
    use WithPagination;

    #[Url] public string $sortBy = 'tanggal_pengajuan_surat_pengantar';
    #[Url] public string $sortDirection = 'desc';
    #[Url] public int $perPage = 10;

    // Tabs: pending | published | rejected
    #[Url] public string $tab = 'pending';
    #[Url] public string $search = '';

    // Publish Modal State
    public ?int $publish_id = null;
    public string $publish_nomor_surat = '';
    public ?int $signatory_id = null;

    // Reject Modal State
    public ?int $reject_id = null;
    public string $catatan_tolak = '';

    // Detail Modal State
    public ?int $detailId = null;

    public function mount(): void
    {
        // Default penandatangan (urutan pertama)
        $this->signatory_id = Signatory::query()->orderBy('position')->value('id');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }
    public function updatingTab(): void
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

    // Helpers

    public function badgeColor(string $status): string
    {
        return match ($status) {
            'Diajukan'    => 'sky',
            'Diterbitkan' => 'emerald',
            'Ditolak'     => 'rose',
            default       => 'zinc',
        };
    }

    public function badgeIcon(string $status): string
    {
        return match ($status) {
            'Diajukan'    => 'clock',
            'Diterbitkan' => 'check-circle',
            'Ditolak'     => 'x-circle',
            default       => 'minus',
        };
    }

    // Queries

    protected function baseQuery(): Builder
    {
        return SuratPengantar::query()
            ->with(['mahasiswa.jurusan', 'mahasiswa.user'])
            ->when($this->search !== '', function ($q) {
                $term = "%{$this->search}%";
                $q->where(function ($qq) use ($term) {
                    $qq->where('lokasi_surat_pengantar', 'like', $term)
                        ->orWhere('penerima_surat_pengantar', 'like', $term)
                        ->orWhere('alamat_surat_pengantar', 'like', $term)
                        ->orWhere('nomor_surat', 'like', $term)
                        ->orWhereHas('mahasiswa', function ($mq) use ($term) {
                            $mq->where('mahasiswa_nim', 'like', $term)
                                ->orWhereHas('user', fn($u) => $u->where('name', 'like', $term));
                        });
                });
            })
            ->tap(fn($q) => $this->sortBy ? $q->orderBy($this->sortBy, $this->sortDirection) : $q);
    }

    #[Computed]
    public function ordersPending()
    {
        return $this->baseQuery()
            ->where('status_surat_pengantar', 'Diajukan')
            ->paginate($this->perPage, ['*'], 'pendingPage');
    }

    #[Computed]
    public function ordersPublished()
    {
        return $this->baseQuery()
            ->where('status_surat_pengantar', 'Diterbitkan')
            ->paginate($this->perPage, ['*'], 'publishedPage');
    }

    #[Computed]
    public function ordersRejected()
    {
        return $this->baseQuery()
            ->where('status_surat_pengantar', 'Ditolak')
            ->paginate($this->perPage, ['*'], 'rejectedPage');
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'pending'   => SuratPengantar::where('status_surat_pengantar', 'Diajukan')->count(),
            'published' => SuratPengantar::where('status_surat_pengantar', 'Diterbitkan')->count(),
            'rejected'  => SuratPengantar::where('status_surat_pengantar', 'Ditolak')->count(),
        ];
    }

    #[Computed]
    public function selectedItem(): ?SuratPengantar
    {
        if (!$this->detailId) return null;
        // Load relasi lengkap untuk modal detail
        return SuratPengantar::with(['mahasiswa.user', 'mahasiswa.jurusan', 'signatory'])->find($this->detailId);
    }

    // Actions

    public function openDetail(int $id): void
    {
        $this->detailId = $id;
        Flux::modal('sp-detail')->show();
    }

    public function closeDetail(): void
    {
        $this->detailId = null;
        Flux::modal('sp-detail')->close();
    }

    public function openPublish(int $id): void
    {
        $sp = SuratPengantar::findOrFail($id);

        $this->publish_id = $sp->id;
        $this->publish_nomor_surat = (string) ($sp->nomor_surat ?? '');
        // Default signatory to existing or first available
        $this->signatory_id = $sp->signatory_id ?? Signatory::query()->orderBy('position')->value('id');

        Flux::modal('sp-publish')->show();
    }

    public function publishConfirm(): void
    {
        // Validasi: Nomor surat boleh kosong, tapi Signatory wajib
        $this->validate([
            'publish_id'          => ['required', 'integer', 'exists:surat_pengantars,id'],
            'publish_nomor_surat' => ['nullable', 'string', 'max:255'],
            'signatory_id'        => ['required', 'integer', 'exists:signatories,id'],
        ]);

        $sp  = SuratPengantar::with('mahasiswa')->findOrFail($this->publish_id);
        $sig = Signatory::findOrFail($this->signatory_id);

        $sp->nomor_surat = $this->publish_nomor_surat;
        $sp->status_surat_pengantar = 'Diterbitkan';
        $sp->tanggal_disetujui_surat_pengantar = now();
        $sp->signatory_id = $sig->id;
        $sp->ttd_signed_at = now();

        // Snapshot data pejabat
        $sp->ttd_signed_by_name     = $sig->name;
        $sp->ttd_signed_by_position = $sig->position;
        $sp->ttd_signed_by_nip      = $sig->nip;
        $sp->save();

        // NOTIFIKASI
        $mhsUserId = $sp->mahasiswa?->user_id;
        if ($mhsUserId) {
            Notifier::toUser(
                $mhsUserId,
                'Surat Pengantar Diterbitkan',
                "SP untuk {$sp->lokasi_surat_pengantar} telah diterbitkan.",
                route('mhs.sp.index'),
                ['type' => 'sp_published', 'sp_id' => $sp->id]
            );
        }

        Flux::modal('sp-publish')->close();
        Flux::toast(heading: 'Diterbitkan', text: 'Surat berhasil diterbitkan.', variant: 'success');

        $this->publish_id = null;
        $this->publish_nomor_surat = '';
        $this->resetPage();
    }

    public function openReject(int $id): void
    {
        $this->reject_id = $id;
        $this->catatan_tolak = '';
        Flux::modal('sp-reject')->show();
    }

    public function submitReject(): void
    {
        if (!$this->reject_id) return;

        // Validasi: Catatan tolak wajib diisi
        $this->validate([
            'catatan_tolak' => ['required', 'string', 'min:5'],
        ], [
            'catatan_tolak.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $sp = SuratPengantar::with('mahasiswa')->findOrFail($this->reject_id);
        $sp->status_surat_pengantar = 'Ditolak';
        $sp->catatan_surat = $this->catatan_tolak;
        $sp->save();

        // NOTIFIKASI
        $mhsUserId = $sp->mahasiswa?->user_id;
        if ($mhsUserId) {
            Notifier::toUser(
                $mhsUserId,
                'Pengajuan SP Ditolak',
                "Catatan: {$sp->catatan_surat}",
                route('mhs.sp.index'),
                ['type' => 'sp_rejected', 'sp_id' => $sp->id]
            );
        }

        Flux::modal('sp-reject')->close();
        Flux::toast(heading: 'Ditolak', text: 'Pengajuan ditolak.', variant: 'warning');

        $this->reject_id = null;
        $this->catatan_tolak = '';
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.bapendik.surat-pengantar.validasi-page');
    }
}
