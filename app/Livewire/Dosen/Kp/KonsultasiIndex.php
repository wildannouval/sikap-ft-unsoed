<?php

namespace App\Livewire\Dosen\Kp;

use App\Models\Dosen;
use App\Models\KerjaPraktik;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class KonsultasiIndex extends Component
{
    use WithPagination;

    // Halaman ini sekarang adalah “Mahasiswa Bimbingan”
    public string $q = '';                       // cari nama / NIM / judul / instansi
    public string $status = 'all';               // all|review_komisi|review_bapendik|spk_terbit|kp_berjalan|ditolak|selesai (sesuaikan enum di modelmu)
    public string $sortBy = 'updated_at';
    public string $sortDirection = 'desc';
    public int $perPage = 10;

    public function updatingQ()
    {
        $this->resetPage();
    }
    public function updatingStatus()
    {
        $this->resetPage();
    }
    public function updatingSortBy()
    {
        $this->resetPage();
    }
    public function updatingPerPage()
    {
        $this->resetPage();
    }
    public function updatingSortDirection()
    {
        $this->resetPage();
    }

    protected function meAsDosen(): Dosen
    {
        return Dosen::where('user_id', Auth::id())->firstOrFail();
    }

    #[Computed]
    public function items()
    {
        $dosen = $this->meAsDosen();
        $kw    = trim($this->q);

        return KerjaPraktik::query()
            ->with([
                'mahasiswa.user:id,name',
            ])
            // ringkasan konsultasi
            ->withCount([
                'consultations',
                'consultations as verified_consultations_count' => fn($q) => $q->whereNotNull('verified_at'),
            ])
            // terakhir konsultasi (butuh MySQL >= 8; Laravel 12 support withMax)
            ->withMax('consultations as last_consultation_at', 'tanggal_konsultasi')
            // scope dosen pembimbing = saya
            ->where('dosen_pembimbing_id', $dosen->getKey())
            // pencarian
            ->when($kw !== '', function ($q) use ($kw) {
                $like = "%{$kw}%";
                $q->where(function ($qq) use ($like) {
                    $qq->where('judul_kp', 'like', $like)
                        ->orWhere('lokasi_kp', 'like', $like)
                        ->orWhereHas(
                            'mahasiswa',
                            fn($m) =>
                            $m->where('mahasiswa_nim', 'like', $like)
                                ->orWhereHas('user', fn($u) => $u->where('name', 'like', $like))
                        );
                });
            })
            // filter status KP (gunakan konstanta dari model jika ada)
            ->when($this->status !== 'all', fn($q) => $q->where('status', $this->status))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage)
            ->withQueryString();
    }

    public function sort(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    // helper badge/label tetap delegasi ke model
    public function badgeColor(string $status): string
    {
        return \App\Models\KerjaPraktik::badgeColor($status);
    }
    public function statusLabel(string $status): string
    {
        return \App\Models\KerjaPraktik::statusLabel($status);
    }

    public function render()
    {
        return view('livewire.dosen.kp.konsultasi-index');
    }
}
