<div class="space-y-6">
    <flux:toast />

    {{-- HEADER --}}
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <flux:heading size="xl" level="1" class="text-stone-900 dark:text-stone-100">
                Jadwal & BA Seminar
            </flux:heading>
            <flux:subheading class="text-zinc-600 dark:text-zinc-300">
                Atur jadwal seminar KP dan terbitkan Berita Acara.
            </flux:subheading>
        </div>
    </div>

    <flux:separator variant="subtle" />

    {{-- GRID UTAMA --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">

        {{-- KOLOM KIRI: TABEL (3/4) --}}
        <div class="lg:col-span-3 space-y-6">

            <flux:tab.group wire:model.live="tab">
                <flux:tabs>
                    <flux:tab name="pending" icon="inbox-arrow-down">
                        Perlu Proses
                        <flux:badge size="sm" inset="top bottom" class="ml-2" color="amber">
                            {{ $this->stats['pending'] }}</flux:badge>
                    </flux:tab>
                    <flux:tab name="completed" icon="check-badge">
                        Riwayat BA
                        <flux:badge size="sm" inset="top bottom" class="ml-2" color="violet">
                            {{ $this->stats['completed'] }}</flux:badge>
                    </flux:tab>
                </flux:tabs>

                {{-- PANEL PENDING --}}
                <flux:tab.panel name="pending" class="pt-4">
                    <flux:card
                        class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm overflow-hidden">
                        {{-- Header Tabel --}}
                        <div
                            class="flex flex-col gap-4 px-6 py-4 border-b border-zinc-200 dark:border-stone-800 bg-amber-50/50 dark:bg-amber-900/10 md:flex-row md:items-center md:justify-between">
                            <h4 class="text-base font-semibold text-stone-900 dark:text-stone-100">Antrean Penjadwalan
                            </h4>
                            <div class="flex items-center gap-3">
                                <flux:input icon="magnifying-glass" placeholder="Cari..."
                                    wire:model.live.debounce.300ms="search"
                                    class="w-full md:w-64 bg-white dark:bg-stone-900" />
                            </div>
                        </div>

                        <flux:table :paginate="$this->itemsPending">
                            <flux:table.columns>
                                <flux:table.column class="w-12 text-center">No</flux:table.column>
                                <flux:table.column sortable wire:click="sort('created_at')"
                                    :sorted="$sortBy === 'created_at'" :direction="$sortDirection">Tgl Masuk
                                </flux:table.column>
                                <flux:table.column>Mahasiswa</flux:table.column>
                                <flux:table.column>Judul Laporan</flux:table.column>
                                <flux:table.column>Status</flux:table.column>
                                <flux:table.column class="text-right">Aksi</flux:table.column>
                            </flux:table.columns>

                            <flux:table.rows>
                                @foreach ($this->itemsPending as $i => $row)
                                    <flux:table.row :key="'p-'.$row->id">
                                        <flux:table.cell class="text-center text-zinc-500">
                                            {{ $this->itemsPending->firstItem() + $i }}</flux:table.cell>
                                        <flux:table.cell class="whitespace-nowrap">
                                            {{ optional($row->created_at)->format('d M Y') }}</flux:table.cell>
                                        <flux:table.cell>
                                            <div class="font-medium text-stone-900 dark:text-stone-100">
                                                {{ $row->kp?->mahasiswa?->user?->name ?? '—' }}</div>
                                            <div class="text-xs text-zinc-500">
                                                {{ $row->kp?->mahasiswa?->mahasiswa_nim ?? '—' }}</div>
                                        </flux:table.cell>
                                        <flux:table.cell class="max-w-[200px]">
                                            <span class="line-clamp-2"
                                                title="{{ $row->judul_laporan }}">{{ $row->judul_laporan ?? '—' }}</span>
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            <flux:badge size="sm" inset="top bottom"
                                                :color="$this->badgeColor($row->status)">
                                                {{ $this->statusLabel($row->status) }}</flux:badge>
                                        </flux:table.cell>
                                        <flux:table.cell class="text-right">
                                            <flux:dropdown position="bottom" align="end">
                                                <flux:button variant="ghost" size="sm"
                                                    icon="ellipsis-horizontal" />
                                                <flux:menu>
                                                    <flux:menu.item icon="eye"
                                                        wire:click="openDetail({{ $row->id }})">Detail
                                                    </flux:menu.item>
                                                    <flux:menu.item icon="pencil-square"
                                                        wire:click="openEdit({{ $row->id }})">Proses (Jadwal & BA)
                                                    </flux:menu.item>
                                                    <flux:menu.separator />
                                                    <flux:menu.item icon="arrow-uturn-left" variant="danger"
                                                        wire:click="openReject({{ $row->id }})">Kembalikan
                                                        (Revisi)
                                                    </flux:menu.item>
                                                </flux:menu>
                                            </flux:dropdown>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>

                        @if ($this->itemsPending->isEmpty())
                            <div class="flex flex-col items-center justify-center py-12 text-center">
                                <div class="rounded-full bg-zinc-100 p-4 dark:bg-stone-900">
                                    <flux:icon.inbox class="size-8 text-zinc-400" />
                                </div>
                                <h3 class="mt-4 text-base font-semibold text-stone-900 dark:text-stone-100">Tidak ada
                                    antrean</h3>
                                <p class="mt-1 text-sm text-zinc-500">Belum ada pengajuan seminar yang menunggu jadwal.
                                </p>
                            </div>
                        @endif
                    </flux:card>
                </flux:tab.panel>

                {{-- PANEL COMPLETED --}}
                <flux:tab.panel name="completed" class="pt-4">
                    <flux:card
                        class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm overflow-hidden">
                        <div
                            class="flex flex-col gap-4 px-6 py-4 border-b border-zinc-200 dark:border-stone-800 bg-violet-50/50 dark:bg-violet-900/10 md:flex-row md:items-center md:justify-between">
                            <h4 class="text-base font-semibold text-stone-900 dark:text-stone-100">Riwayat Berita Acara
                            </h4>
                            <div class="flex items-center gap-3">
                                <flux:input icon="magnifying-glass" placeholder="Cari..."
                                    wire:model.live.debounce.300ms="search"
                                    class="w-full md:w-64 bg-white dark:bg-stone-900" />
                            </div>
                        </div>

                        <flux:table :paginate="$this->itemsCompleted">
                            <flux:table.columns>
                                <flux:table.column class="w-12 text-center">No</flux:table.column>
                                <flux:table.column sortable wire:click="sort('tanggal_ba')"
                                    :sorted="$sortBy === 'tanggal_ba'" :direction="$sortDirection">Tgl BA
                                </flux:table.column>
                                <flux:table.column>Mahasiswa</flux:table.column>
                                <flux:table.column>Nomor BA</flux:table.column>
                                <flux:table.column>Status</flux:table.column>
                                <flux:table.column class="text-right">Aksi</flux:table.column>
                            </flux:table.columns>

                            <flux:table.rows>
                                @foreach ($this->itemsCompleted as $i => $row)
                                    <flux:table.row :key="'c-'.$row->id">
                                        <flux:table.cell class="text-center text-zinc-500">
                                            {{ $this->itemsCompleted->firstItem() + $i }}</flux:table.cell>
                                        <flux:table.cell class="whitespace-nowrap">
                                            {{ optional($row->tanggal_ba)->format('d M Y') }}</flux:table.cell>
                                        <flux:table.cell>
                                            <div class="font-medium text-stone-900 dark:text-stone-100">
                                                {{ $row->kp?->mahasiswa?->user?->name ?? '—' }}</div>
                                        </flux:table.cell>
                                        <flux:table.cell class="font-mono text-xs">{{ $row->nomor_ba ?? '-' }}
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            <flux:badge size="sm" inset="top bottom"
                                                :color="$this->badgeColor($row->status)">
                                                {{ $this->statusLabel($row->status) }}</flux:badge>
                                        </flux:table.cell>
                                        <flux:table.cell class="text-right">
                                            <flux:dropdown position="bottom" align="end">
                                                <flux:button variant="ghost" size="sm"
                                                    icon="ellipsis-horizontal" />
                                                <flux:menu>
                                                    <flux:menu.item icon="eye"
                                                        wire:click="openDetail({{ $row->id }})">Detail
                                                    </flux:menu.item>
                                                    <flux:menu.item icon="pencil-square"
                                                        wire:click="openEdit({{ $row->id }})">Edit BA
                                                    </flux:menu.item>
                                                </flux:menu>
                                            </flux:dropdown>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>

                        @if ($this->itemsCompleted->isEmpty())
                            <div class="flex flex-col items-center justify-center py-12 text-center">
                                <div class="rounded-full bg-zinc-100 p-4 dark:bg-stone-900">
                                    <flux:icon.document-text class="size-8 text-zinc-400" />
                                </div>
                                <h3 class="mt-4 text-base font-semibold text-stone-900 dark:text-stone-100">Belum ada
                                    data</h3>
                                <p class="mt-1 text-sm text-zinc-500">Belum ada Berita Acara yang diterbitkan.</p>
                            </div>
                        @endif
                    </flux:card>
                </flux:tab.panel>
            </flux:tab.group>
        </div>

        {{-- KOLOM KANAN: SIDEBAR (1/4) --}}
        <div class="lg:col-span-1 space-y-6">

            {{-- 1. RINGKASAN --}}
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
                            <div class="size-2 rounded-full bg-amber-500"></div>
                            <span class="text-sm font-medium text-stone-700 dark:text-stone-300">Perlu Proses</span>
                        </div>
                        <span
                            class="text-lg font-bold text-amber-600 dark:text-amber-400">{{ $this->stats['pending'] }}</span>
                    </div>
                    <div
                        class="flex items-center justify-between p-2 rounded-lg bg-violet-50/50 dark:bg-violet-900/10 border border-violet-100 dark:border-violet-800/30">
                        <div class="flex items-center gap-2">
                            <div class="size-2 rounded-full bg-violet-500"></div>
                            <span class="text-sm font-medium text-stone-700 dark:text-stone-300">Riwayat BA</span>
                        </div>
                        <span
                            class="text-lg font-bold text-violet-600 dark:text-violet-400">{{ $this->stats['completed'] }}</span>
                    </div>
                </div>
            </flux:card>

            {{-- PANDUAN --}}
            <flux:card
                class="rounded-xl border bg-indigo-50/50 dark:bg-indigo-900/10 border-indigo-100 dark:border-indigo-800/30 shadow-sm">
                <div class="flex items-start gap-3">
                    <flux:icon.information-circle class="mt-0.5 size-5 text-indigo-600 dark:text-indigo-400" />
                    <div>
                        <h3 class="font-semibold text-indigo-900 dark:text-indigo-100 text-sm">Alur Proses</h3>
                        <ul class="mt-3 text-xs text-indigo-800 dark:text-indigo-200 space-y-2 list-disc list-inside">
                            <li>Cek tab <strong>Perlu Proses</strong>.</li>
                            <li>Klik <strong>Proses</strong> untuk mengatur jadwal dan menerbitkan BA sekaligus.</li>
                            <li>Jika jadwal tidak sesuai/bentrok, gunakan opsi <strong>Kembalikan</strong> agar
                                mahasiswa revisi.</li>
                            <li>Data yang sudah terbit akan pindah ke tab <strong>Riwayat BA</strong>.</li>
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
                <flux:heading size="lg">Detail Seminar</flux:heading>
                <p class="text-sm text-zinc-500">Informasi pengajuan dan status.</p>
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

                    @if ($selectedItem->tanggal_seminar)
                        <div class="grid grid-cols-2 gap-4">
                            <div class="p-3 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                <div class="text-xs text-zinc-500">Jadwal</div>
                                <div class="font-medium">{{ $selectedItem->tanggal_seminar->format('d M Y') }}</div>
                                <div class="text-xs">{{ $selectedItem->jam_mulai }} -
                                    {{ $selectedItem->jam_selesai }}</div>
                            </div>
                            <div class="p-3 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                <div class="text-xs text-zinc-500">Ruangan</div>
                                <div class="font-medium">{{ $selectedItem->ruangan_nama ?? '-' }}</div>
                            </div>
                        </div>
                    @endif

                    <div class="p-3 rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <div class="text-xs text-zinc-500 mb-1">Berkas Persyaratan</div>
                        @if ($selectedItem->berkas_laporan_path)
                            <a href="{{ asset('storage/' . $selectedItem->berkas_laporan_path) }}" target="_blank"
                                class="flex items-center gap-2 text-sm text-indigo-600 hover:underline">
                                <flux:icon.document-text class="size-4" /> Lihat Dokumen
                            </a>
                        @else
                            <span class="text-sm text-zinc-400 italic">Tidak ada berkas.</span>
                        @endif
                    </div>

                    <flux:separator variant="subtle" />

                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-zinc-500">Status</span>
                            <flux:badge size="sm" :color="$this->badgeColor($selectedItem->status)">
                                {{ $this->statusLabel($selectedItem->status) }}</flux:badge>
                        </div>
                    </div>
                </div>
            @endif

            <div class="flex justify-end">
                <flux:button variant="ghost" wire:click="closeDetail">Tutup</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- MODAL UNIFIED: Jadwal & BA --}}
    <flux:modal name="process-seminar" class="min-w-[34rem]" :show="$editId !== null">
        {{-- ... (Isi sama dengan sebelumnya, tidak perlu diubah) ... --}}
        <div class="space-y-6">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <flux:heading size="lg">Jadwalkan & Terbitkan BA</flux:heading>
                    <p class="text-sm text-zinc-500">Atur jadwal dan detail Berita Acara dalam satu langkah.</p>
                </div>
            </div>

            <div class="grid gap-4">
                {{-- Section Jadwal --}}
                <div
                    class="p-3 bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-zinc-100 dark:border-zinc-800 space-y-4">
                    <div class="text-xs font-bold text-zinc-500 uppercase tracking-wider">Pengaturan Jadwal</div>

                    <flux:input type="date" label="Tanggal Seminar" wire:model="tanggal_seminar" />

                    <div class="grid grid-cols-2 gap-4">
                        <flux:input type="time" label="Jam Mulai" wire:model="jam_mulai" />
                        <flux:input type="time" label="Jam Selesai" wire:model="jam_selesai" />
                    </div>

                    <flux:select label="Ruangan" wire:model="ruangan_id" placeholder="Pilih Ruangan...">
                        @foreach ($this->rooms as $r)
                            <flux:select.option :value="$r->id">{{ $r->room_number }} ({{ $r->building }})
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                {{-- Section BA --}}
                <div
                    class="p-3 bg-violet-50 dark:bg-violet-900/10 rounded-lg border border-violet-100 dark:border-violet-800 space-y-4">
                    <div class="text-xs font-bold text-violet-600 dark:text-violet-400 uppercase tracking-wider">Detail
                        Berita Acara</div>

                    <div class="grid grid-cols-2 gap-4">
                        <flux:input label="Nomor BA (Opsional)" wire:model="nomor_ba"
                            placeholder="Auto / Kosongkan" />
                        <flux:input type="date" label="Tanggal BA" wire:model="tanggal_ba" />
                    </div>

                    <flux:select label="Penandatangan BA" wire:model="signatory_id" placeholder="Pilih Pejabat...">
                        @foreach ($this->signatories as $sig)
                            <flux:select.option :value="$sig->id">{{ $sig->name }} ({{ $sig->position }})
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>

                <flux:button variant="primary" icon="check-circle" wire:click="saveProcess">
                    Simpan & Terbitkan
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- MODAL REJECT --}}
    <flux:modal name="reject-seminar" class="md:w-96" :show="$rejectId !== null">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Kembalikan Pengajuan?</flux:heading>
                <p class="text-sm text-zinc-500">Berikan alasan mengapa pengajuan ini dikembalikan ke mahasiswa.</p>
            </div>

            <flux:textarea label="Alasan Pengembalian" wire:model.defer="rejectReason"
                placeholder="Contoh: Jadwal bentrok dengan seminar lain..." />

            {{-- @error('rejectReason')
                <div class="text-xs text-rose-600">{{ $message }}</div>
            @enderror --}}

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" wire:click="submitReject">Kembalikan</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
