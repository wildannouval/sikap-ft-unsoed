<div class="space-y-6">
    <flux:toast />

    {{-- HEADER --}}
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <flux:heading size="xl" level="1" class="text-stone-900 dark:text-stone-100">
                Arsip Nilai & BA
            </flux:heading>
            <flux:subheading class="text-zinc-600 dark:text-zinc-300">
                Rekap nilai akhir, arsip Berita Acara, dan bukti distribusi.
            </flux:subheading>
        </div>
    </div>

    <flux:separator variant="subtle" />

    {{-- GRID UTAMA --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">

        {{-- KOLOM KIRI: TABEL (3/4) --}}
        <div class="lg:col-span-3 space-y-6">
            <flux:card
                class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm overflow-hidden">

                {{-- Header Tabel --}}
                <div
                    class="flex flex-col gap-4 px-6 py-4 border-b border-zinc-200 dark:border-stone-800 bg-sky-50/50 dark:bg-sky-900/10 md:flex-row md:items-center md:justify-between">
                    <h4 class="text-base font-semibold text-stone-900 dark:text-stone-100">Daftar Arsip</h4>

                    <div class="flex flex-col gap-3 md:flex-row md:items-center">
                        <div class="w-full md:w-56">
                            <flux:input icon="magnifying-glass" placeholder="Cari..."
                                wire:model.live.debounce.300ms="search" class="bg-white dark:bg-stone-900" />
                        </div>
                        <div class="w-full md:w-40">
                            <flux:select wire:model.live="statusFilter" class="bg-white dark:bg-stone-900">
                                <flux:select.option value="all">Semua Status</flux:select.option>
                                <flux:select.option value="ba_terbit">BA Terbit</flux:select.option>
                                <flux:select.option value="dinilai">Dinilai</flux:select.option>
                                <flux:select.option value="selesai">Selesai</flux:select.option>
                            </flux:select>
                        </div>
                    </div>
                </div>

                <flux:table :paginate="$this->items">
                    <flux:table.columns>
                        <flux:table.column class="w-10 text-center">No</flux:table.column>
                        <flux:table.column sortable :sorted="$sortBy === 'updated_at'" :direction="$sortDirection"
                            wire:click="sort('updated_at')">Tgl Update</flux:table.column>
                        <flux:table.column>Mahasiswa</flux:table.column>
                        <flux:table.column>Judul Laporan</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                        <flux:table.column>Nilai</flux:table.column>
                        <flux:table.column>Arsip</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @forelse ($this->items as $i => $row)
                            @php
                                $status = $row->status;
                                $badgeTheme = match ($status) {
                                    'dinilai' => ['color' => 'purple', 'icon' => 'star'],
                                    'ba_terbit' => ['color' => 'violet', 'icon' => 'document-text'],
                                    'selesai' => ['color' => 'teal', 'icon' => 'check-badge'],
                                    default => ['color' => 'zinc', 'icon' => 'minus'],
                                };
                            @endphp

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

                                <flux:table.cell class="max-w-[250px]">
                                    <span class="line-clamp-2" title="{{ $row->judul_laporan }}">
                                        {{ $row->judul_laporan ?? '—' }}
                                    </span>
                                </flux:table.cell>

                                <flux:table.cell>
                                    <flux:badge size="sm" inset="top bottom" :color="$badgeTheme['color']"
                                        :icon="$badgeTheme['icon']">
                                        {{ $this->statusLabel($status) }}
                                    </flux:badge>
                                </flux:table.cell>

                                <flux:table.cell>
                                    @if ($row->grade)
                                        <div class="font-medium text-sm">
                                            {{ number_format($row->grade->final_score, 2) }}
                                            <span class="text-xs text-zinc-500">({{ $row->grade->final_letter }})</span>
                                        </div>
                                    @else
                                        <span class="text-xs text-zinc-400">—</span>
                                    @endif
                                </flux:table.cell>

                                <flux:table.cell>
                                    <div class="flex flex-col gap-1">
                                        @if ($row->status === 'ba_terbit' || $row->status === 'dinilai' || $row->status === 'selesai')
                                            <a class="text-xs text-indigo-600 hover:underline flex items-center gap-1"
                                                href="{{ route('bap.kp.seminar.download.ba', $row->id) }}"
                                                target="_blank">
                                                <flux:icon.arrow-down-tray class="size-3" /> Unduh BA
                                            </a>
                                        @endif

                                        @if ($row->grade?->ba_scan_path)
                                            <a class="text-xs text-purple-600 hover:underline flex items-center gap-1"
                                                href="{{ asset('storage/' . $row->grade->ba_scan_path) }}"
                                                target="_blank">
                                                <flux:icon.document-text class="size-3" /> Scan BA
                                            </a>
                                        @endif

                                        @if ($row->distribusi_proof_path)
                                            <a class="text-xs text-teal-600 hover:underline flex items-center gap-1"
                                                href="{{ asset('storage/' . $row->distribusi_proof_path) }}"
                                                target="_blank">
                                                <flux:icon.check-circle class="size-3" /> Bukti Distribusi
                                            </a>
                                        @endif
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="7">
                                    <div class="py-8 text-center text-sm text-zinc-500">
                                        Tidak ada data arsip nilai atau BA.
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>

                {{-- Empty State --}}
                @if ($this->items->isEmpty())
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <div class="rounded-full bg-zinc-100 p-4 dark:bg-stone-900">
                            <flux:icon.archive-box class="size-8 text-zinc-400" />
                        </div>
                        <h3 class="mt-4 text-base font-semibold text-stone-900 dark:text-stone-100">
                            Data Kosong
                        </h3>
                        <p class="mt-1 text-sm text-zinc-500">
                            @if ($search)
                                Tidak ditemukan data yang cocok dengan pencarian "{{ $search }}".
                            @else
                                Belum ada arsip nilai yang masuk.
                            @endif
                        </p>
                    </div>
                @endif
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
                class="rounded-xl border bg-sky-50/50 dark:bg-sky-900/10 border-sky-100 dark:border-sky-800/30 shadow-sm">
                <div class="flex items-start gap-3">
                    <flux:icon.information-circle class="mt-0.5 size-5 text-sky-600 dark:text-sky-400" />
                    <div>
                        <h3 class="font-semibold text-sky-900 dark:text-sky-100 text-sm">Informasi Arsip</h3>
                        <ul class="mt-3 text-xs text-sky-800 dark:text-sky-200 space-y-2 list-disc list-inside">
                            <li><strong>BA Terbit:</strong> Berita acara sudah diterbitkan, belum dinilai dosen.</li>
                            <li><strong>Dinilai:</strong> Dosen sudah memberi nilai dan mengunggah scan BA.</li>
                            <li><strong>Selesai:</strong> Mahasiswa sudah mengunggah bukti distribusi laporan.</li>
                            <li>Gunakan kolom <strong>Arsip</strong> untuk mengunduh dokumen terkait.</li>
                        </ul>
                    </div>
                </div>
            </flux:card>
        </div>
    </div>
</div>
