<?php

namespace App\Http\Controllers\SP;

use App\Http\Controllers\Controller;
use App\Models\SuratPengantar;
use Carbon\Carbon;

class VerifyController extends Controller
{
    /**
     * Tampilkan halaman verifikasi QR berdasarkan token.
     * Route: GET /sp/verify/{token}  (name: sp.verify)
     */
    public function show(string $token)
    {
        $sp = SuratPengantar::with(['mahasiswa.jurusan', 'signatory'])
            ->where('qr_token', $token)
            ->first();

        if (! $sp) {
            return view('sp.verify', [
                'found'       => false,
                'status'      => 'invalid',
                'status_text' => 'Token tidak valid',
                'description' => 'QR code tidak dikenali atau surat tidak ditemukan.',
                'sp'          => null,
            ]);
        }

        $now = Carbon::now();
        $isExpired = $sp->qr_expires_at ? Carbon::parse($sp->qr_expires_at)->lt($now) : false;

        if ($isExpired) {
            $status = 'expired';
            $statusText = 'Token Kedaluwarsa';
            $desc = 'QR code sudah melewati masa berlaku. Silakan hubungi Bapendik untuk verifikasi manual.';
        } else {
            $status = 'valid';
            $statusText = 'Surat Terverifikasi';
            $desc = 'QR code valid. Detail surat pengantar ditampilkan di bawah.';
        }

        return view('sp.verify', [
            'found'       => true,
            'status'      => $status,     // valid | expired
            'status_text' => $statusText,
            'description' => $desc,
            'sp'          => $sp,
        ]);
    }
}
