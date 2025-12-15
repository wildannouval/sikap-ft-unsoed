<div class="space-y-6">
    <flux:toast />

    {{-- HEADER --}}
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <flux:heading size="xl" level="1" class="text-stone-900 dark:text-stone-100">
                Penerbitan SPK
            </flux:heading>
            <flux:subheading class="text-zinc-600 dark:text-zinc-300">
                Kelola penerbitan Surat Perintah Kerja (SPK) KP.
            </flux:subheading>
        </div>
    </div>

    <flux:separator variant="subtle" />

    {{-- GRID UTAMA 3:1 --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">

        {{-- KOLOM KIRI: TABEL (3) --}}
        <div class="lg:col-span-3 space-y-6">

            <flux:tab.group wire:model.live="tab">
                <flux:tabs>
                    <flux:tab name="pending" icon="inbox-arrow-down">
                        Belum Diterbitkan
                        <flux:badge size="sm" inset="top bottom" class="ml-2">{{ $this->stats['pending'] }}
                        </flux:badge>
                    </flux:tab>
                    <flux:tab name="published" icon="check-badge">
                        Sudah Diterbitkan
                        <flux:badge size="sm" inset="top bottom" class="ml-2">{{ $this->stats['published'] }}
                        </flux:badge>
                    </flux:tab>
                </flux:tabs>

                {{-- PANEL PENDING --}}
                <flux:tab.panel name="pending" class="pt-4">
                    <flux:card
                        class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm overflow-hidden">
                        {{-- Header Tabel --}}
                        <div
                            class="flex flex-col gap-4 px-6 py-4 border-b border-zinc-200 dark:border-stone-800 bg-amber-50/50 dark:bg-amber-900/10 md:flex-row md:items-center md:justify-between">
                            <h4 class="text-base font-semibold text-stone-900 dark:text-stone-100">Antrean Penerbitan
                            </h4>

                            <div class="flex items-center gap-3">
                                <flux:input icon="magnifying-glass" placeholder="Cari..."
                                    wire:model.live.debounce.300ms="search"
                                    class="w-full md:w-64 bg-white dark:bg-stone-900" />
                                <flux:select wire:model.live="perPage" class="w-20">
                                    <flux:select.option :value="10">10</flux:select.option>
                                    <flux:select.option :value="25">25</flux:select.option>
                                    <flux:select.option :value="50">50</flux:select.option>
                                </flux:select>
                            </div>
                        </div>

                        <flux:table :paginate="$this->itemsPending">
                            <flux:table.columns>
                                <flux:table.column class="w-12 text-center">No</flux:table.column>
                                <flux:table.column sortable wire:click="sort('updated_at')"
                                    :sorted="$sortBy === 'updated_at'" :direction="$sortDirection">Tgl Masuk
                                </flux:table.column>
                                <flux:table.column>Mahasiswa</flux:table.column>
                                <flux:table.column>Judul & Instansi</flux:table.column>
                                <flux:table.column>Status</flux:table.column>
                                <flux:table.column class="text-right">Aksi</flux:table.column>
                            </flux:table.columns>

                            <flux:table.rows>
                                @foreach ($this->itemsPending as $i => $row)
                                    <flux:table.row :key="$row->id">
                                        <flux:table.cell class="text-center text-zinc-500">
                                            {{ $this->itemsPending->firstItem() + $i }}</flux:table.cell>

                                        <flux:table.cell class="whitespace-nowrap">
                                            {{ optional($row->updated_at)->format('d M Y') }}
                                        </flux:table.cell>

                                        <flux:table.cell>
                                            <div class="font-medium text-stone-900 dark:text-stone-100">
                                                {{ $row->mahasiswa?->user?->name }}</div>
                                            <div class="text-xs text-zinc-500">{{ $row->mahasiswa?->mahasiswa_nim }}
                                            </div>
                                        </flux:table.cell>

                                        <flux:table.cell class="max-w-[250px]">
                                            <div class="line-clamp-1 text-sm font-medium">{{ $row->judul_kp }}</div>
                                            <div class="text-xs text-zinc-500 truncate">{{ $row->lokasi_kp }}</div>
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
                                                    icon="ellipsis-horizontal" />
                                                <flux:menu>
                                                    <flux:menu.item icon="eye"
                                                        wire:click="openDetail({{ $row->id }})">Detail
                                                    </flux:menu.item>
                                                    <flux:menu.item icon="check"
                                                        wire:click="openPublish({{ $row->id }})">Terbitkan
                                                    </flux:menu.item>
                                                </flux:menu>
                                            </flux:dropdown>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>

                        {{-- Empty State Pending --}}
                        @if ($this->itemsPending->isEmpty())
                            <div class="flex flex-col items-center justify-center py-12 text-center">
                                <div class="rounded-full bg-zinc-100 p-4 dark:bg-stone-900">
                                    <flux:icon.inbox class="size-8 text-zinc-400" />
                                </div>
                                <h3 class="mt-4 text-base font-semibold text-stone-900 dark:text-stone-100">
                                    Tidak ada antrean
                                </h3>
                                <p class="mt-1 text-sm text-zinc-500">
                                    @if ($search)
                                        Tidak ditemukan data yang cocok dengan pencarian "{{ $search }}".
                                    @else
                                        Belum ada pengajuan KP yang menunggu penerbitan SPK.
                                    @endif
                                </p>
                            </div>
                        @endif
                    </flux:card>
                </flux:tab.panel>

                {{-- PANEL PUBLISHED --}}
                <flux:tab.panel name="published" class="pt-4">
                    <flux:card
                        class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm overflow-hidden">
                        {{-- Header Tabel --}}
                        <div
                            class="flex flex-col gap-4 px-6 py-4 border-b border-zinc-200 dark:border-stone-800 bg-emerald-50/50 dark:bg-emerald-900/10 md:flex-row md:items-center md:justify-between">
                            <h4 class="text-base font-semibold text-stone-900 dark:text-stone-100">Riwayat Terbit</h4>

                            <div class="flex items-center gap-3">
                                <flux:input icon="magnifying-glass" placeholder="Cari..."
                                    wire:model.live.debounce.300ms="search"
                                    class="w-full md:w-64 bg-white dark:bg-stone-900" />
                                <flux:select wire:model.live="perPage" class="w-20">
                                    <flux:select.option :value="10">10</flux:select.option>
                                    <flux:select.option :value="25">25</flux:select.option>
                                    <flux:select.option :value="50">50</flux:select.option>
                                </flux:select>
                            </div>
                        </div>

                        <flux:table :paginate="$this->itemsPublished">
                            <flux:table.columns>
                                <flux:table.column class="w-12 text-center">No</flux:table.column>
                                <flux:table.column sortable wire:click="sort('tanggal_terbit_spk')"
                                    :sorted="$sortBy === 'tanggal_terbit_spk'" :direction="$sortDirection">Tgl Terbit
                                </flux:table.column>
                                <flux:table.column>Nomor SPK</flux:table.column>
                                <flux:table.column>Mahasiswa</flux:table.column>
                                <flux:table.column>Status</flux:table.column>
                                <flux:table.column class="text-right">Aksi</flux:table.column>
                            </flux:table.columns>

                            <flux:table.rows>
                                @foreach ($this->itemsPublished as $i => $row)
                                    <flux:table.row :key="$row->id">
                                        <flux:table.cell class="text-center text-zinc-500">
                                            {{ $this->itemsPublished->firstItem() + $i }}</flux:table.cell>

                                        <flux:table.cell class="whitespace-nowrap">
                                            {{ optional($row->tanggal_terbit_spk)->format('d M Y') }}
                                        </flux:table.cell>

                                        <flux:table.cell>
                                            <span class="font-mono text-xs">{{ $row->nomor_spk }}</span>
                                        </flux:table.cell>

                                        <flux:table.cell>
                                            <div class="font-medium text-stone-900 dark:text-stone-100">
                                                {{ $row->mahasiswa?->user?->name }}</div>
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
                                                        href="{{ route('bap.kp.download.docx', $row->id) }}"
                                                        target="_blank">Unduh SPK</flux:menu.item>
                                                </flux:menu>
                                            </flux:dropdown>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>

                        {{-- Empty State Published --}}
                        @if ($this->itemsPublished->isEmpty())
                            <div class="flex flex-col items-center justify-center py-12 text-center">
                                <div class="rounded-full bg-zinc-100 p-4 dark:bg-stone-900">
                                    <flux:icon.document-text class="size-8 text-zinc-400" />
                                </div>
                                <h3 class="mt-4 text-base font-semibold text-stone-900 dark:text-stone-100">
                                    Belum ada data
                                </h3>
                                <p class="mt-1 text-sm text-zinc-500">
                                    @if ($search)
                                        Tidak ditemukan data yang cocok dengan pencarian "{{ $search }}".
                                    @else
                                        Belum ada SPK yang diterbitkan.
                                    @endif
                                </p>
                            </div>
                        @endif
                    </flux:card>
                </flux:tab.panel>
            </flux:tab.group>
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
                        class="flex items-center justify-between p-2 rounded-lg bg-amber-50/50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-800/30">
                        <div class="flex items-center gap-2">
                            <div class="size-2 rounded-full bg-amber-500"></div>
                            <span class="text-sm font-medium text-stone-700 dark:text-stone-300">Pending</span>
                        </div>
                        <span
                            class="text-lg font-bold text-amber-600 dark:text-amber-400">{{ $this->stats['pending'] }}</span>
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
                </div>
            </flux:card>

            {{-- 2. PANDUAN --}}
            <flux:card
                class="rounded-xl border bg-sky-50/50 dark:bg-sky-900/10 border-sky-100 dark:border-sky-800/30 shadow-sm">
                <div class="flex items-start gap-3">
                    <flux:icon.information-circle class="mt-0.5 size-5 text-sky-600 dark:text-sky-400" />
                    <div>
                        <h3 class="font-semibold text-sky-900 dark:text-sky-100 text-sm">Alur Penerbitan</h3>
                        <ul class="mt-3 text-xs text-sky-800 dark:text-sky-200 space-y-2 list-disc list-inside">
                            <li>Cek data pengajuan di tab <strong>Belum Diterbitkan</strong>.</li>
                            <li>Pastikan judul dan lokasi KP sudah sesuai.</li>
                            <li>Klik <strong>Terbitkan</strong> dan masukkan Nomor SPK.</li>
                            <li>SPK yang terbit akan muncul di tab <strong>Sudah Diterbitkan</strong>.</li>
                        </ul>
                    </div>
                </div>
            </flux:card>
        </div>
    </div>

    {{-- MODAL DETAIL --}}
    <flux:modal name="detail-spk" :show="$detailId !== null" class="md:w-[32rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Detail Pengajuan</flux:heading>
                <p class="text-sm text-zinc-500">Informasi lengkap pengajuan KP.</p>
            </div>

            @if ($selectedItem = $this->selectedItem)
                <div class="space-y-4">
                    <div class="p-3 rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <div class="text-xs text-zinc-500">Mahasiswa</div>
                        <div class="font-medium">{{ $selectedItem->mahasiswa->user->name }}</div>
                        <div class="text-xs">{{ $selectedItem->mahasiswa->mahasiswa_nim }}</div>
                    </div>

                    <div class="p-3 rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <div class="text-xs text-zinc-500">Judul KP</div>
                        <div class="font-medium">{{ $selectedItem->judul_kp }}</div>
                    </div>

                    <div class="p-3 rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <div class="text-xs text-zinc-500">Lokasi</div>
                        <div class="font-medium">{{ $selectedItem->lokasi_kp }}</div>
                    </div>

                    <div class="p-3 rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <div class="text-xs text-zinc-500">Dosen Pembimbing</div>
                        <div class="font-medium">{{ $selectedItem->dosenPembimbing->dosen_name ?? '-' }}</div>
                    </div>
                </div>
            @endif

            <div class="flex justify-end">
                <flux:button variant="ghost" wire:click="closeDetail">Tutup</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- MODAL PUBLISH --}}
    <flux:modal name="spk-publish" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Terbitkan SPK</flux:heading>
                <p class="text-sm text-zinc-500">Masukkan nomor surat untuk menerbitkan.</p>
            </div>

            <div class="space-y-4">
                <flux:input label="Nomor SPK" placeholder="Contoh: 001/UNSOED/2025" wire:model="nomor_spk" />

                <flux:select label="Penandatangan" wire:model="signatory_id">
                    @foreach (\App\Models\Signatory::orderBy('position')->get() as $sig)
                        <flux:select.option :value="$sig->id">{{ $sig->name }} ({{ $sig->position }})
                        </flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" wire:click="publishSave">Simpan & Terbitkan</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
