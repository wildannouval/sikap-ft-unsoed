<div class="space-y-6">

    {{-- PANDUAN (aksen violet agar konsisten dgn Komisi) --}}
    <flux:card
        class="space-y-4 rounded-xl border
               bg-white dark:bg-stone-950
               border-zinc-200 dark:border-stone-800
               shadow-xs">
        <div class="flex items-start gap-2 px-1.5 -mt-1">
            <span
                class="inline-flex items-center justify-center rounded-md p-1.5
                         bg-violet-500 text-white dark:bg-violet-400">
                <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M12 16v-4" />
                    <path d="M12 8h.01" />
                </svg>
            </span>
            <div>
                <h3 class="text-base font-semibold text-stone-900 dark:text-stone-100">
                    Panduan Status KP Dinilai
                </h3>
                <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-300 space-y-1.5">
                    <div><span class="font-medium">1)</span> Gunakan kolom <em>cari</em> untuk menemukan mahasiswa/judul
                        cepat.</div>
                    <div><span class="font-medium">2)</span> Filter <strong>BA Terbit</strong> atau
                        <strong>Dinilai</strong> via dropdown status.</div>
                    <div><span class="font-medium">3)</span> Cek <strong>Distribusi</strong> (bukti unggah) sebagai
                        prasyarat menampilkan nilai ke mahasiswa.</div>
                    <div><span class="font-medium">4)</span> Kolom <strong>Rincian</strong> memperlihatkan komponen
                        nilai (Dospem & PL) bila tersedia.</div>
                </div>
            </div>
        </div>
    </flux:card>

    {{-- FILTER BAR + LIST --}}
    <flux:card
        class="rounded-xl border
               bg-white dark:bg-stone-950
               border-zinc-200 dark:border-stone-800
               shadow-xs">

        {{-- Header beraksen violet --}}
        <div
            class="px-4 py-3 border-b
                   bg-violet-50 text-violet-700
                   dark:bg-violet-900/20 dark:text-violet-300
                   border-violet-100 dark:border-violet-900/40
                   rounded-t-xl">
            <div class="flex items-center justify-between gap-3">
                <h3 class="text-sm font-medium tracking-wide">Status KP Dinilai</h3>
                <div class="flex items-center gap-2">
                    <flux:input class="md:w-80" placeholder="Cari nama / NIM / judul…"
                        wire:model.live.debounce.400ms="q" icon="magnifying-glass" />
                    <flux:select wire:model.live="statusFilter" class="w-44">
                        <option value="all">Semua Status</option>
                        <option value="ba_terbit">BA Terbit</option>
                        <option value="dinilai">Dinilai</option>
                    </flux:select>
                    <flux:select wire:model.live="perPage" class="w-24">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </flux:select>
                </div>
            </div>
        </div>

        <div class="p-4">
            <flux:table
                class="[&_thead_th]:bg-zinc-50 [&_thead_th]:dark:bg-stone-900/40
                       [&_thead_th]:text-zinc-600 [&_thead_th]:dark:text-stone-200
                       [&_tbody_tr]:hover:bg-zinc-50/60 [&_tbody_tr]:dark:hover:bg-stone-900/30"
                :paginate="$this->items">

                <flux:table.columns>
                    <flux:table.column class="w-10">#</flux:table.column>
                    <flux:table.column>Mahasiswa</flux:table.column>
                    <flux:table.column>Judul</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Nilai Akhir</flux:table.column>
                    <flux:table.column>Rincian</flux:table.column>
                    <flux:table.column>Distribusi</flux:table.column>
                    <flux:table.column>Diperbarui</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->items as $i => $row)
                        <flux:table.row :key="$row->id">
                            <flux:table.cell>{{ $this->items->firstItem() + $i }}</flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                <div class="font-medium text-stone-900 dark:text-stone-100">
                                    {{ $row->kp?->mahasiswa?->user?->name ?? '—' }}
                                </div>
                                <div class="text-xs text-zinc-500">
                                    {{ $row->kp?->mahasiswa?->nim ?? ($row->kp?->mahasiswa?->mahasiswa_nim ?? '') }}
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="max-w-[420px]">
                                <div class="line-clamp-2 text-stone-900 dark:text-stone-100">
                                    {{ $row->judul_laporan ?? '—' }}
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge size="sm" :color="$row::badgeColor($row->status)">
                                    {{ $row::statusLabel($row->status) }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell>
                                @if ($row->grade)
                                    <div class="text-sm font-medium text-stone-900 dark:text-stone-100">
                                        {{ number_format($row->grade->final_score, 2) }}
                                        ({{ $row->grade->final_letter }})
                                    </div>
                                @else
                                    <span class="text-xs text-zinc-400">—</span>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell class="text-sm">
                                @if ($row->grade)
                                    Dospem {{ number_format($row->grade->score_dospem, 2) }}
                                    • PL {{ number_format($row->grade->score_pl, 2) }}
                                @else
                                    —
                                @endif
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                @if ($row->distribusi_proof_path)
                                    <a class="text-sm underline hover:no-underline"
                                        href="{{ asset('storage/' . $row->distribusi_proof_path) }}" target="_blank">
                                        Lihat Bukti
                                    </a>
                                @else
                                    <span class="text-xs text-zinc-400">Belum diupload</span>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell class="text-xs text-zinc-500">
                                {{ $row->updated_at?->format('d M Y H:i') ?? '—' }}
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="8">
                                <div class="py-6 text-center text-sm text-zinc-500">Belum ada data.</div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </flux:card>
</div>
