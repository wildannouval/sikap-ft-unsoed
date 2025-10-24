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

// bacon/bacon-qr-code (tanpa import backend spesifik agar IDE tenang)
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

    /* ============================
     * Path & util
     * ============================ */

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

    /** PhpWord lebih aman dengan forward-slash */
    protected function normalizePath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }

    /** Hanya set variabel kalau ada di template */
    protected function setIfPresent(TemplateProcessor $tpl, array $vars, string $key, $value): void
    {
        if (in_array($key, $vars, true)) {
            $tpl->setValue($key, $value);
        }
    }

    /* ============================
     * Build DOCX
     * ============================ */

    /**
     * Build DOCX & return absolute path file sementara.
     */
    public function buildDocx(): string
    {
        $templatePath = $this->templatePath();
        $tpl = new TemplateProcessor($templatePath);

        // Verifikasi placeholder agar QR benar-benar kebaca
        $vars = method_exists($tpl, 'getVariables') ? $tpl->getVariables() : [];

        if (! in_array('qr_code_ttd', $vars, true)) {
            $list = empty($vars) ? '(tidak ada satu pun variable terdeteksi)' : implode(', ', $vars);
            throw new RuntimeException(
                "Placeholder \${qr_code_ttd} TIDAK ditemukan di template. ".
                "Variables terdeteksi: {$list}. ".
                "Pastikan menulis persis \${qr_code_ttd} sebagai TEKS polos (bukan shape/header/footer)."
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

        // URL verifikasi (route sudah kamu buat: spk.verify)
        $verifyUrl = route('spk.verify', ['token' => $this->kp->spk_qr_token]);

        // ====== Isi placeholder aman (hanya kalau ada di template) ======
        // Beberapa nama disesuaikan dengan yang pernah kamu sebut.
        $this->setIfPresent($tpl, $vars, 'nomor_spk', $this->kp->nomor_spk ?? '-');

        // dukung beberapa variasi nama tanggal di template:
        $tgl = optional($this->kp->tanggal_terbit_spk ?? $now)->translatedFormat('d F Y');
        foreach (['tanggal_spk','tanggal_terbit_spk','tanggal_penunjukan'] as $k) {
            $this->setIfPresent($tpl, $vars, $k, $tgl);
        }

        // identitas mahasiswa
        $this->setIfPresent($tpl, $vars, 'nama_mahasiswa', $mhs?->user?->name ?? $mhs?->nama_mahasiswa ?? '-');
        $this->setIfPresent($tpl, $vars, 'nim_mahasiswa', $mhs?->nim ?? '-');
        $this->setIfPresent($tpl, $vars, 'jurusan_mahasiswa', $jur?->nama_jurusan ?? '-');

        // KP
        $this->setIfPresent($tpl, $vars, 'judul_kp', $this->kp->judul_kp ?? '-');
        $this->setIfPresent($tpl, $vars, 'judul_kerja_praktik_mahasiswa', $this->kp->judul_kp ?? '-'); // alias lain
        $this->setIfPresent($tpl, $vars, 'lokasi_kp', $this->kp->lokasi_kp ?? '-');

        // pembimbing & komisi (ambil dari relasi/snapshot)
        $this->setIfPresent($tpl, $vars, 'dosen_pembimbing_nama', $this->kp->dosenPembimbing?->nama ?? $this->kp->dosen_pembimbing_nama ?? '-');
        $this->setIfPresent($tpl, $vars, 'nama_dosen_pembimbing', $this->kp->dosenPembimbing?->nama ?? $this->kp->dosen_pembimbing_nama ?? '-'); // alias
        $this->setIfPresent($tpl, $vars, 'dosen_komisi_nama', $this->kp->dosenKomisi?->nama ?? $this->kp->dosen_komisi_nama ?? '-');
        $this->setIfPresent($tpl, $vars, 'nama_dosen_komisi', $this->kp->dosenKomisi?->nama ?? $this->kp->dosen_komisi_nama ?? '-'); // alias

        // penandatangan (pakai signatory terkini kalau ada; kalau tidak, snapshot dari KP)
        $this->setIfPresent($tpl, $vars, 'penandatangan_nama', $this->signatory?->name ?? $this->kp->ttd_signed_by_name ?? '-');
        $this->setIfPresent($tpl, $vars, 'penandatangan_jabatan', $this->signatory?->position ?? $this->kp->ttd_signed_by_position ?? '-');
        $this->setIfPresent($tpl, $vars, 'penandatangan_nip', $this->signatory?->nip ?? $this->kp->ttd_signed_by_nip ?? '-');

        // ====== Generate QR PNG ======
        $qrPath = $this->generateQrPng($verifyUrl);
        if (! $qrPath || ! file_exists($qrPath) || filesize($qrPath) === 0) {
            throw new RuntimeException('Gagal membuat QR PNG. Cek ekstensi GD/Imagick & permission storage/app/tmp.');
        }
        $normalized = $this->normalizePath($qrPath);

        // Sisipkan ke ${qr_code_ttd} (2 strategi)
        $inserted = false;

        // 1) setImageValue
        try {
            if (method_exists($tpl, 'setImageValue')) {
                $tpl->setImageValue('qr_code_ttd', [
                    'path'   => $normalized,
                    'width'  => 120,
                    'height' => 120,
                ]);
                $inserted = true;
            }
        } catch (\Throwable $e) {
            Log::warning('[SPK DOCX] setImageValue gagal, fallback complex block', ['err' => $e->getMessage()]);
        }

        // 2) setComplexBlock + TextRun->addImage
        if (! $inserted) {
            $run = new TextRun();
            $run->addImage($normalized, [
                'width'  => 120,
                'height' => 120,
            ]);
            $tpl->setComplexBlock('qr_code_ttd', $run);
            $inserted = true;
        }

        // simpan file .docx sementara
        $tmpName = 'spk_' . $this->kp->id . '_' . time() . '.docx';
        $tmpPath = $this->normalizePath($this->tmpPath($tmpName));
        $tpl->saveAs($tmpPath);

        return $tmpPath;
    }

    /**
     * (Opsional) Build PDF
     */
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

    /**
     * Generate PNG QR yang kompatibel lintas-versi bacon/bacon-qr-code:
     * 1) Modern (v2/v3): ImageRenderer + Imagick/GD backend
     * 2) Lama  (v1)   : Renderer\Image\Png
     * 3) Fallback HTTP: unduh dari API publik (tanpa GD/Imagick)
     */
    protected function generateQrPng(string $text): ?string
    {
        $name = 'qr_spk_' . $this->kp->id . '_' . time() . '.png';
        $path = $this->tmpPath($name);

        // === 1) Jalur modern (v2/v3)
        try {
            if (class_exists('\BaconQrCode\Renderer\ImageRenderer')) {
                $backendClass = null;

                // Prefer imagick
                if (extension_loaded('imagick') && class_exists('\BaconQrCode\Renderer\Image\ImagickImageBackEnd')) {
                    $backendClass = '\BaconQrCode\Renderer\Image\ImagickImageBackEnd';
                }
                // GD backend (kalau tersedia)
                elseif (extension_loaded('gd') && class_exists('\BaconQrCode\Renderer\Image\GdImageBackEnd')) {
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
            // lanjut ke v1
        }

        // === 2) Jalur lama (v1)
        try {
            if (class_exists('\BaconQrCode\Renderer\Image\Png')) {
                $pngRenderer = new \BaconQrCode\Renderer\Image\Png();
                if (method_exists($pngRenderer, 'setHeight')) $pngRenderer->setHeight(360);
                if (method_exists($pngRenderer, 'setWidth'))  $pngRenderer->setWidth(360);
                if (method_exists($pngRenderer, 'setMargin')) $pngRenderer->setMargin(0);

                $writer = new Writer($pngRenderer);

                if (method_exists($writer, 'writeFile')) {
                    $writer->writeFile($text, $path);
                    if (file_exists($path) && filesize($path) > 0) {
                        return $path;
                    }
                } else {
                    $binary = $writer->writeString($text);
                    if (@file_put_contents($path, $binary) !== false) {
                        return $path;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('[SPK DOCX] QR legacy renderer fail', ['err' => $e->getMessage()]);
            // lanjut ke HTTP fallback
        }

        // === 3) Fallback HTTP (tanpa GD/Imagick) ===
        try {
            $encoded = rawurlencode($text);
            $url     = "https://api.qrserver.com/v1/create-qr-code/?size=360x360&margin=0&data={$encoded}";

            // Prefer file_get_contents
            $png = @file_get_contents($url);
            if ($png !== false && @file_put_contents($path, $png) !== false) {
                return $path;
            }

            // Fallback cURL kalau tersedia
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

        return null; // Gagal semua
    }
}
