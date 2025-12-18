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

    {{-- GRID UTAMA --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- KOLOM KIRI: FORMULIR (2/3) --}}
        <div class="lg:col-span-2 space-y-6">
            <flux:card
                class="space-y-6 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm">

                {{-- Header Form --}}
                <div class="flex items-center gap-3">
                    <div
                        class="flex items-center justify-center rounded-lg p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                        <flux:icon.pencil-square class="size-5" />
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-stone-900 dark:text-stone-100">
                            {{ $editingId ? 'Edit Catatan' : 'Catat Konsultasi Baru' }}
                        </h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            Simpan hasil diskusi bimbingan Anda.
                        </p>
                    </div>
                </div>

                <flux:separator />

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    {{-- Dropdown Pihak Konsultasi --}}
                    <div>
                        <flux:select label="Konsultasi Dengan" wire:model.live="konsultasi_tipe">
                            <flux:select.option value="Dosen Pembimbing">Dosen Pembimbing ({{ $this->dosenName }})
                            </flux:select.option>
                            <flux:select.option value="Pembimbing Lapangan">Pembimbing Lapangan (Instansi)
                            </flux:select.option>
                            <flux:select.option value="Lainnya">Lainnya...</flux:select.option>
                        </flux:select>
                    </div>

                    {{-- Input Manual jika Lainnya --}}
                    @if ($konsultasi_tipe === 'Lainnya')
                        <div>
                            <flux:input label="Nama Pihak Konsultasi" wire:model.defer="konsultasi_custom_name"
                                placeholder="Misal: Senior Engineer" />
                        </div>
                    @endif

                    {{-- Tanggal --}}
                    <div>
                        <flux:input type="date" label="Tanggal Konsultasi" wire:model.defer="tanggal_konsultasi"
                            :invalid="$errors->has('tanggal_konsultasi')" />
                    </div>

                    {{-- Topik --}}
                    <div class="md:col-span-2">
                        <flux:input label="Topik Bimbingan" wire:model.defer="topik_konsultasi"
                            placeholder="Contoh: Pembahasan Database Schema..."
                            :invalid="$errors->has('topik_konsultasi')" />
                    </div>

                    {{-- Hasil --}}
                    <div class="md:col-span-2">
                        <flux:textarea rows="4" label="Hasil / Arahan" wire:model.defer="hasil_konsultasi"
                            placeholder="Catat poin-poin revisi atau arahan selanjutnya..."
                            :invalid="$errors->has('hasil_konsultasi')" />
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

        {{-- KOLOM KANAN: STATUS & PANDUAN (1/3) --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- RINGKASAN STATUS --}}
            <flux:card
                class="rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm">
                <div class="mb-5">
                    <div class="flex items-center gap-2">
                        <flux:icon.chart-bar class="size-5 text-zinc-500" />
                        <h3 class="font-semibold text-stone-900 dark:text-stone-100">Progres Bimbingan</h3>
                    </div>
                    <p class="mt-1.5 text-xs text-zinc-500 dark:text-zinc-400 pl-7">Target minimal
                        {{ $this->stats['target'] }} kali.</p>
                </div>

                <div class="space-y-3">
                    <div
                        class="flex items-center justify-between p-2 rounded-lg bg-emerald-50/50 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-800/30">
                        <div class="flex items-center gap-3">
                            <div class="size-2 rounded-full bg-emerald-500"></div>
                            <div>
                                <p class="text-sm font-medium text-stone-700 dark:text-stone-300">Terverifikasi</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Sudah disetujui</p>
                            </div>
                        </div>
                        <span
                            class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ $this->stats['verified'] }}</span>
                    </div>
                    <div
                        class="flex items-center justify-between p-2 rounded-lg bg-zinc-50/50 dark:bg-zinc-900/10 border border-zinc-100 dark:border-zinc-800/30">
                        <div class="flex items-center gap-3">
                            <div class="size-2 rounded-full bg-zinc-400"></div>
                            <div>
                                <p class="text-sm font-medium text-stone-700 dark:text-stone-300">Menunggu</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Belum divalidasi</p>
                            </div>
                        </div>
                        <span
                            class="text-lg font-bold text-zinc-600 dark:text-zinc-400">{{ $this->stats['pending'] }}</span>
                    </div>
                </div>

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

            {{-- PANDUAN --}}
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

    {{-- TABEL --}}
    <flux:card
        class="rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm overflow-hidden">
        <div
            class="flex flex-col gap-4 px-6 py-4 border-b border-zinc-200 dark:border-stone-800 bg-zinc-50/50 dark:bg-stone-900/50 md:flex-row md:items-center md:justify-between">
            <h4 class="text-base font-semibold text-stone-900 dark:text-stone-100">Daftar Log Konsultasi</h4>

            {{-- Filter & Search --}}
            <div class="flex items-center gap-2">
                <flux:input icon="magnifying-glass" placeholder="Cari..." wire:model.live.debounce.300ms="search"
                    class="w-full md:w-64" />
                <flux:select wire:model.live="filterStatus" class="w-40">
                    <flux:select.option value="">Semua Status</flux:select.option>
                    <flux:select.option value="verified">Terverifikasi</flux:select.option>
                    <flux:select.option value="pending">Menunggu</flux:select.option>
                </flux:select>
            </div>
        </div>

        <flux:table :paginate="$this->items">
            <flux:table.columns>
                <flux:table.column sortable :sorted="$sortBy === 'tanggal_konsultasi'" :direction="$sortDirection"
                    wire:click="sort('tanggal_konsultasi')">Tanggal</flux:table.column>
                <flux:table.column>Topik</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column class="text-right">Aksi</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->items as $item)
                    <flux:table.row :key="$item->id">
                        <flux:table.cell class="whitespace-nowrap">
                            {{ optional($item->tanggal_konsultasi)->format('d M Y') }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="font-medium text-stone-900 dark:text-stone-100 line-clamp-1">
                                {{ $item->topik_konsultasi }}</div>
                            <div class="text-xs text-zinc-500">dgn: {{ $item->konsultasi_dengan }}</div>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($item->verified_at)
                                <flux:badge size="sm" color="emerald" icon="check-circle">Terverifikasi
                                </flux:badge>
                            @else
                                <flux:badge size="sm" color="zinc" icon="clock">Menunggu</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell class="text-right">
                            <flux:dropdown position="bottom" align="end">
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                <flux:menu>
                                    <flux:menu.item icon="eye" wire:click="openDetail({{ $item->id }})">
                                        Detail</flux:menu.item>
                                    @if (!$item->verified_at)
                                        <flux:menu.item icon="pencil-square" wire:click="edit({{ $item->id }})">
                                            Edit</flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item icon="trash" variant="danger"
                                            wire:click="openDelete({{ $item->id }})">Hapus</flux:menu.item>
                                    @endif
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:card>

    {{-- MODAL DETAIL --}}
    <flux:modal name="detail-konsultasi" :show="$detailId !== null" class="md:w-[32rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Detail Konsultasi</flux:heading>
                <p class="text-sm text-zinc-500">Informasi lengkap catatan bimbingan.</p>
            </div>

            @if ($item = $this->selectedItem)
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div
                            class="p-3 bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-zinc-100 dark:border-zinc-800">
                            <div class="text-xs text-zinc-500 mb-1">Tanggal</div>
                            <div class="font-medium text-stone-900 dark:text-stone-100">
                                {{ optional($item->tanggal_konsultasi)->format('d M Y') }}</div>
                        </div>
                        <div
                            class="p-3 bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-zinc-100 dark:border-zinc-800">
                            <div class="text-xs text-zinc-500 mb-1">Status</div>
                            @if ($item->verified_at)
                                <flux:badge size="sm" color="emerald" icon="check-circle">Terverifikasi
                                </flux:badge>
                            @else
                                <flux:badge size="sm" color="zinc" icon="clock">Menunggu</flux:badge>
                            @endif
                        </div>
                    </div>

                    <div
                        class="p-3 bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-zinc-100 dark:border-zinc-800">
                        <div class="text-xs text-zinc-500 mb-1">Konsultasi Dengan</div>
                        <div class="font-medium text-stone-900 dark:text-stone-100">{{ $item->konsultasi_dengan }}
                        </div>
                    </div>

                    <div
                        class="p-3 bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-zinc-100 dark:border-zinc-800">
                        <div class="text-xs text-zinc-500 mb-1">Topik Pembahasan</div>
                        <div class="font-medium text-stone-900 dark:text-stone-100">{{ $item->topik_konsultasi }}
                        </div>
                    </div>

                    <div
                        class="p-3 bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-zinc-100 dark:border-zinc-800">
                        <div class="text-xs text-zinc-500 mb-1">Hasil / Arahan</div>
                        <div class="text-sm text-stone-700 dark:text-stone-300 whitespace-pre-line">
                            {{ $item->hasil_konsultasi }}</div>
                    </div>

                    @if ($item->verified_at && $item->verifier_note)
                        <div
                            class="p-3 bg-emerald-50 dark:bg-emerald-900/10 rounded-lg border border-emerald-100 dark:border-emerald-800/30">
                            <div class="text-xs text-emerald-600 font-bold mb-1">Catatan Dosen</div>
                            <div class="text-sm text-emerald-800 dark:text-emerald-200">{{ $item->verifier_note }}
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <div class="flex justify-end">
                <flux:button variant="ghost" wire:click="closeDetail">Tutup</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- MODAL DELETE --}}
    <flux:modal name="delete-konsultasi" class="md:w-96" :show="$deleteId !== null">
        <div class="space-y-6">
            <div class="text-center">
                <div
                    class="mx-auto mb-4 flex size-12 items-center justify-center rounded-full bg-rose-100 dark:bg-rose-900/30">
                    <flux:icon.trash class="size-6 text-rose-600 dark:text-rose-400" />
                </div>
                <flux:heading size="lg">Hapus Catatan?</flux:heading>
                <flux:subheading class="mt-2">Data ini akan dihapus permanen.</flux:subheading>
            </div>
            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost" wire:click="$set('deleteId', null)">Batal</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" wire:click="confirmDelete">Hapus</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
