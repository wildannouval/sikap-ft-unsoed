<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class KpConsultation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kp_consultations';

    protected $fillable = [
        'kerja_praktik_id',
        'mahasiswa_id',
        'dosen_pembimbing_id',
        'konsultasi_dengan',
        'tanggal_konsultasi',
        'topik_konsultasi',
        'hasil_konsultasi',
        'verified_at',
        'verified_by_dosen_id',
        'verified_note',
    ];

    protected $casts = [
        'tanggal_konsultasi' => 'date',
        'verified_at' => 'datetime',
    ];

    // ===== Relations =====
    public function kerjaPraktik(): BelongsTo
    {
        return $this->belongsTo(KerjaPraktik::class);
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function dosenPembimbing(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'dosen_pembimbing_id');
    }

    public function verifiedByDosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'verified_by_dosen_id', 'dosen_id');
    }

    // Scope: hanya yang sudah diverifikasi
    public function scopeVerified($q)
    {
        return $q->whereNotNull('verified_at');
    }

    public function  scopeUnverified($q)
    {
        return $q->whereNull('verified_at');
    }

    public function consultations()
    {
        return $this->hasMany(\App\Models\KpConsultation::class, 'kerja_praktik_id');
    }
}
