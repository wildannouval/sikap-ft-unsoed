{{-- resources/views/livewire/mahasiswa/kp/konsultasi-page.blade.php --}}
<div class="space-y-6">

    {{-- ALERTS --}}
    @if (session('ok'))
        <div class="rounded-md border border-emerald-300/60 bg-emerald-50 px-3 py-2 text-emerald-800">
            <div class="font-medium">{{ session('ok') }}</div>
        </div>
    @endif

    {{-- FORM KONSULTASI --}}
    <flux:card
        class="space-y-6 rounded-xl border
               bg-white dark:bg-stone-950
               border-zinc-200 dark:border-stone-800
               shadow-xs">

        {{-- Header kartu dengan aksen indigo --}}
        <div class="flex items-center gap-2 px-1.5 -mt-1">
            <span
                class="inline-flex items-center justify-center rounded-md p-1.5
                       bg-indigo-500 text-white dark:bg-indigo-400">
                <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M8 10h8M8 14h5" />
                    <rect x="3" y="4" width="18" height="16" rx="2" />
                </svg>
            </span>
            <div>
                <h3 class="text-base font-semibold text-stone-900 dark:text-stone-100">
                    Catat Konsultasi KP
                </h3>
                <p class="text-sm text-zinc-500 dark:text-zinc-300">
                    Simpan topik & hasil diskusi dengan Dosen Pembimbing.
                </p>
            </div>
        </div>

        <flux:separator />

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <flux:input label="Konsultasi dengan" wire:model.defer="konsultasi_dengan"
                    placeholder="Dosen/WA/Zoom/Offline" />
            </div>

            <div>
                <flux:input type="date" label="Tanggal konsultasi" wire:model.defer="tanggal_konsultasi"
                    :invalid="$errors->has('tanggal_konsultasi')" />
                @error('tanggal_konsultasi')
                    <div class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</div>
                @enderror
            </div>

            <div class="md:col-span-2">
                <flux:input label="Topik konsultasi" wire:model.defer="topik_konsultasi"
                    :invalid="$errors->has('topik_konsultasi')" />
                @error('topik_konsultasi')
                    <div class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</div>
                @enderror
            </div>

            <div class="md:col-span-2">
                <flux:textarea rows="4" label="Hasil konsultasi" wire:model.defer="hasil_konsultasi"
                    :invalid="$errors->has('hasil_konsultasi')" />
                @error('hasil_konsultasi')
                    <div class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="flex justify-end gap-2">
            @if ($editingId)
                <flux:button variant="ghost" wire:click="cancelEdit" icon="x-mark">Batal</flux:button>
                <flux:button variant="primary" icon="check" wire:click="updateItem">Simpan</flux:button>
            @else
                <flux:button variant="primary" icon="plus" wire:click="submit">Tambah</flux:button>
            @endif
        </div>
    </flux:card>

    {{-- TABEL KONSULTASI --}}
    <flux:card
        class="space-y-4 rounded-xl border
               bg-white dark:bg-stone-950
               border-zinc-200 dark:border-stone-800
               shadow-xs">

        {{-- Header tabel dengan aksen indigo, + search & perPage --}}
        <div
            class="px-4 py-3 border-b
                   bg-indigo-50 text-indigo-700
                   dark:bg-indigo-900/20 dark:text-indigo-300
                   border-indigo-100 dark:border-indigo-900/40
                   rounded-t-xl">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <h4 class="text-sm font-medium tracking-wide">Daftar Konsultasi</h4>
                <div class="flex items-center gap-2">
                    <div class="hidden md:flex text-xs md:text-sm text-indigo-800 dark:text-indigo-200">
                        Terverifikasi: {{ $kp->verifiedConsultationsCount() }} / 6
                    </div>
                    <div class="md:w-80">
                        <flux:input placeholder="Cari topik / hasil…" wire:model.debounce.400ms="q"
                            icon="magnifying-glass" />
                    </div>
                    <flux:select wire:model="perPage" class="w-36">
                        <option value="10">10 / halaman</option>
                        <option value="25">25 / halaman</option>
                        <option value="50">50 / halaman</option>
                    </flux:select>
                </div>
            </div>
        </div>

        <flux:table
            class="[&_thead_th]:bg-zinc-50 [&_thead_th]:dark:bg-stone-900/40
                   [&_thead_th]:text-zinc-600 [&_thead_th]:dark:text-stone-200
                   [&_tbody_tr]:hover:bg-zinc-50/60 [&_tbody_tr]:dark:hover:bg-stone-900/30"
            :paginate="$this->items">

            <flux:table.columns>
                <flux:table.column class="w-12">#</flux:table.column>

                <flux:table.column sortable :sorted="$sortBy === 'tanggal_konsultasi'" :direction="$sortDirection"
                    wire:click="sort('tanggal_konsultasi')">
                    Tanggal
                </flux:table.column>

                <flux:table.column sortable :sorted="$sortBy === 'topik_konsultasi'" :direction="$sortDirection"
                    wire:click="sort('topik_konsultasi')">
                    Topik
                </flux:table.column>

                <flux:table.column>Hasil</flux:table.column>

                <flux:table.column class="w-32" sortable :sorted="$sortBy === 'verified_at'"
                    :direction="$sortDirection" wire:click="sort('verified_at')">
                    Status
                </flux:table.column>

                <flux:table.column class="w-28 text-right">Aksi</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->items as $i => $row)
                    <flux:table.row :key="$row->id">
                        <flux:table.cell>{{ $this->items->firstItem() + $i }}</flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap">
                            {{ optional($row->tanggal_konsultasi)->format('d M Y') ?: '—' }}
                        </flux:table.cell>

                        <flux:table.cell class="max-w-[280px]">
                            <span class="line-clamp-2 text-stone-900 dark:text-stone-100">
                                {{ $row->topik_konsultasi }}
                            </span>
                        </flux:table.cell>

                        <flux:table.cell class="max-w-[420px]">
                            <span class="line-clamp-2 text-zinc-700 dark:text-stone-300">
                                {{ $row->hasil_konsultasi }}
                            </span>
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($row->verified_at)
                                <flux:badge size="sm"
                                    class="border border-emerald-200 dark:border-emerald-900/40
                                           bg-emerald-50 text-emerald-700
                                           dark:bg-emerald-900/20 dark:text-emerald-300">
                                    Terverifikasi
                                </flux:badge>
                            @else
                                <flux:badge size="sm"
                                    class="border border-zinc-200 dark:border-stone-700
                                           bg-zinc-100 text-zinc-700
                                           dark:bg-stone-900/30 dark:text-stone-200">
                                    Menunggu
                                </flux:badge>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="text-right">
                            @if (!$row->verified_at)
                                <flux:button size="sm" variant="ghost" icon="pencil-square" title="Edit"
                                    wire:click="edit({{ $row->id }})" />
                                <flux:button size="sm" variant="ghost" icon="trash" title="Hapus"
                                    wire:click="deleteItem({{ $row->id }})" />
                            @else
                                <span class="text-xs text-zinc-500">—</span>
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6">
                            <div class="py-6 text-center text-sm text-zinc-500">
                                Belum ada catatan konsultasi.
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>
</div>
