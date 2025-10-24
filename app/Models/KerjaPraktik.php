<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KerjaPraktik extends Model
{
    protected $fillable = [
        'mahasiswa_id',
        'judul_kp',
        'lokasi_kp',
        'proposal_path',
        'surat_keterangan_path',
        'status',
        'catatan',
        'dosen_pembimbing_id',
        // kolom SPK:
        'nomor_spk',
        'tanggal_terbit_spk',
        'signatory_id',
        'ttd_signed_at',
        'ttd_signed_by_name',
        'ttd_signed_by_position',
        'ttd_signed_by_nip',
        'spk_qr_token',
    ];
    
    protected $casts = [
        'tanggal_terbit_spk' => 'date',
        'ttd_signed_at'       => 'datetime',
    ];

    // Status constants
    public const ST_REVIEW_KOMISI   = 'review_komisi';
    public const ST_REVIEW_BAPENDIK = 'review_bapendik';
    public const ST_DITOLAK         = 'ditolak';
    public const ST_SPK_TERBIT      = 'spk_terbit';

    public static function statuses(): array
    {
        return [
            self::ST_REVIEW_KOMISI,
            self::ST_REVIEW_BAPENDIK,
            self::ST_DITOLAK,
            self::ST_SPK_TERBIT,
        ];
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::ST_REVIEW_KOMISI   => 'Menunggu Review Komisi',
            self::ST_REVIEW_BAPENDIK => 'Menunggu Terbit SPK',
            self::ST_DITOLAK         => 'Ditolak',
            self::ST_SPK_TERBIT      => 'SPK Terbit',
            default                  => ucfirst(str_replace('_',' ',$status)),
        };
    }

    public static function badgeColor(string $status): string
    {
        return match ($status) {
            self::ST_REVIEW_KOMISI   => 'blue',
            self::ST_REVIEW_BAPENDIK => 'amber',
            self::ST_DITOLAK         => 'red',
            self::ST_SPK_TERBIT      => 'green',
            default                  => 'zinc',
        };
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }
    public function dosenPembimbing()
    { 
        return $this->belongsTo(\App\Models\Dosen::class, 'dosen_pembimbing_id'); 
    }
    
    public function dosenKomisi()
    {
        return $this->belongsTo(Dosen::class, 'dosen_komisi_id');
    }
    public function signatory()
    {
        return $this->belongsTo(Signatory::class);
    }

}
