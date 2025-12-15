<?php

namespace App\Livewire\Mahasiswa\Kp;

use App\Models\KpSeminar;
use App\Models\Mahasiswa;
use App\Services\Notifier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Flux\Flux;

class NilaiIndex extends Component
{
    use WithPagination, WithFileUploads;

    public string $q = '';
    public string $filterStatus = '';
    public int $perPage = 10;

    /** ID seminar yang sedang dibuka upload-nya */
    public ?int $uploadSeminarId = null;

    /** State Form Upload */
    public $file; // TemporaryUploadedFile
    public bool $showUploadModal = false;

    public function updatingQ()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
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
            ->when($this->filterStatus !== '', function ($q) {
                $q->where('status', $this->filterStatus);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage)
            ->withQueryString();
    }

    #[Computed]
    public function stats(): array
    {
        $base = KpSeminar::whereHas('kp', fn($q) => $q->where('mahasiswa_id', $this->mahasiswaId));

        return [
            'dinilai'   => (clone $base)->where('status', KpSeminar::ST_DINILAI)->count(),
            'ba_terbit' => (clone $base)->where('status', KpSeminar::ST_BA_TERBIT)->count(),
            'selesai'   => (clone $base)->whereNotNull('distribusi_proof_path')->count(),
        ];
    }

    #[Computed]
    public function statusOptions(): array
    {
        return [
            ['value' => KpSeminar::ST_DINILAI, 'label' => 'Dinilai'],
            ['value' => KpSeminar::ST_BA_TERBIT, 'label' => 'BA Terbit'],
            ['value' => KpSeminar::ST_SELESAI, 'label' => 'Selesai'],
        ];
    }

    /** Buka modal upload untuk seminar tertentu */
    public function openUpload(int $seminarId): void
    {
        $this->reset(['file']); // Reset file input
        $this->uploadSeminarId = $seminarId;
        $this->showUploadModal = true;

        // FIX: Panggil show() eksplisit agar modal muncul
        Flux::modal('mhs-upload-distribusi')->show();
    }

    /** Tutup modal upload */
    public function closeUpload(): void
    {
        $this->showUploadModal = false;
        $this->uploadSeminarId = null;
        $this->reset(['file']);

        // Opsional: Panggil close() eksplisit
        Flux::modal('mhs-upload-distribusi')->close();
    }

    /** Simpan File Distribusi */
    public function saveUpload(): void
    {
        $this->validate([
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'], // 10MB
        ]);

        // Load Seminar & Validasi Kepemilikan
        $seminar = KpSeminar::where('id', $this->uploadSeminarId)
            ->whereHas('kp', fn($q) => $q->where('mahasiswa_id', $this->mahasiswaId))
            ->firstOrFail();

        // Guard: hanya boleh upload setelah dinilai atau BA terbit
        if (!in_array($seminar->status, [KpSeminar::ST_DINILAI, KpSeminar::ST_BA_TERBIT])) {
            Flux::toast(heading: 'Gagal', text: 'Status seminar tidak valid untuk upload bukti.', variant: 'danger');
            return;
        }

        // Simpan file
        $path = $this->file->store('kp/distribusi', 'public');

        // Update seminar
        $seminar->update([
            'distribusi_proof_path'  => $path,
            'distribusi_uploaded_at' => now(),
            // Opsional: ubah status ke 'selesai' jika diinginkan
            'status' => KpSeminar::ST_SELESAI
        ]);

        // --- Logika Notifikasi ---
        try {
            $notified = 0;
            // Notif ke Dospem
            if ($seminar->dosen_pembimbing_id) {
                $dosenClass = '\\App\\Models\\Dosen';
                if (class_exists($dosenClass)) {
                    $dosen = $dosenClass::find($seminar->dosen_pembimbing_id);
                    if ($dosen && $dosen->user_id) {
                        Notifier::toUser(
                            (int) $dosen->user_id,
                            'Bukti Distribusi Diunggah',
                            'Mahasiswa telah mengunggah bukti distribusi KP.',
                            route('dsp.nilai'),
                            ['kp_seminar_id' => $seminar->id]
                        );
                        $notified++;
                    }
                }
            }
            // Fallback Role Dospem
            if ($notified === 0) {
                Notifier::toRole('Dosen Pembimbing', 'Bukti Distribusi KP', 'Cek menu penilaian.', route('dsp.nilai'));
            }

            // Notif Admin & Komisi
            Notifier::toRole('Bapendik', 'Bukti Distribusi KP', 'Mahasiswa upload bukti distribusi.', route('bap.kp.nilai'));
            Notifier::toRole('Dosen Komisi', 'Bukti Distribusi KP', 'Mahasiswa upload bukti distribusi.', route('komisi.kp.nilai'));
        } catch (\Throwable $e) {
            // Silent fail notification
        }

        $this->closeUpload();
        $this->resetPage(); // Refresh tabel
        Flux::toast(heading: 'Berhasil', text: 'Bukti distribusi berhasil diunggah.', variant: 'success');
    }

    public function render()
    {
        return view('livewire.mahasiswa.kp.nilai-index');
    }
}
