<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1">Kelola Penandatangan</flux:heading>
            <flux:subheading class="text-zinc-600">
                Tambah atau ubah data penandatangan surat pengantar.
            </flux:subheading>
        </div>

        <flux:button icon="plus" variant="primary" wire:click="openCreate">
            Penandatangan Baru
        </flux:button>
    </div>

    <flux:separator variant="subtle" />

    <flux:card>
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-3">
            <div class="flex items-center gap-2">
                <flux:select wire:model.live="perPage" class="w-32">
                    <flux:select.option :value="5">5 / halaman</flux:select.option>
                    <flux:select.option :value="10">10 / halaman</flux:select.option>
                    <flux:select.option :value="25">25 / halaman</flux:select.option>
                </flux:select>
            </div>

            <div class="flex items-center gap-2 md:w-96">
                <flux:input
                    icon="magnifying-glass"
                    placeholder="Cari nama, jabatan, atau NIP…"
                    wire:model.live.debounce.300ms="search"
                />
                @if($search !== '')
                    <flux:button size="sm" variant="ghost" icon="x-mark" wire:click="$set('search','')">
                        Bersihkan
                    </flux:button>
                @endif
            </div>
        </div>

        <flux:table :paginate="$this->orders">
            <flux:table.columns>
                <flux:table.column>Nama</flux:table.column>

                <flux:table.column
                    sortable
                    :sorted="$sortBy === 'position'"
                    :direction="$sortDirection"
                    wire:click="sort('position')">
                    Jabatan
                </flux:table.column>

                <flux:table.column
                    sortable
                    :sorted="$sortBy === 'nip'"
                    :direction="$sortDirection"
                    wire:click="sort('nip')">
                    NIP
                </flux:table.column>

                <flux:table.column class="w-16 text-right">Aksi</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->orders as $row)
                    <flux:table.row :key="$row->id">
                        <flux:table.cell class="flex items-center gap-3">
                            <flux:avatar size="xs" :src="null" />
                            {{ $row->name }}
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap">
                            {{ $row->position }}
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap">
                            {{ $row->nip ?: '—' }}
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:dropdown position="bottom" align="end">
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom"></flux:button>

                                <flux:menu class="min-w-40">
                                    <flux:menu.item icon="pencil-square" wire:click="openEdit({{ $row->id }})">
                                        Edit
                                    </flux:menu.item>

                                    <flux:menu.separator />

                                    <flux:modal.trigger name="signatory-delete">
                                        <flux:menu.item icon="trash" wire:click="markDelete({{ $row->id }})">
                                            Hapus
                                        </flux:menu.item>
                                    </flux:modal.trigger>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4">
                            <div class="py-6 text-center text-sm text-zinc-500">
                                Tidak ada data{{ $search ? " untuk kata kunci \"$search\"" : '' }}.
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

    {{-- Modal Form (Create/Edit) --}}
    <flux:modal name="signatory-form" class="min-w-[34rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingId ? 'Ubah Penandatangan' : 'Penandatangan Baru' }}</flux:heading>
                <flux:subheading class="mt-1">
                    Isi data penandatangan yang akan tampil di surat pengantar.
                </flux:subheading>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <flux:input
                    label="Nama Lengkap"
                    wire:model.defer="name"
                    placeholder="Nama penandatangan"
                    :invalid="$errors->has('name')" />
                {{-- @error('name') <p class="text-sm text-red-600">{{ $message }}</p> @enderror --}}

                <flux:input
                    label="Jabatan"
                    wire:model.defer="position"
                    placeholder="Mis: Wakil Dekan Bidang Akademik"
                    :invalid="$errors->has('position')" />
                {{-- @error('position') <p class="text-sm text-red-600">{{ $message }}</p> @enderror --}}

                <flux:input label="NIP (opsional)" wire:model.defer="nip" placeholder="NIP" />
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost" wire:click="cancel">Batal</flux:button>
                </flux:modal.close>

                <flux:button
                    variant="primary"
                    icon="check"
                    wire:click="save"
                    wire:loading.attr="disabled"
                    wire:target="save">
                    Simpan
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Modal Delete --}}
    <flux:modal name="signatory-delete" class="min-w-[24rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Hapus penandatangan?</flux:heading>
                <flux:text class="mt-2">Tindakan ini tidak dapat dibatalkan.</flux:text>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button
                    variant="danger"
                    wire:click="confirmDelete"
                    wire:loading.attr="disabled"
                    wire:target="confirmDelete">
                    Hapus
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
