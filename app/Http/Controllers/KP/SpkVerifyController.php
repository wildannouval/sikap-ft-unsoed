<?php

namespace App\Http\Controllers\KP;

use App\Http\Controllers\Controller;
use App\Models\KerjaPraktik;
use Illuminate\Http\Request;

class SpkVerifyController extends Controller
{
    public function __invoke(string $token)
    {
        $kp = KerjaPraktik::with(['mahasiswa.user'])
            ->where('spk_qr_token', $token)
            ->first();

        if (! $kp) {
            abort(404, 'Token verifikasi SPK tidak ditemukan.');
        }

        $isValid = true;
        if ($kp->spk_qr_expires_at && now()->greaterThan($kp->spk_qr_expires_at)) {
            $isValid = false;
        }

        return view('kp.spk-verify', [
            'kp'      => $kp,
            'isValid' => $isValid,
        ]);
    }
}
