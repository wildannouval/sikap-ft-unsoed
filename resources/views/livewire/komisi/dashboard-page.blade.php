<div class="space-y-6">
    {{-- HEADER --}}
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-stone-900 dark:text-stone-100">Dashboard Dosen Komisi</h2>
        <flux:badge size="sm"
            class="bg-violet-100 text-violet-800 dark:bg-violet-900/40 dark:text-violet-300 border border-violet-200 dark:border-violet-800">
            SIKAP FT UNSOED
        </flux:badge>
    </div>

    {{-- STAT TILES --}}
    @php
        // Menggunakan mapping manual agar sesuai dengan key stats di component
        // Warna disesuaikan dengan status di Model KpSeminar
        $cards = [
            [
                'label' => 'Menunggu Review',
                'key' => 'menungguReview',
                'color' => 'amber', // Status: diajukan
                'icon' => 'inbox',
            ],
            [
                'label' => 'Disetujui Pembimbing',
                'key' => 'disetujuiPemb',
                'color' => 'sky', // Status: disetujui_pembimbing
                'icon' => 'check-circle', // thumbs-up diganti check-circle agar konsisten
            ],
            [
                'label' => 'Dijadwalkan',
                'key' => 'dijadwalkan',
                'color' => 'emerald', // Status: dijadwalkan
                'icon' => 'calendar',
            ],
            [
                'label' => 'BA Terbit',
                'key' => 'baTerbit',
                'color' => 'violet', // Status: ba_terbit
                'icon' => 'document-text', // file-check diganti document-text
            ],
        ];
    @endphp

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
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
                    <div
                        class="p-2 rounded-lg bg-{{ $c['color'] }}-100 dark:bg-{{ $c['color'] }}-900/30 text-{{ $c['color'] }}-600 dark:text-{{ $c['color'] }}-400">
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
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Pengajuan yang baru diperbarui.</p>
            </div>
            <a href="{{ route('komisi.kp.review') }}" class="text-sm text-indigo-600 hover:underline">Lihat Semua</a>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>Mahasiswa</flux:table.column>
                <flux:table.column>Judul Laporan</flux:table.column>
                <flux:table.column>Status</flux:table.column>
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

                        <flux:table.cell class="text-xs text-zinc-500 whitespace-nowrap">
                            {{ $row->updated_at?->diffForHumans() ?? '—' }}
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4">
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
