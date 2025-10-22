<x-layouts.guest :title="__('Verifikasi Surat Pengantar')">
    <flux:toast />

    <div class="max-w-4xl mx-auto space-y-6">
        <div>
            <flux:heading size="xl" level="1">{{ __('Verifikasi Surat Pengantar') }}</flux:heading>
            <flux:subheading class="text-zinc-600">
                {{ __('Pemeriksaan keaslian surat melalui QR code') }}
            </flux:subheading>
        </div>

        <flux:separator variant="subtle" />

        @if (!$found)
            {{-- Banner: token tidak valid --}}
            <flux:card>
                <div class="flex items-start gap-4 border-l-4 border-red-500 bg-red-50 p-4 rounded-md">
                    <div class="mt-0.5 h-2.5 w-2.5 rounded-full bg-red-500"></div>
                    <div class="space-y-1">
                        <h3 class="font-semibold text-red-700">{{ $status_text }}</h3>
                        <p class="text-sm text-red-700/90">{{ $description }}</p>
                    </div>
                </div>
            </flux:card>
        @else
            {{-- Banner status valid / expired --}}
            <flux:card>
                @php
                    $isValid = $status === 'valid';
                    $wrapClass = $isValid
                        ? 'border-l-4 border-emerald-500 bg-emerald-50'
                        : 'border-l-4 border-amber-500 bg-amber-50';
                    $titleClass = $isValid ? 'text-emerald-800' : 'text-amber-800';
                    $descClass = $isValid ? 'text-emerald-800/90' : 'text-amber-800/90';
                    $dotClass = $isValid ? 'bg-emerald-500' : 'bg-amber-500';
                @endphp

                <div class="flex items-start gap-4 p-4 rounded-md {{ $wrapClass }}">
                    <div class="mt-0.5 h-2.5 w-2.5 rounded-full {{ $dotClass }}"></div>
                    <div class="space-y-1">
                        <h3 class="font-semibold {{ $titleClass }}">{{ $status_text }}</h3>
                        <p class="text-sm {{ $descClass }}">{{ $description }}</p>
                    </div>
                </div>
            </flux:card>

            {{-- Ringkasan Surat --}}
            <flux:card>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="space-y-3">
                        <h3 class="text-sm font-semibold text-zinc-700">Informasi Surat</h3>

                        <div class="text-sm">
                            <div class="text-zinc-500">Nomor Surat</div>
                            <div class="font-medium break-all">{{ $sp->nomor_surat ?: '—' }}</div>
                        </div>

                        <div class="text-sm">
                            <div class="text-zinc-500">Status</div>
                            <flux:badge size="sm" inset="top bottom"
                                :color="$sp->status_surat_pengantar === 'Diterbitkan' ? 'green' : ($sp->status_surat_pengantar === 'Diajukan' ? 'zinc' : 'red')">
                                {{ $sp->status_surat_pengantar }}
                            </flux:badge>
                        </div>

                        <div class="text-sm">
                            <div class="text-zinc-500">Tanggal Disetujui</div>
                            <div class="font-medium">
                                {{ optional($sp->tanggal_disetujui_surat_pengantar)->translatedFormat('d F Y') ?: '—' }}
                            </div>
                        </div>

                        <div class="text-sm">
                            <div class="text-zinc-500">Berlaku s.d.</div>
                            <div class="font-medium">
                                {{ optional($sp->qr_expires_at)->translatedFormat('d F Y H:i') ?: '—' }}
                                @if($status !== 'valid')
                                    <span class="text-red-600 font-medium">(kedaluwarsa)</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <h3 class="text-sm font-semibold text-zinc-700">Pemohon</h3>

                        <div class="text-sm">
                            <div class="text-zinc-500">Nama</div>
                            <div class="font-medium">{{ $sp->mahasiswa?->nama_mahasiswa ?: '—' }}</div>
                        </div>

                        <div class="text-sm">
                            <div class="text-zinc-500">NIM</div>
                            <div class="font-medium">{{ $sp->mahasiswa?->nim ?: '—' }}</div>
                        </div>

                        <div class="text-sm">
                            <div class="text-zinc-500">Program Studi / Jurusan</div>
                            <div class="font-medium">{{ $sp->mahasiswa?->jurusan?->nama_jurusan ?: '—' }}</div>
                        </div>

                        <div class="text-sm">
                            <div class="text-zinc-500">Penandatangan</div>
                            <div class="font-medium">
                                {{ $sp->ttd_signed_by_name ?: $sp->signatory?->name ?: '—' }}<br>
                                <span class="text-zinc-500">
                                    {{ $sp->ttd_signed_by_position ?: $sp->signatory?->position ?: '' }}
                                </span><br>
                                @if($sp->ttd_signed_by_nip ?: $sp->signatory?->nip)
                                    <span class="text-zinc-500">
                                        NIP: {{ $sp->ttd_signed_by_nip ?: $sp->signatory?->nip }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </flux:card>

            {{-- Detail Instansi --}}
            <flux:card>
                <h3 class="mb-3 text-sm font-semibold text-zinc-700">Tujuan Surat</h3>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="text-sm">
                        <div class="text-zinc-500">Perusahaan / Instansi</div>
                        <div class="font-medium">{{ $sp->lokasi_surat_pengantar ?: '—' }}</div>
                    </div>
                    <div class="text-sm">
                        <div class="text-zinc-500">Penerima</div>
                        <div class="font-medium">{{ $sp->penerima_surat_pengantar ?: '—' }}</div>
                    </div>
                    <div class="text-sm md:col-span-2">
                        <div class="text-zinc-500">Alamat</div>
                        <div class="font-medium">{{ $sp->alamat_surat_pengantar ?: '—' }}</div>
                    </div>
                    @if($sp->tembusan_surat_pengantar)
                        <div class="text-sm md:col-span-2">
                            <div class="text-zinc-500">Tembusan</div>
                            <div class="font-medium">{{ $sp->tembusan_surat_pengantar }}</div>
                        </div>
                    @endif
                </div>
            </flux:card>
        @endif

        <div class="flex items-center justify-end">
            <a href="{{ url('/') }}">
                <flux:button icon="home">Kembali ke Beranda</flux:button>
            </a>
        </div>
    </div>
</x-layouts.guest>
