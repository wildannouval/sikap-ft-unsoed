<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class Dosen extends Model
{
    use HasFactory;

    protected $table = 'dosens';
    protected $primaryKey = 'dosen_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'dosen_name',
        'dosen_nip',
        'jurusan_id',
        'is_komisi_kp',
    ];

    protected $casts = [
        'is_komisi_kp' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id', 'id');
    }

    protected static function booted(): void
    {
        static::creating(function (Dosen $model) {
            if (is_null($model->user_id)) {
                $local = Str::of($model->dosen_name)
                    ->lower()
                    ->replaceMatches('/[^a-z]/', '.')
                    ->squish()
                    ->value();

                $email = $local . '@unsoed.ac.id';

                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name'     => $model->dosen_name,
                        'password' => Hash::make('password'),
                    ]
                );

                if (method_exists($user, 'assignRole')) {
                    $user->assignRole('Dosen Pembimbing');
                }

                $model->user_id = $user->id;
            }
        });
    }
}
