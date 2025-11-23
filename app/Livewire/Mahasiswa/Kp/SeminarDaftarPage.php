<?php

namespace App\Livewire\Mahasiswa\Kp;

use App\Models\KerjaPraktik;
use App\Models\Mahasiswa;
use App\Models\KpSeminar;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\Notifier;

class SeminarDaftarPage extends Component
{
    use WithFileUploads;

    /** KP milik mahasiswa */
    public KerjaPraktik $kp;

    /** Entity seminar (jika sudah ada) */
    public ?KpSeminar $seminar = null;

    /** Form fields */
    public string $judul_kp_final = '';
    public ?string $tanggal_seminar = null; // YYYY-MM-DD
    public ?int $ruangan_id = null;
    public ?string $jam_mulai = null;   // HH:MM
    public ?string $jam_selesai = null; // HH:MM

    /** Optional tambahan */
    public ?string $abstrak = null;

    /** Upload (opsional) */
    public $berkas_laporan; // UploadedFile (PDF)
    public ?string $berkas_laporan_path = null;

    /** Status string */
    public string $status = KpSeminar::ST_DIAJUKAN;

    public function mount(KerjaPraktik $kp): void
    {
        $mhs = Mahasiswa::where('user_id', Auth::id())->firstOrFail();

        // Pastikan KP milik mahasiswa login (bandingkan dengan PK sebenarnya)
        abort_unless((int)$kp->mahasiswa_id === (int)$mhs->getKey(), 403, 'KP bukan milik Anda.');

        // Status KP harus aktif
        abort_unless(
            in_array($kp->status, [KerjaPraktik::ST_SPK_TERBIT, KerjaPraktik::ST_KP_BERJALAN], true),
            403,
            'Daftar seminar hanya untuk KP aktif.'
        );

        // Minimal 6 konsultasi terverifikasi
        abort_unless($kp->verifiedConsultationsCount() >= 6, 403, 'Minimal 6 konsultasi terverifikasi.');

        $this->kp = $kp;

        // Ambil/isi seminar yang sudah ada
        $this->seminar = KpSeminar::where('kerja_praktik_id', $kp->id)
            ->where('mahasiswa_id', $mhs->getKey())
            ->latest('id')
            ->first();

        if ($this->seminar) {
            $this->judul_kp_final      = (string)($this->seminar->judul_laporan ?? '');
            $this->abstrak             = $this->seminar->abstrak;
            $this->berkas_laporan_path = $this->seminar->berkas_laporan_path;
            $this->status              = $this->seminar->status;

            $this->tanggal_seminar = optional($this->seminar->tanggal_seminar)->toDateString();
            $this->ruangan_id      = $this->seminar->ruangan_id;
            $this->jam_mulai       = $this->seminar->jam_mulai;
            $this->jam_selesai     = $this->seminar->jam_selesai;
        }
    }

    protected function rules(): array
    {
        return [
            'judul_kp_final'  => ['required', 'string', 'min:5', 'max:255'],
            'tanggal_seminar' => ['required', 'date'],
            'ruangan_id'      => ['required', Rule::exists('rooms', 'id')],
            'jam_mulai'       => ['required', 'date_format:H:i'],
            'jam_selesai'     => ['required', 'date_format:H:i', 'after:jam_mulai'],

            'abstrak'         => ['nullable', 'string', 'max:5000'],
            'berkas_laporan'  => ['nullable', 'file', 'mimes:pdf', 'max:10240'], // 10MB
        ];
    }

    #[Computed]
    public function statusLabel(): string
    {
        return KpSeminar::statusLabel($this->status);
    }

    #[Computed]
    public function isLocked(): bool
    {
        return in_array($this->status, [
            KpSeminar::ST_DISETUJUI_PEMBIMBING,
            KpSeminar::ST_DIJADWALKAN,
            KpSeminar::ST_BA_TERBIT,
        ], true);
    }

    #[Computed]
    public function rooms(): array
    {
        return Room::orderBy('building')
            ->orderBy('room_number')
            ->get()
            ->map(fn(Room $r) => [
                'id'    => $r->id,
                'label' => "{$r->room_number} — {$r->building}",
            ])
            ->all();
    }

    /** Gabung tanggal + jam_mulai jadi datetime untuk kolom `tanggal_seminar` */
    private function buildSeminarDateTime(): ?string
    {
        if (!$this->tanggal_seminar || !$this->jam_mulai) return null;
        return "{$this->tanggal_seminar} {$this->jam_mulai}:00";
    }

    public function saveDraft(): void
    {
        $this->validate();

        $mhs = Mahasiswa::where('user_id', Auth::id())->firstOrFail();

        // Snapshot nama ruangan
        $ruanganNama = null;
        if ($this->ruangan_id) {
            $room = Room::find($this->ruangan_id);
            if ($room) {
                $ruanganNama = "{$room->room_number} — {$room->building}";
            }
        }

        $payload = [
            'kerja_praktik_id'    => $this->kp->id,
            'mahasiswa_id'        => $mhs->getKey(),
            'dosen_pembimbing_id' => $this->kp->dosen_pembimbing_id,
            'judul_laporan'       => $this->judul_kp_final,
            'abstrak'             => $this->abstrak,
            'tanggal_seminar'     => $this->buildSeminarDateTime(),
            'jam_mulai'           => $this->jam_mulai,
            'jam_selesai'         => $this->jam_selesai,
            'ruangan_id'          => $this->ruangan_id,
            'ruangan_nama'        => $ruanganNama,
        ];

        if ($this->berkas_laporan) {
            $payload['berkas_laporan_path'] = $this->berkas_laporan->store('kp/seminar/laporan', 'public');
            $this->berkas_laporan_path      = $payload['berkas_laporan_path'];
        }

        if ($this->seminar) {
            if ($this->isLocked()) {
                session()->flash('err', 'Tidak dapat mengubah data pada status saat ini.');
                return;
            }
            $this->seminar->update($payload);
        } else {
            $payload['status'] = KpSeminar::ST_DIAJUKAN;
            $this->seminar = KpSeminar::create($payload);
            $this->status  = $this->seminar->status;
        }

        session()->flash('ok', 'Draf pendaftaran tersimpan.');
    }

    public function submitToAdvisor(): void
    {
        $this->validate();

        // Pastikan draf tersimpan (agar path/file & snapshot ruangan terisi)
        $this->saveDraft();

        // Tetapkan status "diajukan" (nanti dospem yang setujui/menolak)
        $this->seminar->update([
            'status' => KpSeminar::ST_DIAJUKAN,
        ]);
        $this->status = KpSeminar::ST_DIAJUKAN;

        // Notif → Dosen Pembimbing
        $dosenUser = $this->kp->dosenPembimbing?->user;
        $mhs = $this->kp->mahasiswa;
        if ($dosenUser) {
            Notifier::toUser(
                $dosenUser,
                'Pengajuan Seminar KP baru',
                sprintf(
                    '%s (%s) mengajukan seminar: %s.',
                    $mhs?->user?->name ?? 'Mahasiswa',
                    $mhs?->nim ?? '-',
                    $this->judul_kp_final
                ),
                route('dsp.kp.seminar.approval'),
                [
                    'type' => 'kp_seminar_submitted',
                    'kp_id' => $this->kp->id,
                    'seminar_id' => $this->seminar->id,
                ]
            );
        }

        session()->flash('ok', 'Pengajuan dikirim ke Dosen Pembimbing.');
    }

    public function removeFile(): void
    {
        if (!$this->seminar || $this->isLocked()) return;

        $path = $this->berkas_laporan_path;
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        $this->berkas_laporan_path = null;
        $this->seminar->update(['berkas_laporan_path' => null]);

        session()->flash('ok', 'Berkas laporan dihapus.');
    }

    public function render()
    {
        return view('livewire.mahasiswa.kp.seminar-daftar-page');
    }
}
