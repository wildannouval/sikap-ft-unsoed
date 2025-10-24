<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Features;

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;

use App\Http\Controllers\SP\DownloadController as SpDownloadController;
use App\Http\Controllers\SP\VerifyController as SpVerifyController;

use App\Http\Controllers\Kp\DownloadSpkController;
use App\Http\Controllers\KP\SpkVerifyController;

/*
|--------------------------------------------------------------------------
| Home & Dashboard
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

/*
|--------------------------------------------------------------------------
| Settings
|--------------------------------------------------------------------------
*/
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
});

/*
|--------------------------------------------------------------------------
| Mahasiswa
|--------------------------------------------------------------------------
*/
Route::prefix('mhs')
    ->middleware(['auth', 'role:Mahasiswa'])
    ->group(function () {
        Route::view('/dashboard', 'mhs.dashboard')->name('mhs.dashboard');

        // Surat Pengantar (SP)
        Route::view('/surat-pengantar', 'mhs.surat-pengantar.index')->name('mhs.sp.index');
        Route::get('/surat-pengantar/{sp}/download-docx', [SpDownloadController::class, 'downloadDocxForMahasiswa'])
            ->name('mhs.sp.download.docx');

        // Kerja Praktik (KP)
        Route::view('/kp', 'mhs.kp.index')->name('mhs.kp.index');

        // UNDUH SPK KP (pastikan controller-nya DownloadSpkController)
        Route::get('/kp/{kp}/download-docx', [DownloadSpkController::class, 'downloadDocxForMahasiswa'])
            ->name('mhs.kp.download.docx');
    });

/*
|--------------------------------------------------------------------------
| Bapendik
|--------------------------------------------------------------------------
*/
Route::prefix('bap')
    ->middleware(['auth', 'role:Bapendik'])
    ->group(function () {
        Route::view('/dashboard', 'bap.dashboard')->name('bap.dashboard');

        // Validasi & Riwayat SP
        Route::view('/surat-pengantar/validasi', 'bap.validasi-surat-pengantar.index')
            ->name('bap.sp.validasi');

        // Unduh DOCX SP
        Route::get('/surat-pengantar/{sp}/download-docx', [SpDownloadController::class, 'downloadDocxForBapendik'])
            ->name('bap.sp.download.docx');

        // Master Penandatangan
        Route::view('/penandatangan', 'bap.penandatangan.index')->name('bap.penandatangan.index');

        // Penerbitan SPK KP (halaman Bapendik)
        Route::view('/kp/spk', 'bap.kp.spk')->name('bap.kp.spk');

        // Unduh DOCX SPK KP (Bapendik)
        Route::get('/kp/{kp}/download-docx', [DownloadSpkController::class, 'downloadDocxForBapendik'])
            ->name('bap.kp.download.docx');
    });

/*
|--------------------------------------------------------------------------
| Komisi
|--------------------------------------------------------------------------
| Gunakan PERMISSION yang sama agar konsisten (tidak 403).
*/
Route::prefix('komisi')
    ->middleware(['auth', 'permission:kp.review'])
    ->group(function () {
        Route::view('/dashboard', 'komisi.dashboard')->name('komisi.dashboard');
        Route::view('/kp/review', 'komisi.kp.index')->name('komisi.kp.review');

        // Unduh SPK (DOCX) untuk Komisi – hanya saat SPK terbit
        Route::get('/kp/{kp}/download-docx', [DownloadSpkController::class, 'downloadDocxForKomisi'])
            ->name('komisi.kp.download.docx');
    });

/*
|--------------------------------------------------------------------------
| Verifikasi QR (Publik)
|--------------------------------------------------------------------------
*/
// Verifikasi SP (surat pengantar)
Route::get('/sp/verify/{token}', [SpVerifyController::class, 'show'])->name('sp.verify');

// Verifikasi SPK (KP) – builder kamu memanggil 'spk.verify'
Route::get('/verify/spk/{token}', SpkVerifyController::class)->name('spk.verify');
