<div class="space-y-6">

    {{-- BARIS ATAS: FORM (kiri) + RINGKASAN (kanan) --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-10">
        {{-- FORM --}}
        <div class="lg:col-span-6">
            <flux:card class="space-y-6 bg-zinc-50">
                <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <h3 class="text-base font-semibold">Pengajuan Kerja Praktik</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-300">
                            Isi data di bawah lalu ajukan ke Komisi & Bapendik.
                        </p>
                    </div>

                    {{-- Dropdown Ambil dari SP terbit --}}
                    <div class="flex flex-col gap-2 md:flex-row md:items-end">
                        <div class="md:w-80">
                            <flux:select wire:model="selectedSpId" label="Ambil dari SP terbit">
                                <option value="">— Pilih SP terbit —</option>
                                @foreach ($this->spOptions as $sp)
                                    <option value="{{ $sp['id'] }}">
                                        {{ $sp['nomor_surat'] }} — {{ $sp['lokasi_surat_pengantar'] }}
                                        @if (!empty($sp['tanggal']))
                                            ({{ \Carbon\Carbon::parse($sp['tanggal'])->format('d M Y') }})
                                        @endif
                                    </option>
                                @endforeach
                            </flux:select>
                        </div>

                        <flux:button size="sm" variant="outline" icon="arrow-path-rounded-square"
                            wire:click="fillFromSP" class="md:ml-2">
                            Ambil
                        </flux:button>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <flux:input label="Judul Kerja Praktik" wire:model.defer="judul_kp"
                            placeholder="Contoh: Otomasi Monitoring X di PT Y" :invalid="$errors->has('judul_kp')" />
                    </div>

                    <div>
                        <flux:input label="Instansi / Lokasi KP" wire:model.defer="lokasi_kp"
                            placeholder="Nama perusahaan / instansi" :invalid="$errors->has('lokasi_kp')" />
                    </div>

                    {{-- Upload Proposal (atas) --}}
                    <div>
                        <div>
                            <flux:input wire:model="proposal_kp" type="file" label="Upload Proposal (PDF, maks 2MB)"
                                required />
                            @if ($proposal_kp && !$errors->has('proposal_kp'))
                                <div
                                    class="mt-1 flex items-center justify-between rounded-lg border bg-zinc-50 p-2 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                                    <span class="truncate">{{ $proposal_kp->getClientOriginalName() }}</span>
                                    <button type="button" wire:click="removeProposal"
                                        class="text-red-500 hover:text-red-700 font-bold text-lg flex-shrink-0 ml-2">&times;</button>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Upload Surat Diterima (bawah) --}}
                    <div>
                        <div>
                            <flux:input wire:model="surat_keterangan_kp" type="file"
                                label="Upload Surat Diterima (PDF/JPG/PNG, maks 2MB)" required />
                            @if ($surat_keterangan_kp && !$errors->has('surat_keterangan_kp'))
                                <div
                                    class="mt-1 flex items-center justify-between rounded-lg border bg-zinc-50 p-2 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                                    <span class="truncate">{{ $surat_keterangan_kp->getClientOriginalName() }}</span>
                                    <button type="button" wire:click="removeSuratKeterangan"
                                        class="text-red-500 hover:text-red-700 font-bold text-lg flex-shrink-0 ml-2">&times;</button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    @if ($editingId)
                        <flux:button variant="ghost" icon="x-mark" wire:click="cancelEdit">Batal</flux:button>
                        <flux:button variant="primary" icon="check" wire:click="update">Simpan Perubahan</flux:button>
                    @else
                        <flux:button variant="primary" icon="paper-airplane" wire:click="submit">Ajukan</flux:button>
                    @endif
                </div>
            </flux:card>
        </div>

        {{-- RINGKASAN STATUS --}}
        <div class="lg:col-span-4">
            <flux:card class="space-y-4 bg-zinc-50">
                <div>
                    <h3 class="text-base font-semibold">Ringkasan Status</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-300">Rekap pengajuan & arti tiap status</p>
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
            </flux:card>
        </div>
    </div>

    {{-- BARIS BAWAH: TABEL + SEARCH --}}
    <div>
        <flux:card class="space-y-4">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div class="md:w-80">
                    <flux:input placeholder="Cari judul / lokasi..." wire:model.debounce.500ms="q"
                        icon="magnifying-glass" />
                </div>

                <div class="flex items-center gap-2">
                    <flux:select wire:model="perPage">
                        <option value="10">10 / halaman</option>
                        <option value="25">25 / halaman</option>
                        <option value="50">50 / halaman</option>
                    </flux:select>
                </div>
            </div>

            <flux:table :paginate="$this->orders">
                <flux:table.columns>
                    <flux:table.column class="w-12">#</flux:table.column>

                    <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection"
                        wire:click="sort('created_at')">Tanggal</flux:table.column>

                    <flux:table.column>Judul</flux:table.column>
                    <flux:table.column>Instansi</flux:table.column>

                    <flux:table.column sortable :sorted="$sortBy === 'status'" :direction="$sortDirection"
                        wire:click="sort('status')">Status</flux:table.column>

                    {{-- Kolom catatan saat ditolak --}}
                    <flux:table.column>Catatan</flux:table.column>

                    <flux:table.column class="w-32 text-center">Aksi</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->orders as $i => $row)
                        <flux:table.row :key="$row->id">
                            <flux:table.cell>{{ $this->orders->firstItem() + $i }}</flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                {{ optional($row->created_at)->format('d M Y') ?: '—' }}
                            </flux:table.cell>

                            <flux:table.cell class="max-w-[360px]">
                                <span class="line-clamp-2">{{ $row->judul_kp }}</span>
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                {{ $row->lokasi_kp }}
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge size="sm" inset="top bottom" :color="$this->badgeColor($row->status)">
                                    {{ $this->statusLabel($row->status) }}
                                </flux:badge>
                            </flux:table.cell>

                            {{-- Catatan hanya nampak bila ditolak --}}
                            <flux:table.cell class="max-w-[260px]">
                                @if ($row->status === 'ditolak' && $row->catatan)
                                    <span
                                        class="text-sm text-zinc-700 dark:text-zinc-300 line-clamp-2">{{ $row->catatan }}</span>
                                @else
                                    —
                                @endif
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:dropdown position="bottom" align="end">
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                        inset="top bottom"></flux:button>
                                    <flux:menu class="min-w-40">

                                        {{-- Detail --}}
                                        <flux:modal.trigger name="detail-kp">
                                            <flux:menu.item icon="eye"
                                                wire:click="openDetail({{ $row->id }})">
                                                Detail
                                            </flux:menu.item>
                                        </flux:modal.trigger>

                                        @if ($row->status === 'review_komisi')
                                            <flux:menu.item icon="pencil-square"
                                                wire:click="edit({{ $row->id }})">
                                                Edit
                                            </flux:menu.item>

                                            <flux:modal.trigger name="delete-kp">
                                                <flux:menu.item icon="trash"
                                                    wire:click="markDelete({{ $row->id }})">
                                                    Hapus
                                                </flux:menu.item>
                                            </flux:modal.trigger>
                                        @else
                                            <flux:menu.item icon="pencil-square" disabled>
                                                Edit
                                            </flux:menu.item>
                                            <flux:menu.item icon="trash" disabled>
                                                Hapus
                                            </flux:menu.item>
                                        @endif
                                        @if ($row->status === 'spk_terbit')
                                            <flux:menu.item icon="arrow-down-tray"
                                                href="{{ route('mhs.kp.download.docx', $row->id) }}" target="_blank">
                                                Unduh SPK (DOCX)
                                            </flux:menu.item>
                                        @endif
                                    </flux:menu>
                                </flux:dropdown>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>

    {{-- Modal konfirmasi hapus --}}
    <flux:modal name="delete-kp" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Hapus pengajuan?</flux:heading>
                <flux:text class="mt-2">
                    Anda akan menghapus pengajuan KP ini.<br>
                    Tindakan ini tidak dapat dibatalkan.
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>

                <flux:button type="button" variant="danger" wire:click="confirmDelete">
                    Hapus
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Modal Detail Pengajuan (Mahasiswa) --}}
    <flux:modal name="detail-kp" :show="$detailId !== null">
        @php $item = $this->selectedItem; @endphp

        <div class="space-y-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <flux:heading size="lg">Detail Pengajuan KP</flux:heading>
                    <p class="text-sm text-zinc-500">Data dan dokumen yang kamu kirim.</p>
                </div>
                {{-- <flux:modal.close>
                    <flux:button variant="ghost" icon="x-mark" wire:click="closeDetail"></flux:button>
                </flux:modal.close> --}}
            </div>

            @if ($item)
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <flux:card class="space-y-2">
                        <div class="text-sm text-zinc-500">Tanggal Pengajuan</div>
                        <div class="font-medium">
                            {{ optional($item->created_at)->format('d M Y') ?: '—' }}
                        </div>
                    </flux:card>

                    <flux:card class="space-y-2">
                        <div class="text-sm text-zinc-500">Status</div>
                        <flux:badge size="sm" inset="top bottom" :color="$this->badgeColor($item->status)">
                            {{ $this->statusLabel($item->status) }}
                        </flux:badge>
                    </flux:card>

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

                    @if ($item->status === 'ditolak' && $item->catatan)
                        <flux:card class="space-y-2 md:col-span-2">
                            <div class="text-sm text-zinc-500">Catatan Penolakan</div>
                            <div class="text-sm">{{ $item->catatan }}</div>
                        </flux:card>
                    @endif
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
</div>
