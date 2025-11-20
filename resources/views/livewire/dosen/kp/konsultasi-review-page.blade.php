{{-- resources/views/livewire/dosen/kp/konsultasi-review-page.blade.php --}}
<div class="space-y-6">
    <flux:card>
        <div class="flex items-center justify-between mb-3">
            <flux:input class="md:w-80" placeholder="Cari topik / hasil…" wire:model.debounce.400ms="q"
                icon="magnifying-glass" />
        </div>

        <flux:table :paginate="$this->items">
            <flux:table.columns>
                <flux:table.column class="w-12">#</flux:table.column>
                <flux:table.column>Tanggal</flux:table.column>
                <flux:table.column>Mahasiswa</flux:table.column>
                <flux:table.column>Topik</flux:table.column>
                <flux:table.column>Hasil</flux:table.column>
                <flux:table.column class="w-36 text-right">Aksi</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->items as $i => $row)
                    <flux:table.row :key="$row->id">
                        <flux:table.cell>{{ $this->items->firstItem() + $i }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">
                            {{ optional($row->tanggal_konsultasi)->format('d M Y') }}</flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">
                            {{ $row->kerjaPraktik?->mahasiswa?->user?->name }}
                            <div class="text-xs text-zinc-500">{{ $row->kerjaPraktik?->mahasiswa?->nim }}</div>
                        </flux:table.cell>
                        <flux:table.cell class="max-w-[240px]">
                            <span class="line-clamp-2">{{ $row->topik_konsultasi }}</span>
                        </flux:table.cell>
                        <flux:table.cell class="max-w-[320px]">
                            <span class="line-clamp-2">{{ $row->hasil_konsultasi }}</span>
                        </flux:table.cell>
                        <flux:table.cell class="text-right">
                            @if ($row->verified_at)
                                <flux:badge size="sm" color="green">Terverifikasi</flux:badge>
                            @else
                                <flux:modal.trigger name="verify-consult">
                                    <flux:button size="sm" variant="primary" icon="check"
                                        wire:click="openVerify({{ $row->id }})">
                                        Verifikasi
                                    </flux:button>
                                </flux:modal.trigger>
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:card>

    {{-- Modal verifikasi --}}
    <flux:modal name="verify-consult" :show="$verifyId !== null" class="min-w-[26rem]">
        <div class="space-y-4">
            <flux:heading size="lg">Verifikasi konsultasi ini?</flux:heading>
            <flux:textarea rows="3" label="Catatan (opsional)" wire:model.defer="verifier_note" />
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost" wire:click="$set('verifyId', null)">Batal</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" icon="check" wire:click="confirmVerify">Verifikasi</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
