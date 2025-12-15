<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-stone-900 dark:text-stone-100">Dashboard Mahasiswa</h2>
        <flux:badge size="sm"
            class="bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
            SIKAP FT UNSOED
        </flux:badge>
    </div>

    {{-- STAT TILES --}}
    @php
        $cards = [
            [
                'label' => 'Total Seminar',
                'val' => $this->seminarStats['total'],
                'color' => 'indigo',
                'icon' => 'chart-bar',
            ],
            [
                'label' => 'Menunggu ACC',
                'val' => $this->seminarStats['diajukan'],
                'color' => 'amber',
                'icon' => 'clock',
            ],
            [
                'label' => 'Dijadwalkan',
                'val' => $this->seminarStats['dijadwalkan'],
                'color' => 'sky',
                'icon' => 'calendar',
            ],
            [
                'label' => 'Selesai / BA',
                'val' => $this->seminarStats['ba_terbit'] + $this->seminarStats['selesai'],
                'color' => 'emerald',
                'icon' => 'check-circle',
            ],
        ];
    @endphp

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($cards as $c)
            <div class="rounded-xl border shadow-sm bg-white dark:bg-stone-950 dark:border-stone-800 overflow-hidden">
                <div class="p-4 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-medium text-zinc-500 uppercase tracking-wide">{{ $c['label'] }}</div>
                        <div class="mt-1 text-2xl font-bold text-stone-900 dark:text-stone-100">{{ $c['val'] }}</div>
                    </div>
                    <div
                        class="p-2 rounded-lg bg-{{ $c['color'] }}-100 dark:bg-{{ $c['color'] }}-900/30 text-{{ $c['color'] }}-600 dark:text-{{ $c['color'] }}-400">
                        <flux:icon :name="$c['icon']" class="size-6" />
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 lg:grid-cols-3">

        {{-- KOLOM KIRI (2): STATUS KP AKTIF --}}
        <div class="lg:col-span-2 space-y-6">
            <flux:card
                class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm">
                <div class="flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800 pb-3">
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 bg-indigo-50 dark:bg-indigo-900/20 rounded-md text-indigo-600">
                            <flux:icon.briefcase class="size-5" />
                        </div>
                        <h3 class="font-semibold text-stone-900 dark:text-stone-100">Status KP Aktif</h3>
                    </div>

                    @if (($this->activeKp?->verified_consultations_count ?? 0) >= 6)
                        <flux:badge size="sm" color="emerald" icon="check-circle">Siap Seminar</flux:badge>
                    @endif
                </div>

                @if ($this->activeKp)
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div
                            class="p-3 rounded-lg bg-zinc-50 dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800">
                            <div class="text-xs text-zinc-500 mb-1">Judul KP</div>
                            <div
                                class="font-medium text-stone-900 dark:text-stone-100 text-sm leading-snug line-clamp-2">
                                {{ $this->activeKp->judul_kp }}
                            </div>
                        </div>
                        <div
                            class="p-3 rounded-lg bg-zinc-50 dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800">
                            <div class="text-xs text-zinc-500 mb-1">Lokasi</div>
                            <div class="font-medium text-stone-900 dark:text-stone-100 text-sm">
                                {{ $this->activeKp->lokasi_kp }}
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-zinc-500">Progres Konsultasi</span>
                            <span class="font-medium">{{ $this->activeKp->verified_consultations_count }} / 6</span>
                        </div>
                        @php $pct = min(100, ($this->activeKp->verified_consultations_count / 6) * 100); @endphp
                        <div class="h-2 w-full bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                            <div class="h-full bg-indigo-500 transition-all duration-500"
                                style="width: {{ $pct }}%"></div>
                        </div>
                    </div>

                    @if ($this->needDistribusi)
                        <div
                            class="p-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50 flex items-start gap-3">
                            <flux:icon.exclamation-triangle class="size-5 text-amber-600 mt-0.5" />
                            <div>
                                <div class="text-sm font-medium text-amber-800 dark:text-amber-200">Perhatian</div>
                                <div class="text-xs text-amber-700 dark:text-amber-300 mt-0.5">
                                    Anda memiliki seminar yang belum diunggah bukti distribusinya.
                                    <a href="{{ route('mhs.nilai') }}"
                                        class="underline font-medium hover:text-amber-900">Upload Sekarang</a>
                                </div>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="py-6 text-center text-sm text-zinc-500">
                        Tidak ada Kerja Praktik yang sedang aktif.
                    </div>
                @endif
            </flux:card>
        </div>

        {{-- KOLOM KANAN (1): AKTIVITAS --}}
        <div class="lg:col-span-1 space-y-6">
            <flux:card
                class="h-full rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-stone-900 dark:text-stone-100">Seminar Terbaru</h3>
                    <a href="{{ route('mhs.kp.seminar', ['kp' => $this->activeKp->id ?? 0]) }}"
                        class="text-xs text-indigo-600 hover:underline">Lihat Semua</a>
                </div>

                <div class="space-y-0 flex-1">
                    @forelse ($this->recentSeminars as $row)
                        <div
                            class="flex items-center gap-3 py-3 border-b border-zinc-100 dark:border-zinc-800 last:border-0">
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-stone-900 dark:text-stone-100 truncate">
                                    {{ $row->judul_laporan ?? 'Tanpa Judul' }}
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    <flux:badge size="xs" :color="$row::badgeColor($row->status)">
                                        {{ $row::statusLabel($row->status) }}
                                    </flux:badge>
                                    <span class="text-[10px] text-zinc-400">
                                        {{ $row->updated_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs text-zinc-500">Belum ada aktivitas seminar.</div>
                    @endforelse
                </div>
            </flux:card>
        </div>
    </div>
</div>
