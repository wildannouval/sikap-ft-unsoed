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
    #[Url] public string $tab = 'pending'; // 'pending' | 'published'
    #[Url] public string $search = '';

    // publish modal state
    public ?int $publish_id = null;
    public string $publish_nomor_surat = '';
    public ?int $signatory_id = null;

    // reject modal state
    public ?int $reject_id = null;
    public string $catatan_tolak = '';

    // detail modal state
    public ?int $detailId = null;

    public function mount(): void
    {
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

    // Helper Badge (Sama dengan KP)
    public function badgeColor(string $status): string
    {
        return match ($status) {
            'Diajukan'    => 'sky',      // Menunggu (Warna Sky utk Bapendik)
            'Diterbitkan' => 'emerald',  // Selesai
            'Ditolak'     => 'rose',     // Gagal
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
        return SuratPengantar::with(['mahasiswa.user', 'mahasiswa.jurusan'])->find($this->detailId);
    }

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
        $this->signatory_id ??= Signatory::query()->orderBy('position')->value('id');

        Flux::modal('sp-publish')->show();
    }

    public function publishConfirm(): void
    {
        $this->validate([
            'publish_id'          => ['required', 'integer', 'exists:surat_pengantars,id'],
            'publish_nomor_surat' => ['required', 'string', 'max:255'],
            'signatory_id'        => ['required', 'integer', 'exists:signatories,id'],
        ]);

        $sp  = SuratPengantar::with('mahasiswa')->findOrFail($this->publish_id);
        $sig = Signatory::findOrFail($this->signatory_id);

        $sp->nomor_surat = $this->publish_nomor_surat;
        $sp->status_surat_pengantar = 'Diterbitkan';
        $sp->tanggal_disetujui_surat_pengantar = now();
        $sp->signatory_id = $sig->id;
        $sp->ttd_signed_at = now();

        // snapshot penandatangan
        $sp->ttd_signed_by_name     = $sig->name;
        $sp->ttd_signed_by_position = $sig->position;
        $sp->ttd_signed_by_nip      = $sig->nip;
        $sp->save();

        // NOTIF → Mahasiswa
        $mhsUserId = $sp->mahasiswa?->user_id;
        if ($mhsUserId) {
            Notifier::toUser(
                $mhsUserId,
                'Surat Pengantar Diterbitkan',
                "SP untuk {$sp->lokasi_surat_pengantar} telah diterbitkan. Nomor: {$sp->nomor_surat}.",
                route('mhs.sp.index'),
                ['type' => 'sp_published', 'sp_id' => $sp->id, 'nomor' => $sp->nomor_surat]
            );
        }

        Flux::modal('sp-publish')->close();
        Flux::toast(heading: 'Diterbitkan', text: 'Nomor surat tersimpan & surat diterbitkan.', variant: 'success');

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

        $sp = SuratPengantar::with('mahasiswa')->findOrFail($this->reject_id);
        $sp->status_surat_pengantar = 'Ditolak';
        $sp->catatan_surat = $this->catatan_tolak ?: null;
        $sp->save();

        // NOTIF → Mahasiswa
        $mhsUserId = $sp->mahasiswa?->user_id;
        if ($mhsUserId) {
            Notifier::toUser(
                $mhsUserId,
                'Pengajuan SP Ditolak',
                $sp->catatan_surat ? "Catatan: {$sp->catatan_surat}" : 'Silakan perbaiki data pengajuan.',
                route('mhs.sp.index'),
                ['type' => 'sp_rejected', 'sp_id' => $sp->id]
            );
        }

        Flux::modal('sp-reject')->close();
        Flux::toast(heading: 'Ditolak', text: 'Pengajuan ditolak dan catatan sudah disimpan.', variant: 'warning');

        $this->reject_id = null;
        $this->catatan_tolak = '';
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.bapendik.surat-pengantar.validasi-page');
    }
}
