<?php

namespace App\Livewire\Mahasiswa\SuratPengantar;

use App\Models\Mahasiswa;
use App\Models\SuratPengantar;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Page extends Component
{
    use WithPagination;

    public ?int $editingId = null;
    public ?int $deletingId = null;

    public string $lokasi_surat_pengantar = '';
    public string $penerima_surat_pengantar = '';
    public string $alamat_surat_pengantar = '';
    public string $tembusan_surat_pengantar = '';

    public string $sortBy = 'tanggal_pengajuan_surat_pengantar';
    public string $sortDirection = 'desc';

    protected function rules(): array
    {
        return [
            'lokasi_surat_pengantar'   => ['required', 'string', 'max:190'],
            'penerima_surat_pengantar' => ['required', 'string', 'max:190'],
            'alamat_surat_pengantar'   => ['required', 'string', 'max:500'],
            'tembusan_surat_pengantar' => ['nullable', 'string', 'max:190'],
        ];
    }

    public function updating($name, $value): void
    {
        if (in_array($name, ['sortBy', 'sortDirection'])) {
            $this->resetPage();
        }
    }

    #[Computed]
    public function orders()
    {
        return $this->mine()
            ->tap(fn ($q) => $this->sortBy ? $q->orderBy($this->sortBy, $this->sortDirection) : $q)
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

    public function badgeColor(string $status): string
    {
        return match ($status) {
            'Diajukan'    => 'blue',
            'Diterbitkan' => 'green',
            'Ditolak'     => 'red',
            default       => 'zinc',
        };
    }

    public function submit(): void
    {
        $this->validate();

        $mhs = Mahasiswa::where('user_id', Auth::id())->firstOrFail();

        SuratPengantar::create([
            'mahasiswa_id'               => $mhs->id,
            'lokasi_surat_pengantar'     => $this->lokasi_surat_pengantar,
            'penerima_surat_pengantar'   => $this->penerima_surat_pengantar,
            'alamat_surat_pengantar'     => $this->alamat_surat_pengantar,
            'tembusan_surat_pengantar'   => $this->tembusan_surat_pengantar ?: null,
            'status_surat_pengantar'     => 'Diajukan',
            'tanggal_pengajuan_surat_pengantar' => now(),
        ]);

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
        $this->lokasi_surat_pengantar = $sp->lokasi_surat_pengantar;
        $this->penerima_surat_pengantar = $sp->penerima_surat_pengantar;
        $this->alamat_surat_pengantar = $sp->alamat_surat_pengantar;
        $this->tembusan_surat_pengantar = $sp->tembusan_surat_pengantar ?: '';
    }

    public function cancelEdit(): void { $this->resetForm(); }

    public function update(): void
    {
        if (! $this->editingId) return;
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
        if (! $this->deletingId) return;
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
        if ($this->sortBy === $c) $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        else { $this->sortBy = $c; $this->sortDirection = 'asc'; }
    }

    protected function mine()
    {
        $mhs = Mahasiswa::where('user_id', Auth::id())->firstOrFail();
        return SuratPengantar::where('mahasiswa_id', $mhs->id);
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
