<?php

namespace App\Livewire\Bapendik\Kp;

use App\Models\KpSeminar;
use App\Models\Signatory;
use App\Services\Notifier;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class SeminarJadwalPage extends Component
{
    use WithPagination;

    public string $q = '';
    public string $statusFilter = 'disetujui_pembimbing'; // all | disetujui_pembimbing | dijadwalkan | ba_terbit | ditolak | diajukan
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 10;

    // schedule/BA modal state
    public ?int $editId = null;
    public ?string $tanggal_seminar = null; // HTML datetime-local format: Y-m-d\TH:i
    public ?int $ruangan_id = null;        // opsional, kalau nanti mau binding ke master room
    public ?string $ruangan_nama = null;

    public ?string $nomor_ba = null;
    public ?string $tanggal_ba = null;     // Y-m-d
    public ?int $signatory_id = null;      // opsional: snapshot penandatangan

    public function updatingQ()
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

    #[Computed]
    public function items()
    {
        return KpSeminar::query()
            ->with(['kp.mahasiswa.user', 'kp.dosenPembimbing'])
            ->when($this->q !== '', function ($q) {
                $term = '%' . $this->q . '%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('judul_laporan', 'like', $term)
                        ->orWhere('nomor_ba', 'like', $term)
                        ->orWhereHas('kp.mahasiswa.user', fn($u) => $u->where('name', 'like', $term))
                        ->orWhereHas('kp.mahasiswa', function ($m) use ($term) {
                            $m->where('nim', 'like', $term)
                                ->orWhere('mahasiswa_nim', 'like', $term);
                        });
                });
            })
            ->when($this->statusFilter !== 'all', fn($q) => $q->where('status', $this->statusFilter))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage)
            ->withQueryString();
    }

    public function openEdit(int $id): void
    {
        $row = KpSeminar::with(['kp.mahasiswa.user'])->findOrFail($id);
        $this->editId          = $row->id;
        $this->tanggal_seminar = optional($row->tanggal_seminar)->format('Y-m-d\TH:i');
        $this->ruangan_id      = $row->ruangan_id;
        $this->ruangan_nama    = $row->ruangan_nama;
        $this->nomor_ba        = $row->nomor_ba;
        $this->tanggal_ba      = optional($row->tanggal_ba)->format('Y-m-d');
        $this->signatory_id    = $row->signatory_id;
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

        // === NOTIFIKASI → Mahasiswa & Dosen Pembimbing
        $mhsUser = $row->kp?->mahasiswa?->user;
        $dspUser = $row->kp?->dosenPembimbing?->user;

        if ($mhsUser) {
            Notifier::toUser(
                $mhsUser,
                'Seminar KP dijadwalkan',
                sprintf(
                    'Seminar kamu dijadwalkan pada %s%s.',
                    optional($row->tanggal_seminar)?->format('d M Y H:i') ?: '-',
                    $row->ruangan_nama ? " • {$row->ruangan_nama}" : ''
                ),
                route('mhs.kp.seminar', ['kp' => $row->kerja_praktik_id]),
                [
                    'type'        => 'kp_seminar_scheduled',
                    'kp_id'       => $row->kerja_praktik_id,
                    'seminar_id'  => $row->id,
                ]
            );
        }

        if ($dspUser) {
            Notifier::toUser(
                $dspUser,
                'Seminar KP bimbingan Anda dijadwalkan',
                sprintf(
                    'Seminar %s dijadwalkan pada %s%s.',
                    $row->kp?->mahasiswa?->user?->name ?? 'Mahasiswa',
                    optional($row->tanggal_seminar)?->format('d M Y H:i') ?: '-',
                    $row->ruangan_nama ? " • {$row->ruangan_nama}" : ''
                ),
                route('dsp.kp.seminar.approval'),
                [
                    'type'        => 'kp_seminar_scheduled_info',
                    'kp_id'       => $row->kerja_praktik_id,
                    'seminar_id'  => $row->id,
                ]
            );
        }

        session()->flash('ok', 'Jadwal seminar disimpan.');
        $this->editId = null;
        $this->resetPage();
    }

    public function publishBA(): void
    {
        $this->validate([
            'editId'     => ['required', 'exists:kp_seminars,id'],
            'nomor_ba'   => ['required', 'string', 'max:100'],
            'tanggal_ba' => ['required', 'date'],
        ]);

        $row = KpSeminar::with([
            'kp.mahasiswa.user',
            'kp.mahasiswa.jurusan',
            'kp.dosenPembimbing.user'
        ])->findOrFail($this->editId);

        // snapshot signer bila diisi
        if ($this->signatory_id) {
            $sign = Signatory::find($this->signatory_id);
            $row->signatory_id           = $sign?->id;
            $row->ttd_signed_by_name     = $sign?->name;
            $row->ttd_signed_by_position = $sign?->position;
            $row->ttd_signed_by_nip      = $sign?->nip;
        }

        $row->nomor_ba   = $this->nomor_ba;
        $row->tanggal_ba = $this->tanggal_ba;
        $row->status     = KpSeminar::ST_BA_TERBIT;
        $row->save();

        // === NOTIFIKASI → Mahasiswa & Dosen Pembimbing (+ tautan unduh)
        $mhsUser = $row->kp?->mahasiswa?->user;
        $dspUser = $row->kp?->dosenPembimbing?->user;

        if ($mhsUser) {
            Notifier::toUser(
                $mhsUser,
                'Berita Acara Seminar terbit',
                'Berita Acara Seminar KP kamu telah terbit. Silakan unduh dokumennya.',
                route('mhs.kp.seminar', ['kp' => $row->kerja_praktik_id]),
                [
                    'type'           => 'kp_seminar_ba_published',
                    'kp_id'          => $row->kerja_praktik_id,
                    'seminar_id'     => $row->id,
                    'download_route' => route('mhs.kp.seminar.download.ba', [
                        'kp'      => $row->kerja_praktik_id,
                        'seminar' => $row->id
                    ]),
                ]
            );
        }

        if ($dspUser) {
            Notifier::toUser(
                $dspUser,
                'Berita Acara Seminar bimbingan terbit',
                sprintf('BA untuk %s telah terbit.', $row->kp?->mahasiswa?->user?->name ?? 'mahasiswa'),
                route('dsp.kp.seminar.approval'),
                [
                    'type'           => 'kp_seminar_ba_published_info',
                    'kp_id'          => $row->kerja_praktik_id,
                    'seminar_id'     => $row->id,
                    'download_route' => route('dsp.kp.seminar.download.ba', $row->id),
                ]
            );
        }

        session()->flash('ok', 'Berita Acara diterbitkan.');
        $this->editId = null;
        $this->resetPage();
    }

    public function badgeColor(string $st): string
    {
        return KpSeminar::badgeColor($st);
    }

    public function statusLabel(string $st): string
    {
        return KpSeminar::statusLabel($st);
    }

    public function render()
    {
        return view('livewire.bapendik.kp.seminar-jadwal-page');
    }
}
