<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KerjaPraktik extends Model
{
    use HasFactory;

    protected $fillable = [
        'mahasiswa_id',
        'judul_kp',
        'lokasi_kp',
        'proposal_path',
        'surat_keterangan_path',
        'status',
        'catatan',
        'dosen_pembimbing_id',
        'dosen_komisi_id',
        'nomor_spk',
        'tanggal_terbit_spk',
        'signatory_id',
        'ttd_signed_at',
        'ttd_signed_by_name',
        'ttd_signed_by_position',
        'ttd_signed_by_nip',
        'spk_qr_token',
        'spk_qr_expires_at',
    ];

    protected $casts = [
        'tanggal_terbit_spk' => 'date',
        'ttd_signed_at'      => 'datetime',
        'spk_qr_expires_at'  => 'datetime',
    ];

    public const ST_REVIEW_KOMISI       = 'review_komisi';
    public const ST_REVIEW_BAPENDIK     = 'review_bapendik';
    public const ST_DITOLAK             = 'ditolak';
    public const ST_SPK_TERBIT          = 'spk_terbit';
    public const ST_KP_BERJALAN         = 'kp_sedang_berjalan';
    public const ST_SEMINAR_DIAJUKAN    = 'seminar_diajukan';
    public const ST_SEMINAR_DIJADWALKAN = 'seminar_dijadwalkan';
    public const ST_NILAI_TERBIT        = 'nilai_terbit';

    public static function statuses(): array
    {
        return [
            self::ST_REVIEW_KOMISI,
            self::ST_REVIEW_BAPENDIK,
            self::ST_DITOLAK,
            self::ST_SPK_TERBIT,
            self::ST_KP_BERJALAN,
            self::ST_SEMINAR_DIAJUKAN,
            self::ST_SEMINAR_DIJADWALKAN,
            self::ST_NILAI_TERBIT,
        ];
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::ST_REVIEW_KOMISI       => 'Menunggu Review Komisi',
            self::ST_REVIEW_BAPENDIK     => 'Menunggu Terbit SPK',
            self::ST_DITOLAK             => 'Ditolak',
            self::ST_SPK_TERBIT          => 'SPK Terbit',
            self::ST_KP_BERJALAN         => 'KP Sedang Berjalan',
            self::ST_SEMINAR_DIAJUKAN    => 'Seminar Diajukan',
            self::ST_SEMINAR_DIJADWALKAN => 'Seminar Dijadwalkan',
            self::ST_NILAI_TERBIT        => 'Nilai Terbit',
            default                      => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    public static function badgeColor(string $status): string
    {
        return match ($status) {
            self::ST_REVIEW_KOMISI       => 'blue',
            self::ST_REVIEW_BAPENDIK     => 'amber',
            self::ST_DITOLAK             => 'red',
            self::ST_SPK_TERBIT          => 'green',
            self::ST_KP_BERJALAN         => 'fuchsia',
            self::ST_SEMINAR_DIAJUKAN    => 'sky',
            self::ST_SEMINAR_DIJADWALKAN => 'purple',
            self::ST_NILAI_TERBIT        => 'emerald',
            default                      => 'zinc',
        };
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id', 'mahasiswa_id');
    }

    public function dosenPembimbing(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'dosen_pembimbing_id', 'dosen_id');
    }

    public function dosenKomisi(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'dosen_komisi_id', 'dosen_id');
    }

    public function signatory(): BelongsTo
    {
        return $this->belongsTo(Signatory::class, 'signatory_id', 'id');
    }

    public function consultations()
    {
        return $this->hasMany(KpConsultation::class, 'kerja_praktik_id');
    }

    /** Konsultasi yang sudah diverifikasi */
    public function verifiedConsultations()
    {
        return $this->consultations()->whereNotNull('verified_at');
    }

    /** Hitung konsultasi terverifikasi TANPA bergantung scope eksternal */
    public function verifiedConsultationsCount(): int
    {
        return (int) $this->verifiedConsultations()->count();
    }

    public function canRegisterSeminar(): bool
    {
        return $this->verifiedConsultationsCount() >= 6;
    }
}
