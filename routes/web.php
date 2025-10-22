<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use Laravel\Fortify\Features;

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;

use App\Http\Controllers\SP\DownloadController;
use App\Http\Controllers\SP\VerifyController;

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
| Settings (Starterkit)
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
        Route::view('/surat-pengantar', 'mhs.surat-pengantar.index')->name('mhs.sp.index');

        // Unduh DOCX (hanya yang diterbitkan)
        Route::get('/surat-pengantar/{sp}/download-docx', [DownloadController::class, 'downloadDocxForMahasiswa'])
            ->name('mhs.sp.download.docx');
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

        // Validasi & riwayat Surat Pengantar (Livewire)
        Route::view('/surat-pengantar/validasi', 'bap.validasi-surat-pengantar.index')
            ->name('bap.sp.validasi');

        // Unduh DOCX
        Route::get('/surat-pengantar/{sp}/download-docx', [DownloadController::class, 'downloadDocxForBapendik'])
            ->name('bap.sp.download.docx');

        // Halaman Penandatangan
        Route::view('/penandatangan', 'bap.penandatangan.index')->name('bap.penandatangan.index');
    });

/*
|--------------------------------------------------------------------------
| Verifikasi QR (publik)
|--------------------------------------------------------------------------
*/
Route::get('/sp/verify/{token}', [VerifyController::class, 'show'])->name('sp.verify');
