<?php

namespace App\Livewire\Mhs;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SuratPengantar;
use Illuminate\Support\Str;

class SuratPengantarPage extends Component
{
    use WithPagination;

    public string $lokasi_surat_pengantar = '';
    public string $penerima_surat_pengantar = '';
    public string $alamat_surat_pengantar  = '';
    public ?string $tembusan_surat_pengantar = null;

    public bool $showForm = false;

    public array $stats = ['Diajukan'=>0,'Disetujui'=>0,'Terbit'=>0,'Ditolak'=>0];

    protected function rules(): array
    {
        return [
            'lokasi_surat_pengantar'   => 'required|string|max:150',
            'penerima_surat_pengantar' => 'required|string|max:150',
            'alamat_surat_pengantar'   => 'required|string|max:300',
            'tembusan_surat_pengantar' => 'nullable|string|max:150',
        ];
    }

    public function openForm(){ $this->resetForm(); $this->showForm = true; }
    public function closeForm(){ $this->showForm = false; }

    public function submit(): void
    {
        $data = $this->validate();

        $mhs = auth()->user()?->mahasiswa;
        if (! $mhs) { session()->flash('message','Akun belum terhubung data Mahasiswa.'); return; }

        SuratPengantar::create([
            'uuid'                              => (string) Str::uuid(),
            'mahasiswa_id'                      => $mhs->id,
            'lokasi_surat_pengantar'            => $data['lokasi_surat_pengantar'],
            'penerima_surat_pengantar'          => $data['penerima_surat_pengantar'],
            'alamat_surat_pengantar'            => $data['alamat_surat_pengantar'],
            'tembusan_surat_pengantar'          => $data['tembusan_surat_pengantar'] ?? null,
            'status_surat_pengantar'            => 'Diajukan',
            'tanggal_pengajuan_surat_pengantar' => now()->toDateString(),
        ]);

        session()->flash('message','Pengajuan berhasil dikirim.');
        $this->resetForm();
        $this->showForm = false;
        $this->resetPage();
    }

    private function resetForm(): void
    {
        $this->lokasi_surat_pengantar='';
        $this->penerima_surat_pengantar='';
        $this->alamat_surat_pengantar='';
        $this->tembusan_surat_pengantar=null;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        $mhs = auth()->user()?->mahasiswa;
        $base = SuratPengantar::query()->when($mhs, fn($q)=>$q->where('mahasiswa_id',$mhs->id));

        $this->stats['Diajukan']  = (clone $base)->where('status_surat_pengantar','Diajukan')->count();
        $this->stats['Disetujui'] = (clone $base)->where('status_surat_pengantar','Disetujui')->count();
        $this->stats['Terbit']    = (clone $base)->where('status_surat_pengantar','Terbit')->count();
        $this->stats['Ditolak']   = (clone $base)->where('status_surat_pengantar','Ditolak')->count();

        $items = (clone $base)->latest('id')->paginate(10);

        return view('livewire.mhs.surat-pengantar-page', compact('items'));
    }
}
