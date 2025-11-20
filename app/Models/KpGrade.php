<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpGrade extends Model
{
    protected $table = 'kp_grades';

    protected $fillable = [
        'kp_seminar_id',
        // dosen pembimbing
        'dospem_sistematika_laporan',
        'dospem_tata_bahasa',
        'dospem_sistematika_seminar',
        'dospem_kecocokan_isi',
        'dospem_materi_kp',
        'dospem_penguasaan_masalah',
        'dospem_diskusi',
        // pembimbing lapangan
        'pl_kesesuaian',
        'pl_kehadiran',
        'pl_kedisiplinan',
        'pl_keaktifan',
        'pl_kecermatan',
        'pl_tanggung_jawab',
        // rekap
        'score_dospem',
        'score_pl',
        'final_score',
        'final_letter',
        // meta
        'graded_by_user_id',
        'graded_at',
    ];

    protected $casts = [
        'graded_at'     => 'datetime',
        'score_dospem'  => 'float',
        'score_pl'      => 'float',
        'final_score'   => 'float',
    ];

    public function seminar(): BelongsTo
    {
        return $this->belongsTo(KpSeminar::class, 'kp_seminar_id');
    }
}
