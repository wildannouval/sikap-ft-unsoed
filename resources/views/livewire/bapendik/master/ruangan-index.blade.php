<div class="space-y-6">
    <flux:toast />

    {{-- HEADER --}}
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <flux:heading size="xl" level="1" class="text-stone-900 dark:text-stone-100">
                Master Data Ruangan
            </flux:heading>
            <flux:subheading class="text-zinc-600 dark:text-zinc-300">
                Kelola daftar ruangan untuk penjadwalan seminar dan kegiatan KP.
            </flux:subheading>
        </div>
    </div>

    <flux:separator variant="subtle" />

    {{-- GRID UTAMA 3:1 --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">

        {{-- KOLOM KIRI: TABEL (3) --}}
        <div class="lg:col-span-3 space-y-6">
            <flux:card
                class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm overflow-hidden">

                {{-- Header Tabel --}}
                <div
                    class="flex flex-col gap-4 px-6 py-4 border-b border-zinc-200 dark:border-stone-800 bg-sky-50/50 dark:bg-sky-900/10 md:flex-row md:items-center md:justify-between">
                    <h4 class="text-base font-semibold text-stone-900 dark:text-stone-100">Daftar Ruangan</h4>

                    <div class="flex items-center gap-3">
                        <flux:input icon="magnifying-glass" placeholder="Cari nomor / gedung..."
                            wire:model.live.debounce.400ms="q" class="w-full md:w-56 bg-white dark:bg-stone-900" />

                        <flux:select wire:model.live="perPage" class="w-20">
                            <flux:select.option :value="10">10</flux:select.option>
                            <flux:select.option :value="25">25</flux:select.option>
                            <flux:select.option :value="50">50</flux:select.option>
                        </flux:select>

                        {{-- Tombol Tambah di Kanan --}}
                        <flux:button icon="plus" variant="primary" size="sm" wire:click="create">Tambah
                        </flux:button>
                    </div>
                </div>

                <flux:table :paginate="$this->items">
                    <flux:table.columns>
                        <flux:table.column class="w-12 text-center">No</flux:table.column>
                        <flux:table.column sortable :sorted="$sortBy === 'room_number'" :direction="$sortDirection"
                            wire:click="sort('room_number')">Nomor</flux:table.column>
                        <flux:table.column sortable :sorted="$sortBy === 'building'" :direction="$sortDirection"
                            wire:click="sort('building')">Gedung</flux:table.column>
                        <flux:table.column>Catatan</flux:table.column>
                        <flux:table.column class="text-right">Aksi</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($this->items as $i => $row)
                            <flux:table.row :key="$row->id">
                                <flux:table.cell class="text-center text-zinc-500">
                                    {{ $this->items->firstItem() + $i }}
                                </flux:table.cell>

                                <flux:table.cell
                                    class="whitespace-nowrap font-medium text-stone-900 dark:text-stone-100">
                                    {{ $row->room_number }}
                                </flux:table.cell>

                                <flux:table.cell class="whitespace-nowrap">
                                    {{ $row->building }}
                                </flux:table.cell>

                                <flux:table.cell class="max-w-[320px]">
                                    <span class="line-clamp-1 text-zinc-500">{{ $row->notes ?? '—' }}</span>
                                </flux:table.cell>

                                <flux:table.cell class="text-right">
                                    <flux:dropdown position="bottom" align="end">
                                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                        <flux:menu class="min-w-32">
                                            <flux:menu.item icon="pencil-square" wire:click="edit({{ $row->id }})">
                                                Edit
                                            </flux:menu.item>
                                            <flux:menu.item icon="trash" variant="danger"
                                                wire:click="delete({{ $row->id }})">
                                                Hapus
                                            </flux:menu.item>
                                        </flux:menu>
                                    </flux:dropdown>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>

                {{-- Empty State --}}
                @if ($this->items->isEmpty())
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <div class="rounded-full bg-zinc-100 p-4 dark:bg-stone-900">
                            <flux:icon.building-office class="size-8 text-zinc-400" />
                        </div>
                        <h3 class="mt-4 text-base font-semibold text-stone-900 dark:text-stone-100">
                            Belum ada data
                        </h3>
                        <p class="mt-1 text-sm text-zinc-500">
                            @if ($q)
                                Tidak ditemukan ruangan dengan kata kunci "{{ $q }}".
                            @else
                                Belum ada ruangan yang ditambahkan.
                            @endif
                        </p>
                    </div>
                @endif
            </flux:card>
        </div>

        {{-- KOLOM KANAN: SIDEBAR (1) --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- PANDUAN --}}
            <flux:card
                class="rounded-xl border bg-sky-50/50 dark:bg-sky-900/10 border-sky-100 dark:border-sky-800/30 shadow-sm">
                <div class="flex items-start gap-3">
                    <flux:icon.information-circle class="mt-0.5 size-5 text-sky-600 dark:text-sky-400" />
                    <div>
                        <h3 class="font-semibold text-sky-900 dark:text-sky-100 text-sm">Panduan Ruangan</h3>
                        <ul class="mt-3 text-xs text-sky-800 dark:text-sky-200 space-y-2 list-disc list-inside">
                            <li>Tambahkan ruangan beserta <strong>Nomor</strong> dan <strong>Gedung</strong>.</li>
                            <li>Gunakan kolom pencarian untuk filter cepat.</li>
                            <li>Kolom Catatan bersifat opsional untuk info tambahan.</li>
                        </ul>
                    </div>
                </div>
            </flux:card>
        </div>
    </div>

    {{-- MODAL FORM (Create/Edit) --}}
    <flux:modal name="ruangan-form" class="min-w-[28rem]" :show="$showForm">
        <div class="space-y-6">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <flux:heading size="lg">{{ $editId ? 'Edit Ruangan' : 'Tambah Ruangan' }}</flux:heading>
                    <flux:subheading class="mt-1">Isi detail ruangan di bawah ini.</flux:subheading>
                </div>
                <flux:modal.close>
                    <flux:button variant="ghost" icon="x-mark" wire:click="closeForm"></flux:button>
                </flux:modal.close>
            </div>

            <div class="grid gap-4">
                <flux:input label="Nomor Ruangan" wire:model.defer="room_number" placeholder="Contoh: R.201"
                    :invalid="$errors->has('room_number')" />

                <flux:input label="Gedung" wire:model.defer="building" placeholder="Contoh: Gedung A"
                    :invalid="$errors->has('building')" />

                <flux:textarea label="Catatan (opsional)" wire:model.defer="notes"
                    placeholder="Keterangan tambahan..." />
            </div>

            <div class="flex gap-2 pt-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost" wire:click="closeForm">Batal</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" icon="check" wire:click="save">Simpan</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
