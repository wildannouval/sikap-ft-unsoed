<?php

namespace App\Livewire\Mahasiswa\Kp;

use App\Models\KpSeminar;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class DistribusiUpload extends Component
{
    use WithFileUploads;

    public ?int $seminarId = null;
    public ?KpSeminar $seminar = null;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $file;

    public bool $showModal = false;

    public function mount(?int $seminarId = null): void
    {
        $this->seminarId = $seminarId;
        $this->loadSeminar();
    }

    public function updatedShowModal(): void
    {
        if ($this->showModal) {
            $this->loadSeminar();
        }
    }

    #[On('open-distribusi-modal')]
    public function openByEvent(int $seminarId): void
    {
        $this->seminarId = $seminarId;
        $this->loadSeminar();
        $this->showModal = true;
    }

    protected function loadSeminar(): void
    {
        if (!$this->seminarId) {
            $this->seminar = null;
            return;
        }

        // Nested whereHas eksplisit agar pasti membatasi ke pemiliknya
        $this->seminar = KpSeminar::query()
            ->with(['kp.mahasiswa.user'])
            ->whereHas(
                'kp',
                fn($q) =>
                $q->whereHas(
                    'mahasiswa',
                    fn($qq) =>
                    $qq->where('user_id', Auth::id())
                )
            )
            ->whereKey($this->seminarId)
            ->first();
    }

    public function save(): void
    {
        $this->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'],
        ], [
            'file.required' => 'Silakan pilih berkas bukti distribusi.',
            'file.max'      => 'Maksimal 10 MB.',
            'file.mimes'    => 'Format harus PDF/JPG/PNG.',
        ]);

        if (!$this->seminar) {
            $this->addError('file', 'Data seminar tidak ditemukan atau bukan milik Anda.');
            return;
        }

        // (Opsional) Batasi hanya jika BA sudah terbit / sudah dinilai
        // if (!in_array($this->seminar->status, ['ba_terbit', 'dinilai'])) {
        //     $this->addError('file', 'Bukti distribusi hanya dapat diunggah setelah BA terbit atau dinilai.');
        //     return;
        // }

        // Hapus file lama (jika ada)
        if ($this->seminar->distribusi_proof_path) {
            Storage::disk('public')->delete($this->seminar->distribusi_proof_path);
        }

        $path = $this->file->store('kp-distribusi', 'public');

        // Gunakan forceFill (abaikan $fillable)
        $this->seminar->forceFill([
            'distribusi_proof_path' => $path,
            'distribusi_uploaded_at' => now(),
        ])->save();

        // Reset state
        $this->reset('file');
        $this->showModal = false;

        // Beri tahu parent untuk refresh
        $this->dispatch('mhs-distribusi-uploaded');
        session()->flash('ok', 'Bukti distribusi berhasil diunggah.');
    }

    public function render()
    {
        return view('livewire.mahasiswa.kp.distribusi-upload');
    }
}
