<?php

namespace App\Livewire\Bapendik\Kp;

use App\Models\KpSeminar;
use App\Models\Room;
use App\Models\Signatory;
use App\Services\Notifier;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Flux\Flux;

class SeminarJadwalPage extends Component
{
    use WithPagination;

    #[Url] public string $search = '';
    #[Url] public string $tab = 'pending'; // 'pending' | 'completed'
    #[Url] public string $sortBy = 'created_at';
    #[Url] public string $sortDirection = 'asc'; // Pending biasanya ASC (yang lama diproses dulu)
    #[Url] public int $perPage = 10;

    // Form State (Unified)
    public ?int $editId = null;
    public ?string $tanggal_seminar = null;
    public ?string $jam_mulai = null;
    public ?string $jam_selesai = null;
    public ?int $ruangan_id = null;

    public ?string $nomor_ba = null;
    public ?string $tanggal_ba = null;
    public ?int $signatory_id = null;

    // Reject State
    public ?int $rejectId = null;
    public string $rejectReason = '';

    // Detail State
    public ?int $detailId = null;

    public function mount(): void
    {
        $this->signatory_id = Signatory::query()->orderBy('position')->value('id');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatingTab()
    {
        $this->resetPage();
    }

    public function sort(string $field): void
    {
        $this->sortBy = $field;
        $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        $this->resetPage();
    }

    protected function baseQuery()
    {
        return KpSeminar::query()
            ->with(['kp.mahasiswa.user', 'kp.dosenPembimbing'])
            ->when($this->search !== '', function ($query) {
                $term = '%' . $this->search . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('judul_laporan', 'like', $term)
                        ->orWhere('nomor_ba', 'like', $term)
                        ->orWhereHas('kp.mahasiswa.user', fn($u) => $u->where('name', 'like', $term));
                });
            })
            ->orderBy($this->sortBy, $this->sortDirection);
    }

    #[Computed]
    public function itemsPending()
    {
        // Hanya yang statusnya disetujui pembimbing (Menunggu Jadwal Bapendik)
        return $this->baseQuery()
            ->where('status', KpSeminar::ST_DISETUJUI_PEMBIMBING)
            ->paginate($this->perPage, ['*'], 'pendingPage');
    }

    #[Computed]
    public function itemsCompleted()
    {
        return $this->baseQuery()
            ->whereIn('status', [KpSeminar::ST_BA_TERBIT, KpSeminar::ST_DINILAI, KpSeminar::ST_SELESAI])
            ->paginate($this->perPage, ['*'], 'completedPage');
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'pending'   => KpSeminar::where('status', KpSeminar::ST_DISETUJUI_PEMBIMBING)->count(),
            'completed' => KpSeminar::whereIn('status', [KpSeminar::ST_BA_TERBIT, KpSeminar::ST_DINILAI, KpSeminar::ST_SELESAI])->count(),
        ];
    }

    #[Computed]
    public function rooms()
    {
        return Room::orderBy('building')->orderBy('room_number')->get();
    }

    #[Computed]
    public function signatories()
    {
        return Signatory::orderBy('position')->get();
    }

    #[Computed]
    public function selectedItem(): ?KpSeminar
    {
        if (!$this->detailId) return null;
        // Pastikan load 'signatory' agar tidak error di view detail
        return KpSeminar::with(['kp.mahasiswa.user', 'kp.dosenPembimbing', 'signatory'])->find($this->detailId);
    }

    // --- Actions ---

    public function openDetail(int $id): void
    {
        $this->detailId = $id;
        Flux::modal('detail-seminar')->show();
    }

    public function openEdit(int $id): void
    {
        $row = KpSeminar::findOrFail($id);

        $this->editId = $row->id;

        $this->tanggal_seminar = $row->tanggal_seminar ? $row->tanggal_seminar->format('Y-m-d') : now()->format('Y-m-d');
        $this->jam_mulai       = $row->jam_mulai ?? '09:00';
        $this->jam_selesai     = $row->jam_selesai ?? '10:00';
        $this->ruangan_id      = $row->ruangan_id;

        $this->nomor_ba        = $row->nomor_ba;
        $this->tanggal_ba      = $row->tanggal_ba ? $row->tanggal_ba->format('Y-m-d') : $this->tanggal_seminar;
        $this->signatory_id    = $row->signatory_id ?? Signatory::query()->orderBy('position')->value('id');

        Flux::modal('process-seminar')->show();
    }

    public function saveProcess(): void
    {
        $this->validate([
            'editId'          => ['required', 'exists:kp_seminars,id'],
            'tanggal_seminar' => ['required', 'date'],
            'jam_mulai'       => ['required'],
            'jam_selesai'     => ['required'],
            'ruangan_id'      => ['required', 'exists:rooms,id'],
            'nomor_ba'        => ['nullable', 'string', 'max:100'],
            'tanggal_ba'      => ['required', 'date'],
            'signatory_id'    => ['required', 'exists:signatories,id'],
        ]);

        $row = KpSeminar::with(['kp.mahasiswa.user'])->findOrFail($this->editId);
        $room = Room::find($this->ruangan_id);
        $sign = Signatory::find($this->signatory_id);

        // Update Jadwal
        $row->tanggal_seminar = $this->tanggal_seminar;
        $row->jam_mulai       = $this->jam_mulai;
        $row->jam_selesai     = $this->jam_selesai;
        $row->ruangan_id      = $room->id;
        $row->ruangan_nama    = $room->room_number . ' (' . $room->building . ')';

        // Update BA
        $row->nomor_ba   = $this->nomor_ba;
        $row->tanggal_ba = $this->tanggal_ba;

        if ($sign) {
            $row->signatory_id           = $sign->id;
            $row->ttd_signed_by_name     = $sign->name;
            $row->ttd_signed_by_position = $sign->position;
            $row->ttd_signed_by_nip      = $sign->nip;
        }

        // Langsung lompat ke BA Terbit
        $row->status = KpSeminar::ST_BA_TERBIT;
        $row->save();

        // Notifikasi ke Mahasiswa
        if ($row->kp?->mahasiswa?->user_id) {
            Notifier::toUser(
                $row->kp->mahasiswa->user_id,
                'Seminar Dijadwalkan & BA Terbit',
                "Seminar tgl {$this->tanggal_seminar} di {$row->ruangan_nama}. BA sudah terbit, silakan cetak.",
                route('mhs.kp.seminar', ['kp' => $row->kerja_praktik_id]),
                ['type' => 'kp_seminar_processed', 'kp_id' => $row->kerja_praktik_id]
            );
        }

        Flux::toast(heading: 'Berhasil', text: 'Jadwal disimpan & BA diterbitkan.', variant: 'success');
        Flux::modal('process-seminar')->close();
        $this->resetPage();
    }

    public function openReject(int $id): void
    {
        $this->rejectId = $id;
        $this->rejectReason = '';
        Flux::modal('reject-seminar')->show();
    }

    public function submitReject(): void
    {
        $this->validate([
            'rejectReason' => ['required', 'string', 'min:5']
        ], [
            'rejectReason.required' => 'Alasan pengembalian wajib diisi.',
            'rejectReason.min'      => 'Alasan pengembalian minimal :min karakter.',
            'rejectReason.string'   => 'Alasan pengembalian harus berupa teks.',
        ]);

        $row = KpSeminar::with(['kp.mahasiswa.user'])->findOrFail($this->rejectId);

        // Status Revisi = Dikembalikan ke Mahasiswa
        $row->update([
            'status' => KpSeminar::ST_REVISI,
            'rejected_reason' => $this->rejectReason
        ]);

        if ($row->kp?->mahasiswa?->user_id) {
            Notifier::toUser(
                $row->kp->mahasiswa->user_id,
                'Pengajuan Seminar Dikembalikan',
                "Alasan: {$this->rejectReason}. Silakan edit pengajuan Anda.",
                route('mhs.kp.seminar', ['kp' => $row->kerja_praktik_id]),
                ['type' => 'kp_seminar_rejected', 'kp_id' => $row->kerja_praktik_id]
            );
        }

        Flux::toast(heading: 'Dikembalikan', text: 'Pengajuan dikembalikan ke mahasiswa.', variant: 'warning');
        Flux::modal('reject-seminar')->close();
        $this->resetPage();
    }

    public function badgeColor(string $status): string
    {
        return KpSeminar::badgeColor($status);
    }

    public function statusLabel(string $status): string
    {
        return KpSeminar::statusLabel($status);
    }

    public function render()
    {
        return view('livewire.bapendik.kp.seminar-jadwal-page');
    }
}
