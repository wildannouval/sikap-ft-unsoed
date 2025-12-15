<div class="space-y-6">
    <flux:toast />

    {{-- HEADER --}}
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <flux:heading size="xl" level="1" class="text-stone-900 dark:text-stone-100">
                Review Pengajuan KP
            </flux:heading>
            <flux:subheading class="text-zinc-600 dark:text-zinc-300">
                Tinjau pengajuan, tetapkan pembimbing, dan setujui penerbitan SPK.
            </flux:subheading>
        </div>
    </div>

    <flux:separator variant="subtle" />

    {{-- GRID UTAMA 3:1 --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">

        {{-- KOLOM KIRI: TABEL (3/4) --}}
        <div class="lg:col-span-3 space-y-6">
            <flux:card
                class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm overflow-hidden">

                {{-- Header Tabel --}}
                <div
                    class="flex flex-col gap-4 px-6 py-4 border-b border-zinc-200 dark:border-stone-800 bg-violet-50/50 dark:bg-violet-900/10 md:flex-row md:items-center md:justify-between">
                    <h4 class="text-base font-semibold text-stone-900 dark:text-stone-100">Daftar Pengajuan</h4>

                    <div class="flex items-center gap-3">
                        <flux:input icon="magnifying-glass" placeholder="Cari..." wire:model.live.debounce.400ms="q"
                            class="w-full md:w-56 bg-white dark:bg-stone-900" />

                        <flux:select wire:model.live="statusFilter" class="w-40">
                            <flux:select.option value="all">Semua</flux:select.option>
                            <flux:select.option value="review_komisi">Review Komisi</flux:select.option>
                            <flux:select.option value="review_bapendik">Tunggu SPK</flux:select.option>
                            <flux:select.option value="spk_terbit">SPK Terbit</flux:select.option>
                            <flux:select.option value="ditolak">Ditolak</flux:select.option>
                        </flux:select>
                    </div>
                </div>

                <flux:table :paginate="$this->orders">
                    <flux:table.columns>
                        <flux:table.column class="w-12 text-center">No</flux:table.column>
                        <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection"
                            wire:click="sort('created_at')">Tanggal</flux:table.column>
                        <flux:table.column>Mahasiswa</flux:table.column>
                        <flux:table.column>Judul</flux:table.column>
                        <flux:table.column>Instansi</flux:table.column>
                        <flux:table.column>Pembimbing</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                        <flux:table.column class="text-right">Aksi</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($this->orders as $i => $row)
                            <flux:table.row :key="$row->id">
                                <flux:table.cell class="text-center text-zinc-500">
                                    {{ $this->orders->firstItem() + $i }}
                                </flux:table.cell>

                                <flux:table.cell class="whitespace-nowrap">
                                    {{ optional($row->created_at)->format('d M Y') }}
                                </flux:table.cell>

                                <flux:table.cell>
                                    <div class="font-medium text-stone-900 dark:text-stone-100">
                                        {{ $row->mahasiswa?->user?->name }}
                                    </div>
                                    <div class="text-xs text-zinc-500">{{ $row->mahasiswa?->mahasiswa_nim }}</div>
                                </flux:table.cell>

                                <flux:table.cell class="max-w-[200px]">
                                    <span class="line-clamp-2 text-sm">{{ $row->judul_kp }}</span>
                                </flux:table.cell>

                                <flux:table.cell class="max-w-[150px]">
                                    <span class="line-clamp-1 text-sm text-zinc-500">{{ $row->lokasi_kp }}</span>
                                </flux:table.cell>

                                <flux:table.cell class="whitespace-nowrap">
                                    {{ $row->dosenPembimbing?->dosen_name ?? '—' }}
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
                                            <flux:menu.item icon="eye" wire:click="openDetail({{ $row->id }})">
                                                Detail
                                            </flux:menu.item>

                                            @if ($row->status === 'review_komisi')
                                                <flux:menu.item icon="user-plus"
                                                    wire:click="openAssign({{ $row->id }})">
                                                    Pilih Pembimbing
                                                </flux:menu.item>

                                                <flux:menu.separator />

                                                {{-- Setujui: boleh diklik. Jika pembimbing belum ada, Livewire akan arahkan ke modal assign --}}
                                                <flux:menu.item icon="check"
                                                    wire:click="triggerApprove({{ $row->id }})">
                                                    Setujui
                                                </flux:menu.item>

                                                <flux:menu.item icon="x-mark" variant="danger"
                                                    wire:click="triggerReject({{ $row->id }})">
                                                    Tolak
                                                </flux:menu.item>
                                            @endif

                                            @if ($row->status === 'spk_terbit')
                                                <flux:menu.separator />
                                                <flux:menu.item icon="arrow-down-tray"
                                                    href="{{ route('komisi.kp.download.docx', $row->id) }}"
                                                    target="_blank">
                                                    Unduh SPK
                                                </flux:menu.item>
                                            @endif
                                        </flux:menu>
                                    </flux:dropdown>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>

                {{-- Empty State --}}
                @if ($this->orders->isEmpty())
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <div class="rounded-full bg-zinc-100 p-4 dark:bg-stone-900">
                            <flux:icon.document-text class="size-8 text-zinc-400" />
                        </div>
                        <h3 class="mt-4 text-base font-semibold text-stone-900 dark:text-stone-100">
                            Belum ada data
                        </h3>
                        <p class="mt-1 text-sm text-zinc-500">
                            @if ($q)
                                Tidak ditemukan data yang cocok dengan pencarian "{{ $q }}".
                            @else
                                Belum ada pengajuan KP yang sesuai filter.
                            @endif
                        </p>
                    </div>
                @endif
            </flux:card>
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
                            <div class="size-2 rounded-full bg-amber-500 animate-pulse"></div>
                            <span class="text-sm font-medium text-stone-700 dark:text-stone-300">Perlu Review</span>
                        </div>
                        <span class="text-lg font-bold text-amber-600 dark:text-amber-400">
                            {{ $this->stats['review_komisi'] }}
                        </span>
                    </div>

                    <div
                        class="flex items-center justify-between p-2 rounded-lg bg-sky-50/50 dark:bg-sky-900/10 border border-sky-100 dark:border-sky-800/30">
                        <div class="flex items-center gap-2">
                            <div class="size-2 rounded-full bg-sky-500"></div>
                            <span class="text-sm font-medium text-stone-700 dark:text-stone-300">Tunggu SPK</span>
                        </div>
                        <span class="text-lg font-bold text-sky-600 dark:text-sky-400">
                            {{ $this->stats['review_bapendik'] }}
                        </span>
                    </div>

                    <div
                        class="flex items-center justify-between p-2 rounded-lg bg-emerald-50/50 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-800/30">
                        <div class="flex items-center gap-2">
                            <div class="size-2 rounded-full bg-emerald-500"></div>
                            <span class="text-sm font-medium text-stone-700 dark:text-stone-300">SPK Terbit</span>
                        </div>
                        <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                            {{ $this->stats['spk_terbit'] }}
                        </span>
                    </div>
                </div>
            </flux:card>

            {{-- 2. PANDUAN --}}
            <flux:card
                class="rounded-xl border bg-violet-50/50 dark:bg-violet-900/10 border-violet-100 dark:border-violet-800/30 shadow-sm">
                <div class="flex items-start gap-3">
                    <flux:icon.information-circle class="mt-0.5 size-5 text-violet-600 dark:text-violet-400" />
                    <div>
                        <h3 class="font-semibold text-violet-900 dark:text-violet-100 text-sm">Alur Review</h3>
                        <ul class="mt-3 text-xs text-violet-800 dark:text-violet-200 space-y-2 list-disc list-inside">
                            <li>Filter status <strong>Menunggu Review Komisi</strong>.</li>
                            <li>Tetapkan <strong>Dosen Pembimbing</strong> terlebih dahulu.</li>
                            <li>Klik <strong>Setujui</strong> untuk meneruskan ke Bapendik.</li>
                            <li>Jika ditolak, berikan alasan yang jelas.</li>
                        </ul>
                    </div>
                </div>
            </flux:card>
        </div>
    </div>

    {{-- MODAL DETAIL --}}
    <flux:modal name="detail-kp" :show="$detailId !== null" class="md:w-[32rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Detail Pengajuan KP</flux:heading>
                <p class="text-sm text-zinc-500">Informasi lengkap pengajuan.</p>
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
                        <div class="font-medium">
                            {{ $selectedItem->dosenPembimbing->dosen_name ?? 'Belum ditetapkan' }}
                        </div>
                    </div>

                    <div class="p-3 rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <div class="text-xs text-zinc-500">Dokumen</div>
                        <div class="flex flex-col gap-1 mt-1">
                            @if ($selectedItem->proposal_path)
                                <a href="{{ asset('storage/' . $selectedItem->proposal_path) }}" target="_blank"
                                    class="text-sm text-indigo-600 hover:underline">Lihat Proposal</a>
                            @else
                                <span class="text-sm text-zinc-400">Proposal tidak ada</span>
                            @endif

                            @if ($selectedItem->surat_keterangan_path)
                                <a href="{{ asset('storage/' . $selectedItem->surat_keterangan_path) }}"
                                    target="_blank" class="text-sm text-indigo-600 hover:underline">Lihat Surat
                                    Diterima</a>
                            @else
                                <span class="text-sm text-zinc-400">Surat diterima tidak ada</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <div class="flex justify-end">
                <flux:button variant="ghost" wire:click="closeDetail">Tutup</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- MODAL ASSIGN MENTOR --}}
    <flux:modal name="assign-mentor" :show="$assignId !== null" class="md:w-[28rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Tetapkan Pembimbing</flux:heading>
                <p class="text-sm text-zinc-500">Pilih dosen pembimbing untuk mahasiswa ini.</p>
            </div>

            <flux:select label="Dosen Pembimbing" wire:model="dosen_id" placeholder="Pilih Dosen...">
                @foreach ($this->dosenOptions as $dosen)
                    <flux:select.option :value="$dosen['id']">{{ $dosen['nama'] }}</flux:select.option>
                @endforeach
            </flux:select>

            @error('dosen_id')
                <div class="text-xs text-rose-600">{{ $message }}</div>
            @enderror

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>

                <flux:button variant="primary" wire:click="saveAssign">Simpan</flux:button>

                <flux:button variant="primary" icon="check" wire:click="saveAssign(true)">
                    Simpan & Setujui
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- MODAL APPROVE --}}
    <flux:modal name="approve-kp" :show="$approveId !== null" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Setujui Pengajuan?</flux:heading>
                <p class="text-sm text-zinc-500">Pengajuan akan diteruskan ke Bapendik untuk dibuatkan SPK.</p>
            </div>
            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" wire:click="confirmApprove">Ya, Setujui</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- MODAL REJECT --}}
    <flux:modal name="reject-kp" :show="$rejectId !== null" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Tolak Pengajuan?</flux:heading>
                <p class="text-sm text-zinc-500">Berikan alasan penolakan kepada mahasiswa.</p>
            </div>

            <flux:textarea label="Alasan Penolakan" wire:model.defer="rejectNote"
                placeholder="Contoh: Judul kurang sesuai..." />

            {{-- @error('rejectNote')
                <div class="text-xs text-rose-600">{{ $message }}</div>
            @enderror --}}

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" wire:click="confirmReject">Tolak Pengajuan</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
