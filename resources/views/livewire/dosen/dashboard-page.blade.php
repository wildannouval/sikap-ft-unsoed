<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold">Dashboard Dosen Pembimbing</h2>
        <flux:badge size="sm"
            class="bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-300 border border-sky-200 dark:border-sky-800">
            SIKAP FT UNSOED
        </flux:badge>
    </div>

    {{-- STAT TILES (accent Sky/Blue) --}}
    @php
        $cards = [
            ['label' => 'Menunggu ACC', 'key' => 'menungguAcc', 'cls' => 'amber', 'icon' => 'clock'],
            ['label' => 'Dijadwalkan', 'key' => 'dijadwalkan', 'cls' => 'sky', 'icon' => 'calendar'],
            ['label' => 'BA Terbit', 'key' => 'baTerbit', 'cls' => 'emerald', 'icon' => 'file-check'],
            ['label' => 'Perlu Dinilai', 'key' => 'perluNilai', 'cls' => 'rose', 'icon' => 'clipboard'],
            // plain (pakai properti Livewire langsung)
            [
                'label' => 'Bimbingan Aktif',
                'key' => 'bimbinganAktif',
                'cls' => 'indigo',
                'icon' => 'users',
                'plain' => true,
            ],
        ];

        $clsHeader = [
            'indigo' =>
                'bg-indigo-50 border-indigo-100 text-indigo-700 dark:bg-indigo-900/20 dark:border-indigo-900/40 dark:text-indigo-300',
            'amber' =>
                'bg-amber-50  border-amber-100  text-amber-700  dark:bg-amber-900/20  dark:border-amber-900/40  dark:text-amber-300',
            'sky' =>
                'bg-sky-50    border-sky-100    text-sky-700    dark:bg-sky-900/20    dark:border-sky-900/40    dark:text-sky-300',
            'emerald' =>
                'bg-emerald-50 border-emerald-100 text-emerald-700 dark:bg-emerald-900/20 dark:border-emerald-900/40 dark:text-emerald-300',
            'rose' =>
                'bg-rose-50   border-rose-100   text-rose-700   dark:bg-rose-900/20   dark:border-rose-900/40   dark:text-rose-300',
            'stone' =>
                'bg-stone-100 border-stone-200  text-stone-700  dark:bg-stone-900/30  dark:border-stone-800     dark:text-stone-300',
        ];

        $clsIcon = [
            'indigo' => 'bg-indigo-500 text-white dark:bg-indigo-400',
            'amber' => 'bg-amber-500  text-white dark:bg-amber-400',
            'sky' => 'bg-sky-500    text-white dark:bg-sky-400',
            'emerald' => 'bg-emerald-500 text-white dark:bg-emerald-400',
            'rose' => 'bg-rose-500   text-white dark:bg-rose-400',
            'stone' => 'bg-stone-500  text-white dark:bg-stone-400',
        ];

        function dspIcon($name)
        {
            $icons = [
                'clock' =>
                    '<svg viewBox="0 0 24 24" class="size-3.5" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v6l4 2"/></svg>',
                'calendar' =>
                    '<svg viewBox="0 0 24 24" class="size-3.5" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>',
                'file-check' =>
                    '<svg viewBox="0 0 24 24" class="size-3.5" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="m9 15 2 2 4-4"/></svg>',
                'clipboard' =>
                    '<svg viewBox="0 0 24 24" class="size-3.5" fill="none" stroke="currentColor" stroke-width="2"><rect x="8" y="2" width="8" height="4" rx="1"/><rect x="4" y="4" width="16" height="18" rx="2"/><path d="M9 12h6M9 16h6"/></svg>',
                'users' =>
                    '<svg viewBox="0 0 24 24" class="size-3.5" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
            ];
            return $icons[$name] ?? '';
        }
    @endphp

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @foreach ($cards as $c)
            <div class="rounded-xl border shadow-xs bg-white dark:bg-stone-950 dark:border-stone-800 overflow-hidden">
                <div class="px-4 py-3 border-b flex items-center gap-2 {{ $clsHeader[$c['cls']] }}">
                    <span class="inline-flex items-center justify-center rounded-md {{ $clsIcon[$c['cls']] }} p-1.5">
                        {!! dspIcon($c['icon']) !!}
                    </span>
                    <div class="text-xs font-medium tracking-wide">{{ strtoupper($c['label']) }}</div>
                </div>
                <div class="px-4 py-5">
                    <div class="text-3xl font-bold text-stone-900 dark:text-stone-100">
                        @if (!empty($c['plain']))
                            {{ number_format($this->bimbinganAktif ?? 0) }}
                        @else
                            {{ number_format($this->stats[$c['key']] ?? 0) }}
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <flux:card>
        <div class="flex items-center justify-between">
            <h3 class="text-base font-semibold">Aktivitas Terbaru</h3>
            <div class="flex gap-3">
                <a class="text-sm underline" href="{{ route('dsp.kp.seminar.approval') }}" wire:navigate>Persetujuan
                    Seminar</a>
                <a class="text-sm underline" href="{{ route('dsp.nilai') }}" wire:navigate>Penilaian KP</a>
            </div>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>Mahasiswa</flux:table.column>
                <flux:table.column>Judul</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Nilai</flux:table.column>
                <flux:table.column>Diperbarui</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse ($this->recent as $row)
                    <flux:table.row :key="$row->id">
                        <flux:table.cell class="whitespace-nowrap">
                            {{ $row->kp?->mahasiswa?->user?->name ?? '—' }}
                            <div class="text-xs text-zinc-500">{{ $row->kp?->mahasiswa?->nim ?? '' }}</div>
                        </flux:table.cell>
                        <flux:table.cell class="max-w-[420px]">
                            <span class="line-clamp-2">{{ $row->judul_laporan ?? '—' }}</span>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" :color="$row::badgeColor($row->status)">
                                {{ $row::statusLabel($row->status) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($row->grade)
                                {{ number_format($row->grade->final_score, 2) }} ({{ $row->grade->final_letter }})
                            @else
                                <span class="text-xs text-zinc-400">—</span>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell class="text-xs text-zinc-500">
                            {{ $row->updated_at?->format('d M Y H:i') ?? '—' }}
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5">
                            <div class="py-6 text-center text-sm text-zinc-500">Belum ada aktivitas.</div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>
</div>
