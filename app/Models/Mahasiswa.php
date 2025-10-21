<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    protected $fillable = [
        'user_id',
        'jurusan_id',
        'nama_mahasiswa',
        'nim',
        'tahun_angkatan',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function suratPengantars()
    {
        return $this->hasMany(SuratPengantar::class);
    }
}
