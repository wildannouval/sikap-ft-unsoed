<div class="space-y-6">
    {{-- <div>
        <h3 class="text-base font-semibold">Konsultasi Mahasiswa Bimbingan</h3>
        <p class="text-sm text-zinc-500">Lihat, cari, dan verifikasi konsultasi untuk mahasiswa bimbingan Anda.</p>
    </div> --}}

    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div class="flex flex-col gap-2 md:flex-row md:items-end">
            <div class="md:w-80">
                <flux:input placeholder="Cari topik/hasil/konsultasi dengan..." wire:model.live.debounce.400ms="q"
                    icon="magnifying-glass" />
            </div>

            <flux:select wire:model.live="status" class="md:ml-2">
                <option value="all">Semua</option>
                <option value="unverified">Belum Diverifikasi</option>
                <option value="verified">Sudah Diverifikasi</option>
            </flux:select>
        </div>

        <div class="flex items-center gap-2">
            <flux:button size="sm" variant="ghost"
                :icon="$sortDirection === 'asc' ? 'arrow-up-circle' : 'arrow-down-circle'"
                wire:click="sort('tanggal_konsultasi')">
                Urut: {{ ucfirst(str_replace('_', ' ', $sortBy)) }} ({{ $sortDirection }})
            </flux:button>
        </div>
    </div>

    <flux:card class="space-y-4">
        <div wire:key="list-{{ $q }}-{{ $status }}-{{ $sortBy }}-{{ $sortDirection }}">
            <flux:table :paginate="$this->items">
                <flux:table.columns>
                    <flux:table.column class="w-12">#</flux:table.column>

                    <flux:table.column sortable :sorted="$sortBy === 'tanggal_konsultasi'" :direction="$sortDirection"
                        wire:click="sort('tanggal_konsultasi')">
                        Tanggal
                    </flux:table.column>

                    <flux:table.column>Mahasiswa</flux:table.column>
                    <flux:table.column>Judul KP</flux:table.column>
                    <flux:table.column>Topik</flux:table.column>
                    <flux:table.column>Hasil</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column class="w-36 text-center">Aksi</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->items as $i => $row)
                        <flux:table.row :key="$row->id">
                            <flux:table.cell>{{ $this->items->firstItem() + $i }}</flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                {{ optional($row->tanggal_konsultasi)->format('d M Y') ?: '—' }}
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                {{ $row->mahasiswa?->user?->name ?? '—' }}
                            </flux:table.cell>

                            <flux:table.cell class="max-w-[280px]">
                                <span class="line-clamp-2">{{ $row->kerjaPraktik?->judul_kp }}</span>
                            </flux:table.cell>

                            <flux:table.cell class="max-w-[240px]">
                                <span class="line-clamp-2">{{ $row->topik_konsultasi }}</span>
                            </flux:table.cell>

                            <flux:table.cell class="max-w-[300px]">
                                <span class="line-clamp-2">{{ $row->hasil_konsultasi }}</span>
                            </flux:table.cell>

                            <flux:table.cell>
                                @if ($row->verified_at)
                                    <flux:badge size="sm" inset="top bottom" color="green">Terverifikasi
                                    </flux:badge>
                                @else
                                    <flux:badge size="sm" inset="top bottom" color="zinc">Menunggu</flux:badge>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell class="text-center">
                                @if ($row->verified_at)
                                    <flux:button size="xs" variant="outline" icon="arrow-uturn-left"
                                        wire:click="unverify({{ $row->id }})">Batalkan</flux:button>
                                @else
                                    <flux:button size="xs" variant="primary" icon="check"
                                        wire:click="verify({{ $row->id }})">Verifikasi</flux:button>
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>
    </flux:card>
</div>
