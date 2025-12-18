<?php

namespace App\Livewire\Mahasiswa\Kp;

use App\Models\KpConsultation;
use App\Models\KerjaPraktik;
use App\Models\Mahasiswa;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use App\Services\Notifier;
use Flux\Flux;

class KonsultasiPage extends Component
{
    use WithPagination;

    /** KP yang sedang dikonsultasikan */
    public KerjaPraktik $kp;

    /** State form & editing */
    public ?int $editingId = null;

    // Logic Dropdown Konsultasi
    public string $konsultasi_tipe = 'Dosen Pembimbing'; // Default
    public string $konsultasi_custom_name = '';

    public ?string $tanggal_konsultasi = null;
    public string $topik_konsultasi = '';
    public string $hasil_konsultasi = '';

    /** State Modal */
    public ?int $detailId = null;
    public ?int $deleteId = null;

    /** Table & Filter State */
    public string $search = '';
    public string $filterStatus = ''; // '' (All), 'verified', 'pending'
    public string $sortBy = 'tanggal_konsultasi';
    public string $sortDirection = 'desc';
    public int $perPage = 10;

    public function mount(KerjaPraktik $kp): void
    {
        $mhs = Mahasiswa::where('user_id', Auth::id())->firstOrFail();

        abort_unless((int) $kp->mahasiswa_id === (int) $mhs->getKey(), 403, 'Forbidden');

        // Izinkan jika status relevan
        abort_unless(
            in_array($kp->status, [
                KerjaPraktik::ST_SPK_TERBIT,
                KerjaPraktik::ST_KP_BERJALAN,
                KerjaPraktik::ST_SEMINAR_DIAJUKAN,
                KerjaPraktik::ST_SEMINAR_DIJADWALKAN,
                KerjaPraktik::ST_NILAI_TERBIT,
            ], true),
            403,
            'Konsultasi hanya untuk KP yang sudah terbit/berjalan.'
        );

        $this->kp = $kp;
    }

    protected function rules(): array
    {
        return [
            'konsultasi_tipe' => ['required', 'string'],
            'konsultasi_custom_name' => ['nullable', 'string', 'max:255'],
            'tanggal_konsultasi' => ['required', 'date'],
            'topik_konsultasi' => ['required', 'string', 'max:255'],
            'hasil_konsultasi' => ['required', 'string', 'min:5'],
        ];
    }

    // --- Hooks ---
    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatingFilterStatus()
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

    public function sort(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    // --- Computed Properties ---

    #[Computed]
    public function dosenName(): string
    {
        return $this->kp->dosenPembimbing->dosen_name ?? 'Dosen Pembimbing';
    }

    #[Computed]
    public function items()
    {
        return KpConsultation::query()
            ->where('kerja_praktik_id', $this->kp->id)
            ->when($this->search !== '', function ($q) {
                $kw = '%' . $this->search . '%';
                $q->where(function ($qq) use ($kw) {
                    $qq->where('topik_konsultasi', 'like', $kw)
                        ->orWhere('hasil_konsultasi', 'like', $kw)
                        ->orWhere('konsultasi_dengan', 'like', $kw);
                });
            })
            // Filter Status Logic
            ->when($this->filterStatus === 'verified', fn($q) => $q->whereNotNull('verified_at'))
            ->when($this->filterStatus === 'pending', fn($q) => $q->whereNull('verified_at'))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage)
            ->withQueryString();
    }

    #[Computed]
    public function stats(): array
    {
        $base = KpConsultation::where('kerja_praktik_id', $this->kp->id);
        $verified = (clone $base)->whereNotNull('verified_at')->count();
        $target = 6; // Target minimal bimbingan

        return [
            'total_log'    => (clone $base)->count(),
            'verified'     => $verified,
            'pending'      => (clone $base)->whereNull('verified_at')->count(),
            'target'       => $target,
            'progress_pct' => min(100, round(($verified / $target) * 100)),
        ];
    }

    #[Computed]
    public function selectedItem(): ?KpConsultation
    {
        if (!$this->detailId) return null;
        return KpConsultation::find($this->detailId);
    }

    // --- Actions ---

    public function submit(): void
    {
        $this->validate();

        // Tentukan nama pihak konsultasi berdasarkan dropdown
        $namaPihak = match ($this->konsultasi_tipe) {
            'Dosen Pembimbing' => $this->dosenName,
            'Pembimbing Lapangan' => 'Pembimbing Lapangan',
            'Lainnya' => $this->konsultasi_custom_name ?: 'Pihak Lain',
            default => $this->dosenName,
        };

        $mhs = Mahasiswa::where('user_id', Auth::id())->firstOrFail();

        $row = KpConsultation::create([
            'kerja_praktik_id'    => $this->kp->id,
            'mahasiswa_id'        => $mhs->getKey(),
            'dosen_pembimbing_id' => $this->kp->dosen_pembimbing_id,
            'konsultasi_dengan'   => $namaPihak,
            'tanggal_konsultasi'  => $this->tanggal_konsultasi,
            'topik_konsultasi'    => $this->topik_konsultasi,
            'hasil_konsultasi'    => $this->hasil_konsultasi,
        ]);

        // Jika status KP masih SPK Terbit, ubah jadi KP Berjalan
        if ($this->kp->status === KerjaPraktik::ST_SPK_TERBIT) {
            $this->kp->update(['status' => KerjaPraktik::ST_KP_BERJALAN]);
        }

        // Notif ke Dosen (hanya jika tipe Dosen Pembimbing)
        if ($this->konsultasi_tipe === 'Dosen Pembimbing' && $this->kp->dosenPembimbing?->user) {
            Notifier::toUser(
                $this->kp->dosenPembimbing->user,
                'Konsultasi Baru',
                "{$mhs->user?->name} menambahkan catatan bimbingan: {$row->topik_konsultasi}",
                route('dsp.kp.konsultasi'),
                ['type' => 'kp_consultation_submitted', 'kp_id' => $this->kp->id]
            );
        }

        $this->resetForm();
        Flux::toast(heading: 'Berhasil', text: 'Catatan konsultasi disimpan.', variant: 'success');
        $this->resetPage();
    }

    public function edit(int $id): void
    {
        $row = KpConsultation::where('id', $id)
            ->where('kerja_praktik_id', $this->kp->id)
            ->firstOrFail();

        if ($row->verified_at) {
            Flux::toast(heading: 'Gagal', text: 'Data sudah diverifikasi, tidak bisa diedit.', variant: 'danger');
            return;
        }

        $this->editingId = $row->id;

        // Reverse logic untuk dropdown
        if ($row->konsultasi_dengan === $this->dosenName) {
            $this->konsultasi_tipe = 'Dosen Pembimbing';
        } elseif ($row->konsultasi_dengan === 'Pembimbing Lapangan') {
            $this->konsultasi_tipe = 'Pembimbing Lapangan';
        } else {
            $this->konsultasi_tipe = 'Lainnya';
            $this->konsultasi_custom_name = $row->konsultasi_dengan ?? '';
        }

        $this->tanggal_konsultasi = optional($row->tanggal_konsultasi)->format('Y-m-d');
        $this->topik_konsultasi   = $row->topik_konsultasi;
        $this->hasil_konsultasi   = $row->hasil_konsultasi;
    }

    public function updateItem(): void
    {
        $this->validate();

        $row = KpConsultation::where('id', $this->editingId)->firstOrFail();

        if ($row->verified_at) {
            Flux::toast(heading: 'Gagal', text: 'Data sudah diverifikasi.', variant: 'danger');
            return;
        }

        $namaPihak = match ($this->konsultasi_tipe) {
            'Dosen Pembimbing' => $this->dosenName,
            'Pembimbing Lapangan' => 'Pembimbing Lapangan',
            'Lainnya' => $this->konsultasi_custom_name ?: 'Pihak Lain',
            default => $this->dosenName,
        };

        $row->update([
            'konsultasi_dengan'  => $namaPihak,
            'tanggal_konsultasi' => $this->tanggal_konsultasi,
            'topik_konsultasi'   => $this->topik_konsultasi,
            'hasil_konsultasi'   => $this->hasil_konsultasi,
        ]);

        $this->cancelEdit();
        Flux::toast(heading: 'Tersimpan', text: 'Perubahan disimpan.', variant: 'success');
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    // --- Modal Actions ---

    public function openDetail(int $id): void
    {
        $this->detailId = $id;
        Flux::modal('detail-konsultasi')->show();
    }

    public function closeDetail(): void
    {
        $this->detailId = null;
        Flux::modal('detail-konsultasi')->close();
    }

    public function openDelete(int $id): void
    {
        $row = KpConsultation::find($id);
        if ($row && !$row->verified_at) {
            $this->deleteId = $id;
            Flux::modal('delete-konsultasi')->show();
        } else {
            Flux::toast(heading: 'Gagal', text: 'Data terkunci / tidak ditemukan.', variant: 'danger');
        }
    }

    public function confirmDelete(): void
    {
        if (!$this->deleteId) return;

        $row = KpConsultation::find($this->deleteId);
        if ($row) {
            $row->delete();
            Flux::toast(heading: 'Terhapus', text: 'Log konsultasi dihapus.', variant: 'success');
        }

        $this->deleteId = null;
        Flux::modal('delete-konsultasi')->close();
        $this->resetPage();
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId',
            'konsultasi_tipe',
            'konsultasi_custom_name',
            'tanggal_konsultasi',
            'topik_konsultasi',
            'hasil_konsultasi'
        ]);
        $this->konsultasi_tipe = 'Dosen Pembimbing';
    }

    public function render()
    {
        return view('livewire.mahasiswa.kp.konsultasi-page');
    }
}
