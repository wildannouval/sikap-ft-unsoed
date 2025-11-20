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

        if (! $row) abort(404, 'Token BA tidak ditemukan.');

        $isValid = true;
        if ($row->ba_qr_expires_at && now()->greaterThan($row->ba_qr_expires_at)) {
            $isValid = false;
        }

        return view('kp.ba-verify', [
            'seminar' => $row,
            'isValid' => $isValid,
        ]);
    }
}
