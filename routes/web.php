<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('profile.edit');
    Route::get('settings/password', Password::class)->name('user-password.edit');
    Route::get('settings/appearance', Appearance::class)->name('appearance.edit');

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');

    // Mahasiswa
    Route::prefix('mhs')->middleware('role:Mahasiswa')->group(function () {
        Route::view('/dashboard', 'mhs.dashboard')->name('mhs.dashboard');
        Route::view('/surat-pengantar', 'mhs.surat-pengantar.index')->name('mhs.sp.index');
        Route::view('/notifikasi', 'mhs.notifikasi')->name('mhs.notifikasi');
        Route::view('/kalender', 'mhs.kalender')->name('mhs.kalender');
    });

    // Bapendik
    Route::prefix('bap')->middleware('role:Bapendik')->group(function () {
        Route::view('/dashboard', 'bap.dashboard')->name('bap.dashboard');
        Route::view('/validasi-sp', 'bap.validasi-sp')->name('bap.sp.validasi');
        Route::view('/validasi-kp', 'bap.validasi-kp')->name('bap.kp.validasi');
        Route::view('/penjadwalan-seminar', 'bap.penjadwalan-seminar')->name('bap.seminar.schedule');
        Route::view('/laporan-arsip', 'bap.laporan-arsip')->name('bap.laporan');
        Route::view('/data-pengguna', 'bap.data-pengguna')->name('bap.users');
        Route::view('/data-ruangan', 'bap.data-ruangan')->name('bap.ruangan');
        Route::view('/data-jurusan', 'bap.data-jurusan')->name('bap.jurusan');
        Route::view('/notifikasi', 'bap.notifikasi')->name('bap.notifikasi');
        Route::view('/kalender', 'bap.kalender')->name('bap.kalender');
    });

    // Dosen Pembimbing
    Route::prefix('dospem')->middleware('role:Dosen Pembimbing')->group(function () {
        Route::view('/dashboard', 'dsp.dashboard')->name('dsp.dashboard');
        Route::view('/mahasiswa-bimbingan', 'dsp.mhs-bimbingan')->name('dsp.mhs');
        Route::view('/penilaian-kp', 'dsp.penilaian')->name('dsp.nilai');
        Route::view('/laporan-arsip', 'dsp.laporan-arsip')->name('dsp.laporan');
        Route::view('/notifikasi', 'dsp.notifikasi')->name('dsp.notifikasi');
        Route::view('/kalender', 'dsp.kalender')->name('dsp.kalender');
    });

    // Dosen Komisi
    Route::prefix('dospem')->middleware('role:Dosen Pembimbing')->group(function () {
        Route::view('/dashboard', 'dsp.dashboard')->name('dsp.dashboard');
        Route::view('/mahasiswa-bimbingan', 'dsp.mhs-bimbingan')->name('dsp.mhs');
        Route::view('/penilaian-kp', 'dsp.penilaian')->name('dsp.nilai');
        Route::view('/laporan-arsip', 'dsp.laporan-arsip')->name('dsp.laporan');
        Route::view('/notifikasi', 'dsp.notifikasi')->name('dsp.notifikasi');
        Route::view('/kalender', 'dsp.kalender')->name('dsp.kalender');
    });
});
