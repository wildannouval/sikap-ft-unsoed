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

    /** KP yang sedang dikonsultasikan (harus milik mahasiswa login) */
    public KerjaPraktik $kp;

    /** State form & editing */
    public ?int $editingId = null;
    public string $konsultasi_dengan = '';
    public ?string $tanggal_konsultasi = null;
    public string $topik_konsultasi = '';
    public string $hasil_konsultasi = '';

    /** Table state */
    public string $q = '';
    public string $sortBy = 'tanggal_konsultasi';
    public string $sortDirection = 'desc';
    public int $perPage = 10;

    /**
     * Pastikan:
     * - KP milik mahasiswa login
     * - Status KP sudah SPK terbit atau sedang berjalan
     */
    public function mount(KerjaPraktik $kp): void
    {
        $mhs = Mahasiswa::where('user_id', Auth::id())->firstOrFail();

        abort_unless((int) $kp->mahasiswa_id === (int) $mhs->getKey(), 403, 'Forbidden');

        abort_unless(
            in_array($kp->status, [
                KerjaPraktik::ST_SPK_TERBIT,
                KerjaPraktik::ST_KP_BERJALAN,
            ], true),
            403,
            'Konsultasi hanya untuk KP yang sudah terbit/berjalan.'
        );

        $this->kp = $kp;
    }

    protected function rules(): array
    {
        return [
            'konsultasi_dengan'  => ['nullable', 'string', 'max:255'],
            'tanggal_konsultasi' => ['required', 'date'],
            'topik_konsultasi'   => ['required', 'string', 'max:255'],
            'hasil_konsultasi'   => ['required', 'string', 'min:5'],
        ];
    }

    /** Reset pagination saat filter berubah */
    public function updatingQ()
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

    /** Toggle sort (biar bisa klik header tabel) */
    public function sort(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    /** Data konsultasi untuk tabel */
    #[Computed]
    public function items()
    {
        return KpConsultation::query()
            ->where('kerja_praktik_id', $this->kp->id)
            ->when($this->q !== '', function ($q) {
                $q->where(function ($qq) {
                    $kw = '%' . $this->q . '%';
                    $qq->where('topik_konsultasi', 'like', $kw)
                        ->orWhere('hasil_konsultasi', 'like', $kw)
                        ->orWhere('konsultasi_dengan', 'like', $kw);
                });
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage)
            ->withQueryString();
    }

    /** Statistik Ringkas untuk Sidebar */
    #[Computed]
    public function stats(): array
    {
        $base = KpConsultation::where('kerja_praktik_id', $this->kp->id);

        $verified = (clone $base)->whereNotNull('verified_at')->count();
        // Minimal 6 kali bimbingan (Aturan umum)
        $target = 6;

        return [
            'total_log'    => (clone $base)->count(),
            'verified'     => $verified,
            'pending'      => (clone $base)->whereNull('verified_at')->count(),
            'target'       => $target,
            'progress_pct' => min(100, round(($verified / $target) * 100)),
        ];
    }

    /** Submit konsultasi baru */
    public function submit(): void
    {
        $this->validate();

        $mhs = Mahasiswa::where('user_id', Auth::id())->firstOrFail();

        $row = KpConsultation::create([
            'kerja_praktik_id'    => $this->kp->id,
            'mahasiswa_id'        => $mhs->getKey(),
            'dosen_pembimbing_id' => $this->kp->dosen_pembimbing_id, // boleh null
            'konsultasi_dengan'   => $this->konsultasi_dengan !== '' ? $this->konsultasi_dengan : null,
            'tanggal_konsultasi'  => $this->tanggal_konsultasi,
            'topik_konsultasi'    => $this->topik_konsultasi,
            'hasil_konsultasi'    => $this->hasil_konsultasi,
        ]);

        if ($this->kp->status === KerjaPraktik::ST_SPK_TERBIT) {
            $this->kp->update(['status' => KerjaPraktik::ST_KP_BERJALAN]);
        }

        // Notif → Dosen Pembimbing
        $dosenUser = $this->kp->dosenPembimbing?->user;
        if ($dosenUser) {
            $title = 'Konsultasi baru diajukan';
            $body  = sprintf(
                '%s mengajukan konsultasi (%s): %s',
                $mhs->user?->name ?? 'Mahasiswa',
                optional($row->tanggal_konsultasi)?->format('d M Y') ?: '-',
                $row->topik_konsultasi
            );
            $link  = route('dsp.kp.konsultasi');
            Notifier::toUser(
                $dosenUser,
                $title,
                $body,
                $link,
                [
                    'type' => 'kp_consultation_submitted',
                    'kp_id' => $this->kp->id,
                    'consultation_id' => $row->id,
                    'mahasiswa_id' => $mhs->getKey(),
                ]
            );
        }

        $this->resetForm();
        Flux::toast(heading: 'Berhasil', text: 'Konsultasi ditambahkan.', variant: 'success');
        $this->resetPage();
    }

    /** Load data untuk edit (hanya jika belum diverifikasi) */
    public function edit(int $id): void
    {
        $row = KpConsultation::where('id', $id)
            ->where('kerja_praktik_id', $this->kp->id)
            ->firstOrFail();

        if ($row->verified_at) {
            Flux::toast(heading: 'Gagal', text: 'Data sudah diverifikasi dosen.', variant: 'danger');
            return;
        }

        $this->editingId          = $row->id;
        $this->konsultasi_dengan  = (string)($row->konsultasi_dengan ?? '');
        $this->tanggal_konsultasi = optional($row->tanggal_konsultasi)->toDateString();
        $this->topik_konsultasi   = $row->topik_konsultasi;
        $this->hasil_konsultasi   = $row->hasil_konsultasi;
    }

    /** Batalkan edit & reset form */
    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    /** Update baris (hanya jika belum diverifikasi) */
    public function updateItem(): void
    {
        $this->validate();

        $row = KpConsultation::where('id', $this->editingId)
            ->where('kerja_praktik_id', $this->kp->id)
            ->firstOrFail();

        if ($row->verified_at) {
            Flux::toast(heading: 'Gagal', text: 'Data sudah diverifikasi dosen.', variant: 'danger');
            return;
        }

        $row->update([
            'konsultasi_dengan'  => $this->konsultasi_dengan !== '' ? $this->konsultasi_dengan : null,
            'tanggal_konsultasi' => $this->tanggal_konsultasi,
            'topik_konsultasi'   => $this->topik_konsultasi,
            'hasil_konsultasi'   => $this->hasil_konsultasi,
        ]);

        $this->cancelEdit();
        Flux::toast(heading: 'Tersimpan', text: 'Perubahan disimpan.', variant: 'success');
        $this->resetPage();
    }

    /** Hapus baris (hanya jika belum diverifikasi) */
    public function deleteItem(int $id): void
    {
        $row = KpConsultation::where('id', $id)
            ->where('kerja_praktik_id', $this->kp->id)
            ->firstOrFail();

        if ($row->verified_at) {
            Flux::toast(heading: 'Gagal', text: 'Data sudah diverifikasi dosen.', variant: 'danger');
            return;
        }

        $row->delete();

        Flux::toast(heading: 'Terhapus', text: 'Konsultasi dihapus.', variant: 'success');
        $this->resetPage();
    }

    /** Helper reset form input */
    private function resetForm(): void
    {
        $this->reset([
            'editingId',
            'konsultasi_dengan',
            'tanggal_konsultasi',
            'topik_konsultasi',
            'hasil_konsultasi',
        ]);
    }

    public function render()
    {
        return view('livewire.mahasiswa.kp.konsultasi-page');
    }
}
