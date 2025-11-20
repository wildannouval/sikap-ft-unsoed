<?php

namespace App\Policies;

use App\Models\User;
use App\Models\KerjaPraktik;

class KerjaPraktikPolicy
{
    public function downloadSpk(User $user, KerjaPraktik $kp): bool
    {
        // Admin superuser (opsional jika kamu punya gate sebelum/role)
        if (method_exists($user, 'hasRole') && $user->hasRole(['Admin', 'Bapendik'])) {
            return true;
        }

        // Mahasiswa pemilik KP
        if ($kp->mahasiswa && (int) $kp->mahasiswa->user_id === (int) $user->id) {
            return true;
        }

        // Dosen pembimbing dari KP ini
        if ($kp->dosen_pembimbing_id && $user->dosen && (int) $user->dosen->dosen_id === (int) $kp->dosen_pembimbing_id) {
            return true;
        }

        // Dosen komisi dari KP ini
        if ($kp->dosen_komisi_id && $user->dosen && (int) $user->dosen->dosen_id === (int) $kp->dosen_komisi_id) {
            return true;
        }

        // Role komisi secara umum (jika ingin izinkan semua komisi melihat)
        if (method_exists($user, 'hasRole') && $user->hasRole(['Dosen Komisi'])) {
            return true;
        }

        return false;
    }
}
