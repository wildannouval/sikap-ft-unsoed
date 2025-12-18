<div class="space-y-6">
    <flux:toast />

    {{-- HEADER --}}
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <flux:heading size="xl" level="1" class="text-stone-900 dark:text-stone-100">
                Persetujuan Seminar
            </flux:heading>
            <flux:subheading class="text-zinc-600 dark:text-zinc-300">
                Tinjau pengajuan seminar mahasiswa bimbingan.
            </flux:subheading>
        </div>
    </div>

    <flux:separator variant="subtle" />

    {{-- GRID UTAMA 3:1 --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">

        {{-- KOLOM KIRI: TABEL --}}
        <div class="lg:col-span-3 space-y-6">

            {{-- TAB: UI tetap Flux Tabs, tapi state mengikuti Livewire via setTab() --}}
            <flux:tab.group>
                <flux:tabs>
                    <flux:tab name="pending" icon="inbox-arrow-down" :active="$tab === 'pending'"
                        wire:click.prevent="setTab('pending')">
                        Menunggu
                        <flux:badge size="sm" inset="top bottom" class="ml-2" color="amber">
                            {{ $this->stats['pending'] }}
                        </flux:badge>
                    </flux:tab>

                    <flux:tab name="approved" icon="check-circle" :active="$tab === 'approved'"
                        wire:click.prevent="setTab('approved')">
                        Disetujui
                        <flux:badge size="sm" inset="top bottom" class="ml-2" color="sky">
                            {{ $this->stats['approved'] }}
                        </flux:badge>
                    </flux:tab>

                    <flux:tab name="history" icon="clock" :active="$tab === 'history'"
                        wire:click.prevent="setTab('history')">
                        Riwayat
                    </flux:tab>
                </flux:tabs>
            </flux:tab.group>

            {{-- PANEL: PENDING --}}
            @if ($tab === 'pending')
                <div class="pt-4">
                    <flux:card
                        class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm overflow-hidden">

                        {{-- Header Tabel --}}
                        <div
                            class="flex flex-col gap-4 px-6 py-4 border-b border-zinc-200 dark:border-stone-800 bg-zinc-50/50 dark:bg-zinc-900/50 md:flex-row md:items-center md:justify-between">
                            <h4 class="text-base font-semibold text-stone-900 dark:text-stone-100">
                                Daftar Menunggu Review
                            </h4>
                            <div class="flex items-center gap-3">
                                <flux:input icon="magnifying-glass" placeholder="Cari nama / judul..."
                                    wire:model.live.debounce.300ms="q"
                                    class="w-full md:w-64 bg-white dark:bg-stone-900" />
                            </div>
                        </div>

                        {{-- Tabel --}}
                        <flux:table :paginate="$this->items">
                            <flux:table.columns>
                                <flux:table.column class="w-10 text-center">No</flux:table.column>

                                <flux:table.column sortable :sorted="$sortBy === 'created_at'"
                                    :direction="$sortDirection" wire:click="sort('created_at')">
                                    Tanggal
                                </flux:table.column>

                                <flux:table.column>Mahasiswa</flux:table.column>
                                <flux:table.column>Judul Laporan</flux:table.column>
                                <flux:table.column>Status</flux:table.column>
                                <flux:table.column class="text-right">Aksi</flux:table.column>
                            </flux:table.columns>

                            <flux:table.rows>
                                @foreach ($this->items as $i => $row)
                                    <flux:table.row :key="'p-' . $row->id">
                                        <flux:table.cell class="text-center text-zinc-500">
                                            {{ ($this->items->firstItem() ?? 0) + $i }}
                                        </flux:table.cell>

                                        <flux:table.cell class="whitespace-nowrap">
                                            {{ optional($row->created_at)->format('d M Y') }}
                                        </flux:table.cell>

                                        <flux:table.cell>
                                            <div class="font-medium text-stone-900 dark:text-stone-100">
                                                {{ $row->kp?->mahasiswa?->user?->name ?? '—' }}
                                            </div>
                                            <div class="text-xs text-zinc-500">
                                                {{ $row->kp?->mahasiswa?->mahasiswa_nim ?? '' }}
                                            </div>
                                        </flux:table.cell>

                                        <flux:table.cell class="max-w-[250px]">
                                            <span class="line-clamp-2 text-sm">
                                                {{ $row->judul_laporan }}
                                            </span>
                                        </flux:table.cell>

                                        <flux:table.cell>
                                            <flux:badge size="sm" :color="$this->badgeColor($row->status)"
                                                :icon="$this->badgeIcon($row->status)">
                                                {{ $this->statusLabel($row->status) }}
                                            </flux:badge>
                                        </flux:table.cell>

                                        <flux:table.cell class="text-right">
                                            <flux:dropdown position="bottom" align="end">
                                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                                    inset="top bottom" />
                                                <flux:menu>
                                                    <flux:menu.item icon="eye"
                                                        wire:click="openDetail({{ $row->id }})">
                                                        Detail
                                                    </flux:menu.item>

                                                    @if ($row->berkas_laporan_path)
                                                        <flux:menu.item icon="document-text"
                                                            href="{{ asset('storage/' . $row->berkas_laporan_path) }}"
                                                            target="_blank">
                                                            Lihat Berkas
                                                        </flux:menu.item>
                                                    @endif

                                                    <flux:menu.separator />

                                                    <flux:menu.item icon="check"
                                                        wire:click="approve({{ $row->id }})">
                                                        Setujui
                                                    </flux:menu.item>

                                                    <flux:menu.item icon="x-mark" variant="danger"
                                                        wire:click="triggerReject({{ $row->id }})">
                                                        Tolak
                                                    </flux:menu.item>
                                                </flux:menu>
                                            </flux:dropdown>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>

                        @if ($this->items->isEmpty())
                            <div class="flex flex-col items-center justify-center py-12 text-center">
                                <div class="rounded-full bg-zinc-100 p-4 dark:bg-stone-900">
                                    <flux:icon.check-badge class="size-8 text-zinc-400" />
                                </div>
                                <h3 class="mt-4 text-base font-semibold text-stone-900 dark:text-stone-100">
                                    Tidak ada data
                                </h3>
                                <p class="mt-1 text-sm text-zinc-500">
                                    Belum ada pengajuan menunggu pada tab ini.
                                </p>
                            </div>
                        @endif
                    </flux:card>
                </div>
            @endif

            {{-- PANEL: APPROVED --}}
            @if ($tab === 'approved')
                <div class="pt-4">
                    <flux:card
                        class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm overflow-hidden">

                        <div
                            class="flex flex-col gap-4 px-6 py-4 border-b border-zinc-200 dark:border-stone-800 bg-zinc-50/50 dark:bg-zinc-900/50 md:flex-row md:items-center md:justify-between">
                            <h4 class="text-base font-semibold text-stone-900 dark:text-stone-100">
                                Daftar Disetujui
                            </h4>
                            <div class="flex items-center gap-3">
                                <flux:input icon="magnifying-glass" placeholder="Cari nama / judul..."
                                    wire:model.live.debounce.300ms="q"
                                    class="w-full md:w-64 bg-white dark:bg-stone-900" />
                            </div>
                        </div>

                        <flux:table :paginate="$this->items">
                            <flux:table.columns>
                                <flux:table.column class="w-10 text-center">No</flux:table.column>

                                <flux:table.column sortable :sorted="$sortBy === 'created_at'"
                                    :direction="$sortDirection" wire:click="sort('created_at')">
                                    Tanggal
                                </flux:table.column>

                                <flux:table.column>Mahasiswa</flux:table.column>
                                <flux:table.column>Judul Laporan</flux:table.column>
                                <flux:table.column>Status</flux:table.column>
                                <flux:table.column class="text-right">Aksi</flux:table.column>
                            </flux:table.columns>

                            <flux:table.rows>
                                @foreach ($this->items as $i => $row)
                                    <flux:table.row :key="'a-' . $row->id">
                                        <flux:table.cell class="text-center text-zinc-500">
                                            {{ ($this->items->firstItem() ?? 0) + $i }}
                                        </flux:table.cell>

                                        <flux:table.cell class="whitespace-nowrap">
                                            {{ optional($row->created_at)->format('d M Y') }}
                                        </flux:table.cell>

                                        <flux:table.cell>
                                            <div class="font-medium text-stone-900 dark:text-stone-100">
                                                {{ $row->kp?->mahasiswa?->user?->name ?? '—' }}
                                            </div>
                                            <div class="text-xs text-zinc-500">
                                                {{ $row->kp?->mahasiswa?->mahasiswa_nim ?? '' }}
                                            </div>
                                        </flux:table.cell>

                                        <flux:table.cell class="max-w-[250px]">
                                            <span class="line-clamp-2 text-sm">{{ $row->judul_laporan }}</span>
                                        </flux:table.cell>

                                        <flux:table.cell>
                                            <flux:badge size="sm" :color="$this->badgeColor($row->status)"
                                                :icon="$this->badgeIcon($row->status)">
                                                {{ $this->statusLabel($row->status) }}
                                            </flux:badge>
                                        </flux:table.cell>

                                        <flux:table.cell class="text-right">
                                            <flux:dropdown position="bottom" align="end">
                                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                                    inset="top bottom" />
                                                <flux:menu>
                                                    <flux:menu.item icon="eye"
                                                        wire:click="openDetail({{ $row->id }})">
                                                        Detail
                                                    </flux:menu.item>

                                                    @if ($row->berkas_laporan_path)
                                                        <flux:menu.item icon="document-text"
                                                            href="{{ asset('storage/' . $row->berkas_laporan_path) }}"
                                                            target="_blank">
                                                            Lihat Berkas
                                                        </flux:menu.item>
                                                    @endif
                                                </flux:menu>
                                            </flux:dropdown>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>

                        @if ($this->items->isEmpty())
                            <div class="flex flex-col items-center justify-center py-12 text-center">
                                <div class="rounded-full bg-zinc-100 p-4 dark:bg-stone-900">
                                    <flux:icon.check-circle class="size-8 text-zinc-400" />
                                </div>
                                <h3 class="mt-4 text-base font-semibold text-stone-900 dark:text-stone-100">
                                    Tidak ada data
                                </h3>
                                <p class="mt-1 text-sm text-zinc-500">
                                    Belum ada pengajuan disetujui pada tab ini.
                                </p>
                            </div>
                        @endif
                    </flux:card>
                </div>
            @endif

            {{-- PANEL: HISTORY --}}
            @if ($tab === 'history')
                <div class="pt-4">
                    <flux:card
                        class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm overflow-hidden">

                        <div
                            class="flex flex-col gap-4 px-6 py-4 border-b border-zinc-200 dark:border-stone-800 bg-zinc-50/50 dark:bg-zinc-900/50 md:flex-row md:items-center md:justify-between">
                            <h4 class="text-base font-semibold text-stone-900 dark:text-stone-100">
                                Riwayat Pengajuan
                            </h4>
                            <div class="flex items-center gap-3">
                                <flux:input icon="magnifying-glass" placeholder="Cari nama / judul..."
                                    wire:model.live.debounce.300ms="q"
                                    class="w-full md:w-64 bg-white dark:bg-stone-900" />
                            </div>
                        </div>

                        <flux:table :paginate="$this->items">
                            <flux:table.columns>
                                <flux:table.column class="w-10 text-center">No</flux:table.column>

                                <flux:table.column sortable :sorted="$sortBy === 'created_at'"
                                    :direction="$sortDirection" wire:click="sort('created_at')">
                                    Tanggal
                                </flux:table.column>

                                <flux:table.column>Mahasiswa</flux:table.column>
                                <flux:table.column>Judul Laporan</flux:table.column>
                                <flux:table.column>Status</flux:table.column>
                                <flux:table.column class="text-right">Aksi</flux:table.column>
                            </flux:table.columns>

                            <flux:table.rows>
                                @foreach ($this->items as $i => $row)
                                    <flux:table.row :key="'h-' . $row->id">
                                        <flux:table.cell class="text-center text-zinc-500">
                                            {{ ($this->items->firstItem() ?? 0) + $i }}
                                        </flux:table.cell>

                                        <flux:table.cell class="whitespace-nowrap">
                                            {{ optional($row->created_at)->format('d M Y') }}
                                        </flux:table.cell>

                                        <flux:table.cell>
                                            <div class="font-medium text-stone-900 dark:text-stone-100">
                                                {{ $row->kp?->mahasiswa?->user?->name ?? '—' }}
                                            </div>
                                            <div class="text-xs text-zinc-500">
                                                {{ $row->kp?->mahasiswa?->mahasiswa_nim ?? '' }}
                                            </div>
                                        </flux:table.cell>

                                        <flux:table.cell class="max-w-[250px]">
                                            <span class="line-clamp-2 text-sm">{{ $row->judul_laporan }}</span>
                                        </flux:table.cell>

                                        <flux:table.cell>
                                            <flux:badge size="sm" :color="$this->badgeColor($row->status)"
                                                :icon="$this->badgeIcon($row->status)">
                                                {{ $this->statusLabel($row->status) }}
                                            </flux:badge>
                                        </flux:table.cell>

                                        <flux:table.cell class="text-right">
                                            <flux:dropdown position="bottom" align="end">
                                                <flux:button variant="ghost" size="sm"
                                                    icon="ellipsis-horizontal" inset="top bottom" />
                                                <flux:menu>
                                                    <flux:menu.item icon="eye"
                                                        wire:click="openDetail({{ $row->id }})">
                                                        Detail
                                                    </flux:menu.item>

                                                    @if ($row->berkas_laporan_path)
                                                        <flux:menu.item icon="document-text"
                                                            href="{{ asset('storage/' . $row->berkas_laporan_path) }}"
                                                            target="_blank">
                                                            Lihat Berkas
                                                        </flux:menu.item>
                                                    @endif
                                                </flux:menu>
                                            </flux:dropdown>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>

                        @if ($this->items->isEmpty())
                            <div class="flex flex-col items-center justify-center py-12 text-center">
                                <div class="rounded-full bg-zinc-100 p-4 dark:bg-stone-900">
                                    <flux:icon.clock class="size-8 text-zinc-400" />
                                </div>
                                <h3 class="mt-4 text-base font-semibold text-stone-900 dark:text-stone-100">
                                    Tidak ada data
                                </h3>
                                <p class="mt-1 text-sm text-zinc-500">
                                    Belum ada riwayat pengajuan.
                                </p>
                            </div>
                        @endif
                    </flux:card>
                </div>
            @endif

        </div>

        {{-- KOLOM KANAN: SIDEBAR --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- RINGKASAN --}}
            <flux:card
                class="rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm">
                <div class="mb-4 flex items-center gap-2">
                    <flux:icon.chart-bar class="size-5 text-zinc-500" />
                    <h3 class="font-semibold text-stone-900 dark:text-stone-100">Ringkasan</h3>
                </div>

                <div class="space-y-3">
                    <div
                        class="flex items-center justify-between p-2 rounded-lg bg-amber-50/50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-800/30">
                        <div class="flex items-center gap-2">
                            <div class="size-2 rounded-full bg-amber-500 animate-pulse"></div>
                            <span class="text-sm font-medium text-stone-700 dark:text-stone-300">Perlu Review</span>
                        </div>
                        <span
                            class="text-lg font-bold text-amber-600 dark:text-amber-400">{{ $this->stats['pending'] }}</span>
                    </div>

                    <div
                        class="flex items-center justify-between p-2 rounded-lg bg-sky-50/50 dark:bg-sky-900/10 border border-sky-100 dark:border-sky-800/30">
                        <div class="flex items-center gap-2">
                            <div class="size-2 rounded-full bg-sky-500"></div>
                            <span class="text-sm font-medium text-stone-700 dark:text-stone-300">Menunggu Jadwal</span>
                        </div>
                        <span
                            class="text-lg font-bold text-sky-600 dark:text-sky-400">{{ $this->stats['approved'] }}</span>
                    </div>

                    <div
                        class="flex items-center justify-between p-2 rounded-lg bg-zinc-50/50 dark:bg-zinc-900/10 border border-zinc-100 dark:border-zinc-800/30">
                        <div class="flex items-center gap-2">
                            <div class="size-2 rounded-full bg-zinc-500"></div>
                            <span class="text-sm font-medium text-stone-700 dark:text-stone-300">Riwayat</span>
                        </div>
                        <span
                            class="text-lg font-bold text-zinc-600 dark:text-zinc-400">{{ $this->stats['history'] }}</span>
                    </div>
                </div>
            </flux:card>

            {{-- PANDUAN --}}
            <flux:card
                class="rounded-xl border bg-indigo-50/50 dark:bg-indigo-900/10 border-indigo-100 dark:border-indigo-800/30 shadow-sm">
                <div class="flex items-start gap-3">
                    <flux:icon.information-circle class="mt-0.5 size-5 text-indigo-600 dark:text-indigo-400" />
                    <div>
                        <h3 class="font-semibold text-indigo-900 dark:text-indigo-100 text-sm">Panduan Dosen</h3>
                        <ul class="mt-3 text-xs text-indigo-800 dark:text-indigo-200 space-y-2 list-disc list-inside">
                            <li>Cek tab <strong>Menunggu</strong> untuk pengajuan baru.</li>
                            <li>Periksa dokumen laporan mahasiswa.</li>
                            <li>Klik <strong>Setujui</strong> jika layak seminar.</li>
                            <li>Bapendik akan menjadwalkan setelah Anda setujui.</li>
                        </ul>
                    </div>
                </div>
            </flux:card>
        </div>
    </div>

    {{-- MODAL DETAIL --}}
    <flux:modal name="detail-seminar" :show="$detailId !== null" class="md:w-[32rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Detail Pengajuan Seminar</flux:heading>
                <p class="text-sm text-zinc-500">Informasi lengkap pengajuan.</p>
            </div>

            @if ($selectedItem = $this->selectedItem)
                <div class="space-y-4">
                    <div class="p-3 rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <div class="text-xs text-zinc-500">Mahasiswa</div>
                        <div class="font-medium">{{ $selectedItem->kp->mahasiswa->user->name ?? '-' }}</div>
                        <div class="text-xs">{{ $selectedItem->kp->mahasiswa->mahasiswa_nim ?? '-' }}</div>
                    </div>

                    <div class="p-3 rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <div class="text-xs text-zinc-500">Judul Laporan</div>
                        <div class="font-medium text-sm">{{ $selectedItem->judul_laporan }}</div>
                    </div>

                    <div class="p-3 rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <div class="text-xs text-zinc-500">Rencana Jadwal</div>
                        <div class="font-medium">
                            {{ optional($selectedItem->tanggal_seminar)->format('d M Y') ?? 'Belum ditentukan' }}
                            @if ($selectedItem->jam_mulai)
                                ({{ $selectedItem->jam_mulai }} - {{ $selectedItem->jam_selesai }})
                            @endif
                        </div>
                        <div class="text-xs mt-1">Usulan: {{ $selectedItem->ruangan_nama ?? 'Belum ada' }}</div>
                    </div>

                    <div class="p-3 rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <div class="text-xs text-zinc-500 mb-1">Dokumen Persyaratan</div>
                        @if ($selectedItem->berkas_laporan_path)
                            <a href="{{ asset('storage/' . $selectedItem->berkas_laporan_path) }}" target="_blank"
                                class="flex items-center gap-2 text-sm text-indigo-600 hover:underline">
                                <flux:icon.document-text class="size-4" /> Lihat / Unduh Berkas
                            </a>
                        @else
                            <span class="text-sm text-zinc-400 italic">Tidak ada berkas.</span>
                        @endif
                    </div>
                </div>
            @endif

            <div class="flex justify-end">
                <flux:button variant="ghost" wire:click="closeDetail">Tutup</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- MODAL TOLAK --}}
    <flux:modal name="reject-seminar" :show="$rejectId !== null" class="md:w-96">
        <div class="space-y-4">
            <flux:heading size="lg">Tolak Pengajuan?</flux:heading>
            <p class="text-sm text-zinc-500">Berikan alasan penolakan agar mahasiswa dapat memperbaiki.</p>

            <flux:textarea label="Alasan Penolakan" wire:model.defer="rejectReason" />
            @error('rejectReason')
                <div class="text-xs text-rose-600">{{ $message }}</div>
            @enderror

            <div class="flex justify-end gap-2 pt-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" wire:click="confirmReject">Tolak Pengajuan</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
