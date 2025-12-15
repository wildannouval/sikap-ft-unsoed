<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class KpSeminar extends Model
{
    use HasFactory;

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
        'nomor_ba',
        'tanggal_ba',
        'signatory_id',
        'ttd_signed_by_name',
        'ttd_signed_by_position',
        'ttd_signed_by_nip',
        'ba_qr_token',
        'ba_qr_expires_at',
        'ba_scan_path',
        'approved_by_dospem_at',
        'rejected_by_dospem_at',
        'rejected_reason',
        'distribusi_proof_path',
        'distribusi_uploaded_at',
        'berkas_laporan_path',
    ];

    protected $casts = [
        'tanggal_seminar'        => 'datetime',
        'tanggal_ba'             => 'datetime',
        'approved_by_dospem_at'  => 'datetime',
        'rejected_by_dospem_at'  => 'datetime',
        'ba_qr_expires_at'       => 'datetime',
        'created_at'             => 'datetime',
        'updated_at'             => 'datetime',
        'distribusi_uploaded_at' => 'datetime',
    ];

    /**
     * Konstanta status
     */
    public const ST_DIAJUKAN              = 'diajukan';
    public const ST_DISETUJUI_PEMBIMBING  = 'disetujui_pembimbing';
    public const ST_DITOLAK               = 'ditolak';
    public const ST_DIJADWALKAN           = 'dijadwalkan';
    public const ST_SELESAI               = 'selesai';
    public const ST_REVISI                = 'revisi';
    public const ST_GAGAL                 = 'gagal';
    public const ST_BA_TERBIT             = 'ba_terbit';
    public const ST_DINILAI               = 'dinilai';

    public static function badgeColor(string $st): string
    {
        return match ($st) {
            self::ST_DIAJUKAN                => 'zinc',
            self::ST_DISETUJUI_PEMBIMBING    => 'sky',
            self::ST_DIJADWALKAN             => 'emerald',
            self::ST_SELESAI                 => 'teal',
            self::ST_REVISI                  => 'amber',
            self::ST_BA_TERBIT               => 'violet',
            self::ST_DINILAI                 => 'purple',
            self::ST_DITOLAK, self::ST_GAGAL => 'rose',
            default                          => 'zinc',
        };
    }

    public static function statusLabel(string $st): string
    {
        return match ($st) {
            self::ST_DIAJUKAN               => 'Menunggu ACC',
            self::ST_DISETUJUI_PEMBIMBING   => 'Disetujui Pembimbing',
            self::ST_DIJADWALKAN            => 'Dijadwalkan',
            self::ST_SELESAI                => 'Selesai Seminar',
            self::ST_REVISI                 => 'Revisi Laporan',
            self::ST_BA_TERBIT              => 'BA Terbit',
            self::ST_DINILAI                => 'Dinilai',
            self::ST_DITOLAK                => 'Ditolak',
            self::ST_GAGAL                  => 'Gagal/Batal',
            default                         => ucfirst($st),
        };
    }

    // Relasi
    public function kp(): BelongsTo
    {
        return $this->belongsTo(KerjaPraktik::class, 'kerja_praktik_id');
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id', 'mahasiswa_id');
    }

    public function dosenPembimbing(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'dosen_pembimbing_id', 'dosen_id');
    }

    public function grade(): HasOne
    {
        return $this->hasOne(KpGrade::class, 'kp_seminar_id');
    }
}
