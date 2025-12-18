<div class="space-y-6">
    <flux:toast />

    {{-- HEADER --}}
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <flux:heading size="xl" level="1" class="text-stone-900 dark:text-stone-100">
                Validasi Surat Pengantar
            </flux:heading>
            <flux:subheading class="text-zinc-600 dark:text-zinc-300">
                Kelola pengajuan masuk dan riwayat yang sudah diterbitkan.
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
                        Belum Diterbitkan
                        <flux:badge size="sm" inset="top bottom" class="ml-2" color="sky">
                            {{ $this->stats['pending'] }}</flux:badge>
                    </flux:tab>

                    <flux:tab name="published" icon="check-badge">
                        Sudah Diterbitkan
                        <flux:badge size="sm" inset="top bottom" class="ml-2" color="emerald">
                            {{ $this->stats['published'] }}</flux:badge>
                    </flux:tab>

                    <flux:tab name="rejected" icon="x-circle">
                        Ditolak
                        <flux:badge size="sm" inset="top bottom" class="ml-2" color="rose">
                            {{ $this->stats['rejected'] }}</flux:badge>
                    </flux:tab>
                </flux:tabs>

                {{-- PANEL PENDING --}}
                <flux:tab.panel name="pending" class="pt-4">
                    <flux:card
                        class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm overflow-hidden">
                        {{-- Header Tabel --}}
                        <div
                            class="flex flex-col gap-4 px-6 py-4 border-b border-zinc-200 dark:border-stone-800 bg-sky-50/50 dark:bg-sky-900/10 md:flex-row md:items-center md:justify-between">
                            <h4 class="text-base font-semibold text-stone-900 dark:text-stone-100">Antrean Validasi</h4>
                            <div class="flex items-center gap-3">
                                <flux:input icon="magnifying-glass" placeholder="Cari..."
                                    wire:model.live.debounce.300ms="search"
                                    class="w-full md:w-64 bg-white dark:bg-stone-900" />
                            </div>
                        </div>

                        <flux:table :paginate="$this->ordersPending">
                            <flux:table.columns>
                                <flux:table.column class="w-12 text-center">No</flux:table.column>
                                <flux:table.column sortable wire:click="sort('tanggal_pengajuan_surat_pengantar')"
                                    :sorted="$sortBy === 'tanggal_pengajuan_surat_pengantar'"
                                    :direction="$sortDirection">Tgl Masuk</flux:table.column>
                                <flux:table.column>Mahasiswa</flux:table.column>
                                <flux:table.column>Tujuan Surat</flux:table.column>
                                <flux:table.column>Status</flux:table.column>
                                <flux:table.column class="text-right">Aksi</flux:table.column>
                            </flux:table.columns>

                            <flux:table.rows>
                                @foreach ($this->ordersPending as $i => $row)
                                    <flux:table.row :key="'p-' . $row->id">
                                        <flux:table.cell class="text-center text-zinc-500">
                                            {{ $this->ordersPending->firstItem() + $i }}</flux:table.cell>
                                        <flux:table.cell class="whitespace-nowrap">
                                            {{ optional($row->tanggal_pengajuan_surat_pengantar)->format('d M Y') }}
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            <div class="font-medium text-stone-900 dark:text-stone-100">
                                                {{ $row->mahasiswa?->user?->name }}</div>
                                            <div class="text-xs text-zinc-500">{{ $row->mahasiswa?->mahasiswa_nim }}
                                            </div>
                                        </flux:table.cell>
                                        <flux:table.cell class="max-w-[200px]">
                                            <div class="truncate font-medium text-sm">{{ $row->lokasi_surat_pengantar }}
                                            </div>
                                            <div class="text-xs text-zinc-500 truncate">
                                                {{ $row->penerima_surat_pengantar }}</div>
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            <flux:badge size="sm"
                                                :color="$this->badgeColor($row->status_surat_pengantar)"
                                                :icon="$this->badgeIcon($row->status_surat_pengantar)">
                                                {{ $row->status_surat_pengantar }}
                                            </flux:badge>
                                        </flux:table.cell>
                                        <flux:table.cell class="text-right">
                                            <flux:dropdown position="bottom" align="end">
                                                <flux:button variant="ghost" size="sm"
                                                    icon="ellipsis-horizontal" />
                                                <flux:menu>
                                                    <flux:menu.item icon="eye"
                                                        wire:click="openDetail({{ $row->id }})">Detail
                                                    </flux:menu.item>
                                                    <flux:menu.item icon="check"
                                                        wire:click="openPublish({{ $row->id }})">Terbitkan
                                                    </flux:menu.item>
                                                    <flux:menu.separator />
                                                    <flux:menu.item icon="x-mark" variant="danger"
                                                        wire:click="openReject({{ $row->id }})">Tolak
                                                    </flux:menu.item>
                                                </flux:menu>
                                            </flux:dropdown>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>

                        @if ($this->ordersPending->isEmpty())
                            <div class="flex flex-col items-center justify-center py-12 text-center">
                                <div class="rounded-full bg-zinc-100 p-4 dark:bg-stone-900">
                                    <flux:icon.inbox class="size-8 text-zinc-400" />
                                </div>
                                <h3 class="mt-4 text-base font-semibold text-stone-900 dark:text-stone-100">Tidak ada
                                    antrean</h3>
                                <p class="mt-1 text-sm text-zinc-500">Belum ada pengajuan baru yang masuk.</p>
                            </div>
                        @endif
                    </flux:card>
                </flux:tab.panel>

                {{-- PANEL PUBLISHED --}}
                <flux:tab.panel name="published" class="pt-4">
                    <flux:card
                        class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm overflow-hidden">
                        <div
                            class="flex flex-col gap-4 px-6 py-4 border-b border-zinc-200 dark:border-stone-800 bg-emerald-50/50 dark:bg-emerald-900/10 md:flex-row md:items-center md:justify-between">
                            <h4 class="text-base font-semibold text-stone-900 dark:text-stone-100">Riwayat Terbit</h4>
                            <div class="flex items-center gap-3">
                                <flux:input icon="magnifying-glass" placeholder="Cari..."
                                    wire:model.live.debounce.300ms="search"
                                    class="w-full md:w-64 bg-white dark:bg-stone-900" />
                            </div>
                        </div>

                        <flux:table :paginate="$this->ordersPublished">
                            <flux:table.columns>
                                <flux:table.column class="w-12 text-center">No</flux:table.column>
                                <flux:table.column sortable wire:click="sort('tanggal_disetujui_surat_pengantar')"
                                    :sorted="$sortBy === 'tanggal_disetujui_surat_pengantar'"
                                    :direction="$sortDirection">Tgl Terbit</flux:table.column>
                                <flux:table.column>Nomor Surat</flux:table.column>
                                <flux:table.column>Mahasiswa</flux:table.column>
                                <flux:table.column>Status</flux:table.column>
                                <flux:table.column class="text-right">Aksi</flux:table.column>
                            </flux:table.columns>

                            <flux:table.rows>
                                @foreach ($this->ordersPublished as $i => $row)
                                    <flux:table.row :key="'pb-' . $row->id">
                                        <flux:table.cell class="text-center text-zinc-500">
                                            {{ $this->ordersPublished->firstItem() + $i }}</flux:table.cell>
                                        <flux:table.cell class="whitespace-nowrap">
                                            {{ optional($row->tanggal_disetujui_surat_pengantar)->format('d M Y') }}
                                        </flux:table.cell>
                                        <flux:table.cell><span class="font-mono text-xs">{{ $row->nomor_surat }}</span>
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            <div class="font-medium text-stone-900 dark:text-stone-100">
                                                {{ $row->mahasiswa?->user?->name }}</div>
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            <flux:badge size="sm"
                                                :color="$this->badgeColor($row->status_surat_pengantar)"
                                                :icon="$this->badgeIcon($row->status_surat_pengantar)">
                                                {{ $row->status_surat_pengantar }}</flux:badge>
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
                                                        wire:click="openPublish({{ $row->id }})">Ubah Nomor
                                                    </flux:menu.item>
                                                    <flux:menu.separator />
                                                    <flux:menu.item icon="arrow-down-tray"
                                                        href="{{ route('bap.sp.download.docx', $row->id) }}"
                                                        target="_blank">Unduh DOCX</flux:menu.item>
                                                </flux:menu>
                                            </flux:dropdown>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>

                        @if ($this->ordersPublished->isEmpty())
                            <div class="flex flex-col items-center justify-center py-12 text-center">
                                <div class="rounded-full bg-zinc-100 p-4 dark:bg-stone-900">
                                    <flux:icon.document-text class="size-8 text-zinc-400" />
                                </div>
                                <h3 class="mt-4 text-base font-semibold text-stone-900 dark:text-stone-100">Belum ada
                                    data</h3>
                                <p class="mt-1 text-sm text-zinc-500">
                                    @if ($search)
                                        Tidak ditemukan data.
                                    @else
                                        Belum ada surat yang diterbitkan.
                                    @endif
                                </p>
                            </div>
                        @endif
                    </flux:card>
                </flux:tab.panel>

                {{-- PANEL REJECTED --}}
                <flux:tab.panel name="rejected" class="pt-4">
                    <flux:card
                        class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm overflow-hidden">
                        <div
                            class="flex flex-col gap-4 px-6 py-4 border-b border-zinc-200 dark:border-stone-800 bg-rose-50/50 dark:bg-rose-900/10 md:flex-row md:items-center md:justify-between">
                            <h4 class="text-base font-semibold text-stone-900 dark:text-stone-100">Riwayat Ditolak</h4>
                            <div class="flex items-center gap-3">
                                <flux:input icon="magnifying-glass" placeholder="Cari..."
                                    wire:model.live.debounce.300ms="search"
                                    class="w-full md:w-64 bg-white dark:bg-stone-900" />
                            </div>
                        </div>

                        <flux:table :paginate="$this->ordersRejected">
                            <flux:table.columns>
                                <flux:table.column class="w-12 text-center">No</flux:table.column>
                                <flux:table.column sortable wire:click="sort('tanggal_pengajuan_surat_pengantar')"
                                    :sorted="$sortBy === 'tanggal_pengajuan_surat_pengantar'"
                                    :direction="$sortDirection">Tgl Masuk</flux:table.column>
                                <flux:table.column>Mahasiswa</flux:table.column>
                                <flux:table.column>Alasan Penolakan</flux:table.column>
                                <flux:table.column>Status</flux:table.column>
                                <flux:table.column class="text-right">Aksi</flux:table.column>
                            </flux:table.columns>

                            <flux:table.rows>
                                @foreach ($this->ordersRejected as $i => $row)
                                    <flux:table.row :key="'rj-' . $row->id">
                                        <flux:table.cell class="text-center text-zinc-500">
                                            {{ $this->ordersRejected->firstItem() + $i }}</flux:table.cell>
                                        <flux:table.cell class="whitespace-nowrap">
                                            {{ optional($row->tanggal_pengajuan_surat_pengantar)->format('d M Y') }}
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            <div class="font-medium text-stone-900 dark:text-stone-100">
                                                {{ $row->mahasiswa?->user?->name }}</div>
                                        </flux:table.cell>
                                        <flux:table.cell class="max-w-[250px]">
                                            <span class="truncate text-rose-600 dark:text-rose-400 text-sm"
                                                title="{{ $row->catatan_surat }}">{{ $row->catatan_surat ?? '-' }}</span>
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            <flux:badge size="sm"
                                                :color="$this->badgeColor($row->status_surat_pengantar)"
                                                :icon="$this->badgeIcon($row->status_surat_pengantar)">
                                                {{ $row->status_surat_pengantar }}</flux:badge>
                                        </flux:table.cell>
                                        <flux:table.cell class="text-right">
                                            <flux:dropdown position="bottom" align="end">
                                                <flux:button variant="ghost" size="sm"
                                                    icon="ellipsis-horizontal" />
                                                <flux:menu>
                                                    <flux:menu.item icon="eye"
                                                        wire:click="openDetail({{ $row->id }})">Detail
                                                    </flux:menu.item>
                                                    <flux:menu.item icon="arrow-path"
                                                        wire:click="openPublish({{ $row->id }})">Proses Ulang
                                                        (Terbitkan)</flux:menu.item>
                                                </flux:menu>
                                            </flux:dropdown>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>

                        @if ($this->ordersRejected->isEmpty())
                            <div class="flex flex-col items-center justify-center py-12 text-center">
                                <div class="rounded-full bg-zinc-100 p-4 dark:bg-stone-900">
                                    <flux:icon.x-circle class="size-8 text-zinc-400" />
                                </div>
                                <h3 class="mt-4 text-base font-semibold text-stone-900 dark:text-stone-100">Belum ada
                                    data ditolak</h3>
                            </div>
                        @endif
                    </flux:card>
                </flux:tab.panel>
            </flux:tab.group>
        </div>

        {{-- KOLOM KANAN: SIDEBAR (1/4) --}}
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
                        class="flex items-center justify-between p-2 rounded-lg bg-sky-50/50 dark:bg-sky-900/10 border border-sky-100 dark:border-sky-800/30">
                        <div class="flex items-center gap-2">
                            <div class="size-2 rounded-full bg-sky-500"></div>
                            <span class="text-sm font-medium text-stone-700 dark:text-stone-300">Pending</span>
                        </div>
                        <span
                            class="text-lg font-bold text-sky-600 dark:text-sky-400">{{ $this->stats['pending'] }}</span>
                    </div>
                    <div
                        class="flex items-center justify-between p-2 rounded-lg bg-emerald-50/50 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-800/30">
                        <div class="flex items-center gap-2">
                            <div class="size-2 rounded-full bg-emerald-500"></div>
                            <span class="text-sm font-medium text-stone-700 dark:text-stone-300">Terbit</span>
                        </div>
                        <span
                            class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ $this->stats['published'] }}</span>
                    </div>
                    <div
                        class="flex items-center justify-between p-2 rounded-lg bg-rose-50/50 dark:bg-rose-900/10 border border-rose-100 dark:border-rose-800/30">
                        <div class="flex items-center gap-2">
                            <div class="size-2 rounded-full bg-rose-500"></div>
                            <span class="text-sm font-medium text-stone-700 dark:text-stone-300">Ditolak</span>
                        </div>
                        <span
                            class="text-lg font-bold text-rose-600 dark:text-rose-400">{{ $this->stats['rejected'] }}</span>
                    </div>
                </div>
            </flux:card>

            {{-- 2. PANDUAN (UPDATED) --}}
            <flux:card
                class="rounded-xl border bg-indigo-50/50 dark:bg-indigo-900/10 border-indigo-100 dark:border-indigo-800/30 shadow-sm">
                <div class="flex items-start gap-3">
                    <flux:icon.information-circle class="mt-0.5 size-5 text-indigo-600 dark:text-indigo-400" />
                    <div>
                        <h3 class="font-semibold text-indigo-900 dark:text-indigo-100 text-sm">Alur Validasi</h3>
                        <ul class="mt-3 text-xs text-indigo-800 dark:text-indigo-200 space-y-2 list-disc list-inside">
                            <li>Cek data di tab <strong>Belum Diterbitkan</strong>.</li>
                            <li>Pastikan tujuan dan alamat surat valid.</li>
                            <li>Klik <strong>Terbitkan</strong>, pilih penandatangan, dan (opsional) isi nomor surat.
                            </li>
                            <li>Jika data tidak sesuai, klik <strong>Tolak</strong>. Data akan pindah ke tab
                                <strong>Ditolak</strong>.</li>
                        </ul>
                    </div>
                </div>
            </flux:card>
        </div>
    </div>

    {{-- MODAL DETAIL --}}
    <flux:modal name="sp-detail" :show="$detailId !== null" class="md:w-[32rem]">
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <flux:heading size="lg">Detail Pengajuan SP</flux:heading>
                    <p class="text-sm text-zinc-500">Informasi lengkap surat pengantar.</p>
                </div>
            </div>

            @if ($selectedItem = $this->selectedItem)
                <div class="space-y-4">
                    {{-- 1. Data Mahasiswa --}}
                    <div
                        class="p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/50">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="text-xs text-zinc-500 uppercase font-bold tracking-wider">Mahasiswa</div>
                                <div class="font-medium mt-1">{{ $selectedItem->mahasiswa->user->name }}</div>
                                <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ $selectedItem->mahasiswa->mahasiswa_nim }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-zinc-500 uppercase font-bold tracking-wider">Jurusan</div>
                                <div class="text-sm mt-1">{{ $selectedItem->mahasiswa->jurusan->nama_jurusan ?? '-' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Detail Surat --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-xs text-zinc-500">Tujuan (Perusahaan)</div>
                            <div class="font-medium text-sm">{{ $selectedItem->lokasi_surat_pengantar }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-zinc-500">Tanggal Pengajuan</div>
                            <div class="text-sm">
                                {{ optional($selectedItem->tanggal_pengajuan_surat_pengantar)->format('d F Y') }}</div>
                        </div>
                        <div class="col-span-2">
                            <div class="text-xs text-zinc-500">Penerima</div>
                            <div class="text-sm">{{ $selectedItem->penerima_surat_pengantar }}</div>
                        </div>
                        <div class="col-span-2">
                            <div class="text-xs text-zinc-500">Alamat Lengkap</div>
                            <div class="text-sm">{{ $selectedItem->alamat_surat_pengantar }}</div>
                        </div>
                        @if ($selectedItem->tembusan_surat_pengantar)
                            <div class="col-span-2">
                                <div class="text-xs text-zinc-500">Tembusan</div>
                                <div class="text-sm">{{ $selectedItem->tembusan_surat_pengantar }}</div>
                            </div>
                        @endif
                    </div>

                    {{-- 3. Status & Legalitas --}}
                    <flux:separator variant="subtle" />

                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <div class="text-sm text-zinc-500">Status</div>
                            <flux:badge size="sm"
                                :color="$this->badgeColor($selectedItem->status_surat_pengantar)">
                                {{ $selectedItem->status_surat_pengantar }}
                            </flux:badge>
                        </div>

                        @if ($selectedItem->status_surat_pengantar === 'Diterbitkan')
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div>
                                    <span class="text-zinc-500">Nomor Surat:</span><br>
                                    <span class="font-mono">{{ $selectedItem->nomor_surat ?? '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-zinc-500">Tgl Disetujui:</span><br>
                                    <span>{{ optional($selectedItem->tanggal_disetujui_surat_pengantar)->format('d/m/Y') }}</span>
                                </div>
                                <div class="col-span-2 mt-1">
                                    <span class="text-zinc-500">Penandatangan:</span><br>
                                    <span>{{ $selectedItem->ttd_signed_by_name ?? ($selectedItem->signatory->name ?? '-') }}</span>
                                </div>
                            </div>
                        @endif

                        @if ($selectedItem->status_surat_pengantar === 'Ditolak')
                            <div
                                class="p-3 rounded-lg border border-rose-200 bg-rose-50 dark:bg-rose-900/20 dark:border-rose-800">
                                <div class="text-xs text-rose-600 font-bold uppercase mb-1">Alasan Penolakan</div>
                                <div class="text-sm text-rose-700 dark:text-rose-300">
                                    {{ $selectedItem->catatan_surat }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="flex justify-end">
                <flux:button variant="ghost" wire:click="closeDetail">Tutup</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- MODAL PUBLISH --}}
    <flux:modal name="sp-publish" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Terbitkan Surat</flux:heading>
                <p class="text-sm text-zinc-500">Pilih pejabat penandatangan.</p>
            </div>

            <div class="space-y-4">
                {{-- Nomor Surat Optional --}}
                <flux:input label="Nomor Surat (Opsional)" placeholder="Kosongkan jika auto-generate"
                    wire:model.defer="publish_nomor_surat" />

                {{-- Penandatangan Wajib --}}
                <flux:select label="Penandatangan" wire:model="signatory_id" placeholder="Pilih Pejabat...">
                    @foreach (\App\Models\Signatory::orderBy('position')->get() as $sig)
                        <flux:select.option :value="$sig->id">{{ $sig->name }} ({{ $sig->position }})
                        </flux:select.option>
                    @endforeach
                </flux:select>
                @error('signatory_id')
                    <div class="text-xs text-red-500">{{ $message }}</div>
                @enderror
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" wire:click="publishConfirm">Simpan & Terbitkan</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- MODAL REJECT --}}
    <flux:modal name="sp-reject" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Tolak Pengajuan?</flux:heading>
                <p class="text-sm text-zinc-500">Berikan alasan penolakan (wajib).</p>
            </div>

            <div>
                <flux:textarea label="Alasan Penolakan" placeholder="Misal: Alamat kurang lengkap..."
                    wire:model.defer="catatan_tolak" />
                @error('catatan_tolak')
                    <div class="text-xs text-red-500 mt-1">{{ $message }}</div>
                @enderror
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" wire:click="submitReject">Tolak Pengajuan</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
