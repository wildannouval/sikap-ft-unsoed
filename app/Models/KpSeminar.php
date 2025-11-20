<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class KpSeminar extends Model
{
    protected $table = 'kp_seminars';
    protected $guarded = [];

    protected $fillable = [
        'kerja_praktik_id',
        'mahasiswa_id',
        'dosen_pembimbing_id',
        'judul_laporan',
        'abstrak',
        'tanggal_seminar',
        'jam_mulai',
        'jam_selesai',
        'ruangan_id',
        'ruangan_nama',
        'status',

        // BA (surat/berita acara) & verifikasi
        'nomor_ba',
        'tanggal_ba',
        'signatory_id',
        'ttd_signed_by_name',
        'ttd_signed_by_position',
        'ttd_signed_by_nip',
        'ba_qr_token',
        'ba_qr_expires_at',
        'ba_scan_path',          // <— kolom baru (scan BA yang diunggah dospem)

        // meta persetujuan
        'approved_by_dospem_at',
        'rejected_by_dospem_at',
        'rejected_reason',

        // new
        'distribusi_proof_path',
        'distribusi_uploaded_at',
    ];

    protected $casts = [
        'tanggal_seminar'      => 'datetime',
        'tanggal_ba'           => 'datetime',
        'approved_by_dospem_at' => 'datetime',
        'rejected_by_dospem_at' => 'datetime',
        'ba_qr_expires_at'     => 'datetime',
        'created_at'           => 'datetime',
        'updated_at'           => 'datetime',
        'distribusi_uploaded_at' => 'datetime',
    ];

    /**
     * Konstanta status
     */
    public const ST_DIAJUKAN              = 'diajukan';
    public const ST_DISETUJUI_PEMBIMBING  = 'disetujui_pembimbing';
    public const ST_DITOLAK               = 'ditolak';
    public const ST_DIJADWALKAN           = 'dijadwalkan';
    public const ST_BA_TERBIT             = 'ba_terbit';
    public const ST_DINILAI               = 'dinilai'; // <— dipakai modul penilaian

    /**
     * Helper warna badge untuk Flux UI
     */
    public static function badgeColor(string $st): string
    {
        return match ($st) {
            self::ST_DIAJUKAN             => 'zinc',
            self::ST_DISETUJUI_PEMBIMBING => 'blue',
            self::ST_DIJADWALKAN          => 'amber',
            self::ST_BA_TERBIT            => 'green',
            self::ST_DINILAI              => 'purple',
            self::ST_DITOLAK              => 'red',
            default                       => 'zinc',
        };
    }

    /**
     * Helper label status yang rapi di UI
     */
    public static function statusLabel(string $st): string
    {
        return match ($st) {
            self::ST_DIAJUKAN             => 'Menunggu ACC',
            self::ST_DISETUJUI_PEMBIMBING => 'Disetujui Pembimbing',
            self::ST_DIJADWALKAN          => 'Dijadwalkan',
            self::ST_BA_TERBIT            => 'BA Terbit',
            self::ST_DINILAI              => 'Dinilai',
            self::ST_DITOLAK              => 'Ditolak',
            default                       => ucfirst($st),
        };
    }

    public static function allStatuses(): array
    {
        return [
            self::ST_DIAJUKAN,
            self::ST_DISETUJUI_PEMBIMBING,
            self::ST_DIJADWALKAN,
            self::ST_BA_TERBIT,
            self::ST_DINILAI,
            self::ST_DITOLAK,
        ];
    }

    /**
     * Relasi ke KerjaPraktik (dipakai: ->with(['kp.mahasiswa.user', ...]))
     */
    public function kp(): BelongsTo
    {
        return $this->belongsTo(KerjaPraktik::class, 'kerja_praktik_id');
    }

    /**
     * Relasi ke Mahasiswa (opsional, jika sering dipakai langsung)
     */
    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id', 'mahasiswa_id');
    }

    /**
     * Relasi ke Dosen Pembimbing (sesuaikan model/PK kolom pada proyekmu)
     * Biasanya tabel dosen memakai PK `dosen_id`.
     */
    public function dosenPembimbing(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'dosen_pembimbing_id', 'dosen_id');
    }

    /**
     * ✅ Relasi yang hilang: grade (HAS ONE)
     * foreign key pada tabel kp_grades: kp_seminar_id
     */
    public function grade(): HasOne
    {
        return $this->hasOne(KpGrade::class, 'kp_seminar_id');
    }
}
