<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratPengantar extends Model
{
    public const ST_DIAJUKAN   = 'Diajukan';
    public const ST_DITERBITKAN= 'Diterbitkan';
    public const ST_DITOLAK    = 'Ditolak';

    protected $fillable = [
        'uuid','nomor_surat','mahasiswa_id','lokasi_surat_pengantar',
        'penerima_surat_pengantar','alamat_surat_pengantar','tembusan_surat_pengantar',
        'status_surat_pengantar','tanggal_pengajuan_surat_pengantar',
        'tanggal_disetujui_surat_pengantar','tanggal_pengambilan_surat_pengantar',
        'catatan_surat','qr_token','qr_expires_at','ttd_signed_at','ttd_signed_by'
    ];

    protected $casts = [
    'tanggal_pengajuan_surat_pengantar'    => 'date',
    'tanggal_disetujui_surat_pengantar'    => 'date',
    'qr_expires_at'                        => 'datetime',
    'ttd_signed_at'                        => 'datetime',
    ];  

    public function mahasiswa() 
    { 
        return $this->belongsTo(Mahasiswa::class); 
    }

    public function signatory()
    { 
        return $this->belongsTo(Signatory::class);
    }

}
