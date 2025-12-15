<div class="space-y-6">
    {{-- TOAST GLOBAL --}}
    <flux:toast />

    {{-- HEADER HALAMAN --}}
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <flux:heading size="xl" level="1" class="text-stone-900 dark:text-stone-100">
                Log Konsultasi KP
            </flux:heading>
            <flux:subheading class="text-zinc-600 dark:text-zinc-300">
                Catatan bimbingan Kerja Praktik dengan Dosen Pembimbing.
            </flux:subheading>
        </div>
    </div>

    <flux:separator variant="subtle" />

    {{-- GRID UTAMA: Form (Kiri) + Info (Kanan) --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- KOLOM KIRI: FORMULIR --}}
        <div class="lg:col-span-2 space-y-6">
            <flux:card
                class="space-y-6 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm">

                {{-- Header Kartu Form --}}
                <div class="flex items-center gap-3">
                    <div
                        class="flex items-center justify-center rounded-lg p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                        <flux:icon.pencil-square class="size-5" />
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-stone-900 dark:text-stone-100">
                            Catat Konsultasi Baru
                        </h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            Simpan topik & hasil diskusi bimbingan Anda.
                        </p>
                    </div>
                </div>

                <flux:separator />

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    {{-- Konsultasi Dengan --}}
                    <div>
                        <flux:input label="Konsultasi Dengan" wire:model.defer="konsultasi_dengan"
                            placeholder="Contoh: Dosen Pembimbing / Praktisi" />
                    </div>

                    {{-- Tanggal --}}
                    <div>
                        <flux:input type="date" label="Tanggal Konsultasi" wire:model.defer="tanggal_konsultasi"
                            :invalid="$errors->has('tanggal_konsultasi')" />
                        {{-- @error('tanggal_konsultasi')
                            <div class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</div>
                        @enderror --}}
                    </div>

                    {{-- Topik --}}
                    <div class="md:col-span-2">
                        <flux:input label="Topik Bimbingan" wire:model.defer="topik_konsultasi"
                            placeholder="Contoh: Pembahasan Database Schema..."
                            :invalid="$errors->has('topik_konsultasi')" />
                        {{-- @error('topik_konsultasi')
                            <div class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</div>
                        @enderror --}}
                    </div>

                    {{-- Hasil --}}
                    <div class="md:col-span-2">
                        <flux:textarea rows="4" label="Hasil / Arahan Dosen" wire:model.defer="hasil_konsultasi"
                            placeholder="Catat poin-poin revisi atau arahan selanjutnya..."
                            :invalid="$errors->has('hasil_konsultasi')" />
                        {{-- @error('hasil_konsultasi')
                            <div class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</div>
                        @enderror --}}
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    @if ($editingId)
                        <flux:button variant="ghost" wire:click="cancelEdit" icon="x-mark">Batal</flux:button>
                        <flux:button variant="primary" icon="check" wire:click="updateItem">Simpan Perubahan
                        </flux:button>
                    @else
                        <flux:button variant="primary" icon="plus" wire:click="submit">Tambah Catatan</flux:button>
                    @endif
                </div>
            </flux:card>
        </div>

        {{-- KOLOM KANAN: STATUS & PANDUAN --}}
        <div class="lg:col-span-1 space-y-6">

            {{-- 1. RINGKASAN STATUS (Boxed Style) --}}
            <flux:card
                class="rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm">
                <div class="mb-5">
                    <div class="flex items-center gap-2">
                        <flux:icon.chart-bar class="size-5 text-zinc-500" />
                        <h3 class="font-semibold text-stone-900 dark:text-stone-100">Progres Bimbingan</h3>
                    </div>
                    <p class="mt-1.5 text-xs text-zinc-500 dark:text-zinc-400 pl-7">
                        Target minimal {{ $this->stats['target'] }} kali bimbingan.
                    </p>
                </div>

                <div class="space-y-3">
                    {{-- Status: Terverifikasi --}}
                    <div
                        class="flex items-center justify-between p-2 rounded-lg bg-emerald-50/50 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-800/30">
                        <div class="flex items-center gap-3">
                            <div class="size-2 rounded-full bg-emerald-500"></div>
                            <div>
                                <p class="text-sm font-medium text-stone-700 dark:text-stone-300">Terverifikasi</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Sudah disetujui dosen</p>
                            </div>
                        </div>
                        <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                            {{ $this->stats['verified'] ?? 0 }}
                        </span>
                    </div>

                    {{-- Status: Menunggu --}}
                    <div
                        class="flex items-center justify-between p-2 rounded-lg bg-zinc-50/50 dark:bg-zinc-900/10 border border-zinc-100 dark:border-zinc-800/30">
                        <div class="flex items-center gap-3">
                            <div class="size-2 rounded-full bg-zinc-400"></div>
                            <div>
                                <p class="text-sm font-medium text-stone-700 dark:text-stone-300">Menunggu</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Belum divalidasi</p>
                            </div>
                        </div>
                        <span class="text-lg font-bold text-zinc-600 dark:text-zinc-400">
                            {{ $this->stats['pending'] ?? 0 }}
                        </span>
                    </div>

                    {{-- Status: Total Log --}}
                    <div
                        class="flex items-center justify-between p-2 rounded-lg bg-indigo-50/50 dark:bg-indigo-900/10 border border-indigo-100 dark:border-indigo-800/30">
                        <div class="flex items-center gap-3">
                            <div class="size-2 rounded-full bg-indigo-500"></div>
                            <div>
                                <p class="text-sm font-medium text-stone-700 dark:text-stone-300">Total Log</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Semua catatan</p>
                            </div>
                        </div>
                        <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400">
                            {{ $this->stats['total_log'] ?? 0 }}
                        </span>
                    </div>
                </div>

                {{-- Progress Bar Simple --}}
                <div class="mt-4">
                    <div class="flex justify-between text-xs mb-1.5">
                        <span class="text-zinc-500">Kelayakan Seminar</span>
                        <span
                            class="font-medium text-stone-900 dark:text-stone-100">{{ $this->stats['progress_pct'] }}%</span>
                    </div>
                    <div class="h-2 w-full bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-500 transition-all duration-500"
                            style="width: {{ $this->stats['progress_pct'] }}%"></div>
                    </div>
                </div>
            </flux:card>

            {{-- 2. PANDUAN --}}
            <flux:card
                class="rounded-xl border bg-amber-50/50 dark:bg-amber-900/10 border-amber-100 dark:border-amber-800/30 shadow-sm">
                <div class="flex items-start gap-3">
                    <flux:icon.information-circle class="mt-0.5 size-5 text-amber-600 dark:text-amber-400" />
                    <div>
                        <h3 class="font-semibold text-amber-900 dark:text-amber-100 text-sm">Tips Pengisian</h3>
                        <ul
                            class="mt-2 text-xs text-amber-800 dark:text-amber-200 list-disc list-outside ml-3 space-y-1.5 leading-relaxed">
                            <li>Isi <strong>Tanggal</strong> sesuai pertemuan aktual.</li>
                            <li><strong>Topik</strong> harus spesifik (mis: "Revisi ERD").</li>
                            <li><strong>Hasil</strong> berisi poin arahan dosen.</li>
                            <li>Data hanya bisa diedit sebelum <strong>Diverifikasi</strong>.</li>
                        </ul>
                    </div>
                </div>
            </flux:card>
        </div>
    </div>

    {{-- BARIS BAWAH: TABEL --}}
    <flux:card
        class="rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm overflow-hidden">

        <div
            class="flex flex-col gap-4 px-6 py-4 border-b border-zinc-200 dark:border-stone-800 bg-zinc-50/50 dark:bg-stone-900/50 md:flex-row md:items-center md:justify-between">
            <h4 class="text-base font-semibold text-stone-900 dark:text-stone-100">Daftar Log Konsultasi</h4>

            <div class="flex flex-col gap-3 md:flex-row md:items-center">
                {{-- Search --}}
                <div class="w-full md:w-64">
                    <flux:input icon="magnifying-glass" placeholder="Cari topik / hasil..."
                        wire:model.live.debounce.300ms="q" class="bg-white dark:bg-stone-900" />
                </div>

                {{-- Per Page --}}
                <div class="w-full md:w-32 hidden md:block">
                    <flux:select wire:model.live="perPage" class="bg-white dark:bg-stone-900">
                        <flux:select.option value="10">10 / hal</flux:select.option>
                        <flux:select.option value="25">25 / hal</flux:select.option>
                        <flux:select.option value="50">50 / hal</flux:select.option>
                    </flux:select>
                </div>
            </div>
        </div>

        <flux:table class="[&_thead_th]:bg-zinc-50 [&_thead_th]:dark:bg-stone-900/40" :paginate="$this->items">
            <flux:table.columns>
                <flux:table.column class="w-12 text-center">No</flux:table.column>

                <flux:table.column sortable :sorted="$sortBy === 'tanggal_konsultasi'" :direction="$sortDirection"
                    wire:click="sort('tanggal_konsultasi')">
                    Tanggal
                </flux:table.column>

                <flux:table.column sortable :sorted="$sortBy === 'topik_konsultasi'" :direction="$sortDirection"
                    wire:click="sort('topik_konsultasi')">
                    Topik
                </flux:table.column>

                <flux:table.column>Hasil Diskusi</flux:table.column>

                <flux:table.column sortable :sorted="$sortBy === 'verified_at'" :direction="$sortDirection"
                    wire:click="sort('verified_at')">
                    Status
                </flux:table.column>

                <flux:table.column class="w-20 text-center">Aksi</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->items as $i => $row)
                    @php
                        $isVerified = $row->verified_at !== null;
                        $statusData = $isVerified
                            ? ['label' => 'Terverifikasi', 'color' => 'emerald', 'icon' => 'check-circle']
                            : ['label' => 'Menunggu', 'color' => 'zinc', 'icon' => 'clock'];
                    @endphp

                    <flux:table.row :key="$row->id">
                        <flux:table.cell class="text-center text-zinc-500">
                            {{ $this->items->firstItem() + $i }}
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap">
                            <div class="font-medium text-stone-900 dark:text-stone-100">
                                {{ optional($row->tanggal_konsultasi)->translatedFormat('d M Y') ?: '-' }}
                            </div>
                        </flux:table.cell>

                        <flux:table.cell class="max-w-[200px]">
                            <div class="font-medium text-stone-900 dark:text-stone-100 line-clamp-2 leading-snug">
                                {{ $row->topik_konsultasi }}
                            </div>
                            <div class="text-xs text-zinc-500 mt-0.5">
                                {{ $row->konsultasi_dengan ?? 'Dosen Pembimbing' }}
                            </div>
                        </flux:table.cell>

                        <flux:table.cell class="max-w-[300px]">
                            <span class="text-sm text-zinc-700 dark:text-zinc-300 line-clamp-2">
                                {{ $row->hasil_konsultasi }}
                            </span>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge size="sm" inset="top bottom" :color="$statusData['color']"
                                icon="{{ $statusData['icon'] }}">
                                {{ $statusData['label'] }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            @if (!$isVerified)
                                <flux:dropdown position="bottom" align="end">
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />

                                    <flux:menu class="min-w-32">
                                        <flux:menu.item icon="pencil-square" wire:click="edit({{ $row->id }})">
                                            Edit
                                        </flux:menu.item>
                                        <flux:menu.item icon="trash" variant="danger"
                                            wire:click="deleteItem({{ $row->id }})">
                                            Hapus
                                        </flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            @else
                                <div class="flex justify-center">
                                    <flux:icon.check class="size-4 text-emerald-500" />
                                </div>
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6">
                            <div class="py-8 text-center">
                                <p class="text-sm text-zinc-500">Belum ada catatan konsultasi.</p>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>
</div>
