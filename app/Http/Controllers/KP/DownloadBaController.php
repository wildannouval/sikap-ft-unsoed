<?php

namespace App\Http\Controllers\KP;

use App\Http\Controllers\Controller;
use App\Models\KpSeminar;
use App\Models\Mahasiswa;
use App\Models\Signatory;
use App\Services\BaDocxBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadBaController extends Controller
{
    // Helper untuk cek status valid download
    private function isDownloadable($status): bool
    {
        // Dokumen boleh didownload jika statusnya sudah terbit, sudah dinilai, atau selesai
        return in_array($status, [
            'ba_terbit',
            'dinilai',
            'selesai'
        ]);
    }

    // Mahasiswa download BA miliknya
    public function downloadForMahasiswa(Request $request, int $kpId, int $seminarId): BinaryFileResponse
    {
        $seminar = KpSeminar::with(['kp.mahasiswa.user', 'kp.mahasiswa.jurusan', 'kp.dosenPembimbing'])
            ->where('id', $seminarId)
            ->where('kerja_praktik_id', $kpId)
            ->firstOrFail();

        // Otorisasi: Pastikan mahasiswa yang login adalah pemilik seminar
        $mhs = Mahasiswa::where('user_id', $request->user()->id)->first();
        abort_unless($mhs && (int)$seminar->mahasiswa_id === (int)$mhs->getKey(), 403, 'Tidak berwenang.');

        // FIX: Izinkan download jika status sudah lanjut (dinilai/selesai)
        if (!$this->isDownloadable($seminar->status)) {
            abort(403, 'Berita Acara belum diterbitkan.');
        }

        return $this->processDownload($seminar);
    }

    // Dosen pembimbing download BA
    public function downloadForDospem(int $seminarId): BinaryFileResponse
    {
        $seminar = KpSeminar::with(['kp.mahasiswa.user', 'kp.dosenPembimbing'])->findOrFail($seminarId);
        $user = auth()->user();

        // Otorisasi: Harus Dosen Pembimbing yang bersangkutan ATAU Dosen Komisi
        $isDospem = $user->dosen?->dosen_id === $seminar->dosen_pembimbing_id;
        $isKomisi = $user->hasRole('Dosen Komisi');

        if (!$isDospem && !$isKomisi) {
            abort(403, 'Anda bukan pembimbing mahasiswa ini.');
        }

        // FIX: Izinkan download jika status sudah lanjut
        if (!$this->isDownloadable($seminar->status)) {
            abort(403, 'Berita Acara belum diterbitkan.');
        }

        return $this->processDownload($seminar);
    }

    // Bapendik download BA
    public function downloadForBapendik(int $seminarId): BinaryFileResponse
    {
        $seminar = KpSeminar::with(['kp.mahasiswa.user'])->findOrFail($seminarId);

        // FIX: Izinkan download jika status sudah lanjut
        if (!$this->isDownloadable($seminar->status)) {
            abort(403, 'Berita Acara belum diterbitkan.');
        }

        return $this->processDownload($seminar);
    }

    // --- Private Helper untuk Generate File ---
    private function processDownload(KpSeminar $seminar)
    {
        // Pastikan Signatory diambil
        $sign = $seminar->signatory_id ? Signatory::find($seminar->signatory_id) : null;

        // Generate file
        // Pastikan class BaDocxBuilder ada dan namespace-nya benar
        $path = (new BaDocxBuilder($seminar, $sign))->buildDocx();

        // Nama file output
        $mhsName = $seminar->kp?->mahasiswa?->user?->name ?? 'mahasiswa';
        $mhsNim  = $seminar->kp?->mahasiswa?->mahasiswa_nim ?? 'nim'; // Ganti 'nim' dengan 'mahasiswa_nim' sesuai tabel

        $who  = trim($mhsName . '_' . $mhsNim);
        $name = 'BA_Seminar_' . ($seminar->nomor_ba ?? 'draft') . '_' . Str::slug($who) . '.docx';

        return response()->download($path, $name)->deleteFileAfterSend(true);
    }
}
