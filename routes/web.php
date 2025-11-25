<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Features;

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;

use App\Http\Controllers\SP\DownloadController as SpDownloadController;
use App\Http\Controllers\SP\VerifyController   as SpVerifyController;

use App\Http\Controllers\KP\DownloadSpkController;
use App\Http\Controllers\KP\SpkVerifyController;
use App\Http\Controllers\KP\DownloadBaController as BaDownloadController;
use App\Http\Controllers\KP\BaVerifyController;

use App\Livewire\Mahasiswa\Kp\KonsultasiPage as MhsKpKonsultasiPage;
use App\Livewire\Dosen\Kp\KonsultasiIndex   as DosenKpKonsultasiIndex;

use App\Livewire\Bapendik\Master\DosenIndex     as BapDosenIndex;
use App\Livewire\Bapendik\Master\MahasiswaIndex as BapMahasiswaIndex;
use App\Livewire\Bapendik\Master\RuanganIndex   as BapRuanganIndex;

use App\Livewire\Mahasiswa\Kp\SeminarDaftarPage;
use App\Livewire\Dosen\Kp\SeminarApprovalIndex;
use App\Livewire\Bapendik\Kp\SeminarJadwalPage;

use App\Livewire\Dosen\Kp\PenilaianForm as DosenPenilaianForm;
use App\Livewire\Mahasiswa\KP\NilaiIndex as MhsNilaiIndex;
use App\Livewire\Bapendik\Kp\NilaiIndex as BapNilaiIndex;
use App\Livewire\Komisi\Kp\NilaiIndex   as KomisiNilaiIndex;

use App\Livewire\Mahasiswa\DashboardPage as MhsDashboardPage;
use App\Livewire\Bapendik\DashboardPage  as BapDashboardPage;
use App\Livewire\Bapendik\Kp\SpkPage;
use App\Livewire\Bapendik\SuratPengantar\ValidasiPage;
use App\Livewire\Dosen\DashboardPage     as DspDashboardPage;
use App\Livewire\Komisi\DashboardPage    as KomisiDashboardPage;
use App\Livewire\Komisi\Kp\ReviewPage;
use App\Livewire\Mahasiswa\Kp\Page as MhsKpPage;
use App\Livewire\Mahasiswa\SuratPengantar\Page as SuratPengantarPage;
use App\Livewire\Notifications\Index as NotificationsIndex;

/*
|--------------------------------------------------------------------------
| Helper redirect role-based
|--------------------------------------------------------------------------
*/

$goToRoleDashboard = function () {
    $user = Auth::user();
    if (!$user) return redirect()->route('login');

    // Prioritas ketika punya dua role
    if ($user->hasRole('Mahasiswa')) return redirect()->route('mhs.dashboard');
    if ($user->hasRole('Bapendik'))  return redirect()->route('bap.dashboard');

    // ✅ Jika punya dua-duanya (Komisi & Pembimbing), arahkan ke Komisi
    if ($user->hasRole('Dosen Komisi')) return redirect()->route('komisi.dashboard');
    if ($user->hasRole('Dosen Pembimbing')) return redirect()->route('dsp.dashboard');

    return redirect()->route('login');
};

/*
|--------------------------------------------------------------------------
| Root
|--------------------------------------------------------------------------
| Kalau sudah login → ke dashboard sesuai role, jika belum → ke login.
*/
Route::get('/', function () use ($goToRoleDashboard) {
    return Auth::check()
        ? $goToRoleDashboard()
        : redirect()->route('login');
})->name('home');

/*
|--------------------------------------------------------------------------
| /dashboard
|--------------------------------------------------------------------------
| Fortify biasanya mendarat di /dashboard -> arahkan sesuai role.
*/
Route::get('/dashboard', function () use ($goToRoleDashboard) {
    return $goToRoleDashboard();
})->middleware(['auth', 'verified'])->name('dashboard');

/** =================== Fallback umum (semua role bisa pakai) =================== */
Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile',   Profile::class)->name('profile.edit');
    Route::get('settings/password',  Password::class)->name('user-password.edit');
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

    // >>> Fallback route notifikasi umum
    Route::get('/notifikasi', NotificationsIndex::class)->name('notifications');
});

/** =================== Mahasiswa =================== */
Route::prefix('mhs')
    ->middleware(['auth', 'role:Mahasiswa'])
    ->group(function () {
        Route::get('/dashboard', MhsDashboardPage::class)->name('mhs.dashboard');
        Route::get('/surat-pengantar', SuratPengantarPage::class)->name('mhs.sp.index');
        Route::get('/surat-pengantar/{sp}/download-docx', [SpDownloadController::class, 'downloadDocxForMahasiswa'])->name('mhs.sp.download.docx');
        Route::get('/kp', MhsKpPage::class)->name('mhs.kp.index');
        Route::get('/kp/{kp}/download-docx', [DownloadSpkController::class, 'downloadDocxForMahasiswa'])->name('mhs.kp.download.docx');
        Route::get('/kp/{kp}/konsultasi', MhsKpKonsultasiPage::class)->name('mhs.kp.konsultasi');
        Route::get('/kp/{kp}/seminar', SeminarDaftarPage::class)->name('mhs.kp.seminar');
        Route::get('/kp/{kp}/seminar/{seminar}/download-ba', [BaDownloadController::class, 'downloadForMahasiswa'])->name('mhs.kp.seminar.download.ba');
        Route::get('/nilai', MhsNilaiIndex::class)->name('mhs.nilai');

        // >>> Route notifikasi khusus Mahasiswa
        Route::get('/notifikasi', NotificationsIndex::class)->name('mhs.notifikasi');
    });

/** =================== Bapendik =================== */
Route::prefix('bap')
    ->middleware(['auth', 'role:Bapendik'])
    ->group(function () {
        Route::get('/dashboard', BapDashboardPage::class)->name('bap.dashboard');
        Route::get('/surat-pengantar/validasi', ValidasiPage::class)->name('bap.sp.validasi');
        Route::get('/surat-pengantar/{sp}/download-docx', [SpDownloadController::class, 'downloadDocxForBapendik'])->name('bap.sp.download.docx');
        Route::view('/penandatangan', 'bap.penandatangan.index')->name('bap.penandatangan.index');
        Route::get('/kp/spk', SpkPage::class)->name('bap.kp.spk');
        Route::get('/kp/{kp}/download-docx', [DownloadSpkController::class, 'downloadDocxForBapendik'])->name('bap.kp.download.docx');
        Route::get('/kp/seminar/jadwal', SeminarJadwalPage::class)->name('bap.kp.seminar.jadwal');
        Route::get('/kp/seminar/{seminar}/download-ba', [BaDownloadController::class, 'downloadForBapendik'])->name('bap.kp.seminar.download.ba');
        Route::get('/kp/nilai', BapNilaiIndex::class)->name('bap.kp.nilai');

        Route::middleware(['permission:masterdata.manage'])->group(function () {
            Route::get('/master/dosen',     BapDosenIndex::class)->name('bap.master.dosen');
            Route::get('/master/mahasiswa', BapMahasiswaIndex::class)->name('bap.master.mahasiswa');
            Route::get('/master/ruangan',   BapRuanganIndex::class)->name('bap.master.ruangan');
        });

        // >>> Route notifikasi khusus Bapendik
        Route::get('/notifikasi', NotificationsIndex::class)->name('bap.notifikasi');
    });

/** =================== Dosen Pembimbing =================== */
Route::prefix('dsp')
    ->middleware(['auth', 'role:Dosen Pembimbing|Dosen Komisi']) // <-- tanpa tanda kutip
    ->group(function () {
        Route::get('/dashboard', \App\Livewire\Dosen\DashboardPage::class)->name('dsp.dashboard');
        Route::get('/kp/konsultasi', \App\Livewire\Dosen\Kp\KonsultasiIndex::class)->name('dsp.kp.konsultasi');
        Route::get('/kp/seminar', \App\Livewire\Dosen\Kp\SeminarApprovalIndex::class)->name('dsp.kp.seminar.approval');
        Route::get('/kp/seminar/{seminar}/download-ba', [\App\Http\Controllers\KP\DownloadBaController::class, 'downloadForDospem'])->name('dsp.kp.seminar.download.ba');
        Route::redirect('/mhs', '/dsp/kp/konsultasi')->name('dsp.mhs');
        Route::view('/laporan', 'dsp.laporan.index')->name('dsp.laporan');
        Route::view('/kalender', 'dsp.kalender.index')->name('dsp.kalender');
        Route::get('/nilai', \App\Livewire\Dosen\Kp\PenilaianForm::class)->name('dsp.nilai');

        Route::get('/notifikasi', \App\Livewire\Notifications\Index::class)->name('dsp.notifikasi');
    });

/** =================== Dosen Komisi =================== */
Route::prefix('komisi')
    ->middleware(['auth', 'permission:kp.review'])
    ->group(function () {
        Route::get('/dashboard', KomisiDashboardPage::class)->name('komisi.dashboard');
        Route::get('/kp/review', ReviewPage::class)->name('komisi.kp.review');
        Route::get('/kp/{kp}/download-docx', [DownloadSpkController::class, 'downloadDocxForKomisi'])->name('komisi.kp.download.docx');
        Route::get('/kp/nilai', KomisiNilaiIndex::class)->name('komisi.kp.nilai');

        // >>> Route notifikasi khusus Komisi
        Route::get('/notifikasi', NotificationsIndex::class)->name('komisi.notifikasi');
    });

/** =================== Verifikasi publik =================== */
Route::get('/sp/verify/{token}', [SpVerifyController::class, 'show'])->name('sp.verify');
Route::get('/verify/spk/{token}', SpkVerifyController::class)->name('spk.verify');
Route::get('/verify/ba/{token}', BaVerifyController::class)->name('ba.verify');
