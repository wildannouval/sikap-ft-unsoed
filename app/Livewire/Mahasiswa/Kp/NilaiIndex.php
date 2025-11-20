<?php

namespace App\Livewire\Mahasiswa\Kp;

use App\Models\KpSeminar;
use App\Models\Mahasiswa;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class NilaiIndex extends Component
{
    use WithPagination;

    public string $q = '';
    public int $perPage = 10;

    public function updatingQ()
    {
        $this->resetPage();
    }
    public function updatingPerPage()
    {
        $this->resetPage();
    }

    #[Computed]
    public function mahasiswaId(): int
    {
        return (int) (Mahasiswa::where('user_id', Auth::id())->value('mahasiswa_id') ?? 0);
    }

    #[Computed]
    public function items()
    {
        return KpSeminar::query()
            ->with(['grade', 'kp.mahasiswa.user'])
            ->whereHas('kp', fn($q) => $q->where('mahasiswa_id', $this->mahasiswaId))
            ->when($this->q !== '', function ($q) {
                $kw = '%' . $this->q . '%';
                $q->where('judul_laporan', 'like', $kw);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage)
            ->withQueryString();
    }

    // Setelah anak (komponen upload) selesai, refresh tabel biar status langsung terlihat
    #[On('mhs-distribusi-uploaded')]
    public function refreshTable(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.mahasiswa.kp.nilai-index');
    }
}
