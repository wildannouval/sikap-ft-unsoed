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

class DistribusiUpload extends Component
{
    use WithFileUploads;

    /** Props dari parent table */
    public int $seminarId;

    /** State */
    public ?KpSeminar $seminar = null;
    public bool $open = false; // expander inline pengganti modal
    public $file;              // uploaded file

    public function mount(int $seminarId): void
    {
        $this->seminarId = $seminarId;
        $this->seminar   = $this->loadSeminarOrFail();
    }

    #[Computed]
    public function mahasiswaId(): int
    {
        return (int) (Mahasiswa::where('user_id', Auth::id())->value('mahasiswa_id') ?? 0);
    }

    protected function loadSeminarOrFail(): KpSeminar
    {
        // pastikan seminar milik mahasiswa yang login
        return KpSeminar::query()
            ->with(['grade', 'kp.mahasiswa.user'])
            ->whereHas('kp', fn($q) => $q->where('mahasiswa_id', $this->mahasiswaId))
            ->findOrFail($this->seminarId);
    }

    protected function rules(): array
    {
        return [
            'file' => [
                'required',
                Rule::when(true, ['file', 'mimes:pdf,jpg,jpeg,png', 'max:10240']), // 10 MB
            ],
        ];
    }

    /** Buka / tutup panel inline (lebih stabil ketimbang modal di dalam row tabel) */
    public function toggle(): void
    {
        // refresh entity biar status & hak akses akurat
        $this->seminar = $this->loadSeminarOrFail();

        // hanya tampilkan panel jika sudah "dinilai" atau "ba_terbit"
        if (!in_array($this->seminar->status, ['dinilai', 'ba_terbit'])) {
            // kalau belum waktunya, beri pesan ringan tapi jangan error-block
            $this->addError('file', 'Bukti distribusi dapat diunggah setelah seminar dinilai atau BA terbit.');
            $this->open = false;
            return;
        }

        $this->open = !$this->open;
    }

    public function save(): void
    {
        $this->validate();

        // reload + cek kepemilikan
        $seminar = $this->loadSeminarOrFail();

        // Guard: hanya boleh upload setelah dinilai atau BA terbit
        if (!in_array($seminar->status, ['dinilai', 'ba_terbit'])) {
            $this->addError('file', 'Bukti distribusi hanya dapat diunggah setelah seminar dinilai atau BA terbit.');
            return;
        }

        // Simpan file
        $path = $this->file->store('kp/distribusi', 'public');

        // Update seminar
        $seminar->update([
            'distribusi_proof_path'  => $path,
            'distribusi_uploaded_at' => now(),
        ]);

        // Notifikasi ke pihak terkait (dosen pembimbing spesifik bila ada, kalau tidak broadcast role)
        try {
            $notified = 0;
            if ($seminar->dosen_pembimbing_id) {
                $dosenClass = '\\App\\Models\\Dosen';
                if (class_exists($dosenClass)) {
                    /** @var \App\Models\Dosen $dosen */
                    $dosen = $dosenClass::find($seminar->dosen_pembimbing_id);
                    if ($dosen && $dosen->user_id) {
                        Notifier::toUser(
                            (int) $dosen->user_id,
                            'Bukti Distribusi Diunggah',
                            'Mahasiswa telah mengunggah bukti distribusi. Silakan tinjau pada menu Penilaian KP.',
                            route('dsp.nilai'),
                            ['kp_seminar_id' => $seminar->id]
                        );
                        $notified++;
                    }
                }
            }
            if ($notified === 0) {
                Notifier::toRole(
                    'Dosen Pembimbing',
                    'Bukti Distribusi Diunggah',
                    'Seorang mahasiswa telah mengunggah bukti distribusi. Tinjau pada menu Penilaian KP.',
                    route('dsp.nilai'),
                    ['kp_seminar_id' => $seminar->id]
                );
            }

            // Bapendik & Komisi
            Notifier::toRole(
                'Bapendik',
                'Bukti Distribusi Diunggah',
                'Bukti distribusi untuk salah satu seminar KP telah diunggah mahasiswa.',
                route('bap.kp.nilai'),
                ['kp_seminar_id' => $seminar->id]
            );

            Notifier::toRole(
                'Dosen Komisi',
                'Bukti Distribusi Diunggah',
                'Bukti distribusi untuk salah satu seminar KP telah diunggah mahasiswa.',
                route('komisi.kp.nilai'),
                ['kp_seminar_id' => $seminar->id]
            );
        } catch (\Throwable $e) {
            // optional: log warning
        }

        // Beres
        $this->reset('file');
        $this->open = false; // tutup panel setelah sukses

        // Refresh entitas lokal
        $this->seminar = $this->loadSeminarOrFail();

        // Beri tahu parent table buat refresh
        $this->dispatch('mhs-distribusi-uploaded');

        session()->flash('ok', 'Bukti distribusi berhasil diunggah.');
    }

    public function render()
    {
        // Pastikan seminar selalu up-to-date
        $this->seminar ??= $this->loadSeminarOrFail();

        return view('livewire.mahasiswa.kp.distribusi-upload');
    }
}
