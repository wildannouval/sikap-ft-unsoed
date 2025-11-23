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

    /** ID seminar yang sedang dibuka upload-nya (untuk modal global) */
    public ?int $uploadSeminarId = null;

    /** Toggle modal global (dipakai bareng fallback modal attr) */
    public bool $showUploadModal = false;

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
        // Konsisten gunakan PK sebenarnya (getKey), bukan tebak kolom
        $mhs = Mahasiswa::where('user_id', Auth::id())->first();
        return (int) ($mhs?->getKey() ?? 0);
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

    /** Buka modal upload untuk seminar tertentu */
    public function openUpload(int $seminarId): void
    {
        $this->uploadSeminarId = $seminarId;
        $this->showUploadModal = true; // untuk :show binding
    }

    /** Tutup modal upload */
    public function closeUpload(): void
    {
        $this->showUploadModal = false;
        $this->uploadSeminarId = null;
    }

    // Setelah anak selesai, refresh tabel + tutup modal
    #[On('mhs-distribusi-uploaded')]
    public function refreshTable(): void
    {
        $this->closeUpload();
        $this->resetPage();
    }

    // Jika user menekan tombol batal di form anak
    #[On('mhs-upload-cancel')]
    public function handleCancel(): void
    {
        $this->closeUpload();
    }

    public function render()
    {
        return view('livewire.mahasiswa.kp.nilai-index');
    }
}
