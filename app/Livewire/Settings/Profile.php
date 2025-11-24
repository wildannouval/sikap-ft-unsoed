<?php

namespace App\Livewire\Settings;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class Profile extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $email = '';

    /** Foto baru (upload) */
    public $photo = null;

    /** Foto yang sedang dipakai (URL) */
    public ?string $photo_url = null;

    /** Hanya Bapendik yang boleh edit profile (name/email/delete) */
    public bool $canEditProfile = false;

    public function mount(): void
    {
        $user = Auth::user();

        $this->name  = $user->name;
        $this->email = $user->email;

        $this->photo_url = $user->profilePhotoUrl();

        $this->canEditProfile = $user->hasRole('Bapendik'); // <-- aturan utamanya
    }

    /**
     * Update profile (name/email) — hanya Bapendik.
     */
    public function updateProfileInformation(): void
    {
        if (!$this->canEditProfile) {
            // Lindungi server-side: tolak kalau bukan Bapendik
            abort(403, 'Anda tidak berwenang mengubah profil.');
        }

        $user = Auth::user();

        $validated = $this->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Upload / ganti foto profil (semua role boleh ganti fotonya sendiri).
     * Besaran file dan tipe file dibatasi.
     */
    public function updateProfilePhoto(): void
    {
        $this->validate([
            'photo' => ['nullable', 'image', 'max:2048', 'mimes:jpg,jpeg,png,webp'],
        ]);

        $user = Auth::user();

        if ($this->photo) {
            // Hapus file lama jika ada
            if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            // Simpan file baru ke disk 'public'
            $path = $this->photo->store('profile-photos', 'public');

            $user->forceFill(['profile_photo_path' => $path])->save();

            // Refresh URL foto di UI
            $this->photo_url = $user->profilePhotoUrl();

            // Reset input file
            $this->reset('photo');

            $this->dispatch('profile-photo-updated', url: $user->profilePhotoUrl());
        }
    }

    /**
     * Hapus foto profil (semua role boleh hapus fotonya sendiri).
     */
    public function removeProfilePhoto(): void
    {
        $user = Auth::user();

        if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $user->forceFill(['profile_photo_path' => null])->save();

        $this->photo_url = null;
        $this->reset('photo');

        $this->dispatch('profile-photo-removed');
    }

    /**
     * Kirim ulang verifikasi email (default bawaan).
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));
            return;
        }

        $user->sendEmailVerificationNotification();
        Session::flash('status', 'verification-link-sent');
    }

    public function render()
    {
        return view('livewire.settings.profile', [
            'user' => Auth::user(),
        ]);
    }
}
