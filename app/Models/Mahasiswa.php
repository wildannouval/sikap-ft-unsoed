<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $table = 'mahasiswas';
    protected $primaryKey = 'mahasiswa_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'jurusan_id',
        'mahasiswa_name',
        'mahasiswa_nim',
        'mahasiswa_tahun_angkatan',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id', 'id');
    }

    public function suratPengantar(): HasMany
    {
        return $this->hasMany(SuratPengantar::class, 'mahasiswa_id', 'mahasiswa_id');
    }

    protected static function booted(): void
    {
        static::creating(function (Mahasiswa $model) {
            if (is_null($model->user_id)) {
                $local = $model->mahasiswa_nim
                    ? strtolower($model->mahasiswa_nim)
                    : Str::of($model->mahasiswa_name)->lower()->replaceMatches('/[^a-z]/', '.')->squish()->value();

                $email = $local . '@mhs.unsoed.ac.id';

                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name'     => $model->mahasiswa_name,
                        'password' => Hash::make('password'),
                    ]
                );

                if (method_exists($user, 'assignRole')) {
                    $user->assignRole('Mahasiswa');
                }

                $model->user_id = $user->id;
            }
        });
    }
}
