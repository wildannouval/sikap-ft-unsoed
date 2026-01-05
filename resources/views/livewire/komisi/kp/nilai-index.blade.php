<div class="space-y-6">
    <flux:toast />

    {{-- HEADER --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <flux:heading size="xl" level="1" class="text-stone-900 dark:text-stone-100">
                Status Nilai Kerja Praktik
            </flux:heading>
            <flux:subheading class="text-zinc-600 dark:text-zinc-300">
                Pantau status penilaian dan ekspor rekap nilai mahasiswa.
            </flux:subheading>
        </div>

        <flux:button variant="primary" icon="document-arrow-down" wire:click="export" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="export">Export Excel</span>
            <span wire:loading wire:target="export">Proses...</span>
        </flux:button>
    </div>

    <flux:separator variant="subtle" />

    {{-- GRID UTAMA 3:1 --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">

        {{-- KOLOM KIRI: TABEL UTAMA --}}
        <div class="lg:col-span-3 space-y-6">
            <flux:card class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 p-0 overflow-hidden shadow-sm">

                {{-- FILTER BAR --}}
                <div class="p-4 border-b bg-zinc-50/50 dark:bg-zinc-900/10 space-y-4">
                    <div class="flex flex-col md:flex-row gap-3">
                        <flux:input icon="magnifying-glass" placeholder="Cari mahasiswa / judul..."
                            wire:model.live.debounce.400ms="q" class="flex-1" />

                        <flux:select wire:model.live="statusFilter" class="w-full md:w-48">
                            <flux:select.option value="all">Semua Status</flux:select.option>
                            <flux:select.option value="ba_terbit">BA Terbit</flux:select.option>
                            <flux:select.option value="dinilai">Dinilai</flux:select.option>
                            <flux:select.option value="selesai">Selesai</flux:select.option>
                        </flux:select>
                    </div>

                    <div class="flex flex-col md:flex-row items-center gap-4">
                        <div class="flex items-center gap-2 w-full md:w-auto">
                            <div class="flex-1 md:w-40">
                                <flux:input type="date" wire:model.live="startDate" label="Dari" size="sm" />
                            </div>
                            <span class="mt-5 text-zinc-400">—</span>
                            <div class="flex-1 md:w-40">
                                <flux:input type="date" wire:model.live="endDate" label="Sampai" size="sm" />
                            </div>
                        </div>

                        <div class="md:ml-auto mt-5">
                            <flux:select wire:model.live="perPage" size="sm" class="w-24">
                                <flux:select.option :value="10">10 / hal</flux:select.option>
                                <flux:select.option :value="25">25 / hal</flux:select.option>
                                <flux:select.option :value="50">50 / hal</flux:select.option>
                            </flux:select>
                        </div>
                    </div>
                </div>

                {{-- TABLE WRAPPER (Menambah Margin agar tidak mepet ke tepi card) --}}
                <div class="px-6 pb-4">
                    <flux:table :paginate="$this->items">
                        <flux:table.columns>
                            <flux:table.column class="w-10 text-center">No</flux:table.column>
                            <flux:table.column sortable :sorted="$sortBy === 'updated_at'" :direction="$sortDirection"
                                wire:click="sort('updated_at')">Terakhir Update</flux:table.column>
                            <flux:table.column>Mahasiswa</flux:table.column>
                            <flux:table.column>Status</flux:table.column>
                            <flux:table.column>Nilai</flux:table.column>
                            <flux:table.column align="end">Berkas</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @forelse ($this->items as $i => $row)
                                <flux:table.row :key="$row->id">
                                    <flux:table.cell class="text-center text-zinc-500">
                                        {{ $this->items->firstItem() + $i }}
                                    </flux:table.cell>
                                    <flux:table.cell class="whitespace-nowrap text-xs">
                                        {{ optional($row->updated_at)->format('d/m/Y H:i') }}
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <div class="font-medium text-stone-900 dark:text-stone-100">
                                            {{ $row->kp?->mahasiswa?->user?->name ?? '—' }}
                                        </div>
                                        <div class="text-xs text-zinc-500">{{ $row->kp?->mahasiswa?->mahasiswa_nim }}
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:badge size="sm" :color="$this->badgeColor($row->status)"
                                            :icon="$this->badgeIcon($row->status)">
                                            {{ $this->statusLabel($row->status) }}
                                        </flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        @if ($row->grade)
                                            <span
                                                class="font-bold text-stone-900 dark:text-stone-100">{{ $row->grade->final_score }}</span>
                                            <span class="text-zinc-500 text-xs">({{ $row->grade->final_letter }})</span>
                                        @else
                                            <span class="text-xs text-zinc-400 italic">—</span>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <div class="flex justify-end gap-2">
                                            @if ($row->grade?->ba_scan_path)
                                                <flux:button size="xs" icon="document-text" variant="ghost"
                                                    href="{{ asset('storage/' . $row->grade->ba_scan_path) }}"
                                                    target="_blank" title="Lihat BA" />
                                            @endif
                                            @if ($row->distribusi_proof_path)
                                                <flux:button size="xs" icon="check-badge" variant="ghost"
                                                    color="emerald"
                                                    href="{{ asset('storage/' . $row->distribusi_proof_path) }}"
                                                    target="_blank" title="Bukti Distribusi" />
                                            @endif
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="6" class="text-center py-12 text-zinc-400 italic">
                                        Data tidak ditemukan.
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </div>
            </flux:card>
        </div>

        {{-- KOLOM KANAN: SIDEBAR --}}
        <div class="lg:col-span-1 space-y-6">

            {{-- CARD STATISTIK --}}
            <flux:card class="rounded-xl border shadow-sm">
                <div class="mb-4 flex items-center gap-2">
                    <flux:icon.chart-bar class="size-5 text-zinc-500" />
                    <h3 class="font-semibold text-stone-900 dark:text-stone-100">Statistik Penilaian</h3>
                </div>

                <div class="space-y-3">
                    <div
                        class="flex justify-between items-center p-2 rounded-lg bg-violet-50 dark:bg-violet-900/10 border border-violet-100 dark:border-violet-900/30">
                        <span class="text-xs font-medium text-violet-700 dark:text-violet-300">BA Terbit</span>
                        <span class="font-bold text-violet-600">{{ $this->stats['ba_terbit'] }}</span>
                    </div>
                    <div
                        class="flex justify-between items-center p-2 rounded-lg bg-purple-50 dark:bg-purple-900/10 border border-purple-100 dark:border-purple-900/30">
                        <span class="text-xs font-medium text-purple-700 dark:text-purple-300">Dinilai</span>
                        <span class="font-bold text-purple-600">{{ $this->stats['dinilai'] }}</span>
                    </div>
                    <div
                        class="flex justify-between items-center p-2 rounded-lg bg-teal-50 dark:bg-teal-900/10 border border-teal-100 dark:border-teal-900/30">
                        <span class="text-xs font-medium text-teal-700 dark:text-teal-300">Selesai</span>
                        <span class="font-bold text-teal-600">{{ $this->stats['selesai'] }}</span>
                    </div>
                </div>
            </flux:card>

            {{-- CARD INFORMASI TAMBAHAN --}}
            <flux:card
                class="rounded-xl border bg-indigo-50/50 dark:bg-indigo-900/10 border-indigo-100 dark:border-indigo-900/30 shadow-sm">
                <div class="flex items-start gap-3">
                    <flux:icon.information-circle class="mt-0.5 size-5 text-indigo-600 dark:text-indigo-400" />
                    <div>
                        <h3 class="font-semibold text-indigo-900 dark:text-indigo-100 text-sm">Informasi Komisi</h3>
                        <div class="mt-3 space-y-2">
                            <p class="text-xs text-indigo-800 dark:text-indigo-200 leading-relaxed">
                                Gunakan fitur <strong>Export Excel</strong> untuk melakukan rekapitulasi nilai akhir
                                mahasiswa sebagai bahan rapat yudisium.
                            </p>
                            <ul
                                class="text-[10px] text-indigo-700 dark:text-indigo-300 space-y-1 list-disc list-inside">
                                <li>Filter tanggal berdasarkan waktu update nilai.</li>
                                <li>Pastikan status sudah <strong>Selesai</strong> untuk nilai final.</li>
                                <li>Ikon berkas menunjukkan kelengkapan administrasi.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </flux:card>
        </div>
    </div>
</div>
