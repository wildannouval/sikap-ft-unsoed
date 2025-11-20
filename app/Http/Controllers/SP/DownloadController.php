<?php

namespace App\Http\Controllers\SP;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\SuratPengantar;
use App\Models\Signatory;
use App\Services\SuratPengantarDocxBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DownloadController extends Controller
{
    /** Nama file aman untuk header Content-Disposition */
    private function safeDocxName(?string $nomorSurat, int|string $fallbackId): string
    {
        $base = $nomorSurat ?: $fallbackId;
        $base = preg_replace('/[\/\\\\]+/', '-', $base);   // hilangkan slash
        $base = preg_replace('/[\r\n\t]+/', ' ', $base);   // hilangkan control chars
        $base = trim($base);
        if (mb_strlen($base) > 120) {
            $base = mb_substr($base, 0, 120);
        }
        if ($base === '') {
            $base = (string) $fallbackId;
        }
        return 'Surat_Pengantar_' . $base . '.docx';
    }

    /** Helper kirim file & auto hapus temp */
    private function sendDocx(string $path, string $filename)
    {
        return response()->download(
            file: $path,
            name: $filename,
            headers: ['Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']
        )->deleteFileAfterSend(true);
    }

    /**
     * Mahasiswa: unduh DOCX miliknya (hanya setelah Diterbitkan)
     * Route: mhs.sp.download.docx
     */
    public function downloadDocxForMahasiswa(Request $request, SuratPengantar $sp)
    {
        // Pastikan pemilik
        $ownerUserId = $sp->mahasiswa?->user_id;
        abort_unless($ownerUserId && $ownerUserId === Auth::id(), 403, 'Anda tidak berhak mengunduh surat ini.');

        // Wajib sudah diterbitkan
        abort_unless($sp->status_surat_pengantar === 'Diterbitkan', 403, 'Surat belum diterbitkan.');

        // Pakai signatory snapshot kalau ada; kalau tidak, ambil dari relasi
        $sig = $sp->signatory_id ? Signatory::find($sp->signatory_id) : null;

        $builder  = new SuratPengantarDocxBuilder($sp, $sig);
        $docxPath = $builder->buildDocx();

        $filename = $this->safeDocxName($sp->nomor_surat, $sp->id);
        return $this->sendDocx($docxPath, $filename);
    }

    /**
     * Bapendik/Admin: unduh DOCX (umumnya hanya yang Diterbitkan)
     * Route: bap.sp.download.docx
     */
    public function downloadDocxForBapendik(Request $request, SuratPengantar $sp)
    {
        // Guard ekstra (selain middleware)
        abort_unless($request->user()?->hasRole('Bapendik'), 403, 'Hanya Bapendik yang berhak.');

        // Biasanya hanya ambil yang sudah terbit (biar konsisten dengan Mahasiswa)
        abort_unless($sp->status_surat_pengantar === 'Diterbitkan', 403, 'Surat belum diterbitkan.');

        $sig = $sp->signatory_id ? Signatory::find($sp->signatory_id) : null;

        $builder  = new SuratPengantarDocxBuilder($sp, $sig);
        $docxPath = $builder->buildDocx();

        $filename = $this->safeDocxName($sp->nomor_surat, $sp->id);
        return $this->sendDocx($docxPath, $filename);
    }
}
