<?php

namespace App\Livewire\Bapendik\SuratPengantar;

use App\Models\Signatory;
use App\Models\SuratPengantar;
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

    public function mount(): void
    {
        // default penandatangan (urutkan sesuai jabatan)
        $this->signatory_id = Signatory::query()->orderBy('position')->value('id');
    }

    public function updatingSearch(): void
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

    public function badgeColor(string $status): string
    {
        return match ($status) {
            'Diajukan'    => 'sky',
            'Diterbitkan' => 'emerald',
            'Ditolak'     => 'rose',
            default       => 'zinc',
        };
    }

    protected function baseQuery(): Builder
    {
        return SuratPengantar::query()
            ->with(['mahasiswa.jurusan'])
            ->when($this->search !== '', function ($q) {
                $term = "%{$this->search}%";
                $q->where(function ($qq) use ($term) {
                    $qq->where('lokasi_surat_pengantar', 'like', $term)
                       ->orWhere('penerima_surat_pengantar', 'like', $term)
                       ->orWhere('alamat_surat_pengantar', 'like', $term)
                       ->orWhere('nomor_surat', 'like', $term)
                       ->orWhereHas('mahasiswa', function ($mq) use ($term) {
                           $mq->where('nama_mahasiswa', 'like', $term)
                              ->orWhere('nim', 'like', $term);
                       });
                });
            })
            ->tap(fn ($q) => $this->sortBy ? $q->orderBy($this->sortBy, $this->sortDirection) : $q);
    }

    #[Computed]
    public function ordersPending()
    {
        return $this->baseQuery()
            ->where('status_surat_pengantar', 'Diajukan')
            ->paginate($this->perPage);
    }

    #[Computed]
    public function ordersPublished()
    {
        return $this->baseQuery()
            ->where('status_surat_pengantar', 'Diterbitkan')
            ->paginate($this->perPage);
    }

    // === NEW: counter untuk badge/tab ===
    #[Computed]
    public function pendingCount(): int
    {
        // hitung total Diajukan (tanpa filter pencarian) untuk ringkasan/tab
        return SuratPengantar::where('status_surat_pengantar', 'Diajukan')->count();
    }

    #[Computed]
    public function publishedCount(): int
    {
        // hitung total Diterbitkan (tanpa filter pencarian) untuk ringkasan/tab
        return SuratPengantar::where('status_surat_pengantar', 'Diterbitkan')->count();
    }

    public function openPublish(int $id): void
    {
        $sp = SuratPengantar::findOrFail($id);

        $this->publish_id = $sp->id;
        $this->publish_nomor_surat = (string) ($sp->nomor_surat ?? '');

        // jika belum ada, set default lagi
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

        $sp = SuratPengantar::findOrFail($this->publish_id);
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

        Flux::modal('sp-publish')->close();
        Flux::toast(heading: 'Diterbitkan', text: 'Nomor surat tersimpan & surat diterbitkan.', variant: 'success');

        // reset state
        $this->publish_id = null;
        $this->publish_nomor_surat = '';
        $this->signatory_id = Signatory::query()->orderBy('position')->value('id');

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
        if (! $this->reject_id) return;

        $sp = SuratPengantar::findOrFail($this->reject_id);
        $sp->status_surat_pengantar = 'Ditolak';
        $sp->catatan_surat = $this->catatan_tolak ?: null;
        $sp->save();

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
