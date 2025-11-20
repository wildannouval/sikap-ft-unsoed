<?php

namespace App\Services;

use App\Models\Signatory;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\Element\TextRun;
use RuntimeException;
use Illuminate\Support\Facades\Log;

// QR (pakai BaconQrCode + fallback)
use BaconQrCode\Writer;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;

class BaDocxBuilder
{
    public function __construct(
        protected $seminar,                         // App\Models\KpSeminar (sudah eager load relasi bila perlu)
        protected ?Signatory $signatory = null,
        protected string $templateName = 'TEMPLATE_BA.docx', // nama template
    ) {}

    /* ===================== Helpers path ===================== */

    protected function templatePath(): string
    {
        $path = storage_path('app/templates/' . $this->templateName);
        if (! file_exists($path)) {
            throw new RuntimeException("Template BA tidak ditemukan: {$path}");
        }
        return $path;
    }

    protected function tmpDir(): string
    {
        $dir = storage_path('app/tmp');
        if (! is_dir($dir)) @mkdir($dir, 0777, true);
        return $dir;
    }

    protected function tmpPath(string $name): string
    {
        return $this->tmpDir() . DIRECTORY_SEPARATOR . $name;
    }

    protected function normalize(string $path): string
    {
        return str_replace('\\', '/', $path);
    }

    protected function setIfPresent(TemplateProcessor $tpl, array $vars, string $key, $value): void
    {
        if (in_array($key, $vars, true)) $tpl->setValue($key, $value);
    }

    /* ===================== Helpers format tanggal ===================== */

    protected function indoDate(\DateTimeInterface $dt): string
    {
        $bulan = [
            1 => 'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        ];
        $n = (int)$dt->format('n');
        return $dt->format('d') . ' ' . ($bulan[$n] ?? $dt->format('F')) . ' ' . $dt->format('Y');
    }

    protected function indoTime(\DateTimeInterface $dt): string
    {
        return $dt->format('H:i') . ' WIB';
    }

    /* ===================== QR ===================== */

    protected function generateQr(string $text): string
    {
        $path = $this->tmpPath('qr_ba_' . $this->seminar->id . '_' . time() . '.png');

        try {
            if (class_exists(ImageRenderer::class)) {
                $backend = null;
                if (extension_loaded('imagick') && class_exists('\BaconQrCode\Renderer\Image\ImagickImageBackEnd')) {
                    $backend = new \BaconQrCode\Renderer\Image\ImagickImageBackEnd();
                } elseif (extension_loaded('gd') && class_exists('\BaconQrCode\Renderer\Image\GdImageBackEnd')) {
                    $backend = new \BaconQrCode\Renderer\Image\GdImageBackEnd();
                }
                if ($backend) {
                    $renderer = new ImageRenderer(new RendererStyle(360), $backend);
                    $writer   = new Writer($renderer);
                    file_put_contents($path, $writer->writeString($text));
                    return $path;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('[BA DOCX] QR modern fail: ' . $e->getMessage());
        }

        // Fallback HTTP (tanpa dependency ekstensi)
        $url = 'https://api.qrserver.com/v1/create-qr-code/?size=360x360&margin=0&data=' . rawurlencode($text);
        $png = @file_get_contents($url);
        if ($png) file_put_contents($path, $png);
        return $path;
    }

    /* ===================== Builder utama ===================== */

    public function buildDocx(): string
    {
        $tpl  = new TemplateProcessor($this->templatePath());
        $vars = method_exists($tpl, 'getVariables') ? $tpl->getVariables() : [];

        // Validasi placeholder gambar QR wajib ada
        if (! in_array('qr_code_ba', $vars, true)) {
            throw new RuntimeException("Placeholder \${qr_code_ba} tidak ada di template BA.");
        }

        $kp   = $this->seminar->kp;
        $mhs  = $kp?->mahasiswa;

        // Pastikan token QR ada
        if (! $this->seminar->ba_qr_token) {
            $this->seminar->ba_qr_token      = (string) Str::uuid();
            $this->seminar->ba_qr_expires_at = now()->addDays(180);
            $this->seminar->save();
        }

        $verifyUrl = route('ba.verify', ['token' => $this->seminar->ba_qr_token]);

        // Ambil value dari model
        $namaMhs   = $mhs?->user?->name ?? $mhs?->nama_mahasiswa ?? '-';
        $nimMhs    = $mhs?->nim ?? $mhs?->mahasiswa_nim ?? '-';
        $judulKP   = $this->seminar->judul_laporan ?? $kp?->judul_kp ?? '-';

        $tglSem    = $this->seminar->tanggal_seminar ? \Carbon\Carbon::parse($this->seminar->tanggal_seminar) : null;
        $tglSemStr = $tglSem ? $this->indoDate($tglSem) : '-';
        $jamSemStr = $tglSem ? $this->indoTime($tglSem) : '-';

        $ruang     = $this->seminar->ruangan_nama ?? '-';

        $tglBA     = $this->seminar->tanggal_ba
            ? \Carbon\Carbon::parse($this->seminar->tanggal_ba)
            : ($tglSem ?: null);
        $tglBAStr  = $tglBA ? $this->indoDate($tglBA) : '-';

        $namaDosp  = $kp?->dosenPembimbing?->nama
            ?? $kp?->dosenPembimbing?->dosen_name
            ?? '-';
        $nipDosp   = $kp?->dosenPembimbing?->nip
            ?? $kp?->dosenPembimbing?->dosen_nip
            ?? '-';

        // ==== SET nilai sesuai placeholder template (10 item) ====
        $this->setIfPresent($tpl, $vars, 'nama_dosen_pembimbing',          $namaDosp);
        $this->setIfPresent($tpl, $vars, 'nama_mahasiswa',                  $namaMhs);
        $this->setIfPresent($tpl, $vars, 'nim_mahasiswa',                   $nimMhs);
        $this->setIfPresent($tpl, $vars, 'judul_kerja_praktik_mahasiswa',   $judulKP);
        $this->setIfPresent($tpl, $vars, 'tanggal_seminar',                 $tglSemStr);
        $this->setIfPresent($tpl, $vars, 'waktu_seminar',                   $jamSemStr);
        $this->setIfPresent($tpl, $vars, 'ruang_seminar',                   $ruang);
        $this->setIfPresent($tpl, $vars, 'tanggal_berita_acara',            $tglBAStr);
        // qr_code_ba -> setImageValue di bawah
        $this->setIfPresent($tpl, $vars, 'nip_dosen_pembimbing',            $nipDosp);

        // ==== QR ====
        $qr = $this->normalize($this->generateQr($verifyUrl));
        try {
            $tpl->setImageValue('qr_code_ba', ['path' => $qr, 'width' => 120, 'height' => 120]);
        } catch (\Throwable $e) {
            // Fallback untuk versi phpword tertentu
            $run = new TextRun();
            $run->addImage($qr, ['width' => 120, 'height' => 120]);
            $tpl->setComplexBlock('qr_code_ba', $run);
        }

        // ==== Simpan DOCX sementara ====
        $who     = trim(($namaMhs ?: 'mahasiswa') . '_' . $nimMhs);
        $outName = 'BA_Seminar_' . ($this->seminar->nomor_ba ?: 'NO-BA') . '_' . Str::slug($who) . '.docx';
        $outPath = $this->normalize($this->tmpPath($outName));
        $tpl->saveAs($outPath);

        return $outPath;
    }
}
