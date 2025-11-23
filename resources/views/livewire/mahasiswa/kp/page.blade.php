<div class="space-y-6">

    {{-- ALERTS --}}
    @if (session('ok'))
        <div class="rounded-md border border-emerald-300/60 bg-emerald-50 px-3 py-2 text-emerald-800">
            <div class="font-medium">{{ session('ok') }}</div>
        </div>
    @endif
    @if (session('err'))
        <div class="rounded-md border border-rose-300/60 bg-rose-50 px-3 py-2 text-rose-800">
            <div class="font-medium">{{ session('err') }}</div>
        </div>
    @endif

    {{-- BARIS ATAS: FORM + RINGKASAN --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-10">

        {{-- FORM --}}
        <div class="lg:col-span-6">
            <flux:card
                class="space-y-6 rounded-xl border
                       bg-white dark:bg-stone-950
                       border-zinc-200 dark:border-stone-800
                       shadow-xs">

                {{-- Header --}}
                <div class="flex items-center gap-2 px-1.5 -mt-1">
                    <span
                        class="inline-flex items-center justify-center rounded-md p-1.5
                                 bg-indigo-500 text-white dark:bg-indigo-400">
                        <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2" />
                            <path d="M8 8h8M8 12h6M8 16h4" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-base font-semibold text-stone-900 dark:text-stone-100">Pengajuan Kerja Praktik
                        </h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-300">Isi data di bawah lalu ajukan ke Komisi
                            &amp; Bapendik.</p>
                    </div>
                </div>

                <flux:separator />

                {{-- Ambil dari SP terbit --}}
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div class="sm:w-96">
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
                        wire:click="fillFromSP">
                        Ambil
                    </flux:button>
                </div>

                {{-- Fields --}}
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <flux:input label="Judul Kerja Praktik" wire:model.defer="judul_kp"
                            placeholder="Contoh: Otomasi Monitoring X di PT Y" :invalid="$errors->has('judul_kp')" />
                        @error('judul_kp')
                            <div class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <flux:input label="Instansi / Lokasi KP" wire:model.defer="lokasi_kp"
                            placeholder="Nama perusahaan / instansi" :invalid="$errors->has('lokasi_kp')" />
                        @error('lokasi_kp')
                            <div class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Upload Proposal --}}
                    <div>
                        <flux:input wire:model="proposal_kp" type="file" label="Upload Proposal (PDF, maks 2MB)"
                            required />
                        {{-- indikator upload --}}
                        <div class="text-xs text-zinc-500" wire:loading wire:target="proposal_kp">
                            Mengunggah proposal…
                        </div>
                        {{-- error --}}
                        @error('proposal_kp')
                            <div class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</div>
                        @enderror

                        {{-- preview nama file + tombol hapus --}}
                        @if ($proposal_kp && !$errors->has('proposal_kp'))
                            <div
                                class="mt-1 flex items-center justify-between rounded-lg border bg-zinc-50 p-2 text-sm
                   dark:border-zinc-700 dark:bg-zinc-800">
                                <span class="truncate">{{ $proposal_kp->getClientOriginalName() }}</span>
                                <button type="button" wire:click="removeProposal"
                                    class="text-rose-500 hover:text-rose-700 font-bold text-lg flex-shrink-0 ml-2">&times;</button>
                            </div>
                        @endif
                    </div>

                    {{-- Upload Surat Diterima / Bukti --}}
                    <div>
                        <flux:input wire:model="surat_keterangan_kp" type="file"
                            label="Upload Surat Diterima (PDF/JPG/PNG, maks 2MB)" required />
                        {{-- indikator upload --}}
                        <div class="text-xs text-zinc-500" wire:loading wire:target="surat_keterangan_kp">
                            Mengunggah surat diterima…
                        </div>
                        {{-- error --}}
                        @error('surat_keterangan_kp')
                            <div class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</div>
                        @enderror

                        {{-- preview nama file + tombol hapus --}}
                        @if ($surat_keterangan_kp && !$errors->has('surat_keterangan_kp'))
                            <div
                                class="mt-1 flex items-center justify-between rounded-lg border bg-zinc-50 p-2 text-sm
                   dark:border-zinc-700 dark:bg-zinc-800">
                                <span class="truncate">{{ $surat_keterangan_kp->getClientOriginalName() }}</span>
                                <button type="button" wire:click="removeSuratKeterangan"
                                    class="text-rose-500 hover:text-rose-700 font-bold text-lg flex-shrink-0 ml-2">&times;</button>
                            </div>
                        @endif
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
            <flux:card
                class="space-y-4 rounded-xl border
                       bg-white dark:bg-stone-950
                       border-zinc-200 dark:border-stone-800
                       shadow-xs">

                <div class="flex items-center gap-2 px-1.5 -mt-1">
                    <span
                        class="inline-flex items-center justify-center rounded-md p-1.5
                                 bg-indigo-500 text-white dark:bg-indigo-400">
                        <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 3v18h18" />
                            <path d="M7 13v5" />
                            <path d="M12 9v9" />
                            <path d="M17 5v13" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-base font-semibold text-stone-900 dark:text-stone-100">Ringkasan Status</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-300">Rekap pengajuan & arti tiap status</p>
                    </div>
                </div>

                <flux:separator />

                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <flux:badge size="sm" inset="top bottom" :color="$this->badgeColor('review_komisi')"
                                class="border border-indigo-200 dark:border-indigo-900/40
                                       bg-indigo-50 text-indigo-700
                                       dark:bg-indigo-900/20 dark:text-indigo-300">
                                Menunggu Review Komisi
                            </flux:badge>
                        </div>
                        <span
                            class="font-semibold text-stone-900 dark:text-stone-100">{{ $this->stats['review_komisi'] }}</span>
                    </div>

                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <flux:badge size="sm" inset="top bottom" :color="$this->badgeColor('review_bapendik')"
                                class="border border-sky-200 dark:border-sky-900/40
                                       bg-sky-50 text-sky-700
                                       dark:bg-sky-900/20 dark:text-sky-300">
                                Menunggu Terbit SPK
                            </flux:badge>
                        </div>
                        <span
                            class="font-semibold text-stone-900 dark:text-stone-100">{{ $this->stats['review_bapendik'] }}</span>
                    </div>

                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <flux:badge size="sm" inset="top bottom" :color="$this->badgeColor('spk_terbit')"
                                class="border border-emerald-200 dark:border-emerald-900/40
                                       bg-emerald-50 text-emerald-700
                                       dark:bg-emerald-900/20 dark:text-emerald-300">
                                SPK Terbit
                            </flux:badge>
                        </div>
                        <span
                            class="font-semibold text-stone-900 dark:text-stone-100">{{ $this->stats['spk_terbit'] }}</span>
                    </div>

                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <flux:badge size="sm" inset="top bottom" :color="$this->badgeColor('ditolak')"
                                class="border border-rose-200 dark:border-rose-900/40
                                       bg-rose-50 text-rose-700
                                       dark:bg-rose-900/20 dark:text-rose-300">
                                Ditolak
                            </flux:badge>
                        </div>
                        <span
                            class="font-semibold text-stone-900 dark:text-stone-100">{{ $this->stats['ditolak'] }}</span>
                    </div>
                </div>
            </flux:card>
        </div>
    </div>

    {{-- TABEL + SEARCH --}}
    <div>
        <flux:card
            class="space-y-4 rounded-xl border
                   bg-white dark:bg-stone-950
                   border-zinc-200 dark:border-stone-800
                   shadow-xs">

            <div
                class="px-4 py-3 border-b
                        bg-indigo-50 text-indigo-700
                        dark:bg-indigo-900/20 dark:text-indigo-300
                        border-indigo-100 dark:border-indigo-900/40
                        rounded-t-xl">
                <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                    <h4 class="text-sm font-medium tracking-wide">Daftar Pengajuan</h4>
                    <div class="flex items-center gap-2">
                        <div class="md:w-80">
                            <flux:input placeholder="Cari judul / lokasi…" wire:model.debounce.500ms="q"
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
                :paginate="$this->orders">

                <flux:table.columns>
                    <flux:table.column class="w-12">#</flux:table.column>

                    <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection"
                        wire:click="sort('created_at')">
                        Tanggal
                    </flux:table.column>

                    <flux:table.column>Judul</flux:table.column>
                    <flux:table.column>Instansi</flux:table.column>

                    <flux:table.column sortable :sorted="$sortBy === 'status'" :direction="$sortDirection"
                        wire:click="sort('status')">
                        Status
                    </flux:table.column>

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
                                <span
                                    class="line-clamp-2 text-stone-900 dark:text-stone-100">{{ $row->judul_kp }}</span>
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                <span class="text-stone-900 dark:text-stone-100">{{ $row->lokasi_kp }}</span>
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge size="sm" inset="top bottom"
                                    :color="$this->badgeColor($row->status)">
                                    {{ $this->statusLabel($row->status) }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell class="max-w-[260px]">
                                @if ($row->status === 'ditolak' && $row->catatan)
                                    <span
                                        class="text-sm text-zinc-700 dark:text-zinc-300 line-clamp-2">{{ $row->catatan }}</span>
                                @else
                                    <span class="text-sm text-zinc-400 dark:text-stone-500">—</span>
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
                                                wire:click="openDetail({{ $row->id }})">Detail</flux:menu.item>
                                        </flux:modal.trigger>

                                        @if ($row->status === 'review_komisi')
                                            <flux:menu.item icon="pencil-square"
                                                wire:click="edit({{ $row->id }})">Edit</flux:menu.item>
                                            <flux:modal.trigger name="delete-kp">
                                                <flux:menu.item icon="trash"
                                                    wire:click="markDelete({{ $row->id }})">Hapus
                                                </flux:menu.item>
                                            </flux:modal.trigger>
                                        @else
                                            <flux:menu.item icon="pencil-square" disabled>Edit</flux:menu.item>
                                            <flux:menu.item icon="trash" disabled>Hapus</flux:menu.item>
                                        @endif

                                        @if ($row->status === 'spk_terbit')
                                            <flux:menu.separator />
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

    {{-- Modal Delete --}}
    <flux:modal name="delete-kp" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg" class="text-stone-900 dark:text-stone-100">Hapus pengajuan?
                </flux:heading>
                <flux:text class="mt-2 text-zinc-700 dark:text-stone-300">
                    Anda akan menghapus pengajuan KP ini.<br> Tindakan ini tidak dapat dibatalkan.
                </flux:text>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button type="button" variant="danger" wire:click="confirmDelete">Hapus</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Modal Detail --}}
    <flux:modal name="detail-kp" :show="$detailId !== null">
        @php $item = $this->selectedItem; @endphp

        <div class="space-y-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <flux:heading size="lg">Detail Pengajuan KP</flux:heading>
                    <p class="text-sm text-zinc-500">Data dan dokumen yang diajukan.</p>
                </div>
            </div>

            @if ($item)
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <flux:card class="space-y-2">
                        <div class="text-sm text-zinc-500">Tanggal Pengajuan</div>
                        <div class="font-medium">{{ optional($item->created_at)->format('d M Y') ?: '—' }}</div>
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
                <div class="text-sm text-rose-600">Data tidak ditemukan.</div>
            @endif

            <div class="flex justify-end">
                <flux:modal.close>
                    <flux:button variant="primary" wire:click="closeDetail">Tutup</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>
