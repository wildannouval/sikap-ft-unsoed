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
    // Mahasiswa download BA miliknya
    public function downloadForMahasiswa(Request $request, int $kpId, int $seminarId): BinaryFileResponse
    {
        $seminar = KpSeminar::with(['kp.mahasiswa.user', 'kp.mahasiswa.jurusan', 'kp.dosenPembimbing'])
            ->where('id', $seminarId)
            ->where('kerja_praktik_id', $kpId)
            ->firstOrFail();

        // otorisasi
        $mhs = Mahasiswa::where('user_id', $request->user()->id)->first();
        abort_unless($mhs && (int)$seminar->mahasiswa_id === (int)$mhs->getKey(), 403, 'Tidak berwenang.');
        abort_unless($seminar->status === 'ba_terbit', 403, 'BA belum terbit.');

        $sign = $seminar->signatory_id ? Signatory::find($seminar->signatory_id) : null;
        $path = (new BaDocxBuilder($seminar, $sign))->buildDocx();

        $who  = trim(($seminar->kp?->mahasiswa?->user?->name ?? 'mahasiswa') . '_' . $seminar->kp?->mahasiswa?->nim);
        $name = 'BA_Seminar_' . $seminar->nomor_ba . '_' . Str::slug($who) . '.docx';
        return response()->download($path, $name)->deleteFileAfterSend(true);
    }

    // Dosen pembimbing download BA
    public function downloadForDospem(int $seminarId): BinaryFileResponse
    {
        $seminar = KpSeminar::with(['kp.mahasiswa.user', 'kp.dosenPembimbing'])->findOrFail($seminarId);

        // user harus dosen pembimbing terkait
        abort_unless(auth()->user()?->dosen?->dosen_id === $seminar->dosen_pembimbing_id, 403, 'Tidak berwenang.');
        abort_unless($seminar->status === 'ba_terbit', 403);

        $sign = $seminar->signatory_id ? Signatory::find($seminar->signatory_id) : null;
        $path = (new BaDocxBuilder($seminar, $sign))->buildDocx();

        $who  = trim(($seminar->kp?->mahasiswa?->user?->name ?? 'mahasiswa') . '_' . $seminar->kp?->mahasiswa?->nim);
        $name = 'BA_Seminar_' . $seminar->nomor_ba . '_' . Str::slug($who) . '.docx';
        return response()->download($path, $name)->deleteFileAfterSend(true);
    }

    // Bapendik download BA
    public function downloadForBapendik(int $seminarId): BinaryFileResponse
    {
        $seminar = KpSeminar::with(['kp.mahasiswa.user'])->findOrFail($seminarId);
        abort_unless($seminar->status === 'ba_terbit', 403);

        $sign = $seminar->signatory_id ? Signatory::find($seminar->signatory_id) : null;
        $path = (new BaDocxBuilder($seminar, $sign))->buildDocx();

        $who  = trim(($seminar->kp?->mahasiswa?->user?->name ?? 'mahasiswa') . '_' . $seminar->kp?->mahasiswa?->nim);
        $name = 'BA_Seminar_' . $seminar->nomor_ba . '_' . Str::slug($who) . '.docx';
        return response()->download($path, $name)->deleteFileAfterSend(true);
    }
}
