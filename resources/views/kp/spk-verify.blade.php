<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Verifikasi SPK KP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial;
            padding: 24px;
        }

        .card {
            max-width: 720px;
            margin: 0 auto;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
        }

        .ok {
            background: #dcfce7;
            color: #166534;
        }

        .bad {
            background: #fee2e2;
            color: #991b1b;
        }

        .row {
            display: flex;
            gap: 12px;
            margin: 6px 0;
        }

        .key {
            width: 200px;
            color: #6b7280;
        }

        .val {
            flex: 1;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="card">
        <h2>Verifikasi SPK Kerja Praktik</h2>
        <p>
            Status:
            @if ($isValid)
                <span class="badge ok">VALID</span>
            @else
                <span class="badge bad">KADALUARSA</span>
            @endif
        </p>

        <div class="row">
            <div class="key">Nomor SPK</div>
            <div class="val">{{ $kp->nomor_spk ?? '—' }}</div>
        </div>
        <div class="row">
            <div class="key">Tanggal Terbit</div>
            <div class="val">{{ optional($kp->tanggal_terbit_spk)->format('d M Y') ?: '—' }}</div>
        </div>
        <div class="row">
            <div class="key">Mahasiswa</div>
            <div class="val">{{ $kp->mahasiswa?->user?->name }} ({{ $kp->mahasiswa?->nim }})</div>
        </div>
        <div class="row">
            <div class="key">Judul KP</div>
            <div class="val">{{ $kp->judul_kp }}</div>
        </div>
        <div class="row">
            <div class="key">Lokasi/Instansi</div>
            <div class="val">{{ $kp->lokasi_kp }}</div>
        </div>
        <div class="row">
            <div class="key">Ditandatangani</div>
            <div class="val">
                {{ $kp->ttd_signed_by_name ?? '—' }} — {{ $kp->ttd_signed_by_position ?? '—' }}<br>
                NIP: {{ $kp->ttd_signed_by_nip ?? '—' }}
            </div>
        </div>
        @if ($kp->spk_qr_expires_at)
            <div class="row">
                <div class="key">Berlaku s.d.</div>
                <div class="val">{{ $kp->spk_qr_expires_at->format('d M Y H:i') }}</div>
            </div>
        @endif

        <p style="margin-top:16px;color:#6b7280;font-size:12px;">
            Token: {{ \Illuminate\Support\Str::limit($kp->spk_qr_token, 12) }}…
        </p>
    </div>
</body>

</html>
