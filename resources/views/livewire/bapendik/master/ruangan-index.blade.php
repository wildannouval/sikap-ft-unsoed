<div class="space-y-6">
    <flux:toast />

    {{-- HEADER --}}
    <div class="flex items-center justify-between">
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

    {{-- PANDUAN --}}
    <flux:card class="space-y-3 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800">
        <div class="flex items-start gap-3">
            <div class="rounded-md p-2 bg-sky-500 text-white dark:bg-sky-400">
                {{-- Icon SVG inline (clipboard-document-list outline) --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 12h6M9 16h6M9 8h6m-3.75-5.25h1.5a2.25 2.25 0 012.25 2.25v.75h1.5A2.25 2.25 0 0120 8.25v10.5A2.25 2.25 0 0117.75 21H6.25A2.25 2.25 0 014 18.75V8.25A2.25 2.25 0 016.25 6.75h1.5V6c0-1.243 1.007-2.25 2.25-2.25z" />
                </svg>
            </div>
            <div>
                <flux:heading size="md">Panduan Data Ruangan</flux:heading>
                <ul class="mt-1 text-sm text-zinc-600 dark:text-zinc-300 list-disc ms-4 space-y-1">
                    <li>Tambahkan ruangan beserta <em>Nomor</em> dan <em>Gedung</em>.</li>
                    <li>Gunakan kolom pencarian untuk filter cepat.</li>
                    <li>Kolom Catatan opsional untuk info tambahan.</li>
                </ul>
            </div>
        </div>
    </flux:card>

    <flux:card class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold">Data Ruangan</h3>
                <p class="text-sm text-zinc-500 dark:text-zinc-300">Kelola nomor ruangan & gedung.</p>
            </div>

            <div class="flex items-center gap-2">
                <flux:input class="md:w-80" placeholder="Cari nomor / gedung / catatan…"
                    wire:model.live.debounce.400ms="q" icon="magnifying-glass" />
                <flux:modal.trigger name="create-ruangan">
                    <flux:button variant="primary" icon="plus" wire:click="create">Tambah</flux:button>
                </flux:modal.trigger>
            </div>
        </div>

        @if (session('ok'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
                {{ session('ok') }}
            </div>
        @endif

        <flux:table :paginate="$this->items">
            <flux:table.columns>
                <flux:table.column class="w-12">#</flux:table.column>
                <flux:table.column>Nomor</flux:table.column>
                <flux:table.column>Gedung</flux:table.column>
                <flux:table.column>Catatan</flux:table.column>
                <flux:table.column class="w-32 text-right">Aksi</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->items as $i => $row)
                    <flux:table.row :key="$row->id">
                        <flux:table.cell>{{ $this->items->firstItem() + $i }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">{{ $row->room_number }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">{{ $row->building }}</flux:table.cell>
                        <flux:table.cell class="max-w-[360px]">
                            <span class="line-clamp-2">{{ $row->notes }}</span>
                        </flux:table.cell>
                        <flux:table.cell class="text-right">
                            <flux:modal.trigger name="edit-ruangan">
                                <flux:button size="sm" variant="ghost" icon="pencil-square"
                                    wire:click="edit({{ $row->id }})" />
                            </flux:modal.trigger>
                            <flux:button size="sm" variant="ghost" icon="trash"
                                wire:click="delete({{ $row->id }})" />
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:card>

    {{-- Create --}}
    <flux:modal name="create-ruangan" class="min-w-[28rem]">
        <div class="space-y-4">
            <h3 class="text-base font-semibold">Tambah Ruangan</h3>
            <div class="grid gap-3">
                <flux:input label="Nomor Ruangan" wire:model.defer="room_number"
                    :invalid="$errors->has('room_number')" />
                <flux:input label="Gedung" wire:model.defer="building" :invalid="$errors->has('building')" />
                <flux:input label="Catatan (opsional)" wire:model.defer="notes" />
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" icon="check" wire:click="store">Simpan</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Edit --}}
    <flux:modal name="edit-ruangan" class="min-w-[28rem]" :show="$editId !== null">
        <div class="space-y-4">
            <h3 class="text-base font-semibold">Ubah Ruangan</h3>
            <div class="grid gap-3">
                <flux:input label="Nomor Ruangan" wire:model.defer="room_number"
                    :invalid="$errors->has('room_number')" />
                <flux:input label="Gedung" wire:model.defer="building" :invalid="$errors->has('building')" />
                <flux:input label="Catatan (opsional)" wire:model.defer="notes" />
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost" wire:click="$set('editId', null)">Batal</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" icon="check" wire:click="update">Simpan</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
