<?php

namespace App\Http\Controllers\KP;

use App\Http\Controllers\Controller;
use App\Models\KerjaPraktik;

class SpkVerifyController extends Controller
{
    /**
     * Halaman verifikasi QR SPK KP
     * Route: GET /kp/spk/verify/{token} (contoh)
     */
    public function __invoke(string $token)
    {
        $kp = KerjaPraktik::with(['mahasiswa.user'])
            ->where('spk_qr_token', $token)
            ->first();

        if (! $kp) {
            return view('kp.spk-verify', [
                'found'       => false,
                'status'      => 'invalid',
                'status_text' => 'Token tidak valid',
                'description' => 'QR code tidak dikenali atau SPK tidak ditemukan.',
                'kp'          => null,
            ]);
        }

        $isExpired = $kp->spk_qr_expires_at ? now()->greaterThan($kp->spk_qr_expires_at) : false;

        if ($isExpired) {
            $status      = 'expired';
            $statusText  = 'Token Kedaluwarsa';
            $desc        = 'QR code sudah melewati masa berlaku. Silakan hubungi Bapendik untuk verifikasi manual.';
        } else {
            $status      = 'valid';
            $statusText  = 'SPK Terverifikasi';
            $desc        = 'QR code valid. Detail SPK Kerja Praktik ditampilkan di bawah.';
        }

        return view('kp.spk-verify', [
            'found'       => true,
            'status'      => $status,
            'status_text' => $statusText,
            'description' => $desc,
            'kp'          => $kp,
        ]);
    }
}
