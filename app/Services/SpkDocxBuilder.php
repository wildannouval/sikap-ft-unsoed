<?php

namespace App\Services;

use App\Models\Signatory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Element\TextRun;
use RuntimeException;

// bacon/bacon-qr-code
use BaconQrCode\Writer;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;

class SpkDocxBuilder
{
    public function __construct(
        protected $kp, // App\Models\KerjaPraktik
        protected ?Signatory $signatory = null,
        protected string $templateName = 'TEMPLATE_SPK.docx',
    ) {}

    protected function templatePath(): string
    {
        $path = storage_path('app/templates/' . $this->templateName);
        if (! file_exists($path)) {
            throw new RuntimeException("Template DOCX tidak ditemukan: {$path}");
        }
        return $path;
    }

    protected function tmpDir(): string
    {
        $dir = storage_path('app/tmp');
        if (! is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        return $dir;
    }

    protected function tmpPath(string $filename): string
    {
        return $this->tmpDir() . DIRECTORY_SEPARATOR . $filename;
    }

    protected function normalizePath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }

    protected function setIfPresent(TemplateProcessor $tpl, array $vars, string $key, $value): void
    {
        if (in_array($key, $vars, true)) {
            $tpl->setValue($key, $value);
        }
    }

    public function buildDocx(): string
    {
        $templatePath = $this->templatePath();
        $tpl = new TemplateProcessor($templatePath);
        $vars = method_exists($tpl, 'getVariables') ? $tpl->getVariables() : [];

        if (! in_array('qr_code_ttd', $vars, true)) {
            $list = empty($vars) ? '(tidak ada satu pun variable terdeteksi)' : implode(', ', $vars);
            throw new RuntimeException(
                "Placeholder \${qr_code_ttd} TIDAK ditemukan di template. " .
                    "Variables terdeteksi: {$list}."
            );
        }

        $mhs = $this->kp->mahasiswa ?? null;
        $jur = $mhs?->jurusan ?? null;
        $now = now();

        // siapkan token + masa berlaku QR jika kosong
        if (! $this->kp->spk_qr_token) {
            $this->kp->spk_qr_token      = (string) Str::uuid();
            $this->kp->spk_qr_expires_at = now()->addDays(180);
            $this->kp->save();
        }

        $verifyUrl = route('spk.verify', ['token' => $this->kp->spk_qr_token]);

        // ====== Isi placeholder ======
        $this->setIfPresent($tpl, $vars, 'nomor_spk', $this->kp->nomor_spk ?? '-');

        $tgl = optional($this->kp->tanggal_terbit_spk ?? $now)->translatedFormat('d F Y');
        foreach (['tanggal_spk', 'tanggal_terbit_spk', 'tanggal_penunjukan'] as $k) {
            $this->setIfPresent($tpl, $vars, $k, $tgl);
        }

        // === Identitas mahasiswa ===
        $namaMhs = $mhs?->user?->name
            ?? $mhs?->nama_mahasiswa
            ?? '-';

        $nimMhs  = $mhs?->mahasiswa_nim
            ?? '-';

        $jurusan = $jur?->nama_jurusan ?? '-';

        $this->setIfPresent($tpl, $vars, 'nama_mahasiswa', $namaMhs);
        $this->setIfPresent($tpl, $vars, 'nim_mahasiswa', $nimMhs);
        $this->setIfPresent($tpl, $vars, 'jurusan_mahasiswa', $jurusan);

        // === Data KP ===
        $this->setIfPresent($tpl, $vars, 'judul_kp', $this->kp->judul_kp ?? '-');
        $this->setIfPresent($tpl, $vars, 'judul_kerja_praktik_mahasiswa', $this->kp->judul_kp ?? '-');
        $this->setIfPresent($tpl, $vars, 'lokasi_kp', $this->kp->lokasi_kp ?? '-');

        // === Dosen Pembimbing & Komisi (pakai kolom kamu: dosen_name, dosen_nip) ===
        $pembimbingNama = $this->kp->dosenPembimbing?->dosen_name
            ?? $this->kp->dosen_pembimbing_nama
            ?? '-';

        $pembimbingNip  = $this->kp->dosenPembimbing?->dosen_nip
            ?? $this->kp->dosen_pembimbing_nip
            ?? '-';

        $komisiNama = $this->kp->dosenKomisi?->dosen_name
            ?? $this->kp->dosen_komisi_nama
            ?? '-';

        $komisiNip  = $this->kp->dosenKomisi?->dosen_nip
            ?? $this->kp->dosen_komisi_nip
            ?? '-';

        // nama
        $this->setIfPresent($tpl, $vars, 'dosen_pembimbing_nama', $pembimbingNama);
        $this->setIfPresent($tpl, $vars, 'nama_dosen_pembimbing', $pembimbingNama);
        $this->setIfPresent($tpl, $vars, 'dosen_komisi_nama', $komisiNama);
        $this->setIfPresent($tpl, $vars, 'nama_dosen_komisi', $komisiNama);

        // NIP
        $this->setIfPresent($tpl, $vars, 'nip_dosen_pembimbing', $pembimbingNip);
        $this->setIfPresent($tpl, $vars, 'nip_dosen_komisi', $komisiNip);

        // === Penandatangan
        $this->setIfPresent($tpl, $vars, 'penandatangan_nama', $this->signatory?->name ?? $this->kp->ttd_signed_by_name ?? '-');
        $this->setIfPresent($tpl, $vars, 'penandatangan_jabatan', $this->signatory?->position ?? $this->kp->ttd_signed_by_position ?? '-');
        $this->setIfPresent($tpl, $vars, 'penandatangan_nip', $this->signatory?->nip ?? $this->kp->ttd_signed_by_nip ?? '-');

        // ====== QR Code ======
        $qrPath = $this->generateQrPng($verifyUrl);
        if (! $qrPath || ! file_exists($qrPath) || filesize($qrPath) === 0) {
            throw new RuntimeException('Gagal membuat QR PNG. Cek ekstensi GD/Imagick & permission storage/app/tmp.');
        }
        $normalized = $this->normalizePath($qrPath);

        $inserted = false;
        try {
            if (method_exists($tpl, 'setImageValue')) {
                $tpl->setImageValue('qr_code_ttd', [
                    'path'   => $normalized,
                    'width'  => 100,
                    'height' => 100,
                ]);
                $inserted = true;
            }
        } catch (\Throwable $e) {
            Log::warning('[SPK DOCX] setImageValue gagal, fallback complex block', ['err' => $e->getMessage()]);
        }

        if (! $inserted) {
            $run = new TextRun();
            $run->addImage($normalized, ['width' => 120, 'height' => 120]);
            $tpl->setComplexBlock('qr_code_ttd', $run);
        }

        $tmpName = 'spk_' . $this->kp->id . '_' . time() . '.docx';
        $tmpPath = $this->normalizePath($this->tmpPath($tmpName));
        $tpl->saveAs($tmpPath);

        return $tmpPath;
    }

    public function buildPdf(): string
    {
        $docx = $this->buildDocx();

        Settings::setPdfRendererName(Settings::PDF_RENDERER_DOMPDF);
        Settings::setPdfRendererPath(base_path('vendor/dompdf/dompdf'));

        $phpWord = IOFactory::load($docx, 'Word2007');
        $pdfName = 'spk_' . $this->kp->id . '_' . time() . '.pdf';
        $pdfPath = $this->normalizePath($this->tmpPath($pdfName));

        $writer = IOFactory::createWriter($phpWord, 'PDF');
        $writer->save($pdfPath);

        return $pdfPath;
    }

    protected function generateQrPng(string $text): ?string
    {
        $name = 'qr_spk_' . $this->kp->id . '_' . time() . '.png';
        $path = $this->tmpPath($name);

        // 1) Jalur modern
        try {
            if (class_exists('\BaconQrCode\Renderer\ImageRenderer')) {
                $backendClass = null;

                if (extension_loaded('imagick') && class_exists('\BaconQrCode\Renderer\Image\ImagickImageBackEnd')) {
                    $backendClass = '\BaconQrCode\Renderer\Image\ImagickImageBackEnd';
                } elseif (extension_loaded('gd') && class_exists('\BaconQrCode\Renderer\Image\GdImageBackEnd')) {
                    $backendClass = '\BaconQrCode\Renderer\Image\GdImageBackEnd';
                }

                if ($backendClass) {
                    $backend  = new $backendClass();
                    $renderer = new ImageRenderer(new RendererStyle(360), $backend);
                    $writer   = new Writer($renderer);

                    $binary = $writer->writeString($text);
                    if (@file_put_contents($path, $binary) !== false) {
                        return $path;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('[SPK DOCX] QR modern renderer fail', ['err' => $e->getMessage()]);
        }

        // 2) Jalur lama
        try {
            if (class_exists('\BaconQrCode\Renderer\Image\Png')) {
                $pngRenderer = new \BaconQrCode\Renderer\Image\Png();
                if (method_exists($pngRenderer, 'setHeight')) $pngRenderer->setHeight(360);
                if (method_exists($pngRenderer, 'setWidth'))  $pngRenderer->setWidth(360);
                if (method_exists($pngRenderer, 'setMargin')) $pngRenderer->setMargin(0);

                $writer = new Writer($pngRenderer);
                if (method_exists($writer, 'writeFile')) {
                    $writer->writeFile($text, $path);
                    if (file_exists($path) && filesize($path) > 0) return $path;
                } else {
                    $binary = $writer->writeString($text);
                    if (@file_put_contents($path, $binary) !== false) return $path;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('[SPK DOCX] QR legacy renderer fail', ['err' => $e->getMessage()]);
        }

        // 3) Fallback HTTP
        try {
            $encoded = rawurlencode($text);
            $url     = "https://api.qrserver.com/v1/create-qr-code/?size=360x360&margin=0&data={$encoded}";

            $png = @file_get_contents($url);
            if ($png !== false && @file_put_contents($path, $png) !== false) {
                return $path;
            }

            if (function_exists('curl_init')) {
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_TIMEOUT => 10,
                ]);
                $data = curl_exec($ch);
                curl_close($ch);

                if ($data !== false && @file_put_contents($path, $data) !== false) {
                    return $path;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('[SPK DOCX] QR http fallback fail', ['err' => $e->getMessage()]);
        }

        return null;
    }
}
