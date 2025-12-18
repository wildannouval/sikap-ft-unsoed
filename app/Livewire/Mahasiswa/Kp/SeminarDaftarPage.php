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
use Flux\Flux;

class SeminarDaftarPage extends Component
{
    use WithFileUploads;

    public KerjaPraktik $kp;
    public ?KpSeminar $seminar = null;

    public string $judul_kp_final = '';
    public ?string $tanggal_seminar = null;
    public ?int $ruangan_id = null;
    public ?string $jam_mulai = null;
    public ?string $jam_selesai = null;
    public ?string $abstrak = null;

    public $berkas_laporan;
    public ?string $berkas_laporan_path = null;

    /**
     * NOTE:
     * - Default jangan "diajukan" supaya ketika belum ada pengajuan (seminar null),
     *   status tidak misleading jadi "Menunggu ACC".
     */
    public string $status = 'draft';

    public ?string $rejected_reason = null; // Tambahan untuk menyimpan alasan penolakan

    public function mount(KerjaPraktik $kp): void
    {
        $mhs = Mahasiswa::where('user_id', Auth::id())->firstOrFail();

        abort_unless((int) $kp->mahasiswa_id === (int) $mhs->getKey(), 403, 'KP bukan milik Anda.');

        abort_unless(
            in_array($kp->status, [KerjaPraktik::ST_SPK_TERBIT, KerjaPraktik::ST_KP_BERJALAN], true),
            403,
            'Daftar seminar hanya untuk KP aktif.'
        );

        abort_unless($kp->verifiedConsultationsCount() >= 6, 403, 'Minimal 6 konsultasi terverifikasi.');

        $this->kp = $kp;

        $this->seminar = KpSeminar::where('kerja_praktik_id', $kp->id)
            ->where('mahasiswa_id', $mhs->getKey())
            ->latest('id')
            ->first();

        if ($this->seminar) {
            $this->judul_kp_final      = (string) ($this->seminar->judul_laporan ?? '');
            $this->abstrak             = $this->seminar->abstrak;
            $this->berkas_laporan_path = $this->seminar->berkas_laporan_path;
            $this->status              = $this->seminar->status;
            $this->rejected_reason     = $this->seminar->rejected_reason; // Load alasan

            $this->tanggal_seminar = optional($this->seminar->tanggal_seminar)->toDateString();
            $this->ruangan_id      = $this->seminar->ruangan_id;
            $this->jam_mulai       = $this->seminar->jam_mulai;
            $this->jam_selesai     = $this->seminar->jam_selesai;
        } else {
            // Belum pernah mengajukan -> status draft agar UI tidak menampilkan "Menunggu ACC"
            $this->status = 'draft';
            $this->rejected_reason = null;
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
            'berkas_laporan'  => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ];
    }

    #[Computed]
    public function statusLabel(): string
    {
        // FIX 1: jika belum ada pengajuan (seminar null) jangan tampil "Menunggu ACC"
        if (! $this->seminar) {
            return 'Belum Diajukan';
        }

        return KpSeminar::statusLabel($this->status);
    }

    #[Computed]
    public function statusBadge(): array
    {
        // FIX 1: jika belum ada pengajuan, beri badge draft yang benar
        if (! $this->seminar) {
            return [
                'color' => 'zinc',
                'icon'  => 'document-text',
                'desc'  => 'Anda belum mengajukan seminar. Lengkapi formulir lalu klik Ajukan Seminar.',
            ];
        }

        // FIX 2: tambahkan mapping untuk DINILAI agar tidak jatuh ke "Status belum tersedia"
        return match ($this->status) {
            KpSeminar::ST_DIAJUKAN             => ['color' => 'indigo', 'icon' => 'clock',         'desc' => 'Menunggu persetujuan Dosen'],
            KpSeminar::ST_DISETUJUI_PEMBIMBING => ['color' => 'sky',    'icon' => 'check-circle',  'desc' => 'Menunggu penjadwalan Koordinator'],
            KpSeminar::ST_SELESAI              => ['color' => 'teal',   'icon' => 'check-badge',   'desc' => 'Seminar telah dilaksanakan'],
            KpSeminar::ST_REVISI               => ['color' => 'amber',  'icon' => 'pencil',        'desc' => 'Perlu revisi jadwal/laporan'],
            KpSeminar::ST_BA_TERBIT            => ['color' => 'violet', 'icon' => 'document-text', 'desc' => 'Berita Acara diterbitkan'],
            KpSeminar::ST_DINILAI              => ['color' => 'purple', 'icon' => 'star',          'desc' => 'Seminar telah dinilai oleh Dosen.'],
            KpSeminar::ST_DITOLAK              => ['color' => 'rose',   'icon' => 'x-circle',      'desc' => 'Pengajuan ditolak, silakan cek.'],
            KpSeminar::ST_GAGAL                => ['color' => 'rose',   'icon' => 'x-circle',      'desc' => 'Seminar dibatalkan/gagal'],
            default                            => ['color' => 'zinc',   'icon' => 'minus',         'desc' => 'Status belum tersedia'],
        };
    }

    #[Computed]
    public function isLocked(): bool
    {
        // ST_REVISI dan ST_DITOLAK TIDAK boleh dikunci agar bisa resubmit
        return in_array($this->status, [
            KpSeminar::ST_DISETUJUI_PEMBIMBING,
            KpSeminar::ST_SELESAI,
            KpSeminar::ST_BA_TERBIT,
            KpSeminar::ST_DINILAI,
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

    private function buildSeminarDateTime(): ?string
    {
        if (! $this->tanggal_seminar || ! $this->jam_mulai) return null;
        return "{$this->tanggal_seminar} {$this->jam_mulai}:00";
    }

    public function submitToAdvisor(): void
    {
        $this->validate();
        $mhs = Mahasiswa::where('user_id', Auth::id())->firstOrFail();

        $ruanganNama = null;
        if ($this->ruangan_id) {
            $room = Room::find($this->ruangan_id);
            if ($room) $ruanganNama = "{$room->room_number} — {$room->building}";
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
                Flux::toast(heading: 'Gagal', text: 'Data terkunci, tidak bisa diubah.', variant: 'danger');
                return;
            }

            // Reset status ke diajukan dan hapus catatan reject lama
            $payload['status'] = KpSeminar::ST_DIAJUKAN;
            $payload['rejected_reason'] = null;

            $this->seminar->update($payload);
        } else {
            $payload['status'] = KpSeminar::ST_DIAJUKAN;
            $this->seminar = KpSeminar::create($payload);
        }

        $this->status = KpSeminar::ST_DIAJUKAN;
        $this->rejected_reason = null;

        // Notifikasi ke Dosen
        $dosenUser = $this->kp->dosenPembimbing?->user;
        if ($dosenUser) {
            Notifier::toUser(
                $dosenUser,
                'Pengajuan Seminar KP',
                "Mahasiswa {$this->kp->mahasiswa->user->name} mengajukan seminar KP.",
                route('dsp.kp.seminar.approval'),
                ['type' => 'kp_seminar_submitted', 'kp_id' => $this->kp->id, 'seminar_id' => $this->seminar->id]
            );
        }

        Flux::toast(heading: 'Terkirim', text: 'Pengajuan seminar berhasil dikirim.', variant: 'success');
    }

    public function removeFile(): void
    {
        if (! $this->seminar || $this->isLocked()) return;

        if ($this->berkas_laporan_path && Storage::disk('public')->exists($this->berkas_laporan_path)) {
            Storage::disk('public')->delete($this->berkas_laporan_path);
        }

        $this->berkas_laporan_path = null;
        $this->seminar->update(['berkas_laporan_path' => null]);

        Flux::toast(heading: 'Terhapus', text: 'Berkas dihapus.', variant: 'success');
    }

    public function render()
    {
        return view('livewire.mahasiswa.kp.seminar-daftar-page');
    }
}
