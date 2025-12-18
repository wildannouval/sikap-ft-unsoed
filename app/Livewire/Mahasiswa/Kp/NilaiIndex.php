<?php

namespace App\Livewire\Mahasiswa\Kp;

use App\Models\Dosen;
use App\Models\KpSeminar;
use App\Models\Mahasiswa;
use App\Models\KerjaPraktik;
use App\Services\Notifier;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Flux\Flux;

class NilaiIndex extends Component
{
    use WithFileUploads;

    public ?int $uploadSeminarId = null;

    /** Form Upload */
    public $fileDistribusi = null;
    public $fileLaporanFinal = null;

    #[Computed]
    public function mahasiswaId(): int
    {
        $mhs = Mahasiswa::where('user_id', Auth::id())->first();
        return (int) ($mhs?->getKey() ?? 0);
    }

    #[Computed]
    public function seminar(): ?KpSeminar
    {
        $mhsId = $this->mahasiswaId;
        if ($mhsId <= 0) return null;

        return KpSeminar::query()
            ->with(['grade', 'kp.mahasiswa.user'])
            ->whereHas('kp', fn($q) => $q->where('mahasiswa_id', $mhsId))
            ->whereIn('status', [KpSeminar::ST_BA_TERBIT, KpSeminar::ST_DINILAI, KpSeminar::ST_SELESAI])
            ->latest('updated_at')
            ->first();
    }

    public function openUpload(): void
    {
        if (! $this->seminar) return;

        $this->resetValidation();
        $this->reset(['fileDistribusi', 'fileLaporanFinal']);

        $this->uploadSeminarId = (int) $this->seminar->id;

        Flux::modal('mhs-upload-distribusi')->show();
    }

    public function closeUpload(): void
    {
        $this->resetValidation();
        $this->uploadSeminarId = null;
        $this->reset(['fileDistribusi', 'fileLaporanFinal']);

        Flux::modal('mhs-upload-distribusi')->close();
    }

    public function saveUpload(): void
    {
        $this->validate([
            'fileDistribusi'   => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'fileLaporanFinal' => ['required', 'file', 'mimes:pdf', 'max:20480'],
        ], [
            'fileDistribusi.required'   => 'Bukti distribusi wajib diunggah.',
            'fileDistribusi.mimes'      => 'Bukti distribusi harus PDF/JPG/JPEG/PNG.',
            'fileDistribusi.max'        => 'Bukti distribusi maksimal 10MB.',
            'fileLaporanFinal.required' => 'Laporan final wajib diunggah.',
            'fileLaporanFinal.mimes'    => 'Laporan final harus PDF.',
            'fileLaporanFinal.max'      => 'Laporan final maksimal 20MB.',
        ]);

        $mhsId = $this->mahasiswaId;
        if ($mhsId <= 0 || ! $this->uploadSeminarId) {
            Flux::toast(heading: 'Gagal', text: 'Data mahasiswa / seminar tidak valid.', variant: 'danger');
            return;
        }

        // Pastikan seminar memang milik mahasiswa (security)
        $seminar = KpSeminar::query()
            ->where('id', $this->uploadSeminarId)
            ->whereHas('kp', fn($q) => $q->where('mahasiswa_id', $mhsId))
            ->firstOrFail();

        $pathDist = null;
        $pathLap  = null;

        try {
            // Simpan file dulu supaya object temporary tidak hilang saat transaksi
            $pathDist = $this->fileDistribusi->store('kp/distribusi', 'public');
            $pathLap  = $this->fileLaporanFinal->store('kp/laporan_final', 'public');

            DB::transaction(function () use ($seminar, $pathDist, $pathLap) {
                // 1) Update kp_seminars
                $seminar->update([
                    'distribusi_proof_path'  => $pathDist,
                    'laporan_final_path'     => $pathLap,
                    'distribusi_uploaded_at' => now(),
                    'status'                 => KpSeminar::ST_SELESAI,
                ]);

                // 2) Update kerja_praktiks.status ke ENUM yang VALID (lihat SQL kamu)
                // enum kerja_praktiks: ... 'nilai_terbit' itu ada, 'selesai' TIDAK ADA
                if ($seminar->kerja_praktik_id) {
                    KerjaPraktik::where('id', $seminar->kerja_praktik_id)->update([
                        'status' => 'nilai_terbit',
                    ]);
                }
            });

            $this->sendNotifications($seminar);

            $this->closeUpload();

            Flux::toast(
                heading: 'Berhasil',
                text: 'Dokumen akhir berhasil diunggah. Status KP selesai.',
                variant: 'success'
            );

            $this->redirect(route('mhs.nilai'), navigate: true);
        } catch (\Throwable $e) {
            // kalau DB gagal, hapus file yang sudah tersimpan agar tidak nyampah
            if ($pathDist && Storage::disk('public')->exists($pathDist)) {
                Storage::disk('public')->delete($pathDist);
            }
            if ($pathLap && Storage::disk('public')->exists($pathLap)) {
                Storage::disk('public')->delete($pathLap);
            }

            report($e);
            Flux::toast(heading: 'Gagal', text: 'Terjadi kesalahan: ' . $e->getMessage(), variant: 'danger');
        }
    }

    private function sendNotifications(KpSeminar $seminar): void
    {
        if ($seminar->dosen_pembimbing_id) {
            $dosen = Dosen::find($seminar->dosen_pembimbing_id);
            if ($dosen?->user_id) {
                Notifier::toUser(
                    (int) $dosen->user_id,
                    'KP Mahasiswa Selesai',
                    'Mahasiswa bimbingan telah mengunggah laporan final & bukti distribusi.',
                    route('dsp.laporan')
                );
            }
        }

        Notifier::toRole(
            'Bapendik',
            'Arsip Laporan Baru',
            'Mahasiswa telah menyelesaikan proses KP (dokumen akhir diunggah).',
            route('bap.kp.nilai')
        );
    }

    public function badgeColor(string $status): string
    {
        return KpSeminar::badgeColor($status);
    }

    public function statusLabel(string $status): string
    {
        return KpSeminar::statusLabel($status);
    }

    public function render(): View
    {
        return view('livewire.mahasiswa.kp.nilai-index');
    }
}
