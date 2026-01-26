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
    public string $tab = 'mahasiswa'; // 'mahasiswa' | 'log_konsultasi'

    // Verify Modal State
    public ?int $verifyId = null;
    public string $verifier_note = '';

    // Log Detail State (View Logs per Student)
    public ?int $viewLogKpId = null;

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
        $this->q = '';
    }

    protected function meAsDosen(): Dosen
    {
        return Dosen::where('user_id', Auth::id())->firstOrFail();
    }


    //FILTER STATUS:
    protected function applyStatusFilter($query)
    {
        $filter = $this->statusFilter;

        if ($filter === 'all') {
            return $query;
        }

        return match ($filter) {
            // UI: "KP Berjalan"
            'kp_sedang_berjalan' => $query->where('status', KerjaPraktik::ST_KP_BERJALAN),

            // UI: "SPK Terbit"
            'spk_terbit' => $query->where('status', KerjaPraktik::ST_SPK_TERBIT),

            // UI: "Selesai" (yang maksudnya: Nilai Terbit)
            'selesai' => $query->whereIn('status', ['nilai_terbit', 'lulus', 'selesai']),

            // Kalau suatu saat UI langsung pakai nilai_terbit
            'nilai_terbit' => $query->whereIn('status', ['nilai_terbit', 'lulus']),

            default => $query->where('status', $filter),
        };
    }

    #[Computed]
    public function mahasiswaItems()
    {
        $dosen = $this->meAsDosen();
        $kw    = trim($this->q);

        $q = KerjaPraktik::query()
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
            });

        // apply mapping filter dari dropdown
        $q = $this->applyStatusFilter($q);

        return $q->orderByDesc('updated_at')
            ->paginate($this->perPage, ['*'], 'mhsPage')
            ->withQueryString();
    }

    #[Computed]
    public function activeKpItems()
    {
        // Untuk Tab Log Konsultasi: Hanya tampilkan mahasiswa yg KP-nya aktif
        $dosen = $this->meAsDosen();
        $kw    = trim($this->q);

        return KerjaPraktik::query()
            ->with(['mahasiswa.user'])
            ->where('dosen_pembimbing_id', $dosen->getKey())
            ->whereIn('status', [KerjaPraktik::ST_SPK_TERBIT, KerjaPraktik::ST_KP_BERJALAN]) // Hanya yg aktif
            ->withCount([
                'consultations as pending_count' => fn($q) => $q->whereNull('verified_at')
            ])
            ->when($kw !== '', function ($q) use ($kw) {
                $q->whereHas('mahasiswa.user', fn($u) => $u->where('name', 'like', "%{$kw}%"));
            })
            ->orderByDesc('updated_at')
            ->paginate($this->perPage, ['*'], 'activePage');
    }

    #[Computed]
    public function selectedKpLogs()
    {
        if (!$this->viewLogKpId) return collect();

        return KpConsultation::where('kerja_praktik_id', $this->viewLogKpId)
            ->orderByDesc('tanggal_konsultasi')
            ->get();
    }

    #[Computed]
    public function selectedKp()
    {
        if (!$this->viewLogKpId) return null;
        return KerjaPraktik::with('mahasiswa.user')->find($this->viewLogKpId);
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

    // Actions

    public function openLogModal(int $kpId): void
    {
        $this->viewLogKpId = $kpId;
        Flux::modal('student-logs')->show();
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

        $mhsUser = $row->kp?->mahasiswa?->user;
        if ($mhsUser) {
            Notifier::toUser(
                $mhsUser,
                'Konsultasi Diverifikasi',
                sprintf('Dosen Pembimbing memverifikasi konsultasi tgl %s.', optional($row->tanggal_konsultasi)->format('d/m')),
                route('mhs.kp.konsultasi', $row->kerja_praktik_id),
                ['type' => 'kp_consultation_verified', 'kp_id' => $row->kerja_praktik_id]
            );
        }

        Flux::modal('verify-consult')->close();
        Flux::toast(heading: 'Berhasil', text: 'Konsultasi diverifikasi.', variant: 'success');

        $this->verifyId = null;
        $this->verifier_note = '';
    }

    public function badgeColor(string $status): string
    {
        return KerjaPraktik::badgeColor($status);
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
