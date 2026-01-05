<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class KpGradeExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function query()
    {
        // Mengambil query dari controller tanpa pagination
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'No',
            'NIM',
            'Nama Mahasiswa',
            'Judul Laporan',
            'Dosen Pembimbing',
            'Status',
            'Nilai Angka',
            'Nilai Huruf',
            'Tanggal Update',
        ];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row->kp?->mahasiswa?->mahasiswa_nim ?? '—',
            $row->kp?->mahasiswa?->user?->name ?? '—',
            $row->judul_laporan,
            $row->kp?->dosenPembimbing?->user?->name ?? '—',
            ucfirst(str_replace('_', ' ', $row->status)),
            $row->grade?->final_score ?? '—',
            $row->grade?->final_letter ?? '—',
            $row->updated_at->format('d/m/Y'),
        ];
    }
}
