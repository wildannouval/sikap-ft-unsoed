<flux:card
    class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm overflow-hidden">

    {{-- Header Tabel --}}
    <div
        class="flex flex-col gap-4 px-6 py-4 border-b border-zinc-200 dark:border-stone-800 bg-zinc-50/50 dark:bg-zinc-900/20 md:flex-row md:items-center md:justify-end">
        <flux:select wire:model.live="perPage" class="w-28">
            <flux:select.option :value="10">10 / hal</flux:select.option>
            <flux:select.option :value="25">25 / hal</flux:select.option>
            <flux:select.option :value="50">50 / hal</flux:select.option>
        </flux:select>
    </div>

    <flux:table :paginate="$rows">
        <flux:table.columns>
            <flux:table.column class="w-10">#</flux:table.column>
            <flux:table.column>Pesan</flux:table.column>
            <flux:table.column class="w-40">Waktu</flux:table.column>
            <flux:table.column class="w-48 text-right">Aksi</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($rows as $i => $n)
                @php
                    $meta = is_array($n->data ?? null) ? $n->data : null;
                    $type = $meta['type'] ?? 'default';
                    $config = $this->getBadgeConfig($type);
                @endphp

                <flux:table.row :key="$n->id"
                    class="{{ is_null($n->read_at) ? 'bg-indigo-50/30 dark:bg-indigo-900/10' : '' }}">
                    <flux:table.cell class="text-center text-zinc-500">
                        {{ $rows->firstItem() + $i }}
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="flex items-start gap-3">
                            <div class="mt-1">
                                @if (is_null($n->read_at))
                                    <div
                                        class="size-2 rounded-full bg-indigo-500 ring-2 ring-white dark:ring-stone-950">
                                    </div>
                                @endif
                            </div>
                            <div>
                                <div class="font-medium text-stone-900 dark:text-stone-100 flex items-center gap-2">
                                    {{ $n->title ?? 'Pemberitahuan' }}
                                    <flux:badge size="xs" :color="$config['color']" :icon="$config['icon']"
                                        inset="top bottom">
                                        {{ $config['label'] }}
                                    </flux:badge>
                                </div>
                                <div class="text-sm text-zinc-600 dark:text-zinc-400 mt-0.5 line-clamp-2">
                                    {{ $n->body ?? '—' }}
                                </div>
                            </div>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell class="whitespace-nowrap text-xs text-zinc-500">
                        {{ $n->created_at?->diffForHumans() ?? '—' }}
                        <div class="text-[10px] text-zinc-400">{{ $n->created_at?->format('d/m/y H:i') }}</div>
                    </flux:table.cell>

                    <flux:table.cell class="text-right">
                        <div class="flex justify-end gap-2">
                            @if (!empty($n->link))
                                <flux:button size="xs" variant="primary" icon="arrow-top-right-on-square"
                                    wire:click="open({{ $n->id }})">
                                    Buka
                                </flux:button>
                            @endif

                            @if (is_null($n->read_at))
                                <flux:button size="xs" variant="ghost" icon="check"
                                    wire:click="markRead({{ $n->id }})" title="Tandai Baca" />
                            @endif

                            <flux:button size="xs" variant="ghost" icon="trash"
                                class="text-rose-500 hover:bg-rose-50" wire:click="deleteOne({{ $n->id }})"
                                title="Hapus" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="4">
                        <div class="flex flex-col items-center justify-center py-12 text-center">
                            <div class="rounded-full bg-zinc-100 p-4 dark:bg-stone-900">
                                <flux:icon.bell-slash class="size-8 text-zinc-400" />
                            </div>
                            <h3 class="mt-4 text-base font-semibold text-stone-900 dark:text-stone-100">
                                Tidak ada notifikasi
                            </h3>
                            <p class="mt-1 text-sm text-zinc-500">
                                Anda belum memiliki notifikasi pada tab ini.
                            </p>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</flux:card>
