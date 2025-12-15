<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <flux:heading size="xl" level="1" class="text-stone-900 dark:text-stone-100">
                Kalender Seminar KP
            </flux:heading>
            <flux:subheading class="text-zinc-600 dark:text-zinc-300">
                Lihat jadwal seminar per-bulan. Gunakan filter ruangan untuk cek ketersediaan.
            </flux:subheading>
        </div>
    </div>
    <flux:separator variant="subtle" />

    {{-- GRID UTAMA 3:1 --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">

        {{-- KOLOM KIRI: KALENDER & TABEL (3) --}}
        <div class="lg:col-span-3 space-y-6">

            {{-- FILTER BAR --}}
            <flux:card
                class="rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-xs">
                <div
                    class="px-4 py-3 border-b
                           bg-blue-50 text-blue-700
                           dark:bg-blue-900/20 dark:text-blue-300
                           border-blue-100 dark:border-blue-900/40
                           rounded-t-xl">
                    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                        <h3 class="text-sm font-medium tracking-wide">Filter Jadwal</h3>

                        <div class="flex flex-col gap-2 md:flex-row md:items-end">
                            {{-- BULAN --}}
                            <div class="w-full md:w-40">
                                <flux:input type="month" wire:model.live="month" class="bg-white dark:bg-stone-900" />
                            </div>

                            {{-- RUANGAN --}}
                            <div class="w-full md:w-48">
                                <flux:select wire:model.live="room" placeholder="Semua Ruangan"
                                    class="bg-white dark:bg-stone-900">
                                    <flux:select.option value="all">Semua Ruangan</flux:select.option>
                                    @foreach ($this->roomOptions as $r)
                                        <flux:select.option value="{{ $r }}">{{ $r }}
                                        </flux:select.option>
                                    @endforeach
                                </flux:select>
                            </div>

                            {{-- CARI --}}
                            <div class="w-full md:w-64">
                                <flux:input placeholder="Cari nama / judul..." wire:model.live.debounce.400ms="q"
                                    icon="magnifying-glass" class="bg-white dark:bg-stone-900" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 flex items-center justify-between border-b border-zinc-100 dark:border-zinc-800">
                    <div class="flex items-center gap-2">
                        <flux:button size="sm" variant="ghost" icon="chevron-left" wire:click="prevMonth" />
                        <flux:button size="sm" variant="ghost" icon="calendar" wire:click="setToday">
                            {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y') }}
                        </flux:button>
                        <flux:button size="sm" variant="ghost" icon="chevron-right" wire:click="nextMonth" />
                    </div>

                    <div class="w-24">
                        <flux:select wire:model.live="perPage" size="sm">
                            <flux:select.option :value="10">10 / hal</flux:select.option>
                            <flux:select.option :value="25">25 / hal</flux:select.option>
                            <flux:select.option :value="50">50 / hal</flux:select.option>
                        </flux:select>
                    </div>
                </div>

                {{-- TABEL --}}
                <div class="p-0">
                    <flux:table
                        class="[&_thead_th]:bg-zinc-50 [&_thead_th]:dark:bg-stone-900/40
                               [&_thead_th]:text-zinc-600 [&_thead_th]:dark:text-stone-200
                               [&_tbody_tr]:hover:bg-zinc-50/60 [&_tbody_tr]:dark:hover:bg-stone-900/30"
                        :paginate="$this->items">

                        <flux:table.columns>
                            <flux:table.column class="w-12 text-center">No</flux:table.column>

                            <flux:table.column sortable :sorted="$sortBy === 'tanggal_seminar'"
                                :direction="$sortDirection" wire:click="sort('tanggal_seminar')">
                                Tanggal
                            </flux:table.column>

                            <flux:table.column>Waktu</flux:table.column>
                            <flux:table.column>Ruangan</flux:table.column>
                            <flux:table.column>Mahasiswa</flux:table.column>
                            <flux:table.column>Judul</flux:table.column>
                            <flux:table.column>Status</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @forelse ($this->items as $i => $row)
                                @php
                                    $status = $row->status;
                                    // Mapping icon manual jika perlu, atau dari model
                                    $icon = match ($status) {
                                        'dijadwalkan' => 'calendar',
                                        'ba_terbit' => 'document-text',
                                        'dinilai' => 'star',
                                        'selesai' => 'check-badge',
                                        default => 'minus',
                                    };
                                @endphp
                                <flux:table.row :key="$row->id">
                                    <flux:table.cell class="text-center text-zinc-500">
                                        {{ $this->items->firstItem() + $i }}
                                    </flux:table.cell>

                                    <flux:table.cell
                                        class="whitespace-nowrap font-medium text-stone-900 dark:text-stone-100">
                                        {{ optional($row->tanggal_seminar)->translatedFormat('d M Y') ?: '—' }}
                                    </flux:table.cell>

                                    <flux:table.cell class="whitespace-nowrap text-xs text-zinc-500">
                                        @php
                                            $mulai = $row->jam_mulai
                                                ? \Carbon\Carbon::parse($row->jam_mulai)->format('H:i')
                                                : '—';
                                            $selesai = $row->jam_selesai
                                                ? \Carbon\Carbon::parse($row->jam_selesai)->format('H:i')
                                                : '—';
                                        @endphp
                                        {{ $mulai }} — {{ $selesai }}
                                    </flux:table.cell>

                                    <flux:table.cell class="whitespace-nowrap font-medium">
                                        {{ $row->ruangan_nama ?? '—' }}
                                    </flux:table.cell>

                                    <flux:table.cell class="whitespace-nowrap">
                                        <div class="text-stone-900 dark:text-stone-100">
                                            {{ $row->kp?->mahasiswa?->user?->name ?? '—' }}
                                        </div>
                                        <div class="text-xs text-zinc-500">
                                            {{ $row->kp?->mahasiswa?->nim ?? ($row->kp?->mahasiswa?->mahasiswa_nim ?? '') }}
                                        </div>
                                    </flux:table.cell>

                                    <flux:table.cell class="max-w-[300px]">
                                        <span class="line-clamp-2 text-sm" title="{{ $row->judul_laporan }}">
                                            {{ $row->judul_laporan ?? '—' }}
                                        </span>
                                    </flux:table.cell>

                                    <flux:table.cell>
                                        <flux:badge size="sm" :color="$this->badgeColor($row->status)"
                                            inset="top bottom" :icon="$icon">
                                            {{ $this->statusLabel($row->status) }}
                                        </flux:badge>
                                    </flux:table.cell>
                                </flux:table.row>
                            @empty
                                <flux:table.row>
                                    <flux:table.cell colspan="7">
                                        <div class="py-12 text-center">
                                            <div
                                                class="inline-flex items-center justify-center p-3 rounded-full bg-zinc-100 dark:bg-zinc-800 mb-3">
                                                <flux:icon.calendar class="size-6 text-zinc-400" />
                                            </div>
                                            <h3 class="text-sm font-medium text-stone-900 dark:text-stone-100">
                                                Tidak ada jadwal
                                            </h3>
                                            <p class="text-xs text-zinc-500 mt-1">
                                                @if ($q || $room !== 'all')
                                                    Tidak ditemukan jadwal yang cocok dengan filter.
                                                @else
                                                    Belum ada seminar terjadwal pada bulan ini.
                                                @endif
                                            </p>
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforelse
                        </flux:table.rows>
                    </flux:table>
                </div>
            </flux:card>
        </div>

        {{-- KOLOM KANAN: SIDEBAR (1) --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- PANDUAN --}}
            <flux:card
                class="rounded-xl border bg-blue-50/50 dark:bg-blue-900/10 border-blue-100 dark:border-blue-800/30 shadow-sm">
                <div class="flex items-start gap-3">
                    <flux:icon.information-circle class="mt-0.5 size-5 text-blue-600 dark:text-blue-400" />
                    <div>
                        <h3 class="font-semibold text-blue-900 dark:text-blue-100 text-sm">Panduan Kalender</h3>
                        <ul class="mt-3 text-xs text-blue-800 dark:text-blue-200 space-y-2 list-disc list-inside">
                            <li>Pilih <strong>Bulan</strong> untuk melihat jadwal periode tersebut.</li>
                            <li>Gunakan <strong>Filter Ruangan</strong> untuk cek ketersediaan.</li>
                            <li>Status <strong>Dijadwalkan</strong> artinya jadwal sudah fix.</li>
                            <li>Status <strong>BA Terbit / Dinilai</strong> artinya seminar sudah selesai.</li>
                        </ul>
                    </div>
                </div>
            </flux:card>
        </div>
    </div>
</div>
