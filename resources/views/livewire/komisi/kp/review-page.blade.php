<div class="space-y-6">

    {{-- FILTER BAR --}}
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
            <h3 class="text-base font-semibold">Review Pengajuan KP</h3>
            <p class="text-sm text-zinc-500 dark:text-zinc-300">
                Tetapkan dosen pembimbing, setujui untuk diteruskan ke Bapendik, atau tolak dengan catatan.
            </p>
        </div>

        <div class="flex flex-col gap-2 md:flex-row md:items-end">
            <div class="md:w-80">
                <flux:input placeholder="Cari judul / instansi / nama / NIM / pembimbing..."
                    wire:model.live.debounce.400ms="q" icon="magnifying-glass" />
            </div>

            <flux:select wire:model.live="statusFilter" class="md:ml-2">
                <option value="all">Semua Status</option>
                <option value="review_komisi">Menunggu Review Komisi</option>
                <option value="review_bapendik">Menunggu Terbit SPK</option>
                <option value="spk_terbit">SPK Terbit</option>
                <option value="ditolak">Ditolak</option>
            </flux:select>
        </div>
    </div>

    {{-- GRID: TABEL (kiri) + RINGKASAN (kanan) --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        {{-- TABEL --}}
        <div class="lg:col-span-8">
            <flux:card class="space-y-4">

                {{-- wire:key memastikan re-mount ketika filter/sort berubah --}}
                <div
                    wire:key="kp-review-{{ $q }}-{{ $statusFilter }}-{{ $sortBy }}-{{ $sortDirection }}">
                    <flux:table :paginate="$this->orders">
                        <flux:table.columns>
                            <flux:table.column class="w-12">#</flux:table.column>

                            <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection"
                                wire:click="sort('created_at')">
                                Tanggal
                            </flux:table.column>

                            <flux:table.column>Mahasiswa</flux:table.column>
                            <flux:table.column>Judul</flux:table.column>
                            <flux:table.column>Instansi</flux:table.column>
                            <flux:table.column>Pembimbing</flux:table.column>

                            <flux:table.column sortable :sorted="$sortBy === 'status'" :direction="$sortDirection"
                                wire:click="sort('status')">
                                Status
                            </flux:table.column>

                            <flux:table.column class="w-32 text-center">Aksi</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @foreach ($this->orders as $i => $row)
                                <flux:table.row :key="$row->id">
                                    <flux:table.cell>{{ $this->orders->firstItem() + $i }}</flux:table.cell>

                                    <flux:table.cell class="whitespace-nowrap">
                                        {{ optional($row->created_at)->format('d M Y') ?: '—' }}
                                    </flux:table.cell>

                                    <flux:table.cell class="whitespace-nowrap">
                                        {{ $row->mahasiswa?->user?->name }}<br>
                                        <span class="text-xs text-zinc-500">{{ $row->mahasiswa?->mahasiswa_nim }}</span>
                                    </flux:table.cell>

                                    <flux:table.cell class="max-w-[360px]">
                                        <span class="line-clamp-2">{{ $row->judul_kp }}</span>
                                    </flux:table.cell>

                                    <flux:table.cell class="whitespace-nowrap">
                                        {{ $row->lokasi_kp }}
                                    </flux:table.cell>

                                    <flux:table.cell class="whitespace-nowrap">
                                        {{ $row->dosenPembimbing?->dosen_name ?? '—' }}
                                    </flux:table.cell>

                                    <flux:table.cell>
                                        <flux:badge size="sm" inset="top bottom"
                                            :color="$this->badgeColor($row->status)">
                                            {{ $this->statusLabel($row->status) }}
                                        </flux:badge>
                                    </flux:table.cell>

                                    <flux:table.cell>
                                        {{-- Dropdown aksi --}}
                                        <flux:dropdown position="bottom" align="end">
                                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                                inset="top bottom"></flux:button>
                                            <flux:menu class="min-w-48">

                                                {{-- Detail --}}
                                                <flux:modal.trigger name="detail-kp">
                                                    <flux:menu.item icon="eye"
                                                        wire:click="openDetail({{ $row->id }})">
                                                        Detail
                                                    </flux:menu.item>
                                                </flux:modal.trigger>

                                                {{-- Tetapkan Pembimbing (hanya saat review_komisi) --}}
                                                @if ($row->status === 'review_komisi')
                                                    <flux:modal.trigger name="assign-mentor">
                                                        <flux:menu.item icon="user-plus"
                                                            wire:click="openAssign({{ $row->id }})">
                                                            Pilih Pembimbing
                                                        </flux:menu.item>
                                                    </flux:modal.trigger>
                                                @else
                                                    <flux:menu.item icon="user-plus" disabled>Pilih Pembimbing
                                                    </flux:menu.item>
                                                @endif

                                                <flux:menu.separator />

                                                @php $bolehSetujui = $row->status === 'review_komisi' && !is_null($row->dosen_pembimbing_id); @endphp

                                                @if ($bolehSetujui)
                                                    <flux:modal.trigger name="approve-kp">
                                                        <flux:menu.item icon="check"
                                                            wire:click="triggerApprove({{ $row->id }})">
                                                            Setujui
                                                        </flux:menu.item>
                                                    </flux:modal.trigger>
                                                @else
                                                    <flux:menu.item icon="check" disabled>Setujui</flux:menu.item>
                                                @endif

                                                @if ($row->status === 'review_komisi')
                                                    <flux:modal.trigger name="reject-kp">
                                                        <flux:menu.item icon="x-mark"
                                                            wire:click="triggerReject({{ $row->id }})">
                                                            Tolak
                                                        </flux:menu.item>
                                                    </flux:modal.trigger>
                                                @else
                                                    <flux:menu.item icon="x-mark" disabled>Tolak</flux:menu.item>
                                                @endif

                                                {{-- Unduh SPK untuk KOMISI hanya saat sudah terbit --}}
                                                @if ($row->status === 'spk_terbit')
                                                    <flux:menu.item icon="arrow-down-tray"
                                                        href="{{ route('komisi.kp.download.docx', $row->id) }}"
                                                        target="_blank">
                                                        Unduh SPK (DOCX)
                                                    </flux:menu.item>
                                                @else
                                                    <flux:menu.item icon="arrow-down-tray" disabled>Unduh SPK (DOCX)
                                                    </flux:menu.item>
                                                @endif
                                            </flux:menu>
                                        </flux:dropdown>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>
            </flux:card>
        </div>

        {{-- RINGKASAN (KANAN) --}}
        <div class="lg:col-span-4">
            <flux:card class="space-y-4">
                <div>
                    <h3 class="text-base font-semibold">Ringkasan Status</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-300">Jumlah pengajuan per status & keterangan.</p>
                </div>

                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <flux:badge size="sm" inset="top bottom" :color="$this->badgeColor('review_komisi')">
                                Menunggu Review Komisi
                            </flux:badge>
                        </div>
                        <span class="font-semibold">{{ $this->stats['review_komisi'] }}</span>
                    </div>

                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <flux:badge size="sm" inset="top bottom" :color="$this->badgeColor('review_bapendik')">
                                Menunggu Terbit SPK
                            </flux:badge>
                        </div>
                        <span class="font-semibold">{{ $this->stats['review_bapendik'] }}</span>
                    </div>

                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <flux:badge size="sm" inset="top bottom" :color="$this->badgeColor('spk_terbit')">
                                SPK Terbit
                            </flux:badge>
                        </div>
                        <span class="font-semibold">{{ $this->stats['spk_terbit'] }}</span>
                    </div>

                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <flux:badge size="sm" inset="top bottom" :color="$this->badgeColor('ditolak')">
                                Ditolak
                            </flux:badge>
                        </div>
                        <span class="font-semibold">{{ $this->stats['ditolak'] }}</span>
                    </div>
                </div>

                <flux:separator />

                <div class="space-y-2 text-sm text-zinc-600 dark:text-zinc-300">
                    <div>
                        <span class="font-semibold">Menunggu Review Komisi:</span>
                        Ajukan oleh mahasiswa; komisi dapat <em>memilih pembimbing</em>, meninjau berkas, lalu
                        setujui/tolak.
                    </div>
                    <div>
                        <span class="font-semibold">Menunggu Terbit SPK:</span>
                        Sudah disetujui komisi; menunggu Bapendik menerbitkan SPK.
                    </div>
                    <div>
                        <span class="font-semibold">SPK Terbit:</span>
                        Bapendik telah menerbitkan SPK (bisa diunduh).
                    </div>
                    <div>
                        <span class="font-semibold">Ditolak:</span>
                        Ditolak oleh komisi, lihat catatan pada detail pengajuan.
                    </div>
                </div>
            </flux:card>
        </div>
    </div>

    {{-- ===== DETAIL MODAL ===== --}}
    <flux:modal name="detail-kp" :show="$detailId !== null">
        @php $item = $this->selectedItem; @endphp

        <div class="space-y-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <flux:heading size="lg">Detail Pengajuan KP</flux:heading>
                    <p class="text-sm text-zinc-500">Periksa data & dokumen yang diunggah mahasiswa.</p>
                </div>
                <flux:modal.close>
                    <flux:button variant="ghost" icon="x-mark" wire:click="closeDetail"></flux:button>
                </flux:modal.close>
            </div>

            @if ($item)
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <flux:card class="space-y-2">
                        <div class="text-sm text-zinc-500">Mahasiswa</div>
                        <div class="font-semibold">
                            {{ $item->mahasiswa?->user?->name }}
                            <div class="text-sm text-zinc-500">{{ $item->mahasiswa?->mahasiswa_nim }}</div>
                        </div>
                    </flux:card>

                    <flux:card class="space-y-2">
                        <div class="text-sm text-zinc-500">Status</div>
                        <flux:badge size="sm" inset="top bottom" :color="$this->badgeColor($item->status)">
                            {{ $this->statusLabel($item->status) }}
                        </flux:badge>
                    </flux:card>

                    <flux:card class="space-y-2">
                        <div class="text-sm text-zinc-500">Pembimbing</div>
                        <div class="font-medium">
                            {{ $item->dosenPembimbing?->dosen_name ?? '—' }}
                        </div>
                    </flux:card>

                    @if ($item->catatan)
                        <flux:card class="space-y-2">
                            <div class="text-sm text-zinc-500">Catatan</div>
                            <div class="text-sm">{{ $item->catatan }}</div>
                        </flux:card>
                    @endif

                    <flux:card class="space-y-2 md:col-span-2">
                        <div class="text-sm text-zinc-500">Judul Kerja Praktik</div>
                        <div class="font-medium">{{ $item->judul_kp }}</div>
                    </flux:card>

                    <flux:card class="space-y-2 md:col-span-2">
                        <div class="text-sm text-zinc-500">Instansi / Lokasi KP</div>
                        <div class="font-medium">{{ $item->lokasi_kp }}</div>
                    </flux:card>

                    <flux:card class="space-y-3 md:col-span-2">
                        <div class="text-sm text-zinc-500">Dokumen</div>
                        <div class="flex flex-col gap-2">
                            <a class="text-sm underline hover:no-underline"
                                href="{{ $item->proposal_path ? asset('storage/' . $item->proposal_path) : '#' }}"
                                target="_blank"
                                @if (!$item->proposal_path) aria-disabled="true" class="pointer-events-none opacity-50" @endif>
                                Lihat Proposal (PDF)
                            </a>

                            <a class="text-sm underline hover:no-underline"
                                href="{{ $item->surat_keterangan_path ? asset('storage/' . $item->surat_keterangan_path) : '#' }}"
                                target="_blank"
                                @if (!$item->surat_keterangan_path) aria-disabled="true" class="pointer-events-none opacity-50" @endif>
                                Lihat Surat Diterima (PDF/JPG/PNG)
                            </a>
                        </div>
                    </flux:card>
                </div>
            @else
                <div class="text-sm text-red-600">Data tidak ditemukan.</div>
            @endif

            <div class="flex justify-end">
                <flux:modal.close>
                    <flux:button variant="primary" wire:click="closeDetail">Tutup</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>

    {{-- ===== MODAL PILIH PEMBIMBING ===== --}}
    <flux:modal name="assign-mentor" class="min-w-[28rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Tetapkan Dosen Pembimbing</flux:heading>
                <flux:subheading class="mt-1">Pilih dosen pembimbing untuk pengajuan ini.</flux:subheading>
            </div>

            <div>
                <flux:select label="Dosen Pembimbing" wire:model="dosen_id">
                    <option value="">— Pilih Dosen —</option>
                    @foreach ($this->dosenOptions as $opt)
                        <option value="{{ $opt['id'] }}">{{ $opt['nama'] }}</option>
                    @endforeach
                </flux:select>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" icon="check" wire:click="saveAssign" wire:loading.attr="disabled">
                    Simpan
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- ===== MODAL SETUJUI ===== --}}
    <flux:modal name="approve-kp" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Setujui pengajuan?</flux:heading>
                <flux:text class="mt-2">
                    Pengajuan akan diteruskan ke Bapendik untuk penerbitan SPK. Pastikan pembimbing sudah ditetapkan.
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>

                <flux:button type="button" variant="primary" wire:click="confirmApprove">
                    Setujui
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- ===== MODAL TOLAK ===== --}}
    <flux:modal name="reject-kp" class="min-w-[26rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Tolak pengajuan?</flux:heading>
                <flux:text class="mt-2">
                    Masukkan catatan penolakan untuk mahasiswa.
                </flux:text>
            </div>

            <div>
                <flux:textarea label="Catatan penolakan" wire:model.defer="rejectNote"
                    placeholder="Sebutkan kekurangan / alasan penolakan" rows="4"
                    :invalid="$errors->has('rejectNote')" />
                @error('rejectNote')
                    <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                @enderror
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>

                <flux:button type="button" variant="danger" wire:click="confirmReject">
                    Tolak
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
