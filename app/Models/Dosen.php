<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
    protected $fillable = [
        'user_id','nama','nip','nidn','jabatan','email','telepon'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
