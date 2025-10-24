<?php

namespace App\Http\Controllers\Kp;

use App\Http\Controllers\Controller;
use App\Models\KerjaPraktik;
use App\Models\Mahasiswa;
use App\Models\Signatory;
use App\Services\SpkDocxBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadSpkController extends Controller
{
    /**
     * Bapendik mengunduh SPK (DOCX).
     */
    public function downloadDocxForBapendik(KerjaPraktik $kp): BinaryFileResponse
    {
        abort_unless($kp->status === KerjaPraktik::ST_SPK_TERBIT, 403, 'SPK belum diterbitkan.');

        // siapkan data untuk builder (muat relasi agar placeholder terisi)
        $kp->load(['mahasiswa.user', 'mahasiswa.jurusan', 'dosenPembimbing', 'dosenKomisi']);
        $signatory = $kp->signatory_id ? Signatory::find($kp->signatory_id) : null;

        $path = (new SpkDocxBuilder($kp, $signatory))->buildDocx();
        $name = 'SPK_KP_' . ($kp->mahasiswa?->nim ?? 'NIM') . '.docx';

        return response()->download($path, $name)->deleteFileAfterSend(true);
    }

    /**
     * Mahasiswa mengunduh SPK (DOCX) miliknya.
     */
    public function downloadDocxForMahasiswa(Request $request, KerjaPraktik $kp): BinaryFileResponse
    {
        $mhs = Mahasiswa::where('user_id', $request->user()->id)->first();
        abort_unless($mhs && $kp->mahasiswa_id === $mhs->id, 403, 'Tidak berwenang.');
        abort_unless($kp->status === KerjaPraktik::ST_SPK_TERBIT, 403, 'SPK belum terbit.');

        $kp->load(['mahasiswa.user', 'mahasiswa.jurusan', 'dosenPembimbing', 'dosenKomisi']);
        $signatory = $kp->signatory_id ? Signatory::find($kp->signatory_id) : null;

        $path = (new SpkDocxBuilder($kp, $signatory))->buildDocx();
        $who  = trim(($kp->mahasiswa?->user?->name ?? 'mahasiswa') . '_' . $kp->mahasiswa?->nim);
        $name = 'SPK_KP_' . ($kp->nomor_spk ?? 'NO') . '_' . Str::slug($who) . '.docx';

        return response()->download($path, $name)->deleteFileAfterSend(true);
    }

    /**
     * Komisi mengunduh SPK (DOCX).
     */
    public function downloadDocxForKomisi(KerjaPraktik $kp): BinaryFileResponse
    {
        abort_unless($kp->status === KerjaPraktik::ST_SPK_TERBIT, 403, 'SPK belum terbit.');

        $kp->load(['mahasiswa.user', 'mahasiswa.jurusan', 'dosenPembimbing', 'dosenKomisi']);
        $signatory = $kp->signatory_id ? Signatory::find($kp->signatory_id) : null;

        $path = (new SpkDocxBuilder($kp, $signatory))->buildDocx();
        $who  = trim(($kp->mahasiswa?->user?->name ?? 'mahasiswa') . '_' . $kp->mahasiswa?->nim);
        $name = 'SPK_KP_' . ($kp->nomor_spk ?? 'NO') . '_' . Str::slug($who) . '.docx';

        return response()->download($path, $name)->deleteFileAfterSend(true);
    }
}
