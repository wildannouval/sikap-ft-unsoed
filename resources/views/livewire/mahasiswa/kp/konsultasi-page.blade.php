{{-- resources/views/livewire/mahasiswa/kp/konsultasi-page.blade.php --}}
<div class="space-y-6">
    <flux:card class="space-y-4">
        <div class="grid md:grid-cols-2 gap-4">
            <flux:input label="Konsultasi dengan" wire:model.defer="konsultasi_dengan"
                placeholder="Dosen/WA/Zoom/Offline" />
            <flux:input type="date" label="Tanggal konsultasi" wire:model.defer="tanggal_konsultasi"
                :invalid="$errors->has('tanggal_konsultasi')" />
            <div class="md:col-span-2">
                <flux:input label="Topik konsultasi" wire:model.defer="topik_konsultasi"
                    :invalid="$errors->has('topik_konsultasi')" />
            </div>
            <div class="md:col-span-2">
                <flux:textarea rows="4" label="Hasil konsultasi" wire:model.defer="hasil_konsultasi"
                    :invalid="$errors->has('hasil_konsultasi')" />
            </div>
        </div>

        <div class="flex justify-end gap-2">
            @if ($editingId)
                <flux:button variant="ghost" wire:click="cancelEdit">Batal</flux:button>
                <flux:button variant="primary" icon="check" wire:click="updateItem">Simpan</flux:button>
            @else
                <flux:button variant="primary" icon="plus" wire:click="submit">Tambah</flux:button>
            @endif
        </div>
    </flux:card>

    <flux:card>
        <div class="flex items-center justify-between mb-3">
            <flux:input class="md:w-80" placeholder="Cari topik / hasil…" wire:model.debounce.400ms="q"
                icon="magnifying-glass" />
            <div class="text-sm text-zinc-600">Terverifikasi: {{ $kp->verifiedConsultationsCount() }} / 6</div>
        </div>

        <flux:table :paginate="$this->items">
            <flux:table.columns>
                <flux:table.column class="w-12">#</flux:table.column>
                <flux:table.column>Tanggal</flux:table.column>
                <flux:table.column>Topik</flux:table.column>
                <flux:table.column>Hasil</flux:table.column>
                <flux:table.column class="w-28">Status</flux:table.column>
                <flux:table.column class="w-28 text-right">Aksi</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->items as $i => $row)
                    <flux:table.row :key="$row->id">
                        <flux:table.cell>{{ $this->items->firstItem() + $i }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">
                            {{ optional($row->tanggal_konsultasi)->format('d M Y') }}</flux:table.cell>
                        <flux:table.cell class="max-w-[280px]">
                            <span class="line-clamp-2">{{ $row->topik_konsultasi }}</span>
                        </flux:table.cell>
                        <flux:table.cell class="max-w-[360px]">
                            <span class="line-clamp-2">{{ $row->hasil_konsultasi }}</span>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($row->verified_at)
                                <flux:badge size="sm" color="green">Terverifikasi</flux:badge>
                            @else
                                <flux:badge size="sm" color="zinc">Menunggu</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell class="text-right">
                            @if (!$row->verified_at)
                                <flux:button size="sm" variant="ghost" icon="pencil-square"
                                    wire:click="edit({{ $row->id }})" />
                                <flux:button size="sm" variant="ghost" icon="trash"
                                    wire:click="deleteItem({{ $row->id }})" />
                            @else
                                <span class="text-xs text-zinc-500">—</span>
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:card>
</div>
