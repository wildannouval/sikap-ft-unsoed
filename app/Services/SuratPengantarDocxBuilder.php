<?php

namespace App\Services;

use App\Models\SuratPengantar;
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

class SuratPengantarDocxBuilder
{
    public function __construct(
        protected SuratPengantar $sp,
        protected ?Signatory $signatory = null,
        protected string $templateName = 'TEMPLATE_SURAT_PENGANTAR.docx',
    ) {}

    // Path & util
    protected function templatePath(): string
    {
        $path = storage_path('app/templates/' . $this->templateName);
        if (! file_exists($path)) {
            throw new RuntimeException("Template DOCX tidak ditemukan di: {$path}");
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

    /* ============================
     * Build DOCX
     * ============================ */

    public function buildDocx(): string
    {
        $templatePath = $this->templatePath();
        $tpl = new TemplateProcessor($templatePath);

        // Verifikasi placeholder agar pasti kebaca
        if (method_exists($tpl, 'getVariables')) {
            $vars = $tpl->getVariables();
            if (! in_array('qr_code_ttd', $vars, true)) {
                $list = empty($vars) ? '(tidak ada satu pun variable terdeteksi)' : implode(', ', $vars);
                throw new RuntimeException(
                    "Placeholder \${qr_code_ttd} TIDAK ditemukan di template. " .
                        "Variables yang terdeteksi: {$list}. " .
                        "Pastikan menulis persis \${qr_code_ttd} sebagai TEKS polos (bukan shape/header/footer) " .
                        "dan file template yang dipakai adalah {$templatePath}."
                );
            }
        }

        $mhs = $this->sp->mahasiswa;
        $today = now();

        if (! $this->sp->qr_token) {
            $this->sp->qr_token = (string) Str::uuid();
            $this->sp->qr_expires_at = now()->addDays(180);
            $this->sp->save();
        }

        $verifyUrl = route('sp.verify', ['token' => $this->sp->qr_token]);

        /**
         * Tanggal acuan:
         * - Prioritas: tanggal_disetujui_surat_pengantar (biar konsisten dengan surat)
         * - Fallback : $today
         *
         * Aturan umum:
         * - Feb–Jul  => Genap (tahun berjalan)
         * - Aug–Dec  => Ganjil (tahun berjalan)
         * - Jan      => masih Ganjil tahun sebelumnya
         */
        $refDate = $this->sp->tanggal_disetujui_surat_pengantar ?? $today;

        $month = (int) $refDate->format('n');
        $year  = (int) $refDate->format('Y');

        if ($month === 1) {
            $semesterNama  = 'Ganjil';
            $semesterTahun = $year - 1;
        } elseif ($month >= 2 && $month <= 7) {
            $semesterNama  = 'Genap';
            $semesterTahun = $year;
        } else { // 8–12
            $semesterNama  = 'Ganjil';
            $semesterTahun = $year;
        }

        // Isi placeholder semester
        $tpl->setValue('semester_tahun_aktif', "{$semesterNama} {$semesterTahun}");

        // Isi placeholder teks lain
        $tpl->setValue('nomor_surat_pengantar', $this->sp->nomor_surat ?? '-');
        $tpl->setValue(
            'tanggal_disetujui_surat_pengantar',
            optional($this->sp->tanggal_disetujui_surat_pengantar ?? $today)->translatedFormat('d F Y')
        );
        $tpl->setValue('nama_mahasiswa', $mhs?->mahasiswa_name ?? '-');
        $tpl->setValue('nim_mahasiswa', $mhs?->mahasiswa_nim ?? '-');
        $tpl->setValue('jurusan_mahasiswa', $mhs?->jurusan?->nama_jurusan ?? '-');

        $tpl->setValue('lokasi_instansi', $this->sp->lokasi_surat_pengantar ?? '-');
        $tpl->setValue('penerima_surat', $this->sp->penerima_surat_pengantar ?? '-');
        $tpl->setValue('alamat_instansi', $this->sp->alamat_surat_pengantar ?? '-');
        $tpl->setValue('tembusan_surat_pengantar', $this->sp->tembusan_surat_pengantar ?: '-');

        $tpl->setValue('penandatangan_nama', $this->signatory?->name ?? $this->sp->ttd_signed_by_name ?? '-');
        $tpl->setValue('penandatangan_jabatan', $this->signatory?->position ?? $this->sp->ttd_signed_by_position ?? '-');
        $tpl->setValue('penandatangan_nip', $this->signatory?->nip ?? $this->sp->ttd_signed_by_nip ?? '-');

        // === Generate QR (PNG) ===
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
            Log::warning('[SP DOCX] setImageValue gagal, fallback complex block', ['err' => $e->getMessage()]);
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

        $tmpName = 'surat_pengantar_' . $this->sp->id . '_' . time() . '.docx';
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
        $pdfName = 'surat_pengantar_' . $this->sp->id . '_' . time() . '.pdf';
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
        $name = 'qr_sp_' . $this->sp->id . '_' . time() . '.png';
        $path = $this->tmpPath($name);

        // === 1) Jalur modern (v2/v3)
        try {
            if (class_exists('\BaconQrCode\Renderer\ImageRenderer')) {
                $backendClass = null;

                // Prefer imagick
                if (extension_loaded('imagick') && class_exists('\BaconQrCode\Renderer\Image\ImagickImageBackEnd')) {
                    $backendClass = '\BaconQrCode\Renderer\Image\ImagickImageBackEnd';
                }
                // Kalau GD backend tersedia di versi kamu
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
            Log::warning('[SP DOCX] QR modern renderer fail', ['err' => $e->getMessage()]);
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
            Log::warning('[SP DOCX] QR legacy renderer fail', ['err' => $e->getMessage()]);
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
            Log::warning('[SP DOCX] QR http fallback fail', ['err' => $e->getMessage()]);
        }

        // Gagal semua
        return null;
    }
}
