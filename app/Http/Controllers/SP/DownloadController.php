<?php

namespace App\Http\Controllers\SP;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\SuratPengantar;
use App\Models\Signatory;
use App\Services\SuratPengantarDocxBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller
{
    /** Pastikan nama file aman untuk header Content-Disposition */
    private function safeDocxName(?string $nomorSurat, int|string $fallbackId): string
    {
        $base = $nomorSurat ?: $fallbackId;
        // ganti karakter terlarang / dan \ serta karakter aneh lain
        $base = preg_replace('/[\/\\\\]+/', '-', $base);     // hilangkan slash
        $base = preg_replace('/[\r\n\t]+/', ' ', $base);     // hilangkan control chars
        $base = trim($base);
        // batasi panjang agar aman
        if (mb_strlen($base) > 120) {
            $base = mb_substr($base, 0, 120);
        }
        // fallback jika hasil kosong
        if ($base === '') {
            $base = (string) $fallbackId;
        }
        return 'Surat_Pengantar_'.$base.'.docx';
    }

    // Mahasiswa: DOCX
    public function downloadDocxForMahasiswa(SuratPengantar $sp): StreamedResponse
    {
        // authorize pemilik
        $mhs = Mahasiswa::where('user_id', Auth::id())->firstOrFail();
        abort_unless($sp->mahasiswa_id === $mhs->id, 403);
        abort_unless($sp->status_surat_pengantar === 'Diterbitkan', 403, 'Belum diterbitkan.');

        $sig = $sp->signatory_id ? Signatory::find($sp->signatory_id) : null;
        $builder = new SuratPengantarDocxBuilder($sp, $sig);
        $docx = $builder->buildDocx();

        $filename = $this->safeDocxName($sp->nomor_surat, $sp->id);

        return response()->streamDownload(function () use ($docx) {
            readfile($docx);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }

    // Bapendik/Admin: DOCX
    public function downloadDocxForBapendik(SuratPengantar $sp): StreamedResponse
    {
        $sig = $sp->signatory_id ? Signatory::find($sp->signatory_id) : null;
        $builder = new SuratPengantarDocxBuilder($sp, $sig);
        $docx = $builder->buildDocx();

        $filename = $this->safeDocxName($sp->nomor_surat, $sp->id);

        return response()->streamDownload(function () use ($docx) {
            readfile($docx);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }
}
