<?php

namespace App\Http\Controllers\KP;

use App\Http\Controllers\Controller;
use App\Models\KpSeminar;

class BaVerifyController extends Controller
{
    public function __invoke(string $token)
    {
        $row = KpSeminar::with(['kp.mahasiswa.user'])
            ->where('ba_qr_token', $token)
            ->first();

        if (! $row) {
            return view('ba.ba-verify', [
                'found'       => false,
                'status'      => 'invalid',
                'status_text' => 'Token tidak valid',
                'description' => 'QR code tidak dikenali atau Berita Acara tidak ditemukan.',
                'seminar'     => null,
            ]);
        }

        $isExpired = $row->ba_qr_expires_at ? now()->greaterThan($row->ba_qr_expires_at) : false;

        if ($isExpired) {
            $status      = 'expired';
            $statusText  = 'Token Kedaluwarsa';
            $desc        = 'QR code sudah melewati masa berlaku. Silakan hubungi Bapendik untuk verifikasi manual.';
        } else {
            $status      = 'valid';
            $statusText  = 'Berita Acara Terverifikasi';
            $desc        = 'QR code valid. Detail Berita Acara ditampilkan di bawah.';
        }

        return view('ba.ba-verify', [
            'found'       => true,
            'status'      => $status,     // valid | expired | invalid
            'status_text' => $statusText,
            'description' => $desc,
            'seminar'     => $row,
        ]);
    }
}
