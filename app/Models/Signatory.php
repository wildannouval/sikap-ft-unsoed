<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Signatory extends Model
{
    protected $fillable = [
    'name',
    'position',
    'nip',
];

    public function scopeActive($q) { return $q->where('is_active', true); }
}
