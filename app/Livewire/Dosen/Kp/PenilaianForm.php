<?php

namespace App\Livewire\Dosen\Kp;

use App\Models\KpGrade;
use App\Models\KpSeminar;
use App\Services\Notifier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Flux\Flux;

class PenilaianForm extends Component
{
    use WithPagination, WithFileUploads;

    public string $q = '';
    public int $perPage = 10;

    // entity yang sedang dinilai (untuk ringkasan di modal)
    public ?KpSeminar $seminar = null;

    // form nilai (dosen pembimbing)
    public ?int $editingId = null; // kp_seminars.id yang sedang dinilai
    public $ba_scan;               // file upload BA scan (pdf/jpg/png)
    public ?string $ba_scan_path = null;

    // komponen dospem
    public $dospem_sistematika_laporan = null;
    public $dospem_tata_bahasa = null;
    public $dospem_sistematika_seminar = null;
    public $dospem_kecocokan_isi = null;
    public $dospem_materi_kp = null;
    public $dospem_penguasaan_masalah = null;
    public $dospem_diskusi = null;

    // komponen pembimbing lapangan
    public $pl_kesesuaian = null;
    public $pl_kehadiran = null;
    public $pl_kedisiplinan = null;
    public $pl_keaktifan = null;
    public $pl_kecermatan = null;
    public $pl_tanggung_jawab = null;

    protected $queryString = [
        'q' => ['except' => ''],
    ];

    public function updatingQ()
    {
        $this->resetPage();
    }
    public function updatingPerPage()
    {
        $this->resetPage();
    }

    #[Computed]
    public function dosenId(): int
    {
        return Auth::user()->dosen?->dosen_id ?? 0;
    }

    #[Computed]
    public function items()
    {
        $term = '%' . $this->q . '%';

        return KpSeminar::query()
            ->with(['kp.mahasiswa.user', 'grade'])
            ->where('dosen_pembimbing_id', $this->dosenId)
            // Hanya tampilkan seminar yang sudah BA terbit atau sedang dinilai
            ->whereIn('status', [KpSeminar::ST_BA_TERBIT, KpSeminar::ST_DINILAI])
            ->when($this->q !== '', function ($q) use ($term) {
                $q->where(function ($qq) use ($term) {
                    $qq->where('judul_laporan', 'like', $term)
                        ->orWhereHas('kp.mahasiswa.user', fn($w) => $w->where('name', 'like', $term))
                        // Pencarian NIM via relasi KP->Mahasiswa
                        ->orWhereHas('kp.mahasiswa', function ($w) use ($term) {
                            $w->where('mahasiswa_nim', 'like', $term);
                        });
                });
            })
            ->orderByDesc('updated_at')
            ->paginate($this->perPage)
            ->withQueryString();
    }

    #[Computed]
    public function stats(): array
    {
        $base = KpSeminar::where('dosen_pembimbing_id', $this->dosenId);

        return [
            'total'     => (clone $base)->whereIn('status', [KpSeminar::ST_BA_TERBIT, KpSeminar::ST_DINILAI])->count(),
            'pending'   => (clone $base)->where('status', KpSeminar::ST_BA_TERBIT)->count(),
            'completed' => (clone $base)->where('status', KpSeminar::ST_DINILAI)->count(),
        ];
    }

    public function open(int $seminarId): void
    {
        $row = KpSeminar::with(['grade', 'kp.mahasiswa.user'])
            ->where('id', $seminarId)
            ->where('dosen_pembimbing_id', $this->dosenId)
            ->firstOrFail();

        $this->seminar      = $row;
        $this->editingId    = $row->id;
        $this->ba_scan_path = $row->grade?->ba_scan_path;

        $g = $row->grade;
        $this->dospem_sistematika_laporan = $g?->dospem_sistematika_laporan;
        $this->dospem_tata_bahasa         = $g?->dospem_tata_bahasa;
        $this->dospem_sistematika_seminar = $g?->dospem_sistematika_seminar;
        $this->dospem_kecocokan_isi       = $g?->dospem_kecocokan_isi;
        $this->dospem_materi_kp           = $g?->dospem_materi_kp;
        $this->dospem_penguasaan_masalah  = $g?->dospem_penguasaan_masalah;
        $this->dospem_diskusi             = $g?->dospem_diskusi;

        $this->pl_kesesuaian     = $g?->pl_kesesuaian;
        $this->pl_kehadiran      = $g?->pl_kehadiran;
        $this->pl_kedisiplinan   = $g?->pl_kedisiplinan;
        $this->pl_keaktifan      = $g?->pl_keaktifan;
        $this->pl_kecermatan     = $g?->pl_kecermatan;
        $this->pl_tanggung_jawab = $g?->pl_tanggung_jawab;
    }

    protected function rules(): array
    {
        $score = ['required', 'numeric', 'min:0', 'max:100'];
        return [
            'dospem_sistematika_laporan' => $score,
            'dospem_tata_bahasa'         => $score,
            'dospem_sistematika_seminar' => $score,
            'dospem_kecocokan_isi'       => $score,
            'dospem_materi_kp'           => $score,
            'dospem_penguasaan_masalah'  => $score,
            'dospem_diskusi'             => $score,

            'pl_kesesuaian'     => $score,
            'pl_kehadiran'      => $score,
            'pl_kedisiplinan'   => $score,
            'pl_keaktifan'      => $score,
            'pl_kecermatan'     => $score,
            'pl_tanggung_jawab' => $score,

            'ba_scan'           => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ];
    }

    protected function computeScores(): array
    {
        $d = collect([
            $this->dospem_sistematika_laporan,
            $this->dospem_tata_bahasa,
            $this->dospem_sistematika_seminar,
            $this->dospem_kecocokan_isi,
            $this->dospem_materi_kp,
            $this->dospem_penguasaan_masalah,
            $this->dospem_diskusi,
        ])->filter(fn($v) => $v !== null)->avg() ?? 0;

        $p = collect([
            $this->pl_kesesuaian,
            $this->pl_kehadiran,
            $this->pl_kedisiplinan,
            $this->pl_keaktifan,
            $this->pl_kecermatan,
            $this->pl_tanggung_jawab,
        ])->filter(fn($v) => $v !== null)->avg() ?? 0;

        $scoreDospem = round($d * 0.60, 2);
        $scorePl     = round($p * 0.40, 2);
        $final       = round(($scoreDospem + $scorePl), 2);

        $letter = match (true) {
            $final >= 85 => 'A',
            $final >= 80 => 'AB',
            $final >= 70 => 'B',
            $final >= 65 => 'BC',
            $final >= 60 => 'C',
            $final >= 55 => 'CD',
            $final >= 0  => 'D',
            default      => 'D',
        };

        return [$scoreDospem, $scorePl, $final, $letter];
    }

    public function save(): void
    {
        $this->validate();

        $seminar = KpSeminar::with(['grade', 'kp.mahasiswa.user'])
            ->where('id', $this->editingId)
            ->where('dosen_pembimbing_id', $this->dosenId)
            ->firstOrFail();

        // upload BA scan (opsional)
        if ($this->ba_scan) {
            $this->ba_scan_path = $this->ba_scan->store('kp/ba-scan', 'public');
        }

        // hitung skor
        [$scoreDospem, $scorePl, $final, $letter] = $this->computeScores();

        $payload = [
            'kp_seminar_id'             => $seminar->id,
            'dospem_sistematika_laporan' => $this->dospem_sistematika_laporan,
            'dospem_tata_bahasa'         => $this->dospem_tata_bahasa,
            'dospem_sistematika_seminar' => $this->dospem_sistematika_seminar,
            'dospem_kecocokan_isi'       => $this->dospem_kecocokan_isi,
            'dospem_materi_kp'           => $this->dospem_materi_kp,
            'dospem_penguasaan_masalah'  => $this->dospem_penguasaan_masalah,
            'dospem_diskusi'             => $this->dospem_diskusi,

            'pl_kesesuaian'     => $this->pl_kesesuaian,
            'pl_kehadiran'      => $this->pl_kehadiran,
            'pl_kedisiplinan'   => $this->pl_kedisiplinan,
            'pl_keaktifan'      => $this->pl_keaktifan,
            'pl_kecermatan'     => $this->pl_kecermatan,
            'pl_tanggung_jawab' => $this->pl_tanggung_jawab,

            'score_dospem'      => $scoreDospem,
            'score_pl'          => $scorePl,
            'final_score'       => $final,
            'final_letter'      => $letter,

            'graded_by_user_id' => Auth::id(),
            'graded_at'         => now(),
        ];

        if ($this->ba_scan_path) {
            $payload['ba_scan_path'] = $this->ba_scan_path;
        }

        // upsert grade
        $grade = KpGrade::firstOrNew(['kp_seminar_id' => $seminar->id]);
        $grade->fill($payload)->save();

        // update status seminar -> dinilai
        $seminar->update(['status' => KpSeminar::ST_DINILAI]);

        // Notif
        try {
            $userId = $seminar->kp?->mahasiswa?->user?->id;
            if ($userId) {
                Notifier::toUser(
                    (int) $userId,
                    'Penilaian KP Selesai',
                    'Nilai KP kamu sudah keluar. Unggah bukti distribusi untuk melihatnya.',
                    route('mhs.nilai'),
                    ['kp_seminar_id' => $seminar->id]
                );
            }
        } catch (\Throwable $e) {
        }

        // refresh entity untuk ringkasan modal
        $this->seminar = $seminar->fresh(['grade', 'kp.mahasiswa.user']);

        Flux::toast(heading: 'Tersimpan', text: 'Nilai berhasil disimpan.', variant: 'success');
        $this->reset('ba_scan');
        $this->resetPage();

        // Tetap di mode edit agar dosen bisa review
    }

    // Helpers Badge
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
        return view('livewire.dosen.kp.penilaian-form', [
            'seminarSelected' => $this->editingId
                ? ($this->seminar ?: KpSeminar::with(['grade', 'kp.mahasiswa.user'])->find($this->editingId))
                : null,
        ]);
    }
}
