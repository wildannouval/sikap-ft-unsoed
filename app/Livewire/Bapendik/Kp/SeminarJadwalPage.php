<?php

namespace App\Livewire\Bapendik\Kp;

use App\Models\KpSeminar;
use App\Models\Signatory;
use App\Services\Notifier;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Flux\Flux;

class SeminarJadwalPage extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'disetujui_pembimbing';
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 10;

    // schedule/BA modal state
    public ?int $editId = null;
    public ?string $tanggal_seminar = null;
    public ?int $ruangan_id = null;
    public ?string $ruangan_nama = null;

    public ?string $nomor_ba = null;
    public ?string $tanggal_ba = null;
    public ?int $signatory_id = null;

    // --- Hooks ---
    public function mount(): void
    {
        // Default signer jika belum ada
        $this->signatory_id = Signatory::query()->orderBy('position')->value('id');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatingStatusFilter()
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

    // Helper Badge
    public function badgeColor(string $status): string
    {
        return KpSeminar::badgeColor($status);
    }

    public function statusLabel(string $status): string
    {
        return KpSeminar::statusLabel($status);
    }

    // --- Query ---
    protected function baseQuery()
    {
        return KpSeminar::query()
            ->with(['kp.mahasiswa.user', 'kp.dosenPembimbing'])
            ->when($this->search !== '', function ($query) {
                $term = '%' . $this->search . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('judul_laporan', 'like', $term)
                        ->orWhere('nomor_ba', 'like', $term)
                        ->orWhereHas('kp', function ($kpq) use ($term) {
                            $kpq->where('judul_kp', 'like', $term)
                                ->orWhereHas('mahasiswa', function ($mq) use ($term) {
                                    $mq->where('mahasiswa_nim', 'like', $term)
                                        ->orWhere('mahasiswa_name', 'like', $term)
                                        ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', $term));
                                })
                                ->orWhereHas('dosenPembimbing', function ($dq) use ($term) {
                                    $dq->where('dosen_name', 'like', $term);
                                });
                        });
                });
            })
            ->when($this->statusFilter !== 'all', fn($q) => $q->where('status', $this->statusFilter))
            ->orderBy($this->sortBy, $this->sortDirection);
    }

    #[Computed]
    public function items()
    {
        return $this->baseQuery()
            ->paginate($this->perPage)
            ->withQueryString();
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'disetujui'   => KpSeminar::where('status', KpSeminar::ST_DISETUJUI_PEMBIMBING)->count(),
            'dijadwalkan' => KpSeminar::where('status', KpSeminar::ST_DIJADWALKAN)->count(),
            'ba_terbit'   => KpSeminar::where('status', KpSeminar::ST_BA_TERBIT)->count(),
        ];
    }

    #[Computed]
    public function statusOptions(): array
    {
        return [
            ['value' => 'all', 'label' => 'Semua Status'],
            ['value' => KpSeminar::ST_DISETUJUI_PEMBIMBING, 'label' => 'Disetujui Pembimbing'],
            ['value' => KpSeminar::ST_DIJADWALKAN, 'label' => 'Dijadwalkan'],
            ['value' => KpSeminar::ST_BA_TERBIT, 'label' => 'BA Terbit'],
            ['value' => KpSeminar::ST_DITOLAK, 'label' => 'Ditolak'],
        ];
    }

    #[Computed]
    public function signatories()
    {
        return Signatory::orderBy('position')->get();
    }

    // --- Actions ---

    public function openEdit(int $id): void
    {
        $row = KpSeminar::with(['kp.mahasiswa.user'])->findOrFail($id);

        $this->editId          = $row->id;
        $this->tanggal_seminar = optional($row->tanggal_seminar)->format('Y-m-d\TH:i');
        $this->ruangan_id      = $row->ruangan_id;
        $this->ruangan_nama    = $row->ruangan_nama;
        $this->nomor_ba        = $row->nomor_ba;
        $this->tanggal_ba      = optional($row->tanggal_ba)->format('Y-m-d');
        // Gunakan signatory yg tersimpan atau default
        $this->signatory_id    = $row->signatory_id ?? Signatory::query()->orderBy('position')->value('id');

        Flux::modal('edit-seminar')->show();
    }

    public function saveSchedule(): void
    {
        $this->validate([
            'editId'          => ['required', 'exists:kp_seminars,id'],
            'tanggal_seminar' => ['required', 'date'],
            'ruangan_nama'    => ['nullable', 'string', 'max:255'],
        ]);

        $row = KpSeminar::with(['kp.mahasiswa.user', 'kp.dosenPembimbing.user'])->findOrFail($this->editId);

        $row->update([
            'tanggal_seminar' => $this->tanggal_seminar,
            'ruangan_id'      => $this->ruangan_id,
            'ruangan_nama'    => $this->ruangan_nama,
            'status'          => KpSeminar::ST_DIJADWALKAN,
        ]);

        // NOTIFIKASI
        $this->notifySchedule($row);

        Flux::toast(heading: 'Tersimpan', text: 'Jadwal seminar disimpan. Silakan lanjutkan isi BA jika perlu.', variant: 'success');

        // JANGAN TUTUP MODAL AGAR BISA LANJUT ISI BA
        $this->resetPage();
    }

    protected function notifySchedule($row)
    {
        $mhsUser = $row->kp?->mahasiswa?->user;
        $dspUser = $row->kp?->dosenPembimbing?->user;

        if ($mhsUser) {
            Notifier::toUser($mhsUser, 'Seminar KP dijadwalkan', "Seminar kamu dijadwalkan pada " . optional($row->tanggal_seminar)->format('d M Y H:i') . ".", route('mhs.kp.seminar', ['kp' => $row->kerja_praktik_id]), ['type' => 'kp_seminar_scheduled', 'kp_id' => $row->kerja_praktik_id, 'seminar_id' => $row->id]);
        }

        if ($dspUser) {
            Notifier::toUser($dspUser, 'Seminar KP bimbingan dijadwalkan', "Seminar mahasiswa bimbingan dijadwalkan.", route('dsp.kp.seminar.approval'), ['type' => 'kp_seminar_scheduled_info', 'kp_id' => $row->kerja_praktik_id, 'seminar_id' => $row->id]);
        }
    }

    public function publishBA(): void
    {
        $this->validate([
            'editId'       => ['required', 'exists:kp_seminars,id'],
            'nomor_ba'     => ['required', 'string', 'max:100'],
            'tanggal_ba'   => ['required', 'date'],
            'signatory_id' => ['required', 'exists:signatories,id'],
        ]);

        $row = KpSeminar::with(['kp.mahasiswa.user'])->findOrFail($this->editId);

        // Snapshot Signer
        $sign = Signatory::find($this->signatory_id);
        if ($sign) {
            $row->signatory_id           = $sign->id;
            $row->ttd_signed_by_name     = $sign->name;
            $row->ttd_signed_by_position = $sign->position;
            $row->ttd_signed_by_nip      = $sign->nip;
        }

        $row->nomor_ba   = $this->nomor_ba;
        $row->tanggal_ba = $this->tanggal_ba;
        $row->status     = KpSeminar::ST_BA_TERBIT;
        $row->save();

        // NOTIFIKASI BA
        $this->notifyBA($row);

        Flux::toast(heading: 'Berhasil', text: 'Berita Acara diterbitkan.', variant: 'success');
        Flux::modal('edit-seminar')->close();
        $this->resetPage();
    }

    protected function notifyBA($row)
    {
        $mhsUser = $row->kp?->mahasiswa?->user;
        if ($mhsUser) {
            Notifier::toUser($mhsUser, 'Berita Acara Seminar Terbit', 'BA Seminar KP telah terbit. Silakan unduh.', route('mhs.kp.seminar', ['kp' => $row->kerja_praktik_id]), ['type' => 'kp_seminar_ba_published', 'kp_id' => $row->kerja_praktik_id, 'seminar_id' => $row->id]);
        }
    }

    public function render()
    {
        return view('livewire.bapendik.kp.seminar-jadwal-page');
    }
}
