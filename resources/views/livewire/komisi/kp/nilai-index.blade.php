<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <flux:heading size="xl" level="1" class="text-stone-900 dark:text-stone-100">
                Status Nilai Kerja Praktik
            </flux:heading>
            <flux:subheading class="text-zinc-600 dark:text-zinc-300">
                Pantau status penilaian, rekap nilai akhir, dan bukti distribusi laporan.
            </flux:subheading>
        </div>
    </div>
    <flux:separator variant="subtle" />

    {{-- GRID UTAMA 3:1 --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">

        {{-- KOLOM KIRI: TABEL (3/4) --}}
        <div class="lg:col-span-3 space-y-6">
            <flux:card
                class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm overflow-hidden">

                {{-- Header Tabel --}}
                <div
                    class="flex flex-col gap-4 px-6 py-4 border-b border-zinc-200 dark:border-stone-800 bg-violet-50/50 dark:bg-violet-900/10 md:flex-row md:items-center md:justify-between">
                    <h4 class="text-base font-semibold text-stone-900 dark:text-stone-100">Daftar Penilaian</h4>

                    <div class="flex flex-col gap-3 md:flex-row md:items-center">
                        <div class="w-full md:w-64">
                            <flux:input icon="magnifying-glass" placeholder="Cari nama / judul..."
                                wire:model.live.debounce.400ms="q" class="bg-white dark:bg-stone-900" />
                        </div>
                        <div class="w-full md:w-40">
                            <flux:select wire:model.live="statusFilter" class="bg-white dark:bg-stone-900">
                                <flux:select.option value="all">Semua Status</flux:select.option>
                                <flux:select.option value="ba_terbit">BA Terbit</flux:select.option>
                                <flux:select.option value="dinilai">Dinilai</flux:select.option>
                                <flux:select.option value="selesai">Selesai</flux:select.option>
                            </flux:select>
                        </div>
                        <div class="w-full md:w-24">
                            <flux:select wire:model.live="perPage" class="bg-white dark:bg-stone-900">
                                <flux:select.option :value="10">10</flux:select.option>
                                <flux:select.option :value="25">25</flux:select.option>
                                <flux:select.option :value="50">50</flux:select.option>
                            </flux:select>
                        </div>
                    </div>
                </div>

                <flux:table :paginate="$this->items">
                    <flux:table.columns>
                        <flux:table.column class="w-10 text-center">No</flux:table.column>
                        <flux:table.column sortable :sorted="$sortBy === 'updated_at'" :direction="$sortDirection"
                            wire:click="sort('updated_at')">Tanggal</flux:table.column>
                        <flux:table.column>Mahasiswa</flux:table.column>
                        <flux:table.column>Judul</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                        <flux:table.column>Nilai Akhir</flux:table.column>
                        <flux:table.column>Arsip</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @forelse ($this->items as $i => $row)
                            <flux:table.row :key="$row->id">
                                <flux:table.cell class="text-center text-zinc-500">
                                    {{ $this->items->firstItem() + $i }}
                                </flux:table.cell>

                                <flux:table.cell class="whitespace-nowrap">
                                    {{ optional($row->updated_at)->format('d M Y') }}
                                </flux:table.cell>

                                <flux:table.cell>
                                    <div class="font-medium text-stone-900 dark:text-stone-100">
                                        {{ $row->kp?->mahasiswa?->user?->name ?? '—' }}
                                    </div>
                                    <div class="text-xs text-zinc-500">
                                        {{ $row->kp?->mahasiswa?->mahasiswa_nim ?? '—' }}
                                    </div>
                                </flux:table.cell>

                                <flux:table.cell class="max-w-[280px]">
                                    <span class="line-clamp-2 text-stone-700 dark:text-stone-300"
                                        title="{{ $row->judul_laporan }}">
                                        {{ $row->judul_laporan ?? '—' }}
                                    </span>
                                </flux:table.cell>

                                <flux:table.cell>
                                    <flux:badge size="sm" :color="$this->badgeColor($row->status)"
                                        inset="top bottom" :icon="$this->badgeIcon($row->status)">
                                        {{ $this->statusLabel($row->status) }}
                                    </flux:badge>
                                </flux:table.cell>

                                <flux:table.cell>
                                    @if ($row->grade)
                                        <div class="font-bold text-stone-900 dark:text-stone-100">
                                            {{ number_format($row->grade->final_score, 2) }}
                                            <span
                                                class="text-zinc-500 font-normal text-xs">({{ $row->grade->final_letter }})</span>
                                        </div>
                                    @else
                                        <span class="text-xs text-zinc-400 italic">Belum dinilai</span>
                                    @endif
                                </flux:table.cell>

                                <flux:table.cell>
                                    <div class="flex flex-col gap-1 items-start">
                                        @if ($row->grade?->ba_scan_path)
                                            <a class="flex items-center gap-1 text-xs text-indigo-600 hover:underline"
                                                href="{{ asset('storage/' . $row->grade->ba_scan_path) }}"
                                                target="_blank">
                                                <flux:icon.document-text class="size-3" /> Scan BA
                                            </a>
                                        @endif
                                        @if ($row->distribusi_proof_path)
                                            <a class="flex items-center gap-1 text-xs text-emerald-600 hover:underline"
                                                href="{{ asset('storage/' . $row->distribusi_proof_path) }}"
                                                target="_blank">
                                                <flux:icon.check-circle class="size-3" /> Distribusi
                                            </a>
                                        @endif
                                        @if (!$row->grade?->ba_scan_path && !$row->distribusi_proof_path)
                                            <span class="text-xs text-zinc-400">—</span>
                                        @endif
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="7">
                                    <div class="py-12 text-center">
                                        <div
                                            class="inline-flex items-center justify-center p-3 rounded-full bg-zinc-100 dark:bg-zinc-800 mb-3">
                                            <flux:icon.clipboard-document-list class="size-6 text-zinc-400" />
                                        </div>
                                        <h3 class="text-sm font-medium text-stone-900 dark:text-stone-100">Belum ada
                                            data</h3>
                                        <p class="text-xs text-zinc-500 mt-1">
                                            @if ($q)
                                                Tidak ada data yang cocok dengan pencarian.
                                            @else
                                                Belum ada KP yang masuk tahap penilaian.
                                            @endif
                                        </p>
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </flux:card>
        </div>

        {{-- KOLOM KANAN: SIDEBAR (1/4) --}}
        <div class="lg:col-span-1 space-y-6">

            {{-- 1. RINGKASAN --}}
            <flux:card
                class="rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm">
                <div class="mb-4 flex items-center gap-2">
                    <flux:icon.chart-bar class="size-5 text-zinc-500" />
                    <h3 class="font-semibold text-stone-900 dark:text-stone-100">Ringkasan</h3>
                </div>

                <div class="space-y-3">
                    <div
                        class="flex items-center justify-between p-2 rounded-lg bg-violet-50/50 dark:bg-violet-900/10 border border-violet-100 dark:border-violet-800/30">
                        <div class="flex items-center gap-2">
                            <div class="size-2 rounded-full bg-violet-500"></div>
                            <span class="text-sm font-medium text-stone-700 dark:text-stone-300">BA Terbit</span>
                        </div>
                        <span
                            class="text-lg font-bold text-violet-600 dark:text-violet-400">{{ $this->stats['ba_terbit'] }}</span>
                    </div>

                    <div
                        class="flex items-center justify-between p-2 rounded-lg bg-purple-50/50 dark:bg-purple-900/10 border border-purple-100 dark:border-purple-800/30">
                        <div class="flex items-center gap-2">
                            <div class="size-2 rounded-full bg-purple-500"></div>
                            <span class="text-sm font-medium text-stone-700 dark:text-stone-300">Dinilai</span>
                        </div>
                        <span
                            class="text-lg font-bold text-purple-600 dark:text-purple-400">{{ $this->stats['dinilai'] }}</span>
                    </div>

                    <div
                        class="flex items-center justify-between p-2 rounded-lg bg-teal-50/50 dark:bg-teal-900/10 border border-teal-100 dark:border-teal-800/30">
                        <div class="flex items-center gap-2">
                            <div class="size-2 rounded-full bg-teal-500"></div>
                            <span class="text-sm font-medium text-stone-700 dark:text-stone-300">Selesai</span>
                        </div>
                        <span
                            class="text-lg font-bold text-teal-600 dark:text-teal-400">{{ $this->stats['selesai'] }}</span>
                    </div>
                </div>
            </flux:card>

            {{-- 2. PANDUAN --}}
            <flux:card
                class="rounded-xl border bg-violet-50/50 dark:bg-violet-900/10 border-violet-100 dark:border-violet-800/30 shadow-sm">
                <div class="flex items-start gap-3">
                    <flux:icon.information-circle class="mt-0.5 size-5 text-violet-600 dark:text-violet-400" />
                    <div>
                        <h3 class="font-semibold text-violet-900 dark:text-violet-100 text-sm">Informasi Nilai</h3>
                        <ul class="mt-3 text-xs text-violet-800 dark:text-violet-200 space-y-2 list-disc list-inside">
                            <li><strong>BA Terbit:</strong> Menunggu penilaian dari Dosen Pembimbing.</li>
                            <li><strong>Dinilai:</strong> Nilai sudah masuk, menunggu mahasiswa upload distribusi.</li>
                            <li><strong>Selesai:</strong> Proses administrasi KP lengkap.</li>
                        </ul>
                    </div>
                </div>
            </flux:card>
        </div>
    </div>
</div>
