<?php

namespace App\Livewire\Mahasiswa\SuratPengantar;

use App\Models\Mahasiswa;
use App\Models\SuratPengantar;
use App\Services\Notifier;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Page extends Component
{
    use WithPagination;

    public ?int $mhsId = null;
    public ?int $editingId = null;
    public ?int $deletingId = null;

    // Form Properties
    public string $lokasi_surat_pengantar = '';
    public string $penerima_surat_pengantar = '';
    public string $alamat_surat_pengantar = '';
    public string $tembusan_surat_pengantar = '';

    // Table & Filter Properties
    public string $sortBy = 'tanggal_pengajuan_surat_pengantar';
    public string $sortDirection = 'desc';
    public string $search = '';
    public string $filterStatus = '';

    public function mount(): void
    {
        $mhs = Mahasiswa::where('user_id', Auth::id())->first();
        abort_if(!$mhs, 403, 'Profile mahasiswa tidak ditemukan.');
        $this->mhsId = $mhs->getKey();
    }

    protected function rules(): array
    {
        return [
            'lokasi_surat_pengantar'   => ['required', 'string', 'max:190'],
            'penerima_surat_pengantar' => ['required', 'string', 'max:190'],
            'alamat_surat_pengantar'   => ['required', 'string', 'max:500'],
            'tembusan_surat_pengantar' => ['nullable', 'string', 'max:190'],
        ];
    }

    // Reset pagination saat user mengetik search atau ganti filter
    public function updating($name, $value): void
    {
        if (in_array($name, ['sortBy', 'sortDirection', 'search', 'filterStatus'])) {
            $this->resetPage();
        }
    }

    #[Computed]
    public function orders()
    {
        return $this->mine()
            // Logic Search
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('lokasi_surat_pengantar', 'like', '%' . $this->search . '%')
                        ->orWhere('penerima_surat_pengantar', 'like', '%' . $this->search . '%');
                });
            })
            // Logic Filter Status
            ->when($this->filterStatus, function ($q) {
                $q->where('status_surat_pengantar', $this->filterStatus);
            })
            // Sorting
            ->tap(fn($q) => $this->sortBy ? $q->orderBy($this->sortBy, $this->sortDirection) : $q)
            ->paginate(10);
    }

    #[Computed]
    public function stats(): array
    {
        $base = $this->mine();
        return [
            'Diajukan'    => (clone $base)->where('status_surat_pengantar', 'Diajukan')->count(),
            'Diterbitkan' => (clone $base)->where('status_surat_pengantar', 'Diterbitkan')->count(),
            'Ditolak'     => (clone $base)->where('status_surat_pengantar', 'Ditolak')->count(),
        ];
    }

    public function submit(): void
    {
        $this->validate();

        $sp = SuratPengantar::create([
            'mahasiswa_id'                    => $this->mhsId,
            'lokasi_surat_pengantar'          => $this->lokasi_surat_pengantar,
            'penerima_surat_pengantar'        => $this->penerima_surat_pengantar,
            'alamat_surat_pengantar'          => $this->alamat_surat_pengantar,
            'tembusan_surat_pengantar'        => $this->tembusan_surat_pengantar ?: null,
            'status_surat_pengantar'          => 'Diajukan',
            'tanggal_pengajuan_surat_pengantar' => now(),
        ]);

        // Notif Bapendik
        Notifier::toRole(
            'Bapendik',
            'SP Baru Diajukan',
            "Ada pengajuan SP baru oleh " . Auth::user()->name . ".",
            route('bap.sp.validasi'),
            [
                'type'   => 'sp_submitted',
                'sp_id'  => $sp->id,
                'mhs_id' => $this->mhsId,
            ]
        );

        // Notif Mahasiswa
        Notifier::toUser(
            Auth::id(),
            'Pengajuan SP diterima',
            'Pengajuan berhasil disimpan. Menunggu validasi Bapendik.',
            route('mhs.sp.index'),
            [
                'type'  => 'sp_ack',
                'sp_id' => $sp->id,
            ]
        );

        $this->resetForm();
        Flux::toast(heading: 'Berhasil', variant: 'success', text: 'Pengajuan dikirim.');
    }

    public function edit(int $id): void
    {
        $sp = $this->mine()->findOrFail($id);
        if ($sp->status_surat_pengantar !== 'Diajukan') {
            Flux::toast(heading: 'Tidak bisa diubah', variant: 'danger', text: 'Hanya status Diajukan.');
            return;
        }
        $this->editingId = $sp->id;
        $this->lokasi_surat_pengantar    = $sp->lokasi_surat_pengantar;
        $this->penerima_surat_pengantar = $sp->penerima_surat_pengantar;
        $this->alamat_surat_pengantar    = $sp->alamat_surat_pengantar;
        $this->tembusan_surat_pengantar = $sp->tembusan_surat_pengantar ?: '';
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function update(): void
    {
        if (!$this->editingId) return;

        $this->validate();

        $sp = $this->mine()->findOrFail($this->editingId);
        if ($sp->status_surat_pengantar !== 'Diajukan') {
            Flux::toast(heading: 'Tidak bisa diubah', variant: 'danger', text: 'Hanya status Diajukan.');
            return;
        }

        $sp->update([
            'lokasi_surat_pengantar'   => $this->lokasi_surat_pengantar,
            'penerima_surat_pengantar' => $this->penerima_surat_pengantar,
            'alamat_surat_pengantar'   => $this->alamat_surat_pengantar,
            'tembusan_surat_pengantar' => $this->tembusan_surat_pengantar ?: null,
        ]);

        $this->resetForm();
        Flux::toast(heading: 'Tersimpan', variant: 'success', text: 'Perubahan disimpan.');
    }

    public function markDelete(int $id): void
    {
        $sp = $this->mine()->findOrFail($id);
        if ($sp->status_surat_pengantar !== 'Diajukan') {
            Flux::toast(heading: 'Tidak bisa dihapus', variant: 'danger', text: 'Hanya status Diajukan.');
            return;
        }
        $this->deletingId = $sp->id;
    }

    public function confirmDelete(): void
    {
        if (!$this->deletingId) return;

        $sp = $this->mine()->findOrFail($this->deletingId);
        if ($sp->status_surat_pengantar !== 'Diajukan') {
            Flux::toast(heading: 'Gagal', variant: 'danger', text: 'Status bukan Diajukan.');
            return;
        }

        $sp->delete();
        $this->deletingId = null;

        Flux::modal('delete-sp')->close();
        Flux::toast(heading: 'Terhapus', variant: 'success', text: 'Pengajuan dihapus.');
    }

    public function sort(string $c): void
    {
        if ($this->sortBy === $c) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $c;
            $this->sortDirection = 'asc';
        }
    }

    protected function mine()
    {
        abort_if(!$this->mhsId, 403, 'Mahasiswa tidak valid.');
        return SuratPengantar::where('mahasiswa_id', $this->mhsId);
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->lokasi_surat_pengantar = '';
        $this->penerima_surat_pengantar = '';
        $this->alamat_surat_pengantar = '';
        $this->tembusan_surat_pengantar = '';
    }

    public function render()
    {
        return view('livewire.mahasiswa.surat-pengantar.page');
    }
}
