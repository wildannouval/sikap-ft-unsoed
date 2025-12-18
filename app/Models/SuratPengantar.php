<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // <--- PASTIKAN INI ADA
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratPengantar extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $table = 'surat_pengantars';

    public const ST_DIAJUKAN    = 'Diajukan';
    public const ST_DITERBITKAN = 'Diterbitkan';
    public const ST_DITOLAK     = 'Ditolak';

    protected $fillable = [
        'uuid',
        'nomor_surat',
        'mahasiswa_id',
        'lokasi_surat_pengantar',
        'penerima_surat_pengantar',
        'alamat_surat_pengantar',
        'tembusan_surat_pengantar',
        'status_surat_pengantar',
        'tanggal_pengajuan_surat_pengantar',
        'tanggal_disetujui_surat_pengantar',
        'tanggal_pengambilan_surat_pengantar',
        'catatan_surat',
        'qr_token',
        'qr_expires_at',
        'ttd_signed_at',
        'ttd_signed_by',
        'signatory_id',
        'ttd_signed_by_name',
        'ttd_signed_by_position',
        'ttd_signed_by_nip',
    ];

    protected $casts = [
        'tanggal_pengajuan_surat_pengantar' => 'datetime',
        'tanggal_disetujui_surat_pengantar' => 'datetime',
        'qr_expires_at'                     => 'datetime',
        'ttd_signed_at'                     => 'datetime',
    ];

    public function mahasiswa(): BelongsTo
    {
        // FK: surat_pengantars.mahasiswa_id -> mahasiswa.mahasiswa_id
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id', 'mahasiswa_id');
    }

    public function signatory(): BelongsTo
    {
        return $this->belongsTo(Signatory::class, 'signatory_id');
    }
}
