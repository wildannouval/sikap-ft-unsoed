<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-stone-900 dark:text-stone-100">Dashboard Bapendik</h2>
        <flux:badge size="sm" color="amber" inset="top bottom">SIKAP FT UNSOED</flux:badge>
    </div>

    {{-- STAT TILES --}}
    @php
        $cards = [
            [
                'label' => 'Menunggu Jadwal',
                'key' => 'menungguJadwal',
                'icon' => 'clock',
                // Definisikan class lengkap agar Tailwind JIT mendeteksinya
                'bgClass' => 'bg-sky-100 dark:bg-sky-900/30',
                'textClass' => 'text-sky-600 dark:text-sky-400',
            ],
            [
                'label' => 'Dijadwalkan',
                'key' => 'dijadwalkan',
                'icon' => 'calendar',
                'bgClass' => 'bg-emerald-100 dark:bg-emerald-900/30',
                'textClass' => 'text-emerald-600 dark:text-emerald-400',
            ],
            [
                'label' => 'BA Terbit',
                'key' => 'baTerbit',
                'icon' => 'document-text',
                'bgClass' => 'bg-violet-100 dark:bg-violet-900/30',
                'textClass' => 'text-violet-600 dark:text-violet-400',
            ],
            [
                'label' => 'Sudah Dinilai',
                'key' => 'dinilai',
                'icon' => 'star',
                'bgClass' => 'bg-purple-100 dark:bg-purple-900/30',
                'textClass' => 'text-purple-600 dark:text-purple-400',
            ],
            [
                'label' => 'Total Mahasiswa',
                'key' => 'mhs',
                'icon' => 'user-group',
                'bgClass' => 'bg-zinc-100 dark:bg-zinc-800',
                'textClass' => 'text-zinc-600 dark:text-zinc-400',
            ],
            [
                'label' => 'Total Dosen',
                'key' => 'dosen',
                'icon' => 'academic-cap',
                'bgClass' => 'bg-indigo-100 dark:bg-indigo-900/30',
                'textClass' => 'text-indigo-600 dark:text-indigo-400',
            ],
        ];
    @endphp

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        @foreach ($cards as $c)
            <div class="rounded-xl border shadow-sm bg-white dark:bg-stone-950 dark:border-stone-800 overflow-hidden">
                <div class="p-4 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-medium text-zinc-500 uppercase tracking-wide truncate"
                            title="{{ $c['label'] }}">
                            {{ $c['label'] }}
                        </div>
                        <div class="mt-1 text-2xl font-bold text-stone-900 dark:text-stone-100">
                            {{ number_format($this->stats[$c['key']] ?? 0) }}
                        </div>
                    </div>

                    {{-- Gunakan class lengkap dari array --}}
                    <div class="p-2 rounded-lg {{ $c['bgClass'] }} {{ $c['textClass'] }}">
                        <flux:icon :name="$c['icon']" class="size-6" />
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- AKTIVITAS TERBARU --}}
    <flux:card>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-base font-semibold text-stone-900 dark:text-stone-100">Aktivitas Seminar Terbaru</h3>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Daftar seminar yang baru diperbarui.</p>
            </div>
            {{-- Tombol Lihat Semua (Button Style) --}}
            <flux:button href="{{ route('bap.kp.seminar.jadwal') }}" variant="ghost" size="sm"
                icon-trailing="arrow-right">
                Lihat Semua
            </flux:button>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>Mahasiswa</flux:table.column>
                <flux:table.column>Judul Laporan</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>BA Scan</flux:table.column>
                <flux:table.column>Diperbarui</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->recent as $row)
                    <flux:table.row :key="$row->id">
                        <flux:table.cell class="whitespace-nowrap">
                            <div class="font-medium text-stone-900 dark:text-stone-100">
                                {{ $row->kp?->mahasiswa?->user?->name ?? '—' }}
                            </div>
                            <div class="text-xs text-zinc-500">
                                {{ $row->kp?->mahasiswa?->mahasiswa_nim ?? '' }}
                            </div>
                        </flux:table.cell>

                        <flux:table.cell class="max-w-[320px]">
                            <span class="line-clamp-2 text-stone-700 dark:text-stone-300"
                                title="{{ $row->judul_laporan }}">
                                {{ $row->judul_laporan ?? '—' }}
                            </span>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge size="sm" :color="$row::badgeColor($row->status)">
                                {{ $row::statusLabel($row->status) }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($row->ba_scan_path)
                                {{-- Tombol Lihat (Button Style) --}}
                                <flux:button href="{{ asset('storage/' . $row->ba_scan_path) }}" target="_blank"
                                    variant="ghost" size="xs" icon="document-text">
                                    Lihat
                                </flux:button>
                            @else
                                <span class="text-xs text-zinc-400">—</span>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="text-xs text-zinc-500 whitespace-nowrap">
                            {{-- DiffForHumans Bahasa Indonesia --}}
                            {{ $row->updated_at?->locale('id')->diffForHumans() ?? '—' }}
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5">
                            <div class="py-12 text-center">
                                <div
                                    class="inline-flex items-center justify-center p-3 rounded-full bg-zinc-100 dark:bg-zinc-800 mb-3">
                                    <flux:icon.inbox class="size-6 text-zinc-400" />
                                </div>
                                <h3 class="text-sm font-medium text-stone-900 dark:text-stone-100">Belum ada aktivitas
                                </h3>
                                <p class="text-xs text-zinc-500 mt-1">Data seminar akan muncul di sini.</p>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>
</div>
