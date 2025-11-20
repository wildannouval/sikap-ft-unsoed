<?php

namespace App\Livewire\Dosen\Kp;

use App\Models\Dosen;
use App\Models\KpConsultation;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use App\Services\Notifier; // <— NOTIFIER

class KonsultasiReviewPage extends Component
{
    use WithPagination;

    public string $q = '';
    public string $sortBy = 'tanggal_konsultasi';
    public string $sortDirection = 'desc';
    public int $perPage = 10;

    public ?int $verifyId = null;
    public string $verifier_note = '';

    public function updatingQ()
    {
        $this->resetPage();
    }

    protected function meAsDosen(): Dosen
    {
        return Dosen::where('user_id', Auth::id())->firstOrFail();
    }

    #[Computed]
    public function items()
    {
        $dosen = $this->meAsDosen();

        return KpConsultation::query()
            // === Samakan nama relasi ke "kerjaPraktik" ===
            ->with(['kerjaPraktik.mahasiswa.user'])
            ->where('dosen_pembimbing_id', $dosen->getKey())
            ->when($this->q !== '', function ($q) {
                $q->where(function ($qq) {
                    $qq->where('topik_konsultasi', 'like', '%' . $this->q . '%')
                        ->orWhere('hasil_konsultasi', 'like', '%' . $this->q . '%');
                });
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage)
            ->withQueryString();
    }

    public function openVerify(int $id)
    {
        $row = KpConsultation::findOrFail($id);
        abort_unless($row->dosen_pembimbing_id === $this->meAsDosen()->getKey(), 403);
        $this->verifyId = $row->id;
        $this->verifier_note = '';
    }

    public function confirmVerify()
    {
        $row = KpConsultation::findOrFail($this->verifyId);
        $dosen = $this->meAsDosen();
        abort_unless($row->dosen_pembimbing_id === $dosen->getKey(), 403);

        // idempoten
        if (! $row->verified_at) {
            $row->update([
                'verified_at'          => now(),
                'verified_by_dosen_id' => $dosen->getKey(),
                'verifier_note'        => $this->verifier_note ?: null,
            ]);

            // === NOTIFIKASI → Mahasiswa
            $mhsUser = $row->mahasiswa?->user;
            if ($mhsUser) {
                $title = 'Konsultasi diverifikasi';
                $note  = $this->verifier_note ? " — Catatan: {$this->verifier_note}" : '';
                $body  = sprintf(
                    'Dosen Pembimbing %s menyetujui konsultasi tanggal %s%s.',
                    $dosen->user?->name ?? '',
                    optional($row->tanggal_konsultasi)?->format('d M Y') ?: '-',
                    $note
                );
                $link  = route('mhs.kp.konsultasi', $row->kerja_praktik_id);
                Notifier::toUser(
                    $mhsUser,
                    $title,
                    $body,
                    $link,
                    [
                        'type' => 'kp_consultation_verified',
                        'kp_id' => $row->kerja_praktik_id,
                        'consultation_id' => $row->id,
                        'verified' => true,
                        'verifier_note' => $this->verifier_note ?: null,
                    ]
                );
            }
        }

        $this->verifyId = null;
        $this->verifier_note = '';
        session()->flash('ok', 'Konsultasi diverifikasi.');
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.dosen.kp.konsultasi-review-page');
    }
}
