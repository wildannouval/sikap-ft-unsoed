<?php

namespace App\Livewire\Bapendik\Kp;

use App\Models\KerjaPraktik;
use App\Models\Signatory;
use App\Services\Notifier;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class SpkPage extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortBy = 'updated_at';
    public string $sortDirection = 'desc';
    public int $perPage = 10;
    public string $tab = 'pending'; // pending | published

    public ?int $select_id = null;
    public string $nomor_spk = '';
    public ?int $signatory_id = null;

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
    public function updatingSortBy()
    {
        $this->resetPage();
    }
    public function updatingSortDirection()
    {
        $this->resetPage();
    }

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    protected function baseQuery()
    {
        return KerjaPraktik::query()
            ->with(['mahasiswa.user', 'dosenPembimbing'])
            ->when($this->search !== '', function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('judul_kp', 'like', $term)
                        ->orWhere('lokasi_kp', 'like', $term)
                        ->orWhere('nomor_spk', 'like', $term)
                        ->orWhereHas('mahasiswa', function ($mq) use ($term) {
                            $mq->where('nim', 'like', $term)
                                ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', $term));
                        })
                        ->orWhereHas('dosenPembimbing', fn($dq) => $dq->where('nama', 'like', $term));
                });
            })
            ->orderBy($this->sortBy, $this->sortDirection);
    }

    #[Computed]
    public function itemsPending()
    {
        return $this->baseQuery()
            ->where('status', KerjaPraktik::ST_REVIEW_BAPENDIK)
            ->paginate($this->perPage);
    }

    #[Computed]
    public function itemsPublished()
    {
        return $this->baseQuery()
            ->where('status', KerjaPraktik::ST_SPK_TERBIT)
            ->paginate($this->perPage);
    }

    #[Computed]
    public function selectedItem(): ?KerjaPraktik
    {
        if (!$this->detailId) return null;
        return KerjaPraktik::with(['mahasiswa.user', 'dosenPembimbing'])->find($this->detailId);
    }

    public function openDetail(int $id): void
    {
        $this->detailId = $id;
        Flux::modal('detail-spk')->show();
    }

    public function closeDetail(): void
    {
        $this->detailId = null;
        Flux::modal('detail-spk')->close();
    }

    public function openPublish(int $id): void
    {
        $row = KerjaPraktik::findOrFail($id);

        if (!in_array($row->status, [KerjaPraktik::ST_REVIEW_BAPENDIK, KerjaPraktik::ST_SPK_TERBIT], true)) {
            Flux::toast('Tidak bisa diterbitkan pada status ini.', variant: 'danger');
            return;
        }

        $this->select_id   = $row->id;
        $this->nomor_spk   = (string) ($row->nomor_spk ?? '');
        $this->signatory_id ??= Signatory::query()->orderBy('position')->value('id');

        Flux::modal('spk-publish')->show();
    }

    public function publishSave(): void
    {
        $this->validate([
            'select_id'    => ['required', 'exists:kerja_praktiks,id'],
            'nomor_spk'    => ['required', 'string', 'max:255'],
            'signatory_id' => ['required', 'exists:signatories,id'],
        ]);

        $row = KerjaPraktik::with(['mahasiswa.user', 'dosenPembimbing'])->findOrFail($this->select_id);
        $sig = Signatory::findOrFail($this->signatory_id);

        if (!$row->spk_qr_token) {
            $row->spk_qr_token = bin2hex(random_bytes(16));
        }

        $row->nomor_spk = $this->nomor_spk;
        $row->status = KerjaPraktik::ST_SPK_TERBIT;
        $row->tanggal_terbit_spk = now()->toDateString();
        $row->signatory_id = $sig->id;

        $row->ttd_signed_at = now();
        $row->ttd_signed_by_name = $sig->name;
        $row->ttd_signed_by_position = $sig->position;
        $row->ttd_signed_by_nip = $sig->nip;
        $row->save();

        // === NOTIF ===
        // 1) Ke Mahasiswa
        Notifier::toUser(
            $row->mahasiswa?->user_id,
            'SPK KP Terbit',
            "SPK untuk pengajuan KP-mu sudah terbit. Nomor: {$row->nomor_spk}.",
            route('mhs.kp.index'),
            [
                'type'  => 'spk_published',
                'kp_id' => $row->id,
            ]
        );

        // 2) Ke Komisi
        Notifier::toRole(
            'Dosen Komisi',
            'SPK KP Terbit',
            "SPK untuk pengajuan KP {$row->mahasiswa?->user?->name} telah diterbitkan Bapendik.",
            route('komisi.kp.review'),
            [
                'type'  => 'spk_published_notify_komisi',
                'kp_id' => $row->id,
            ]
        );

        // 3) Opsional: Ke Dosen Pembimbing (kalau ada mapping user_id)
        $dospem = $row->dosenPembimbing;
        if ($dospem && !empty($dospem->user_id)) {
            Notifier::toUser(
                $dospem->user_id,
                'SPK Mahasiswa Bimbingan Terbit',
                "SPK mahasiswa bimbingan ({$row->mahasiswa?->user?->name}) telah diterbitkan.",
                route('dsp.kp.konsultasi'),
                [
                    'type'  => 'spk_published_notify_dospem',
                    'kp_id' => $row->id,
                ]
            );
        }

        Flux::modal('spk-publish')->close();
        Flux::toast(heading: 'SPK Terbit', text: 'Nomor SPK disimpan & SPK diterbitkan.', variant: 'success');

        $this->select_id = null;
        $this->nomor_spk = '';
        $this->signatory_id = Signatory::query()->orderBy('position')->value('id');
        $this->resetPage();
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
        return view('livewire.bapendik.kp.spk-page');
    }
}
