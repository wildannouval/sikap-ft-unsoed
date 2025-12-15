<x-layouts.guest :title="__('Verifikasi Berita Acara KP')">
    <div class="space-y-6">
        {{-- HEADER --}}
        <div>
            <flux:heading size="xl" level="1" class="text-stone-900">
                {{ __('Verifikasi Berita Acara Kerja Praktik') }}
            </flux:heading>
            <flux:subheading class="text-zinc-600">
                {{ __('Pemeriksaan keaslian Berita Acara melalui QR code') }}
            </flux:subheading>
        </div>

        <flux:separator variant="subtle" />

        {{-- GRID UTAMA (3:1) --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">

            {{-- KIRI --}}
            <div class="lg:col-span-3 space-y-6">

                {{-- STATUS BANNER --}}
                <flux:card class="rounded-xl border border-zinc-200 bg-white shadow-sm overflow-hidden">
                    @php
                        $isValid = ($status ?? null) === 'valid';
                        $isExpired = ($status ?? null) === 'expired';

                        $icon = $isValid ? 'check-circle' : ($isExpired ? 'exclamation-triangle' : 'x-circle');

                        $wrapClass = $isValid
                            ? 'border-emerald-200 bg-emerald-50/60'
                            : ($isExpired
                                ? 'border-amber-200 bg-amber-50/60'
                                : 'border-rose-200 bg-rose-50/60');

                        $titleClass = $isValid ? 'text-emerald-800' : ($isExpired ? 'text-amber-800' : 'text-rose-800');

                        $descClass = $isValid
                            ? 'text-emerald-800/90'
                            : ($isExpired
                                ? 'text-amber-800/90'
                                : 'text-rose-800/90');

                        $badgeColor = $isValid ? 'green' : ($isExpired ? 'amber' : 'red');
                    @endphp

                    <div class="p-5 border {{ $wrapClass }} rounded-xl">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5">
                                <flux:icon :name="$icon"
                                    class="size-6 {{ $isValid ? 'text-emerald-600' : ($isExpired ? 'text-amber-600' : 'text-rose-600') }}" />
                            </div>

                            <div class="flex-1 space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-base font-semibold {{ $titleClass }}">
                                        {{ $status_text ?? __('Status') }}
                                    </h3>
                                    <flux:badge size="sm" :color="$badgeColor" inset="top bottom">
                                        {{ strtoupper($status ?? '—') }}
                                    </flux:badge>
                                </div>

                                <p class="text-sm {{ $descClass }}">
                                    {{ $description ?? '' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </flux:card>

                @if (!$found)
                    {{-- TOKEN INVALID --}}
                    <flux:card class="rounded-xl border border-zinc-200 bg-white shadow-sm">
                        <div class="p-6">
                            <div class="flex items-start gap-4">
                                <div
                                    class="flex items-center justify-center rounded-lg p-2 bg-rose-500/10 text-rose-600">
                                    <flux:icon.x-circle class="size-5" />
                                </div>
                                <div class="space-y-1">
                                    <h3 class="text-base font-semibold text-stone-900">Berita Acara tidak ditemukan</h3>
                                    <p class="text-sm text-zinc-600">
                                        Pastikan QR code berasal dari Berita Acara KP resmi dan tidak rusak.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </flux:card>
                @else
                    @php
                        $kp = $seminar->kp;

                        $namaPemohon = $kp?->mahasiswa?->user?->name ?: $kp?->mahasiswa?->nama_mahasiswa ?: '—';

                        $nimPemohon = $kp?->mahasiswa?->mahasiswa_nim ?: $kp?->mahasiswa?->nim ?: '—';
                    @endphp

                    {{-- RINGKASAN BA --}}
                    <flux:card class="rounded-xl border border-zinc-200 bg-white shadow-sm">
                        <div class="p-6 space-y-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="text-base font-semibold text-stone-900">Ringkasan Berita Acara</h3>
                                    <p class="text-sm text-zinc-500">Informasi inti terkait Berita Acara Seminar KP.</p>
                                </div>

                                <div class="text-right">
                                    <div class="text-xs text-zinc-500">Nomor BA</div>
                                    <div class="font-semibold text-stone-900 break-all">
                                        {{ $seminar->nomor_ba ?? '—' }}
                                    </div>
                                </div>
                            </div>

                            <flux:separator />

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                {{-- Kolom A --}}
                                <div class="space-y-4">
                                    <h4 class="text-sm font-semibold text-zinc-700">Informasi BA</h4>

                                    <div class="text-sm">
                                        <div class="text-zinc-500">Tanggal BA</div>
                                        <div class="font-medium text-stone-900">
                                            {{ optional($seminar->tanggal_ba)->translatedFormat('d F Y') ?: '—' }}
                                        </div>
                                    </div>

                                    <div class="text-sm">
                                        <div class="text-zinc-500">Tanggal Seminar</div>
                                        <div class="font-medium text-stone-900">
                                            {{ optional($seminar->tanggal_seminar)->translatedFormat('d F Y') ?: '—' }}
                                        </div>
                                    </div>

                                    <div class="text-sm">
                                        <div class="text-zinc-500">Waktu</div>
                                        <div class="font-medium text-stone-900">
                                            {{ $seminar->jam_mulai ?: '—' }} — {{ $seminar->jam_selesai ?: '—' }}
                                        </div>
                                    </div>

                                    <div class="text-sm">
                                        <div class="text-zinc-500">Ruangan</div>
                                        <div class="font-medium text-stone-900">
                                            {{ $seminar->ruangan_nama ?: '—' }}
                                        </div>
                                    </div>

                                    @if ($seminar->ba_qr_expires_at)
                                        <div class="text-sm">
                                            <div class="text-zinc-500">Berlaku s.d.</div>
                                            <div class="font-medium text-stone-900">
                                                {{ optional($seminar->ba_qr_expires_at)->translatedFormat('d F Y H:i') ?: '—' }}
                                                @if (($status ?? null) !== 'valid')
                                                    <span class="text-rose-600 font-semibold ms-1">(kedaluwarsa)</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                {{-- Kolom B --}}
                                <div class="space-y-4">
                                    <h4 class="text-sm font-semibold text-zinc-700">Mahasiswa</h4>

                                    <div class="text-sm">
                                        <div class="text-zinc-500">Nama</div>
                                        <div class="font-medium text-stone-900">
                                            {{ $namaPemohon }}
                                        </div>
                                    </div>

                                    <div class="text-sm">
                                        <div class="text-zinc-500">NIM</div>
                                        <div class="font-medium text-stone-900">
                                            {{ $nimPemohon }}
                                        </div>
                                    </div>

                                    <div class="text-sm">
                                        <div class="text-zinc-500">Judul Laporan</div>
                                        <div class="font-medium text-stone-900">
                                            {{ $seminar->judul_laporan ?: ($kp?->judul_kp ?: '—') }}
                                        </div>
                                    </div>

                                    <div class="text-sm">
                                        <div class="text-zinc-500">Lokasi/Instansi</div>
                                        <div class="font-medium text-stone-900">
                                            {{ $kp?->lokasi_kp ?: '—' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </flux:card>

                    {{-- PENANDATANGAN --}}
                    <flux:card class="rounded-xl border border-zinc-200 bg-white shadow-sm">
                        <div class="p-6 space-y-5">
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex items-center justify-center rounded-lg p-2 bg-violet-500/10 text-violet-600">
                                    <flux:icon.pencil-square class="size-5" />
                                </div>
                                <div>
                                    <h3 class="text-base font-semibold text-stone-900">Penandatangan</h3>
                                    <p class="text-sm text-zinc-500">Informasi pejabat yang menerbitkan/menandatangani
                                        BA.</p>
                                </div>
                            </div>

                            <flux:separator />

                            <div class="grid grid-cols-1 gap-4">
                                <div class="text-sm">
                                    <div class="text-zinc-500">Nama</div>
                                    <div class="font-medium text-stone-900">
                                        {{ $seminar->ttd_signed_by_name ?? '—' }}
                                    </div>
                                </div>

                                <div class="text-sm">
                                    <div class="text-zinc-500">Jabatan</div>
                                    <div class="font-medium text-stone-900">
                                        {{ $seminar->ttd_signed_by_position ?? '—' }}
                                    </div>
                                </div>

                                <div class="text-sm">
                                    <div class="text-zinc-500">NIP</div>
                                    <div class="font-medium text-stone-900">
                                        {{ $seminar->ttd_signed_by_nip ?? '—' }}
                                    </div>
                                </div>
                            </div>

                            <div class="pt-1">
                                <div class="text-xs text-zinc-500">
                                    Token: {{ \Illuminate\Support\Str::limit($seminar->ba_qr_token, 16) }}…
                                </div>
                            </div>
                        </div>
                    </flux:card>
                @endif
            </div>

            {{-- KANAN --}}
            <div class="lg:col-span-1 space-y-6">
                {{-- PANDUAN --}}
                <flux:card class="rounded-xl border bg-violet-50/50 border-violet-100 shadow-sm">
                    <div class="p-5">
                        <div class="flex items-start gap-3">
                            <flux:icon.information-circle class="mt-0.5 size-5 text-violet-600" />
                            <div>
                                <h3 class="font-semibold text-violet-900 text-sm">Panduan Verifikasi</h3>
                                <ul class="mt-3 text-xs text-violet-800 space-y-2 list-disc list-inside">
                                    <li>Pastikan QR code berasal dari BA KP resmi SIKAP.</li>
                                    <li>Jika status <strong>KEDALUWARSA</strong>, verifikasi manual ke Bapendik.</li>
                                    <li>Data di halaman ini menampilkan detail sesuai yang tersimpan di sistem.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </flux:card>

                {{-- INFO --}}
                <flux:card class="rounded-xl border bg-white border-zinc-200 shadow-sm">
                    <div class="p-5 space-y-3">
                        <div class="flex items-center gap-2">
                            <flux:icon.shield-check class="size-5 text-zinc-500" />
                            <h3 class="font-semibold text-stone-900 text-sm">Keamanan</h3>
                        </div>
                        <p class="text-xs text-zinc-600 leading-relaxed">
                            Halaman ini digunakan untuk validasi Berita Acara. Jika ada ketidaksesuaian,
                            silakan hubungi Bapendik untuk pemeriksaan.
                        </p>
                    </div>
                </flux:card>
            </div>
        </div>
    </div>
</x-layouts.guest>
