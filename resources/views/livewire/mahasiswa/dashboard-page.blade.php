<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold">Dashboard Mahasiswa</h2>
        <flux:badge size="sm"
            class="bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
            SIKAP FT UNSOED
        </flux:badge>
    </div>

    {{-- STAT TILES (accent Indigo) --}}
    @php
        // label, key, color classes, icon (inline svg)
        $cards = [
            ['label' => 'Total Seminar', 'key' => 'total', 'cls' => 'indigo', 'icon' => 'chart'],
            ['label' => 'Menunggu ACC', 'key' => 'diajukan', 'cls' => 'amber', 'icon' => 'clock'],
            ['label' => 'Dijadwalkan', 'key' => 'dijadwalkan', 'cls' => 'sky', 'icon' => 'calendar'],
            ['label' => 'BA Terbit', 'key' => 'ba_terbit', 'cls' => 'emerald', 'icon' => 'file-check'],
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

        function svgIcon($name)
        {
            $icons = [
                'chart' =>
                    '<svg viewBox="0 0 24 24" class="size-3.5" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M7 13v5"/><path d="M12 9v9"/><path d="M17 5v13"/></svg>',
                'clock' =>
                    '<svg viewBox="0 0 24 24" class="size-3.5" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v6l4 2"/></svg>',
                'calendar' =>
                    '<svg viewBox="0 0 24 24" class="size-3.5" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>',
                'file-check' =>
                    '<svg viewBox="0 0 24 24" class="size-3.5" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="m9 15 2 2 4-4"/></svg>',
            ];
            return $icons[$name] ?? '';
        }
    @endphp

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($cards as $c)
            <div class="rounded-xl border shadow-xs bg-white dark:bg-stone-950 dark:border-stone-800 overflow-hidden">
                <div class="px-4 py-3 border-b flex items-center gap-2 {{ $clsHeader[$c['cls']] }}">
                    <span class="inline-flex items-center justify-center rounded-md {{ $clsIcon[$c['cls']] }} p-1.5">
                        {!! svgIcon($c['icon']) !!}
                    </span>
                    <div class="text-xs font-medium tracking-wide">{{ strtoupper($c['label']) }}</div>
                </div>
                <div class="px-4 py-5">
                    <div class="text-3xl font-bold text-stone-900 dark:text-stone-100">
                        {{ number_format($this->seminarStats[$c['key']] ?? 0) }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- STATUS KP AKTIF --}}
    <flux:card class="space-y-3">
        <div class="flex items-center gap-2">
            <h3 class="text-base font-semibold">Status KP Aktif</h3>
            <flux:spacer />
            @if (($this->activeKp?->verified_consultations_count ?? 0) >= 6)
                <flux:badge size="sm"
                    class="bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                    Siap Daftar Seminar
                </flux:badge>
            @endif
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="space-y-1">
                <div class="text-sm text-zinc-500">Status KP</div>
                <div class="font-medium">{{ $this->activeKp->status ?? '—' }}</div>
            </div>
            <div class="space-y-1">
                <div class="text-sm text-zinc-500">Konsultasi Terverifikasi</div>
                <div class="font-medium">{{ $this->activeKp->verified_consultations_count ?? 0 }}</div>
            </div>
            <div class="space-y-1">
                <div class="text-sm text-zinc-500">Butuh Upload Distribusi</div>
                <div class="font-medium">
                    @if ($this->needDistribusi)
                        <span class="inline-flex items-center gap-1">
                            <span class="size-2 rounded-full bg-amber-500 animate-pulse"></span> Ya
                        </span>
                    @else
                        Tidak
                    @endif
                </div>
            </div>
        </div>
    </flux:card>

    {{-- AKTIVITAS SEMINAR TERBARU --}}
    <flux:card>
        <div class="flex items-center justify-between">
            <h3 class="text-base font-semibold">Aktivitas Seminar Terbaru</h3>
            <a class="text-sm underline" href="{{ route('mhs.nilai') }}" wire:navigate>Lihat Nilai</a>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>Judul</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Nilai</flux:table.column>
                <flux:table.column>Diperbarui</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse ($this->recentSeminars as $row)
                    <flux:table.row :key="$row->id">
                        <flux:table.cell class="max-w-[420px]">
                            <span class="line-clamp-2">{{ $row->judul_laporan ?? '—' }}</span>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" :color="$row::badgeColor($row->status)">
                                {{ $row::statusLabel($row->status) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($row->distribusi_proof_path && $row->grade)
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
                        <flux:table.cell colspan="4">
                            <div class="py-6 text-center text-sm text-zinc-500">Belum ada aktivitas.</div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>
</div>
