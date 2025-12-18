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

            <flux:tab.group wire:model.live="tab">
                <flux:tabs>
                    <flux:tab name="ba_terbit" icon="document-text">
                        BA Terbit
                        <flux:badge size="sm" inset="top bottom" class="ml-2" color="violet">
                            {{ $this->stats['ba_terbit'] }}</flux:badge>
                    </flux:tab>
                    <flux:tab name="dinilai" icon="star">
                        Sudah Dinilai
                        <flux:badge size="sm" inset="top bottom" class="ml-2" color="purple">
                            {{ $this->stats['dinilai'] }}</flux:badge>
                    </flux:tab>
                    <flux:tab name="selesai" icon="check-badge">
                        Selesai Distribusi
                        <flux:badge size="sm" inset="top bottom" class="ml-2" color="teal">
                            {{ $this->stats['selesai'] }}</flux:badge>
                    </flux:tab>
                </flux:tabs>

                {{-- PANEL BA TERBIT --}}
                <flux:tab.panel name="ba_terbit" class="pt-4">
                    <flux:card
                        class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm overflow-hidden">
                        {{-- Header Tabel --}}
                        <div
                            class="flex flex-col gap-4 px-6 py-4 border-b border-zinc-200 dark:border-stone-800 bg-violet-50/50 dark:bg-violet-900/10 md:flex-row md:items-center md:justify-between">
                            <h4 class="text-base font-semibold text-stone-900 dark:text-stone-100">Berita Acara Terbit
                            </h4>
                            <div class="flex items-center gap-3">
                                <flux:input icon="magnifying-glass" placeholder="Cari..."
                                    wire:model.live.debounce.300ms="search"
                                    class="w-full md:w-64 bg-white dark:bg-stone-900" />
                            </div>
                        </div>

                        <flux:table :paginate="$this->itemsBATerbit">
                            <flux:table.columns>
                                <flux:table.column class="w-10 text-center">No</flux:table.column>
                                <flux:table.column sortable wire:click="sort('updated_at')"
                                    :sorted="$sortBy === 'updated_at'" :direction="$sortDirection">Tgl Update
                                </flux:table.column>
                                <flux:table.column>Mahasiswa</flux:table.column>
                                <flux:table.column>Judul Laporan</flux:table.column>
                                <flux:table.column>Status</flux:table.column>
                                <flux:table.column class="text-right">Aksi</flux:table.column>
                            </flux:table.columns>

                            <flux:table.rows>
                                @foreach ($this->itemsBATerbit as $i => $row)
                                    <flux:table.row :key="'ba-'.$row->id">
                                        <flux:table.cell class="text-center text-zinc-500">
                                            {{ $this->itemsBATerbit->firstItem() + $i }}</flux:table.cell>
                                        <flux:table.cell class="whitespace-nowrap">
                                            {{ optional($row->updated_at)->format('d M Y') }}</flux:table.cell>
                                        <flux:table.cell>
                                            <div class="font-medium text-stone-900 dark:text-stone-100">
                                                {{ $row->kp?->mahasiswa?->user?->name ?? '—' }}</div>
                                            <div class="text-xs text-zinc-500">
                                                {{ $row->kp?->mahasiswa?->mahasiswa_nim ?? '—' }}</div>
                                        </flux:table.cell>
                                        <flux:table.cell class="max-w-[250px]">
                                            <span class="line-clamp-2"
                                                title="{{ $row->judul_laporan }}">{{ $row->judul_laporan ?? '—' }}</span>
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            <flux:badge size="sm" inset="top bottom"
                                                :color="$this->badgeColor($row->status)">
                                                {{ $this->statusLabel($row->status) }}</flux:badge>
                                        </flux:table.cell>
                                        <flux:table.cell class="text-right">
                                            <flux:button href="{{ route('bap.kp.seminar.download.ba', $row->id) }}"
                                                target="_blank" icon="arrow-down-tray" variant="ghost" size="xs">
                                                Unduh BA</flux:button>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>

                        @if ($this->itemsBATerbit->isEmpty())
                            <div class="flex flex-col items-center justify-center py-12 text-center">
                                <div class="rounded-full bg-zinc-100 p-4 dark:bg-stone-900">
                                    <flux:icon.document-text class="size-8 text-zinc-400" />
                                </div>
                                <h3 class="mt-4 text-base font-semibold text-stone-900 dark:text-stone-100">Belum ada
                                    data</h3>
                                <p class="mt-1 text-sm text-zinc-500">Belum ada BA yang terbit.</p>
                            </div>
                        @endif
                    </flux:card>
                </flux:tab.panel>

                {{-- PANEL DINILAI --}}
                <flux:tab.panel name="dinilai" class="pt-4">
                    <flux:card
                        class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm overflow-hidden">
                        <div
                            class="flex flex-col gap-4 px-6 py-4 border-b border-zinc-200 dark:border-stone-800 bg-purple-50/50 dark:bg-purple-900/10 md:flex-row md:items-center md:justify-between">
                            <h4 class="text-base font-semibold text-stone-900 dark:text-stone-100">Sudah Dinilai</h4>
                            <div class="flex items-center gap-3">
                                <flux:input icon="magnifying-glass" placeholder="Cari..."
                                    wire:model.live.debounce.300ms="search"
                                    class="w-full md:w-64 bg-white dark:bg-stone-900" />
                            </div>
                        </div>

                        <flux:table :paginate="$this->itemsDinilai">
                            <flux:table.columns>
                                <flux:table.column class="w-10 text-center">No</flux:table.column>
                                <flux:table.column sortable wire:click="sort('updated_at')"
                                    :sorted="$sortBy === 'updated_at'" :direction="$sortDirection">Tgl Nilai
                                </flux:table.column>
                                <flux:table.column>Mahasiswa</flux:table.column>
                                <flux:table.column>Nilai</flux:table.column>
                                <flux:table.column>Arsip</flux:table.column>
                            </flux:table.columns>

                            <flux:table.rows>
                                @foreach ($this->itemsDinilai as $i => $row)
                                    <flux:table.row :key="'dn-'.$row->id">
                                        <flux:table.cell class="text-center text-zinc-500">
                                            {{ $this->itemsDinilai->firstItem() + $i }}</flux:table.cell>
                                        <flux:table.cell class="whitespace-nowrap">
                                            {{ optional($row->updated_at)->format('d M Y') }}</flux:table.cell>
                                        <flux:table.cell>
                                            <div class="font-medium text-stone-900 dark:text-stone-100">
                                                {{ $row->kp?->mahasiswa?->user?->name ?? '—' }}</div>
                                            <div class="text-xs text-zinc-500">
                                                {{ $row->kp?->mahasiswa?->mahasiswa_nim ?? '—' }}</div>
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            @if ($row->grade)
                                                <div class="font-bold text-stone-900 dark:text-stone-100">
                                                    {{ number_format($row->grade->final_score, 2) }} <span
                                                        class="text-xs font-normal text-zinc-500">({{ $row->grade->final_letter }})</span>
                                                </div>
                                            @else
                                                <span class="text-zinc-400 text-xs">-</span>
                                            @endif
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            <div class="flex gap-2">
                                                <flux:button href="{{ route('bap.kp.seminar.download.ba', $row->id) }}"
                                                    target="_blank" icon="arrow-down-tray" variant="ghost"
                                                    size="xs">BA Asli</flux:button>
                                                @if ($row->grade?->ba_scan_path)
                                                    <flux:button
                                                        href="{{ asset('storage/' . $row->grade->ba_scan_path) }}"
                                                        target="_blank" icon="document-text" variant="ghost"
                                                        size="xs">Scan BA</flux:button>
                                                @endif
                                            </div>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>

                        @if ($this->itemsDinilai->isEmpty())
                            <div class="flex flex-col items-center justify-center py-12 text-center">
                                <div class="rounded-full bg-zinc-100 p-4 dark:bg-stone-900">
                                    <flux:icon.star class="size-8 text-zinc-400" />
                                </div>
                                <h3 class="mt-4 text-base font-semibold text-stone-900 dark:text-stone-100">Belum ada
                                    data</h3>
                                <p class="mt-1 text-sm text-zinc-500">Belum ada seminar yang dinilai dosen.</p>
                            </div>
                        @endif
                    </flux:card>
                </flux:tab.panel>

                {{-- PANEL SELESAI --}}
                <flux:tab.panel name="selesai" class="pt-4">
                    <flux:card
                        class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm overflow-hidden">
                        <div
                            class="flex flex-col gap-4 px-6 py-4 border-b border-zinc-200 dark:border-stone-800 bg-teal-50/50 dark:bg-teal-900/10 md:flex-row md:items-center md:justify-between">
                            <h4 class="text-base font-semibold text-stone-900 dark:text-stone-100">Selesai Distribusi
                            </h4>
                            <div class="flex items-center gap-3">
                                <flux:input icon="magnifying-glass" placeholder="Cari..."
                                    wire:model.live.debounce.300ms="search"
                                    class="w-full md:w-64 bg-white dark:bg-stone-900" />
                            </div>
                        </div>

                        <flux:table :paginate="$this->itemsSelesai">
                            <flux:table.columns>
                                <flux:table.column class="w-10 text-center">No</flux:table.column>
                                <flux:table.column sortable wire:click="sort('updated_at')"
                                    :sorted="$sortBy === 'updated_at'" :direction="$sortDirection">Tgl Selesai
                                </flux:table.column>
                                <flux:table.column>Mahasiswa</flux:table.column>
                                <flux:table.column>Nilai Akhir</flux:table.column>
                                <flux:table.column>Arsip Lengkap</flux:table.column>
                            </flux:table.columns>

                            <flux:table.rows>
                                @foreach ($this->itemsSelesai as $i => $row)
                                    <flux:table.row :key="'sl-'.$row->id">
                                        <flux:table.cell class="text-center text-zinc-500">
                                            {{ $this->itemsSelesai->firstItem() + $i }}</flux:table.cell>
                                        <flux:table.cell class="whitespace-nowrap">
                                            {{ optional($row->updated_at)->format('d M Y') }}</flux:table.cell>
                                        <flux:table.cell>
                                            <div class="font-medium text-stone-900 dark:text-stone-100">
                                                {{ $row->kp?->mahasiswa?->user?->name ?? '—' }}</div>
                                            <div class="text-xs text-zinc-500">
                                                {{ $row->kp?->mahasiswa?->mahasiswa_nim ?? '—' }}</div>
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            @if ($row->grade)
                                                <div class="font-bold text-teal-600 dark:text-teal-400">
                                                    {{ $row->grade->final_letter }} <span
                                                        class="text-xs font-normal text-zinc-500">({{ number_format($row->grade->final_score, 2) }})</span>
                                                </div>
                                            @else
                                                <span class="text-zinc-400 text-xs">-</span>
                                            @endif
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            <div class="flex gap-2 flex-wrap">
                                                <flux:button
                                                    href="{{ route('bap.kp.seminar.download.ba', $row->id) }}"
                                                    target="_blank" icon="arrow-down-tray" variant="ghost"
                                                    size="xs">BA Asli</flux:button>

                                                @if ($row->grade?->ba_scan_path)
                                                    <flux:button
                                                        href="{{ asset('storage/' . $row->grade->ba_scan_path) }}"
                                                        target="_blank" icon="document-text" variant="ghost"
                                                        size="xs">Scan BA</flux:button>
                                                @endif

                                                @if ($row->distribusi_proof_path)
                                                    <flux:button
                                                        href="{{ asset('storage/' . $row->distribusi_proof_path) }}"
                                                        target="_blank" icon="check-circle" variant="ghost"
                                                        size="xs">Bukti Distribusi</flux:button>
                                                @endif
                                            </div>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>

                        @if ($this->itemsSelesai->isEmpty())
                            <div class="flex flex-col items-center justify-center py-12 text-center">
                                <div class="rounded-full bg-zinc-100 p-4 dark:bg-stone-900">
                                    <flux:icon.check-badge class="size-8 text-zinc-400" />
                                </div>
                                <h3 class="mt-4 text-base font-semibold text-stone-900 dark:text-stone-100">Belum ada
                                    data</h3>
                                <p class="mt-1 text-sm text-zinc-500">Belum ada mahasiswa yang menyelesaikan proses
                                    distribusi.</p>
                            </div>
                        @endif
                    </flux:card>
                </flux:tab.panel>
            </flux:tab.group>
        </div>

        {{-- KOLOM KANAN: SIDEBAR (1/4) --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- RINGKASAN --}}
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

            {{-- PANDUAN --}}
            <flux:card
                class="rounded-xl border bg-indigo-50/50 dark:bg-indigo-900/10 border-indigo-100 dark:border-indigo-800/30 shadow-sm">
                <div class="flex items-start gap-3">
                    <flux:icon.information-circle class="mt-0.5 size-5 text-indigo-600 dark:text-indigo-400" />
                    <div>
                        <h3 class="font-semibold text-indigo-900 dark:text-indigo-100 text-sm">Informasi Arsip</h3>
                        <ul class="mt-3 text-xs text-indigo-800 dark:text-indigo-200 space-y-2 list-disc list-inside">
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
