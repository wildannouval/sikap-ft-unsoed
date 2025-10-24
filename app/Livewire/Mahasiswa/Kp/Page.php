<?php

namespace App\Livewire\Mahasiswa\Kp;

use App\Models\KerjaPraktik;
use App\Models\Mahasiswa;
use App\Models\SuratPengantar;
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

    // Detail modal
    public ?int $detailId  = null;

    public string $judul_kp = '';
    public string $lokasi_kp = '';

    public $proposal_kp;         // UploadedFile
    public $surat_keterangan_kp; // UploadedFile

    public ?int $selectedSpId = null;

    public string $q = '';
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 10;

    protected function rules(): array
    {
        return [
            'judul_kp' => ['required', 'string', 'min:5', 'max:255'],
            'lokasi_kp' => ['required', 'string', 'min:3', 'max:255'],
            'proposal_kp' => [$this->editingId ? 'nullable' : 'required', 'file', 'mimes:pdf', 'max:2048'],
            'surat_keterangan_kp' => [$this->editingId ? 'nullable' : 'required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'selectedSpId' => ['nullable', Rule::exists('surat_pengantars','id')->where(function ($q) {
                $mhs = Mahasiswa::where('user_id', Auth::id())->first();
                $q->where('mahasiswa_id', $mhs?->id)->where('status_surat_pengantar', 'Diterbitkan');
            })],
        ];
    }

    public function updatingQ() { $this->resetPage(); }
    public function updatingSortBy() { $this->resetPage(); }
    public function updatingSortDirection() { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

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
            ->select('id','nomor_surat','lokasi_surat_pengantar','created_at')
            ->where('mahasiswa_id', $mhs->id)
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

    public function fillFromSP(): void
    {
        if (!$this->selectedSpId) return;

        $sp = SuratPengantar::find($this->selectedSpId);
        if ($sp) {
            $lokasi = (string) $sp->lokasi_surat_pengantar;
            $this->lokasi_kp = $lokasi;

            // Auto isi judul seperti lokasi_kp, hanya bila judul_kp masih kosong
            if (trim($this->judul_kp) === '') {
                $this->judul_kp = $lokasi;
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

        KerjaPraktik::create([
            'mahasiswa_id'          => $mhs->id,
            'judul_kp'              => $this->judul_kp,
            'lokasi_kp'             => $this->lokasi_kp,
            'proposal_path'         => $proposalPath,
            'surat_keterangan_path' => $suratPath,
            'status'                => KerjaPraktik::ST_REVIEW_KOMISI,
            'catatan'               => null,
        ]);

        $this->reset(['editingId','judul_kp','lokasi_kp','proposal_kp','surat_keterangan_kp','selectedSpId']);
        session()->flash('ok','Pengajuan KP berhasil diajukan ke Komisi.');
    }

    public function edit(int $id): void
    {
        $kp = KerjaPraktik::where('id', $id)
            ->whereHas('mahasiswa', fn($q)=>$q->where('user_id', Auth::id()))
            ->firstOrFail();

        // Hanya bisa edit saat menunggu review komisi
        if ($kp->status !== KerjaPraktik::ST_REVIEW_KOMISI) {
            session()->flash('err', 'Pengajuan dengan status saat ini tidak dapat diedit.');
            return;
        }

        $this->editingId = $id;
        $this->judul_kp  = $kp->judul_kp;
        $this->lokasi_kp = $kp->lokasi_kp;
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingId','judul_kp','lokasi_kp','proposal_kp','surat_keterangan_kp']);
    }

    public function update(): void
    {
        $this->validate();

        $kp = KerjaPraktik::where('id', $this->editingId)
            ->whereHas('mahasiswa', fn($q)=>$q->where('user_id', Auth::id()))
            ->firstOrFail();

        // Kunci update selain review_komisi
        if ($kp->status !== KerjaPraktik::ST_REVIEW_KOMISI) {
            session()->flash('err','Pengajuan dengan status saat ini tidak dapat diperbarui.');
            return;
        }

        $data = [
            'judul_kp'  => $this->judul_kp,
            'lokasi_kp' => $this->lokasi_kp,
        ];

        if ($this->proposal_kp) {
            $data['proposal_path'] = $this->proposal_kp->store('kp/proposal','public');
        }
        if ($this->surat_keterangan_kp) {
            $data['surat_keterangan_path'] = $this->surat_keterangan_kp->store('kp/surat-keterangan','public');
        }

        // Jika sebelumnya ditolak dan diedit ulang, lempar ke review komisi lagi (opsional)
        if ($kp->status === KerjaPraktik::ST_DITOLAK) {
            $data['status'] = KerjaPraktik::ST_REVIEW_KOMISI;
        }

        $kp->update($data);

        $this->cancelEdit();
        session()->flash('ok','Pengajuan KP diperbarui.');
    }

    public function markDelete(int $id): void
    {
        $kp = KerjaPraktik::where('id', $id)
            ->whereHas('mahasiswa', fn($q)=>$q->where('user_id', Auth::id()))
            ->first();

        if (!$kp) return;

        // Hanya bisa hapus saat review_komisi
        if ($kp->status !== KerjaPraktik::ST_REVIEW_KOMISI) {
            session()->flash('err','Pengajuan dengan status saat ini tidak dapat dihapus.');
            return;
        }

        $this->deleteId = $kp->id;
    }

    public function confirmDelete(): void
    {
        if (!$this->deleteId) return;

        $kp = KerjaPraktik::where('id', $this->deleteId)
            ->whereHas('mahasiswa', fn($q)=>$q->where('user_id', Auth::id()))
            ->first();

        if ($kp && $kp->status === KerjaPraktik::ST_REVIEW_KOMISI) {
            $kp->delete();
            session()->flash('ok','Pengajuan KP berhasil dihapus.');
        } else {
            session()->flash('err','Pengajuan tidak dapat dihapus.');
        }

        $this->deleteId = null;
        Flux::modal('delete-kp')->close();
    }

    // ===== Detail modal =====
    public function openDetail(int $id): void { $this->detailId = $id; }
    public function closeDetail(): void { $this->detailId = null; }

    #[Computed]
    public function selectedItem(): ?KerjaPraktik
    {
        if (!$this->detailId) return null;

        return KerjaPraktik::with(['mahasiswa.user'])
            ->whereHas('mahasiswa', fn($q)=>$q->where('user_id', Auth::id()))
            ->find($this->detailId);
    }

    public function removeProposal(): void { $this->proposal_kp = null; }
    public function removeSuratKeterangan(): void { $this->surat_keterangan_kp = null; }

    #[Computed]
    public function orders()
    {
        $mhs = Mahasiswa::where('user_id', Auth::id())->first();

        return KerjaPraktik::query()
            ->where('mahasiswa_id', $mhs?->id ?? 0)
            ->when($this->q !== '', function ($q) {
                $q->where(function ($qq) {
                    $qq->where('judul_kp','like','%'.$this->q.'%')
                       ->orWhere('lokasi_kp','like','%'.$this->q.'%');
                });
            })
            ->orderBy($this->sortBy,$this->sortDirection)
            ->paginate($this->perPage);
    }

    #[Computed]
    public function stats(): array
    {
        $mhs = Mahasiswa::where('user_id', Auth::id())->first();

        $base = KerjaPraktik::where('mahasiswa_id', $mhs?->id ?? 0);
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
