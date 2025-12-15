<div class="space-y-6">
    <flux:toast />

    {{-- HEADER --}}
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <flux:heading size="xl" level="1" class="text-stone-900 dark:text-stone-100">
                Penjadwalan Seminar KP
            </flux:heading>
            <flux:subheading class="text-zinc-600 dark:text-zinc-300">
                Atur jadwal seminar KP dan terbitkan Berita Acara (BA).
            </flux:subheading>
        </div>
    </div>

    <flux:separator variant="subtle" />

    {{-- GRID UTAMA 3:1 --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">

        {{-- KOLOM KIRI: TABEL (3) --}}
        <div class="lg:col-span-3 space-y-6">
            <flux:card
                class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm overflow-hidden">

                {{-- Header Tabel --}}
                <div
                    class="flex flex-col gap-4 px-6 py-4 border-b border-zinc-200 dark:border-stone-800 bg-sky-50/50 dark:bg-sky-900/10 md:flex-row md:items-center md:justify-between">
                    <h4 class="text-base font-semibold text-stone-900 dark:text-stone-100">Daftar Seminar</h4>

                    <div class="flex flex-col gap-3 md:flex-row md:items-center">
                        <div class="w-full md:w-56">
                            <flux:input icon="magnifying-glass" placeholder="Cari..."
                                wire:model.live.debounce.300ms="search" class="bg-white dark:bg-stone-900" />
                        </div>
                        <div class="w-full md:w-40">
                            <flux:select wire:model.live="statusFilter" class="bg-white dark:bg-stone-900">
                                @foreach ($this->statusOptions as $opt)
                                    <flux:select.option :value="$opt['value']">{{ $opt['label'] }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>
                    </div>
                </div>

                <flux:table :paginate="$this->items">
                    <flux:table.columns>
                        <flux:table.column class="w-12 text-center">No</flux:table.column>
                        <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection"
                            wire:click="sort('created_at')">Dibuat</flux:table.column>
                        <flux:table.column>Mahasiswa</flux:table.column>
                        <flux:table.column>Judul</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                        <flux:table.column>Jadwal</flux:table.column>
                        <flux:table.column class="text-right">Aksi</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($this->items as $i => $row)
                            <flux:table.row :key="$row->id">
                                <flux:table.cell class="text-center text-zinc-500">
                                    {{ $this->items->firstItem() + $i }}
                                </flux:table.cell>

                                <flux:table.cell class="whitespace-nowrap">
                                    {{ optional($row->created_at)->format('d M Y') }}
                                </flux:table.cell>

                                <flux:table.cell>
                                    <div class="font-medium text-stone-900 dark:text-stone-100">
                                        {{ $row->kp?->mahasiswa?->user?->name ?? '—' }}
                                    </div>
                                    <div class="text-xs text-zinc-500">
                                        {{ $row->kp?->mahasiswa?->mahasiswa_nim ?? '—' }}
                                    </div>
                                </flux:table.cell>

                                <flux:table.cell class="max-w-[200px]">
                                    <span class="line-clamp-2" title="{{ $row->judul_laporan }}">
                                        {{ $row->judul_laporan ?? ($row->kp?->judul_kp ?? '—') }}
                                    </span>
                                </flux:table.cell>

                                <flux:table.cell>
                                    <flux:badge size="sm" inset="top bottom"
                                        :color="$this->badgeColor($row->status)">
                                        {{ $this->statusLabel($row->status) }}
                                    </flux:badge>
                                </flux:table.cell>

                                <flux:table.cell class="whitespace-nowrap">
                                    @if ($row->tanggal_seminar)
                                        <div class="text-sm font-medium">
                                            {{ $row->tanggal_seminar->format('d M Y H:i') }}
                                        </div>
                                        @if ($row->ruangan_nama)
                                            <div class="text-xs text-zinc-500">{{ $row->ruangan_nama }}</div>
                                        @endif
                                    @else
                                        <span class="text-xs text-zinc-400 italic">Belum dijadwalkan</span>
                                    @endif
                                </flux:table.cell>

                                <flux:table.cell class="text-right">
                                    <flux:button variant="ghost" size="sm" icon="pencil-square"
                                        wire:click="openEdit({{ $row->id }})" title="Jadwalkan / BA" />
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>

                {{-- Empty State --}}
                @if ($this->items->isEmpty())
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <div class="rounded-full bg-zinc-100 p-4 dark:bg-stone-900">
                            <flux:icon.calendar class="size-8 text-zinc-400" />
                        </div>
                        <h3 class="mt-4 text-base font-semibold text-stone-900 dark:text-stone-100">
                            Data tidak ditemukan
                        </h3>
                        <p class="mt-1 text-sm text-zinc-500">
                            @if ($search || $statusFilter !== 'all')
                                Tidak ada data yang cocok dengan filter pencarian.
                            @else
                                Belum ada pengajuan seminar yang masuk.
                            @endif
                        </p>
                    </div>
                @endif
            </flux:card>
        </div>

        {{-- KOLOM KANAN: SIDEBAR (1) --}}
        <div class="lg:col-span-1 space-y-6">

            {{-- 1. RINGKASAN STATUS --}}
            <flux:card
                class="rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm">
                <div class="mb-4 flex items-center gap-2">
                    <flux:icon.chart-bar class="size-5 text-zinc-500" />
                    <h3 class="font-semibold text-stone-900 dark:text-stone-100">Ringkasan</h3>
                </div>

                <div class="space-y-3">
                    <div
                        class="flex items-center justify-between p-2 rounded-lg bg-sky-50/50 dark:bg-sky-900/10 border border-sky-100 dark:border-sky-800/30">
                        <div class="flex items-center gap-2">
                            <div class="size-2 rounded-full bg-sky-500"></div>
                            <span class="text-sm font-medium text-stone-700 dark:text-stone-300">Disetujui</span>
                        </div>
                        <span
                            class="text-lg font-bold text-sky-600 dark:text-sky-400">{{ $this->stats['disetujui'] }}</span>
                    </div>

                    <div
                        class="flex items-center justify-between p-2 rounded-lg bg-emerald-50/50 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-800/30">
                        <div class="flex items-center gap-2">
                            <div class="size-2 rounded-full bg-emerald-500"></div>
                            <span class="text-sm font-medium text-stone-700 dark:text-stone-300">Dijadwalkan</span>
                        </div>
                        <span
                            class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ $this->stats['dijadwalkan'] }}</span>
                    </div>

                    <div
                        class="flex items-center justify-between p-2 rounded-lg bg-violet-50/50 dark:bg-violet-900/10 border border-violet-100 dark:border-violet-800/30">
                        <div class="flex items-center gap-2">
                            <div class="size-2 rounded-full bg-violet-500"></div>
                            <span class="text-sm font-medium text-stone-700 dark:text-stone-300">BA Terbit</span>
                        </div>
                        <span
                            class="text-lg font-bold text-violet-600 dark:text-violet-400">{{ $this->stats['ba_terbit'] }}</span>
                    </div>
                </div>
            </flux:card>

            {{-- 2. PANDUAN --}}
            <flux:card
                class="rounded-xl border bg-indigo-50/50 dark:bg-indigo-900/10 border-indigo-100 dark:border-indigo-800/30 shadow-sm">
                <div class="flex items-start gap-3">
                    <flux:icon.information-circle class="mt-0.5 size-5 text-indigo-600 dark:text-indigo-400" />
                    <div>
                        <h3 class="font-semibold text-indigo-900 dark:text-indigo-100 text-sm">Alur Penjadwalan</h3>
                        <ul class="mt-3 text-xs text-indigo-800 dark:text-indigo-200 space-y-2 list-disc list-inside">
                            <li>Filter status <strong>Disetujui Pembimbing</strong> untuk melihat yang perlu
                                dijadwalkan.</li>
                            <li>Klik tombol <strong>Jadwalkan</strong> untuk atur tanggal & ruangan.</li>
                            <li>Setelah seminar selesai, input nomor BA & tanggal, lalu <strong>Terbitkan BA</strong>.
                            </li>
                        </ul>
                    </div>
                </div>
            </flux:card>
        </div>
    </div>

    {{-- MODAL EDIT: Jadwal & BA --}}
    <flux:modal name="edit-seminar" class="min-w-[34rem]" :show="$editId !== null">
        <div class="space-y-6">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <flux:heading size="lg">Jadwalkan & Terbitkan BA</flux:heading>
                    <p class="text-sm text-zinc-500 dark:text-zinc-300">
                        Lengkapi data di bawah, simpan jadwal, lalu terbitkan BA.
                    </p>
                </div>
                <flux:modal.close>
                    <flux:button variant="ghost" icon="x-mark" wire:click="$set('editId', null)"></flux:button>
                </flux:modal.close>
            </div>

            {{-- Jadwal --}}
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <flux:input type="datetime-local" label="Tanggal & Jam Seminar" wire:model.defer="tanggal_seminar"
                        :invalid="$errors->has('tanggal_seminar')" />
                    @error('tanggal_seminar')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <flux:input label="Ruangan (nama)" wire:model.defer="ruangan_nama"
                        :invalid="$errors->has('ruangan_nama')" />
                    @error('ruangan_nama')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <flux:button variant="primary" icon="calendar" wire:click="saveSchedule" wire:loading.attr="disabled">
                    Simpan Jadwal
                </flux:button>
            </div>

            <flux:separator />

            {{-- BA --}}
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <flux:input label="Nomor BA" wire:model.defer="nomor_ba" :invalid="$errors->has('nomor_ba')"
                        placeholder="No. Berita Acara" />
                    @error('nomor_ba')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <flux:input type="date" label="Tanggal BA" wire:model.defer="tanggal_ba"
                        :invalid="$errors->has('tanggal_ba')" />
                    @error('tanggal_ba')
                        <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div>
                <flux:select label="Penandatangan" wire:model="signatory_id" placeholder="Pilih Penandatangan...">
                    @foreach ($this->signatories as $sig)
                        <flux:select.option :value="$sig->id">
                            {{ $sig->name }} ({{ $sig->position }})
                        </flux:select.option>
                    @endforeach
                </flux:select>
                @error('signatory_id')
                    <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                @enderror
            </div>


            <div class="flex justify-end gap-2 pt-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Tutup</flux:button>
                </flux:modal.close>

                <flux:button variant="primary" icon="check" wire:click="publishBA" wire:loading.attr="disabled">
                    Terbitkan BA
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
