<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-stone-900 dark:text-stone-100">Dashboard Dosen Pembimbing</h2>
        <flux:badge size="sm"
            class="bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-300 border border-sky-200 dark:border-sky-800">
            SIKAP FT UNSOED
        </flux:badge>
    </div>

    {{-- STAT TILES --}}
    @php
        $cards = [
            [
                'label' => 'Bimbingan Aktif',
                'val' => $this->bimbinganAktif,
                'color' => 'indigo',
                'icon' => 'users',
            ],
            [
                'label' => 'Menunggu ACC',
                'val' => $this->stats['menungguAcc'],
                'color' => 'amber', // Pending
                'icon' => 'clock',
            ],
            [
                'label' => 'Dijadwalkan',
                'val' => $this->stats['dijadwalkan'],
                'color' => 'sky', // Scheduled
                'icon' => 'calendar',
            ],
            [
                'label' => 'BA Terbit',
                'val' => $this->stats['baTerbit'],
                'color' => 'violet', // Document
                'icon' => 'document-text',
            ],
            [
                'label' => 'Perlu Dinilai',
                'val' => $this->stats['perluNilai'],
                'color' => 'rose', // Action needed
                'icon' => 'pencil-square',
            ],
        ];
    @endphp

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @foreach ($cards as $c)
            <div class="rounded-xl border shadow-sm bg-white dark:bg-stone-950 dark:border-stone-800 overflow-hidden">
                <div class="p-4 flex items-center justify-between">
                    <div>
                        <div class="text-xs font-medium text-zinc-500 uppercase tracking-wide truncate"
                            title="{{ $c['label'] }}">
                            {{ $c['label'] }}
                        </div>
                        <div class="mt-1 text-2xl font-bold text-stone-900 dark:text-stone-100">
                            {{ number_format($c['val']) }}
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
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Daftar seminar mahasiswa bimbingan terkini.</p>
            </div>

            <div class="flex gap-3">
                <a class="text-sm text-indigo-600 hover:underline" href="{{ route('dsp.kp.seminar.approval') }}"
                    wire:navigate>
                    Lihat Persetujuan
                </a>
                <a class="text-sm text-indigo-600 hover:underline" href="{{ route('dsp.nilai') }}" wire:navigate>
                    Lihat Penilaian
                </a>
            </div>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>Mahasiswa</flux:table.column>
                <flux:table.column>Judul Laporan</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Nilai</flux:table.column>
                <flux:table.column>Diperbarui</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->recent as $row)
                    @php
                        // Helper badge dari model KpSeminar
                        $badgeColor = \App\Models\KpSeminar::badgeColor($row->status);
                        $statusLabel = \App\Models\KpSeminar::statusLabel($row->status);
                    @endphp

                    <flux:table.row :key="$row->id">
                        <flux:table.cell class="whitespace-nowrap">
                            <div class="font-medium text-stone-900 dark:text-stone-100">
                                {{ $row->kp?->mahasiswa?->user?->name ?? '—' }}
                            </div>
                            <div class="text-xs text-zinc-500">
                                {{ $row->kp?->mahasiswa?->nim ?? ($row->kp?->mahasiswa?->mahasiswa_nim ?? '') }}
                            </div>
                        </flux:table.cell>

                        <flux:table.cell class="max-w-[320px]">
                            <span class="line-clamp-2 text-stone-700 dark:text-stone-300">
                                {{ $row->judul_laporan ?? '—' }}
                            </span>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge size="sm" :color="$badgeColor">
                                {{ $statusLabel }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($row->grade)
                                <span class="font-bold text-stone-900 dark:text-stone-100">
                                    {{ number_format($row->grade->final_score, 2) }}
                                </span>
                                <span class="text-xs text-zinc-500">({{ $row->grade->final_letter }})</span>
                            @else
                                <span class="text-xs text-zinc-400">—</span>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="text-xs text-zinc-500 whitespace-nowrap">
                            {{ $row->updated_at?->diffForHumans() ?? '—' }}
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
                                <p class="text-xs text-zinc-500 mt-1">
                                    Pengajuan seminar akan muncul di sini.
                                </p>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>
</div>
