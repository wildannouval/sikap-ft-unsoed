<?php

namespace App\Livewire\Dosen\Kp;

use App\Models\Dosen;
use App\Models\KpConsultation;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use App\Services\Notifier; // <— NOTIFIER

class KonsultasiIndex extends Component
{
    use WithPagination;

    public string $q = '';
    public string $status = 'all';    // all|verified|unverified
    public string $sortBy = 'tanggal_konsultasi';
    public string $sortDirection = 'desc';
    public int $perPage = 10;

    public ?int $rowId = null;

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
    public function updatingSortDirection()
    {
        $this->resetPage();
    }
    public function updatingPerPage()
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
        $dosen = $this->meAsDosen(); // PK = dosen_id

        return KpConsultation::query()
            ->with([
                // relasi standar: mahasiswa -> user
                'mahasiswa.user:id,name',
                // relasi kerja praktik + minimal kolom yang perlu
                'kerjaPraktik:id,judul_kp,lokasi_kp,mahasiswa_id,dosen_pembimbing_id',
            ])
            // === FIX UTAMA: pakai getKey() (dosen_id), bukan "id" ===
            ->where('dosen_pembimbing_id', $dosen->getKey())
            ->when($this->q !== '', function ($q) {
                $q->where(function ($qq) {
                    $qq->where('topik_konsultasi', 'like', '%' . $this->q . '%')
                        ->orWhere('hasil_konsultasi', 'like', '%' . $this->q . '%')
                        ->orWhere('konsultasi_dengan', 'like', '%' . $this->q . '%');
                });
            })
            ->when($this->status === 'verified', fn($q) => $q->whereNotNull('verified_at'))
            ->when($this->status === 'unverified', fn($q) => $q->whereNull('verified_at'))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage)
            ->withQueryString();
    }

    public function verify(int $id): void
    {
        $dosen = $this->meAsDosen();

        $row = KpConsultation::where('id', $id)
            ->where('dosen_pembimbing_id', $dosen->getKey())
            ->firstOrFail();

        if (is_null($row->verified_at)) {
            $row->forceFill([
                'verified_at'          => now(),
                'verified_by_dosen_id' => $dosen->getKey(),
            ])->save();

            // === NOTIFIKASI → Mahasiswa
            $mhsUser = $row->mahasiswa?->user;
            if ($mhsUser) {
                $title = 'Konsultasi diverifikasi';
                $body  = sprintf(
                    'Dosen Pembimbing %s menyetujui konsultasi tanggal %s.',
                    $dosen->user?->name ?? '',
                    optional($row->tanggal_konsultasi)?->format('d M Y') ?: '-'
                );
                $link  = route('mhs.kp.konsultasi', $row->kerja_praktik_id);
                Notifier::toUser(
                    $mhsUser,
                    $title,
                    $body,
                    $link,
                    [
                        'type' => 'kp_consultation_verified',
                        'kp_id' => $row->kerja_praktik_id,
                        'consultation_id' => $row->id,
                        'verified' => true,
                    ]
                );
            }
        }

        session()->flash('ok', 'Konsultasi diverifikasi.');
        $this->resetPage();
    }

    public function unverify(int $id): void
    {
        $dosen = $this->meAsDosen();

        $row = KpConsultation::where('id', $id)
            ->where('dosen_pembimbing_id', $dosen->getKey())
            ->firstOrFail();

        if (! is_null($row->verified_at)) {
            $row->forceFill([
                'verified_at'          => null,
                'verified_by_dosen_id' => null,
            ])->save();

            // === NOTIFIKASI → Mahasiswa
            $mhsUser = $row->mahasiswa?->user;
            if ($mhsUser) {
                $title = 'Verifikasi konsultasi dibatalkan';
                $body  = sprintf(
                    'Dosen Pembimbing %s membatalkan verifikasi konsultasi tanggal %s.',
                    $dosen->user?->name ?? '',
                    optional($row->tanggal_konsultasi)?->format('d M Y') ?: '-'
                );
                $link  = route('mhs.kp.konsultasi', $row->kerja_praktik_id);
                Notifier::toUser(
                    $mhsUser,
                    $title,
                    $body,
                    $link,
                    [
                        'type' => 'kp_consultation_unverified',
                        'kp_id' => $row->kerja_praktik_id,
                        'consultation_id' => $row->id,
                        'verified' => false,
                    ]
                );
            }
        }

        session()->flash('ok', 'Verifikasi dibatalkan.');
        $this->resetPage();
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

    public function render()
    {
        return view('livewire.dosen.kp.konsultasi-index');
    }
}
