<div class="space-y-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl" level="1">Dashboard Mahasiswa</flux:heading>
        <flux:badge size="sm"
            class="bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
            SIKAP FT UNSOED
        </flux:badge>
    </div>

    {{-- STAT TILES UTAMA --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Card 1: Surat Pengantar --}}
        <flux:card class="p-4 flex items-center justify-between overflow-hidden shadow-sm">
            <div class="min-w-0">
                <div class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Surat Pengantar</div>
                <div class="mt-1 text-lg font-bold text-stone-900 dark:text-stone-100 truncate">
                    {{ $this->mainStats['surat_pengantar'] }}
                </div>
            </div>
            <div class="p-2 rounded-lg bg-sky-100 text-sky-600 dark:bg-sky-900/30 dark:text-sky-400">
                <flux:icon.envelope class="size-6" />
            </div>
        </flux:card>

        {{-- Card 2: Status Kerja Praktik (TIDAK GANTI WARNA SAAT SELESAI/NILAI TERBIT) --}}
        @php
            $isFinished = $this->mainStats['kp_status'] === 'Selesai';
        @endphp

        <flux:card class="p-0 overflow-hidden shadow-sm">
            <div class="p-4 flex items-center justify-between">
                <div class="min-w-0">
                    <div class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Status KP</div>
                    <div class="mt-1 text-lg font-bold text-stone-900 dark:text-stone-100 truncate">
                        {{ $this->mainStats['kp_status'] }}
                    </div>
                </div>

                {{-- Icon tetap konsisten dengan card lain (indigo), hanya ikon yang berubah --}}
                <div class="p-2 rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400">
                    <flux:icon :name="$isFinished ? 'check-badge' : 'briefcase'" class="size-6" />
                </div>
            </div>

            {{-- Optional footer (netral), tidak mengubah theme card --}}
            @if ($isFinished)
                <div
                    class="px-4 py-1.5 bg-zinc-50/60 dark:bg-zinc-900/30 border-t border-zinc-100 dark:border-stone-800">
                    <span class="text-[10px] font-bold text-zinc-600 dark:text-zinc-300 uppercase">
                        KP Telah Selesai
                    </span>
                </div>
            @endif
        </flux:card>

        {{-- Card 3: Konsultasi --}}
        <flux:card class="p-4 flex items-center justify-between overflow-hidden shadow-sm">
            <div class="min-w-0">
                <div class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Konsultasi</div>
                <div class="mt-1 text-lg font-bold text-stone-900 dark:text-stone-100 truncate">
                    {{ $this->mainStats['konsultasi'] }}
                </div>
            </div>
            <div class="p-2 rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400">
                <flux:icon.chat-bubble-left-right class="size-6" />
            </div>
        </flux:card>

        {{-- Card 4: Seminar Selesai --}}
        <flux:card class="p-4 flex items-center justify-between overflow-hidden shadow-sm">
            <div class="min-w-0">
                <div class="text-xs font-medium text-zinc-500 uppercase tracking-wider">Seminar / BA</div>
                <div class="mt-1 text-lg font-bold text-stone-900 dark:text-stone-100 truncate">
                    {{ $this->mainStats['seminar_selesai'] }}
                </div>
            </div>
            <div class="p-2 rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                <flux:icon.check-circle class="size-6" />
            </div>
        </flux:card>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">

        {{-- KOLOM KIRI (2/3): DETAIL KP & SHORTCUTS --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- 1. CARD DETAIL KP AKTIF --}}
            <flux:card class="space-y-4 rounded-xl border shadow-sm">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <div class="flex items-center gap-2">
                        <flux:icon.briefcase class="size-5 text-indigo-600" />
                        <h3 class="font-semibold text-stone-900 dark:text-stone-100">Informasi Kerja Praktik</h3>
                    </div>
                    @if (($this->activeKp?->verified_consultations_count ?? 0) >= 6 && !$isFinished)
                        <flux:badge size="sm" color="emerald" icon="check-circle" inset="top bottom">Siap Seminar
                        </flux:badge>
                    @endif
                </div>

                @if ($this->activeKp)
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div
                            class="p-3 rounded-lg bg-zinc-50 dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800">
                            <div class="text-xs text-zinc-500 mb-1">Judul Laporan</div>
                            <div
                                class="font-medium text-sm text-stone-900 dark:text-stone-100 leading-snug line-clamp-2">
                                {{ $this->activeKp->judul_kp }}
                            </div>
                        </div>
                        <div
                            class="p-3 rounded-lg bg-zinc-50 dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800">
                            <div class="text-xs text-zinc-500 mb-1">Lokasi Instansi</div>
                            <div class="font-medium text-sm text-stone-900 dark:text-stone-100">
                                {{ $this->activeKp->lokasi_kp }}
                            </div>
                        </div>
                    </div>

                    {{-- Progres Bimbingan --}}
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-zinc-500">Progres Bimbingan Terverifikasi</span>
                            <span
                                class="font-medium text-indigo-600 dark:text-indigo-400">{{ $this->activeKp->verified_consultations_count }}
                                / 6</span>
                        </div>
                        @php $pct = min(100, ($this->activeKp->verified_consultations_count / 6) * 100); @endphp
                        <div class="h-2 w-full bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                            <div class="h-full bg-indigo-500 transition-all duration-700"
                                style="width: {{ $pct }}%"></div>
                        </div>
                    </div>

                    {{-- Reminder Alert --}}
                    @if ($this->needDistribusi)
                        <div
                            class="p-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 flex items-start gap-3 mt-2">
                            <flux:icon.exclamation-triangle class="size-5 text-amber-600 mt-0.5" />
                            <div class="flex-1">
                                <div class="text-sm font-bold text-amber-800 dark:text-amber-200">Kewajiban Dokumen
                                    Akhir</div>
                                <div class="text-xs text-amber-700 dark:text-amber-300 mt-0.5">
                                    Seminar selesai. Silakan upload <strong>Laporan Final</strong> & <strong>Bukti
                                        Distribusi</strong> untuk melihat nilai akhir.
                                </div>
                                <flux:button href="{{ route('mhs.nilai') }}" size="xs" variant="primary"
                                    class="mt-2" wire:navigate>Lengkapi Sekarang</flux:button>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="py-10 text-center">
                        <div class="text-sm text-zinc-500">Anda belum memiliki Kerja Praktik yang aktif.</div>
                        <flux:button href="{{ route('mhs.kp.index') }}" variant="primary" size="sm" class="mt-4"
                            wire:navigate>Daftar KP Baru</flux:button>
                    </div>
                @endif
            </flux:card>

            {{-- 2. DOWNLOAD SHORTCUTS --}}
            <flux:card class="space-y-4 rounded-xl border shadow-sm">
                <div class="flex items-center gap-2 border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <flux:icon.arrow-down-tray class="size-5 text-zinc-500" />
                    <h3 class="font-semibold text-stone-900 dark:text-stone-100">Unduh Dokumen Cepat</h3>
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    @forelse ($this->downloadLinks as $link)
                        <a href="{{ $link['url'] }}" target="_blank"
                            class="flex flex-col items-center justify-center p-4 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/50 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-all group">
                            <div
                                class="p-2 rounded-full bg-{{ $link['color'] }}-100 dark:bg-{{ $link['color'] }}-900/30 text-{{ $link['color'] }}-600 group-hover:scale-110 transition-transform">
                                <flux:icon :name="$link['icon']" class="size-6" />
                            </div>
                            <span
                                class="mt-2 text-xs font-bold text-stone-700 dark:text-stone-300 text-center uppercase tracking-tighter">{{ $link['label'] }}</span>
                        </a>
                    @empty
                        <div class="col-span-full py-4 text-center text-sm text-zinc-500 italic">
                            Belum ada dokumen yang dapat diunduh.
                        </div>
                    @endforelse
                </div>
            </flux:card>
        </div>

        {{-- KOLOM KANAN (1/3): SEMINAR TERBARU --}}
        <div class="lg:col-span-1">
            <flux:card class="h-full rounded-xl border shadow-sm flex flex-col">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-semibold text-stone-900 dark:text-stone-100">Aktivitas Seminar</h3>
                </div>

                <div class="space-y-5 flex-1">
                    @forelse ($this->recentSeminars as $row)
                        <div class="flex gap-4 relative">
                            {{-- Timeline line --}}
                            @if (!$loop->last)
                                <div
                                    class="absolute left-[15px] top-8 bottom-[-20px] w-0.5 bg-zinc-100 dark:bg-zinc-800">
                                </div>
                            @endif

                            <div
                                class="size-8 rounded-full flex-shrink-0 bg-{{ $row::badgeColor($row->status) }}-100 text-{{ $row::badgeColor($row->status) }}-600 flex items-center justify-center z-10">
                                <flux:icon.calendar class="size-4" />
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="text-sm font-bold text-stone-900 dark:text-stone-100 truncate">
                                    {{ $row->judul_laporan }}
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    <flux:badge size="xs" :color="$row::badgeColor($row->status)">
                                        {{ $row::statusLabel($row->status) }}
                                    </flux:badge>
                                    <span class="text-[10px] text-zinc-400 font-medium">
                                        {{ $row->updated_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-10 text-center">
                            <flux:icon.calendar class="size-10 text-zinc-200 mb-2" />
                            <p class="text-xs text-zinc-500">Belum ada aktivitas pendaftaran seminar.</p>
                        </div>
                    @endforelse
                </div>

                @if ($this->activeKp)
                    <div class="mt-6 pt-4 border-t border-zinc-100 dark:border-zinc-800 text-center">
                        <flux:button href="{{ route('mhs.kp.seminar', ['kp' => $this->activeKp->id]) }}"
                            variant="ghost" size="sm" class="w-full" wire:navigate>
                            Kelola Seminar
                        </flux:button>
                    </div>
                @endif
            </flux:card>
        </div>
    </div>
</div>
