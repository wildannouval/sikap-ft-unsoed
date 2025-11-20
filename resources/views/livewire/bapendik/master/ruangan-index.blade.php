<div class="space-y-6">
    <flux:card class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold">Data Ruangan</h3>
                <p class="text-sm text-zinc-500">Kelola nomor ruangan & gedung.</p>
            </div>

            <div class="flex items-center gap-2">
                <flux:input class="md:w-80" placeholder="Cari nomor / gedung / catatan…" wire:model.debounce.400ms="q"
                    icon="magnifying-glass" />
                <flux:modal.trigger name="create-ruangan">
                    <flux:button variant="primary" icon="plus" wire:click="create">Tambah</flux:button>
                </flux:modal.trigger>
            </div>
        </div>

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
