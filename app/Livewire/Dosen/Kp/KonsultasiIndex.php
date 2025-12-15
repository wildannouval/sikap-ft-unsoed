<?php

namespace App\Livewire\Dosen\Kp;

use App\Models\Dosen;
use App\Models\KerjaPraktik;
use App\Models\KpConsultation;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use App\Services\Notifier;
use Flux\Flux;

class KonsultasiIndex extends Component
{
    use WithPagination;

    // Filter & Paging
    public string $q = '';
    public string $statusFilter = 'all';
    public int $perPage = 10;
    public string $tab = 'mahasiswa'; // 'mahasiswa' | 'konsultasi'

    // Verify Modal State
    public ?int $verifyId = null;
    public string $verifier_note = '';

    public function updatingQ()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingTab()
    {
        $this->resetPage();
        $this->q = ''; // Reset search saat ganti tab agar tidak bingung
    }

    protected function meAsDosen(): Dosen
    {
        return Dosen::where('user_id', Auth::id())->firstOrFail();
    }

    #[Computed]
    public function mahasiswaItems()
    {
        $dosen = $this->meAsDosen();
        $kw    = trim($this->q);

        return KerjaPraktik::query()
            ->with(['mahasiswa.user'])
            ->withCount([
                'consultations',
                'consultations as verified_consultations_count' => fn($q) => $q->whereNotNull('verified_at'),
            ])
            ->withMax('consultations as last_consultation_at', 'tanggal_konsultasi')
            ->where('dosen_pembimbing_id', $dosen->getKey())
            ->when($kw !== '', function ($q) use ($kw) {
                $q->where(function ($qq) use ($kw) {
                    $qq->where('judul_kp', 'like', "%{$kw}%")
                        ->orWhere('lokasi_kp', 'like', "%{$kw}%")
                        ->orWhereHas(
                            'mahasiswa',
                            fn($m) =>
                            $m->where('mahasiswa_nim', 'like', "%{$kw}%")
                                ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$kw}%"))
                        );
                });
            })
            ->when($this->statusFilter !== 'all', fn($q) => $q->where('status', $this->statusFilter))
            ->orderByDesc('updated_at')
            ->paginate($this->perPage, ['*'], 'mhsPage')
            ->withQueryString();
    }

    #[Computed]
    public function konsultasiItems()
    {
        $dosen = $this->meAsDosen();
        $kw    = trim($this->q);

        // Menggunakan relasi 'kp' yang sudah diperbaiki di Model
        return KpConsultation::query()
            ->with(['kp.mahasiswa.user'])
            ->where('dosen_pembimbing_id', $dosen->getKey())
            ->when($kw !== '', function ($q) use ($kw) {
                $q->where(function ($qq) use ($kw) {
                    $qq->where('topik_konsultasi', 'like', "%{$kw}%")
                        ->orWhere('hasil_konsultasi', 'like', "%{$kw}%")
                        ->orWhereHas('kp.mahasiswa.user', fn($u) => $u->where('name', 'like', "%{$kw}%"));
                });
            })
            ->orderByDesc('tanggal_konsultasi')
            ->paginate($this->perPage, ['*'], 'konsultasiPage')
            ->withQueryString();
    }

    #[Computed]
    public function stats(): array
    {
        $dosenId = $this->meAsDosen()->getKey();

        return [
            'total_mhs' => KerjaPraktik::where('dosen_pembimbing_id', $dosenId)->count(),
            'pending_verifikasi' => KpConsultation::where('dosen_pembimbing_id', $dosenId)
                ->whereNull('verified_at')->count(),
        ];
    }

    public function openVerify(int $id): void
    {
        $row = KpConsultation::findOrFail($id);

        if ($row->dosen_pembimbing_id !== $this->meAsDosen()->getKey()) {
            Flux::toast(heading: 'Gagal', text: 'Anda tidak berhak memverifikasi ini.', variant: 'danger');
            return;
        }

        $this->verifyId = $row->id;
        $this->verifier_note = '';
        Flux::modal('verify-consult')->show();
    }

    public function confirmVerify(): void
    {
        $row = KpConsultation::findOrFail($this->verifyId);
        $dosen = $this->meAsDosen();

        if ($row->verified_at) {
            Flux::toast(heading: 'Info', text: 'Sudah diverifikasi sebelumnya.', variant: 'warning');
            Flux::modal('verify-consult')->close();
            return;
        }

        $row->update([
            'verified_at'          => now(),
            'verified_by_dosen_id' => $dosen->getKey(),
            'verifier_note'        => $this->verifier_note ?: null,
        ]);

        // Notifikasi ke Mahasiswa
        $mhsUser = $row->kp?->mahasiswa?->user;
        if ($mhsUser) {
            Notifier::toUser(
                $mhsUser,
                'Konsultasi Diverifikasi',
                sprintf('Dosen Pembimbing telah memverifikasi konsultasi tanggal %s.', optional($row->tanggal_konsultasi)->format('d M Y')),
                route('mhs.kp.konsultasi', $row->kerja_praktik_id),
                ['type' => 'kp_consultation_verified', 'kp_id' => $row->kerja_praktik_id]
            );
        }

        Flux::modal('verify-consult')->close();
        Flux::toast(heading: 'Berhasil', text: 'Konsultasi diverifikasi.', variant: 'success');

        $this->verifyId = null;
        $this->verifier_note = '';
        $this->resetPage();
    }

    public function badgeColor(string $status): string
    {
        return KerjaPraktik::badgeColor($status);
    }

    public function badgeIcon(string $status): string
    {
        // Peta icon untuk status KP
        return match ($status) {
            'kp_berjalan' => 'play-circle',
            'spk_terbit'  => 'document-check',
            'selesai'     => 'check-badge',
            'ditolak'     => 'x-circle',
            default       => 'clock',
        };
    }

    public function statusLabel(string $status): string
    {
        return KerjaPraktik::statusLabel($status);
    }

    public function render()
    {
        return view('livewire.dosen.kp.konsultasi-index');
    }
}
