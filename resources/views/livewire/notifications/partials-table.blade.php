<flux:card>
    <div class="mb-3 flex items-center justify-end gap-2">
        <flux:select wire:model.live="perPage" class="w-32">
            <flux:select.option :value="10">10 / halaman</flux:select.option>
            <flux:select.option :value="25">25 / halaman</flux:select.option>
            <flux:select.option :value="50">50 / halaman</flux:select.option>
        </flux:select>
    </div>

    <flux:table :paginate="$rows">
        <flux:table.columns>
            <flux:table.column class="w-12">#</flux:table.column>
            <flux:table.column>Judul</flux:table.column>
            <flux:table.column>Isi</flux:table.column>
            <flux:table.column class="w-40">Waktu</flux:table.column>
            <flux:table.column class="w-36 text-right">Aksi</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($rows as $i => $n)
                @php
                    // meta opsional (untuk badge/teks tambahan)
                    $meta = is_array($n->data ?? null) ? $n->data : null;
                    $type = $meta['type'] ?? null;

                    $badgeColor = match ($type) {
                        'sp_submitted' => 'sky',
                        'sp_published' => 'emerald',
                        'sp_rejected' => 'rose',
                        'sp_ack' => 'zinc',
                        default => null,
                    };

                    $metaLine = match ($type) {
                        'sp_submitted' => 'Pengajuan SP baru',
                        'sp_published' => 'SP diterbitkan',
                        'sp_rejected' => 'SP ditolak',
                        'sp_ack' => 'Pengajuan tersimpan',
                        default => null,
                    };
                @endphp

                <flux:table.row :key="$n->id">
                    <flux:table.cell>{{ $rows->firstItem() + $i }}</flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap">
                        <div class="flex items-center gap-2">
                            @if (is_null($n->read_at))
                                <span class="inline-block h-2 w-2 rounded-full bg-emerald-500"></span>
                            @endif
                            <span class="font-medium">{{ $n->title ?? '—' }}</span>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell class="max-w-[560px]">
                        <div class="text-sm text-zinc-700">{{ $n->body ?? '—' }}</div>

                        @if ($metaLine || ($meta['sp_id'] ?? false) || ($meta['nomor'] ?? false))
                            <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-zinc-600">
                                @if ($badgeColor)
                                    <flux:badge size="xs" inset="top bottom" :color="$badgeColor">
                                        {{ $metaLine }}
                                    </flux:badge>
                                @endif
                                @if (!empty($meta['sp_id']))
                                    <span>SP# {{ $meta['sp_id'] }}</span>
                                @endif
                                @if (!empty($meta['nomor']))
                                    <span>No: {{ $meta['nomor'] }}</span>
                                @endif
                            </div>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap text-sm text-zinc-500">
                        {{ $n->created_at?->format('d M Y H:i') ?? '—' }}
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="flex justify-end gap-1">
                            @if (!empty($n->link))
                                <flux:button size="sm" variant="outline" icon="arrow-top-right-on-square"
                                    wire:click="open({{ $n->id }})">
                                    Buka
                                </flux:button>
                            @endif

                            @if (is_null($n->read_at))
                                <flux:button size="sm" variant="primary" icon="check"
                                    wire:click="markRead({{ $n->id }})">
                                    Terbaca
                                </flux:button>
                            @endif

                            <flux:button size="sm" variant="ghost" icon="trash"
                                wire:click="deleteOne({{ $n->id }})" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5">
                        <div class="py-6 text-center text-sm text-zinc-500">
                            Tidak ada notifikasi.
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</flux:card>
