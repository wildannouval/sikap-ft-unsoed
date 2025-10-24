<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpSubmission extends Model
{
    protected $fillable = [
        'mahasiswa_id',
        'judul_kp','instansi','lokasi',
        'file_proposal_path','file_suket_path',
        'status',
        'dosen_komisi_id','dosen_pembimbing_id','catatan_review',
        'nomor_spk','tanggal_terbit_spk',
        'signatory_id','ttd_signed_at',
        'ttd_signed_by_name','ttd_signed_by_position','ttd_signed_by_nip',
        'qr_token','qr_expires_at',
    ];

    protected $casts = [
        'tanggal_terbit_spk' => 'date',
        'ttd_signed_at'      => 'datetime',
        'qr_expires_at'      => 'datetime',
    ];

    public function mahasiswa() { return $this->belongsTo(Mahasiswa::class); }
    public function dosenPembimbing() { return $this->belongsTo(Dosen::class, 'dosen_pembimbing_id'); }
    public function dosenKomisi() { return $this->belongsTo(Dosen::class, 'dosen_komisi_id'); }
    public function signatory() { return $this->belongsTo(Signatory::class); }
}
