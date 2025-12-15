<?php

namespace App\Livewire\Mahasiswa\Kp;

use App\Models\KerjaPraktik;
use App\Models\Mahasiswa;
use App\Models\SuratPengantar;
use App\Services\Notifier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Flux\Flux;

class Page extends Component
{
    use WithPagination, WithFileUploads;

    public ?int $editingId = null;
    public ?int $deleteId  = null;
    public ?int $detailId  = null;

    // Form Inputs
    public string $judul_kp = '';
    public string $lokasi_kp = '';

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $proposal_kp;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $surat_keterangan_kp;

    public ?int $selectedSpId = null;

    // Table & Filters
    public string $search = ''; // Diganti dari $q agar konsisten
    public string $filterStatus = '';
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 10;

    protected function rules(): array
    {
        return [
            'judul_kp' => ['required', 'string', 'min:5', 'max:255'],
            'lokasi_kp' => ['required', 'string', 'min:3', 'max:255'],

            // saat create: wajib; saat edit: opsional
            'proposal_kp' => [$this->editingId ? 'nullable' : 'required', 'file', 'mimes:pdf', 'max:2048'],
            'surat_keterangan_kp' => [$this->editingId ? 'nullable' : 'required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],

            'selectedSpId' => [
                'nullable',
                Rule::exists('surat_pengantars', 'id')->where(function ($q) {
                    $mhs = Mahasiswa::where('user_id', Auth::id())->first();
                    $q->where('mahasiswa_id', $mhs?->getKey())
                        ->where('status_surat_pengantar', 'Diterbitkan');
                }),
            ],
        ];
    }

    // Reset pagination saat filter/search berubah
    public function updating($name, $value): void
    {
        if (in_array($name, ['search', 'filterStatus', 'sortBy', 'sortDirection', 'perPage'])) {
            $this->resetPage();
        }
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

    #[Computed]
    public function spOptions(): array
    {
        $mhs = Mahasiswa::where('user_id', Auth::id())->first();
        if (!$mhs) return [];

        return SuratPengantar::query()
            ->select('id', 'nomor_surat', 'lokasi_surat_pengantar', 'created_at')
            ->where('mahasiswa_id', $mhs->getKey())
            ->where('status_surat_pengantar', 'Diterbitkan')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($sp) => [
                'id' => $sp->id,
                'nomor_surat' => $sp->nomor_surat,
                'lokasi_surat_pengantar' => $sp->lokasi_surat_pengantar,
                'tanggal' => optional($sp->created_at)->toDateString(),
            ])->all();
    }

    // Opsi untuk Dropdown Filter Status
    #[Computed]
    public function statusOptions(): array
    {
        return [
            ['value' => KerjaPraktik::ST_REVIEW_KOMISI, 'label' => 'Menunggu Review Komisi'],
            ['value' => KerjaPraktik::ST_REVIEW_BAPENDIK, 'label' => 'Menunggu Terbit SPK'],
            ['value' => KerjaPraktik::ST_SPK_TERBIT, 'label' => 'SPK Terbit'],
            ['value' => KerjaPraktik::ST_DITOLAK, 'label' => 'Ditolak'],
        ];
    }

    public function fillFromSP(): void
    {
        if (!$this->selectedSpId) return;

        $mhs = Mahasiswa::where('user_id', Auth::id())->first();
        if (!$mhs) return;

        $sp = SuratPengantar::where('id', $this->selectedSpId)
            ->where('mahasiswa_id', $mhs->getKey())
            ->where('status_surat_pengantar', 'Diterbitkan')
            ->first();

        if ($sp) {
            $this->lokasi_kp = (string) $sp->lokasi_surat_pengantar;
            if (trim($this->judul_kp) === '') {
                $this->judul_kp = $this->lokasi_kp;
            }
        }
    }

    public function submit(): void
    {
        $this->validate();

        $mhs = Mahasiswa::where('user_id', Auth::id())->firstOrFail();

        $proposalPath = $this->proposal_kp
            ? $this->proposal_kp->store('kp/proposal', 'public')
            : null;

        $suratPath = $this->surat_keterangan_kp
            ? $this->surat_keterangan_kp->store('kp/surat-keterangan', 'public')
            : null;

        $kp = KerjaPraktik::create([
            'mahasiswa_id'          => $mhs->getKey(),
            'judul_kp'              => $this->judul_kp,
            'lokasi_kp'             => $this->lokasi_kp,
            'proposal_path'         => $proposalPath,
            'surat_keterangan_path' => $suratPath,
            'status'                => KerjaPraktik::ST_REVIEW_KOMISI,
            'catatan'               => null,
        ]);

        // Notif Komisi
        Notifier::toRole(
            'Dosen Komisi',
            'Pengajuan KP Baru',
            "Mahasiswa {$mhs->user?->name} mengajukan KP: {$this->judul_kp} di {$this->lokasi_kp}.",
            route('komisi.kp.review'),
            ['type' => 'kp_submitted', 'kp_id' => $kp->id, 'mhs_id' => $mhs->getKey()]
        );

        // Notif ACK ke Mahasiswa
        Notifier::toUser(
            Auth::id(),
            'Pengajuan KP diterima',
            'Pengajuan berhasil disimpan. Menunggu review Komisi.',
            route('mhs.kp.index'),
            ['type' => 'kp_ack', 'kp_id' => $kp->id]
        );

        $this->reset(['editingId', 'judul_kp', 'lokasi_kp', 'proposal_kp', 'surat_keterangan_kp', 'selectedSpId']);
        Flux::toast(heading: 'Berhasil', text: 'Pengajuan KP dikirim ke Komisi.', variant: 'success');
    }

    public function edit(int $id): void
    {
        $kp = KerjaPraktik::where('id', $id)
            ->whereHas('mahasiswa', fn($q) => $q->where('user_id', Auth::id()))
            ->firstOrFail();

        if ($kp->status !== KerjaPraktik::ST_REVIEW_KOMISI) {
            Flux::toast(heading: 'Gagal', text: 'Pengajuan dengan status saat ini tidak dapat diedit.', variant: 'danger');
            return;
        }

        $this->editingId = $id;
        $this->judul_kp  = $kp->judul_kp;
        $this->lokasi_kp = $kp->lokasi_kp;
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId', 'judul_kp', 'lokasi_kp', 'proposal_kp', 'surat_keterangan_kp']);
    }

    public function update(): void
    {
        $this->validate();

        $kp = KerjaPraktik::where('id', $this->editingId)
            ->whereHas('mahasiswa', fn($q) => $q->where('user_id', Auth::id()))
            ->firstOrFail();

        if ($kp->status !== KerjaPraktik::ST_REVIEW_KOMISI) {
            Flux::toast(heading: 'Gagal', text: 'Pengajuan dengan status saat ini tidak dapat diperbarui.', variant: 'danger');
            return;
        }

        $data = [
            'judul_kp'  => $this->judul_kp,
            'lokasi_kp' => $this->lokasi_kp,
        ];

        if ($this->proposal_kp) {
            $data['proposal_path'] = $this->proposal_kp->store('kp/proposal', 'public');
        }
        if ($this->surat_keterangan_kp) {
            $data['surat_keterangan_path'] = $this->surat_keterangan_kp->store('kp/surat-keterangan', 'public');
        }

        if ($kp->status === KerjaPraktik::ST_DITOLAK) {
            $data['status'] = KerjaPraktik::ST_REVIEW_KOMISI;
        }

        $kp->update($data);

        $this->cancelEdit();
        Flux::toast(heading: 'Tersimpan', text: 'Pengajuan KP diperbarui.', variant: 'success');
    }

    public function markDelete(int $id): void
    {
        $kp = KerjaPraktik::where('id', $id)
            ->whereHas('mahasiswa', fn($q) => $q->where('user_id', Auth::id()))
            ->first();

        if (!$kp) return;

        if ($kp->status !== KerjaPraktik::ST_REVIEW_KOMISI) {
            Flux::toast(heading: 'Gagal', text: 'Pengajuan dengan status saat ini tidak dapat dihapus.', variant: 'danger');
            return;
        }

        $this->deleteId = $kp->id;
    }

    public function confirmDelete(): void
    {
        if (!$this->deleteId) return;

        $kp = KerjaPraktik::where('id', $this->deleteId)
            ->whereHas('mahasiswa', fn($q) => $q->where('user_id', Auth::id()))
            ->first();

        if ($kp && $kp->status === KerjaPraktik::ST_REVIEW_KOMISI) {
            $kp->delete();
            Flux::toast(heading: 'Terhapus', text: 'Pengajuan KP berhasil dihapus.', variant: 'success');
        } else {
            Flux::toast(heading: 'Gagal', text: 'Pengajuan tidak dapat dihapus.', variant: 'danger');
        }

        $this->deleteId = null;
        Flux::modal('delete-kp')->close();
    }

    public function openDetail(int $id): void
    {
        $this->detailId = $id;
    }
    public function closeDetail(): void
    {
        $this->detailId = null;
    }

    #[Computed]
    public function selectedItem(): ?KerjaPraktik
    {
        if (!$this->detailId) return null;

        return KerjaPraktik::with(['mahasiswa.user'])
            ->whereHas('mahasiswa', fn($q) => $q->where('user_id', Auth::id()))
            ->find($this->detailId);
    }

    public function removeProposal(): void
    {
        $this->proposal_kp = null;
    }
    public function removeSuratKeterangan(): void
    {
        $this->surat_keterangan_kp = null;
    }

    #[Computed]
    public function orders()
    {
        $mhs = Mahasiswa::where('user_id', Auth::id())->first();

        return KerjaPraktik::query()
            ->where('mahasiswa_id', $mhs?->getKey() ?? 0)
            // Logic Search
            ->when($this->search, function ($q) {
                $q->where(function ($qq) {
                    $qq->where('judul_kp', 'like', '%' . $this->search . '%')
                        ->orWhere('lokasi_kp', 'like', '%' . $this->search . '%');
                });
            })
            // Logic Filter Status
            ->when($this->filterStatus, function ($q) {
                $q->where('status', $this->filterStatus);
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);
    }

    #[Computed]
    public function stats(): array
    {
        $mhs = Mahasiswa::where('user_id', Auth::id())->first();
        $base = KerjaPraktik::where('mahasiswa_id', $mhs?->getKey() ?? 0);

        return [
            'review_komisi'   => (clone $base)->where('status', KerjaPraktik::ST_REVIEW_KOMISI)->count(),
            'review_bapendik' => (clone $base)->where('status', KerjaPraktik::ST_REVIEW_BAPENDIK)->count(),
            'ditolak'         => (clone $base)->where('status', KerjaPraktik::ST_DITOLAK)->count(),
            'spk_terbit'      => (clone $base)->where('status', KerjaPraktik::ST_SPK_TERBIT)->count(),
        ];
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
        return view('livewire.mahasiswa.kp.page');
    }
}
