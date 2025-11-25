{{-- resources/views/livewire/mahasiswa/kp/konsultasi-page.blade.php --}}
<div class="space-y-6">

    {{-- ALERTS --}}
    @if (session('ok'))
        <div class="rounded-md border border-emerald-300/60 bg-emerald-50 px-3 py-2 text-emerald-800">
            <div class="font-medium">{{ session('ok') }}</div>
        </div>
    @endif

    {{-- BARIS ATAS: FORM (kiri) + PANDUAN (kanan) --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-10">

        {{-- FORM KONSULTASI --}}
        <div class="lg:col-span-7">
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
        </div>

        {{-- CARD PANDUAN (baru) --}}
        <div class="lg:col-span-3">
            <flux:card
                class="space-y-4 rounded-xl border
                       bg-white dark:bg-stone-950
                       border-zinc-200 dark:border-stone-800
                       shadow-xs">

                <div class="flex items-center gap-2 px-1.5 -mt-1">
                    <span
                        class="inline-flex items-center justify-center rounded-md p-1.5 bg-amber-500 text-white dark:bg-amber-400">
                        <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 9v4" />
                            <path d="M12 17h.01" />
                            <path d="M10 2h4l7 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-base font-semibold text-stone-900 dark:text-stone-100">
                            Panduan Konsultasi
                        </h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-300">Tips ringkas & progres verifikasi</p>
                    </div>
                </div>

                <flux:separator />

                {{-- PROGRES SINGKAT (x/6) --}}
                <div
                    class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-amber-800
                            dark:border-amber-900/40 dark:bg-amber-900/20 dark:text-amber-200">
                    <div class="flex items-center justify-between">
                        <div class="text-xs">Terverifikasi</div>
                        <div class="text-xs font-semibold">
                            {{ $kp->verifiedConsultationsCount() }} / 6
                        </div>
                    </div>
                    <div class="mt-2 h-2 overflow-hidden rounded bg-amber-200/60 dark:bg-amber-800/40">
                        @php
                            $v = (int) $kp->verifiedConsultationsCount();
                            $pct = max(0, min(100, ($v / 6) * 100));
                        @endphp
                        <div class="h-2 bg-amber-500 dark:bg-amber-400" style="width: {{ $pct }}%"></div>
                    </div>
                    <p class="mt-2 text-[11px] leading-4">
                        Minimal <span class="font-semibold">6</span> konsultasi terverifikasi untuk daftar seminar.
                    </p>
                </div>

                {{-- LIST TIPS --}}
                <div class="space-y-3 text-sm leading-6 text-zinc-700 dark:text-stone-300">
                    <div class="flex items-start gap-2">
                        <flux:badge size="xs" inset="top bottom" color="zinc">1</flux:badge>
                        <p><span class="font-medium">Tanggal</span> isi sesuai pertemuan, bukan tanggal input.</p>
                    </div>
                    <div class="flex items-start gap-2">
                        <flux:badge size="xs" inset="top bottom" color="zinc">2</flux:badge>
                        <p><span class="font-medium">Topik</span> singkat & spesifik (mis. “Validasi ERD”, “Uji SUS”).
                        </p>
                    </div>
                    <div class="flex items-start gap-2">
                        <flux:badge size="xs" inset="top bottom" color="zinc">3</flux:badge>
                        <p><span class="font-medium">Hasil konsultasi</span> tulis keputusan/aksi lanjut (action items).
                        </p>
                    </div>
                    <div class="flex items-start gap-2">
                        <flux:badge size="xs" inset="top bottom" color="zinc">4</flux:badge>
                        <p>Catatan hanya bisa <span class="font-medium">diubah/hapus</span> sebelum diverifikasi dosen.
                        </p>
                    </div>
                    <div class="flex items-start gap-2">
                        <flux:badge size="xs" inset="top bottom" color="zinc">5</flux:badge>
                        <p>Jika status masih <em>Menunggu</em>, gunakan menu <span class="font-medium">⋯</span> untuk
                            Edit/Hapus.</p>
                    </div>
                </div>
            </flux:card>
        </div>
    </div>

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
