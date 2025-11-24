<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles;

    protected $guard_name = 'web';

    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_photo_path', // <-- tambahkan ini
    ];

    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** Inisial nama */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /** URL foto profil (fallback inisial) */
    public function profilePhotoUrl(): ?string
    {
        if (!$this->profile_photo_path) return null;

        // Jika path sudah absolute (http...), kembalikan apa adanya.
        if (Str::startsWith($this->profile_photo_path, ['http://', 'https://', '/storage/'])) {
            return $this->profile_photo_path;
        }

        // Anggap disimpan di disk 'public'
        return Storage::disk('public')->url($this->profile_photo_path);
    }

    public function mahasiswa()
    {
        return $this->hasOne(Mahasiswa::class, 'user_id', 'id');
    }

    public function dosen()
    {
        return $this->hasOne(Dosen::class, 'user_id', 'id');
    }
}
