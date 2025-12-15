<x-layouts.guest :title="__('Verifikasi Surat Pengantar')">
    <div class="space-y-6">
        {{-- HEADER --}}
        <div>
            <flux:heading size="xl" level="1" class="text-stone-900">
                {{ __('Verifikasi Surat Pengantar') }}
            </flux:heading>
            <flux:subheading class="text-zinc-600">
                {{ __('Pemeriksaan keaslian surat melalui QR code') }}
            </flux:subheading>
        </div>

        <flux:separator variant="subtle" />

        {{-- GRID UTAMA (3:1) --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">

            {{-- KIRI (utama) --}}
            <div class="lg:col-span-3 space-y-6">

                {{-- STATUS BANNER --}}
                <flux:card class="rounded-xl border border-zinc-200 bg-white shadow-sm overflow-hidden">
                    @php
                        $isValid = ($status ?? null) === 'valid';
                        $isExpired = ($status ?? null) === 'expired';
                        $isInvalid = ($status ?? null) === 'invalid';

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
                    {{-- INVALID TOKEN --}}
                    <flux:card class="rounded-xl border border-zinc-200 bg-white shadow-sm">
                        <div class="p-6">
                            <div class="flex items-start gap-4">
                                <div
                                    class="flex items-center justify-center rounded-lg p-2 bg-rose-500/10 text-rose-600">
                                    <flux:icon.x-circle class="size-5" />
                                </div>
                                <div class="space-y-1">
                                    <h3 class="text-base font-semibold text-stone-900">Surat tidak ditemukan</h3>
                                    <p class="text-sm text-zinc-600">
                                        Pastikan QR code berasal dari surat pengantar SIKAP FT Unsoed dan tidak rusak.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </flux:card>
                @else
                    {{-- INFORMASI SURAT --}}
                    <flux:card class="rounded-xl border border-zinc-200 bg-white shadow-sm">
                        <div class="p-6 space-y-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="text-base font-semibold text-stone-900">Ringkasan Surat</h3>
                                    <p class="text-sm text-zinc-500">Informasi inti terkait surat pengantar.</p>
                                </div>

                                <div class="text-right">
                                    <div class="text-xs text-zinc-500">Nomor Surat</div>
                                    <div class="font-semibold text-stone-900 break-all">
                                        {{ $sp->nomor_surat ?: '—' }}
                                    </div>
                                </div>
                            </div>

                            <flux:separator />

                            @php
                                // Nama & NIM: pakai field mahasiswa dulu, fallback ke user->name
                                $namaPemohon = $sp->mahasiswa?->nama_mahasiswa ?: $sp->mahasiswa?->user?->name ?: '—';

                                // NIM di project kamu umumnya mahasiswa_nim
                                $nimPemohon = $sp->mahasiswa?->mahasiswa_nim ?: $sp->mahasiswa?->nim ?: '—';
                            @endphp

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                {{-- Kolom A --}}
                                <div class="space-y-4">
                                    <h4 class="text-sm font-semibold text-zinc-700">Informasi Surat</h4>

                                    <div class="text-sm">
                                        <div class="text-zinc-500">Status Surat</div>
                                        <flux:badge size="sm" inset="top bottom"
                                            :color="$sp->status_surat_pengantar === 'Diterbitkan'
                                                                                            ? 'green'
                                                                                            : ($sp->status_surat_pengantar === 'Diajukan' ? 'zinc' : 'red')">
                                            {{ $sp->status_surat_pengantar }}
                                        </flux:badge>
                                    </div>

                                    <div class="text-sm">
                                        <div class="text-zinc-500">Tanggal Disetujui</div>
                                        <div class="font-medium text-stone-900">
                                            {{ optional($sp->tanggal_disetujui_surat_pengantar)->translatedFormat('d F Y') ?: '—' }}
                                        </div>
                                    </div>

                                    <div class="text-sm">
                                        <div class="text-zinc-500">Berlaku s.d.</div>
                                        <div class="font-medium text-stone-900">
                                            {{ optional($sp->qr_expires_at)->translatedFormat('d F Y H:i') ?: '—' }}
                                            @if (($status ?? null) !== 'valid')
                                                <span class="text-rose-600 font-semibold ms-1">(kedaluwarsa)</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Kolom B --}}
                                <div class="space-y-4">
                                    <h4 class="text-sm font-semibold text-zinc-700">Pemohon</h4>

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
                                        <div class="text-zinc-500">Program Studi / Jurusan</div>
                                        <div class="font-medium text-stone-900">
                                            {{ $sp->mahasiswa?->jurusan?->nama_jurusan ?: '—' }}
                                        </div>
                                    </div>

                                    <div class="text-sm">
                                        <div class="text-zinc-500">Penandatangan</div>
                                        <div class="font-medium text-stone-900">
                                            {{ $sp->ttd_signed_by_name ?: $sp->signatory?->name ?: '—' }}
                                            <div class="text-zinc-500 font-normal">
                                                {{ $sp->ttd_signed_by_position ?: $sp->signatory?->position ?: '' }}
                                            </div>
                                            @if ($sp->ttd_signed_by_nip ?: $sp->signatory?->nip)
                                                <div class="text-zinc-500 font-normal">
                                                    NIP: {{ $sp->ttd_signed_by_nip ?: $sp->signatory?->nip }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </flux:card>

                    {{-- TUJUAN SURAT --}}
                    <flux:card class="rounded-xl border border-zinc-200 bg-white shadow-sm">
                        <div class="p-6 space-y-5">
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex items-center justify-center rounded-lg p-2 bg-indigo-500/10 text-indigo-600">
                                    <flux:icon.building-office-2 class="size-5" />
                                </div>
                                <div>
                                    <h3 class="text-base font-semibold text-stone-900">Tujuan Surat</h3>
                                    <p class="text-sm text-zinc-500">Instansi/perusahaan yang dituju pada surat.</p>
                                </div>
                            </div>

                            <flux:separator />

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="text-sm">
                                    <div class="text-zinc-500">Perusahaan / Instansi</div>
                                    <div class="font-medium text-stone-900">
                                        {{ $sp->lokasi_surat_pengantar ?: '—' }}
                                    </div>
                                </div>

                                <div class="text-sm">
                                    <div class="text-zinc-500">Penerima</div>
                                    <div class="font-medium text-stone-900">
                                        {{ $sp->penerima_surat_pengantar ?: '—' }}
                                    </div>
                                </div>

                                <div class="text-sm md:col-span-2">
                                    <div class="text-zinc-500">Alamat</div>
                                    <div class="font-medium text-stone-900">
                                        {{ $sp->alamat_surat_pengantar ?: '—' }}
                                    </div>
                                </div>

                                @if ($sp->tembusan_surat_pengantar)
                                    <div class="text-sm md:col-span-2">
                                        <div class="text-zinc-500">Tembusan</div>
                                        <div class="font-medium text-stone-900">
                                            {{ $sp->tembusan_surat_pengantar }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </flux:card>
                @endif
            </div>

            {{-- KANAN (sidebar) --}}
            <div class="lg:col-span-1 space-y-6">
                {{-- PANDUAN --}}
                <flux:card class="rounded-xl border bg-violet-50/50 border-violet-100 shadow-sm">
                    <div class="p-5">
                        <div class="flex items-start gap-3">
                            <flux:icon.information-circle class="mt-0.5 size-5 text-violet-600" />
                            <div>
                                <h3 class="font-semibold text-violet-900 text-sm">Panduan Verifikasi</h3>
                                <ul class="mt-3 text-xs text-violet-800 space-y-2 list-disc list-inside">
                                    <li>Pastikan QR code berasal dari surat pengantar SIKAP resmi.</li>
                                    <li>Jika status <strong>KEDALUWARSA</strong>, lakukan verifikasi manual ke Bapendik.
                                    </li>
                                    <li>Data di halaman ini adalah data yang tersimpan pada sistem.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </flux:card>

                {{-- INFO SISTEM --}}
                <flux:card class="rounded-xl border bg-white border-zinc-200 shadow-sm">
                    <div class="p-5 space-y-3">
                        <div class="flex items-center gap-2">
                            <flux:icon.shield-check class="size-5 text-zinc-500" />
                            <h3 class="font-semibold text-stone-900 text-sm">Keamanan</h3>
                        </div>
                        <p class="text-xs text-zinc-600 leading-relaxed">
                            Halaman ini menampilkan informasi verifikasi. Jika ada ketidaksesuaian,
                            silakan hubungi Bapendik untuk pemeriksaan lebih lanjut.
                        </p>
                    </div>
                </flux:card>
            </div>
        </div>
    </div>
</x-layouts.guest>
