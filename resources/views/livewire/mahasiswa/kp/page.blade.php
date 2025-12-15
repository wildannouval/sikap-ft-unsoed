<div class="space-y-6">
    {{-- TOAST GLOBAL --}}
    <flux:toast />

    {{-- HEADER HALAMAN --}}
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <flux:heading size="xl" level="1" class="text-stone-900 dark:text-stone-100">
                Pengajuan Kerja Praktik
            </flux:heading>
            <flux:subheading class="text-zinc-600 dark:text-zinc-300">
                Kelola pengajuan KP mahasiswa.
            </flux:subheading>
        </div>
    </div>

    <flux:separator variant="subtle" />

    {{-- GRID UTAMA --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- KOLOM KIRI: FORM --}}
        <div class="lg:col-span-2 space-y-6">
            <flux:card
                class="space-y-6 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm">

                {{-- Header Kartu Form --}}
                <div class="flex items-center gap-3">
                    <div
                        class="flex items-center justify-center rounded-lg p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                        <flux:icon.pencil-square class="size-5" />
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-stone-900 dark:text-stone-100">
                            Formulir Pengajuan
                        </h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            Lengkapi data KP &amp; upload dokumen.
                        </p>
                    </div>
                </div>

                <flux:separator />

                {{-- Ambil dari SP terbit --}}
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div class="w-full sm:w-3/4">
                        <flux:select wire:model="selectedSpId" label="Ambil dari SP terbit (Otomatis isi lokasi)">
                            <option value="">— Pilih Surat Pengantar —</option>
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
                    <flux:button class="w-full sm:w-auto" size="sm" variant="outline"
                        icon="arrow-path-rounded-square" wire:click="fillFromSP">
                        Ambil Data
                    </flux:button>
                </div>

                {{-- Fields --}}
                <div class="grid grid-cols-1 gap-5">
                    <div>
                        <flux:input label="Judul Kerja Praktik" wire:model.defer="judul_kp"
                            placeholder="Contoh: Perancangan Sistem X di PT Y..." :invalid="$errors->has('judul_kp')" />
                        {{-- @error('judul_kp')
                            <div class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</div>
                        @enderror --}}
                    </div>

                    <div>
                        <flux:input label="Instansi / Lokasi KP" wire:model.defer="lokasi_kp"
                            placeholder="Nama perusahaan / instansi" :invalid="$errors->has('lokasi_kp')" />
                        {{-- @error('lokasi_kp')
                            <div class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</div>
                        @enderror --}}
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        {{-- Upload Proposal --}}
                        <div>
                            <flux:input wire:model="proposal_kp" type="file" label="Proposal (PDF, maks 2MB)"
                                required />
                            <div class="text-xs text-zinc-500 mt-1" wire:loading wire:target="proposal_kp">
                                Mengunggah proposal…
                            </div>
                            {{-- @error('proposal_kp')
                                <div class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</div>
                            @enderror --}}

                            @if ($proposal_kp && !$errors->has('proposal_kp'))
                                <div
                                    class="mt-2 flex items-center justify-between rounded-lg border bg-zinc-50 p-2 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                                    <span class="truncate">{{ $proposal_kp->getClientOriginalName() }}</span>
                                    <button type="button" wire:click="removeProposal"
                                        class="text-rose-500 hover:text-rose-700 font-bold text-lg flex-shrink-0 ml-2">
                                        &times;
                                    </button>
                                </div>
                            @endif
                        </div>

                        {{-- Upload Surat Diterima / Bukti --}}
                        <div>
                            <flux:input wire:model="surat_keterangan_kp" type="file"
                                label="Bukti Diterima (PDF/Img, maks 2MB)" required />
                            <div class="text-xs text-zinc-500 mt-1" wire:loading wire:target="surat_keterangan_kp">
                                Mengunggah bukti…
                            </div>
                            {{-- @error('surat_keterangan_kp')
                                <div class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</div>
                            @enderror --}}

                            @if ($surat_keterangan_kp && !$errors->has('surat_keterangan_kp'))
                                <div
                                    class="mt-2 flex items-center justify-between rounded-lg border bg-zinc-50 p-2 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                                    <span class="truncate">{{ $surat_keterangan_kp->getClientOriginalName() }}</span>
                                    <button type="button" wire:click="removeSuratKeterangan"
                                        class="text-rose-500 hover:text-rose-700 font-bold text-lg flex-shrink-0 ml-2">
                                        &times;
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    @if ($editingId)
                        <flux:button variant="subtle" wire:click="cancelEdit">Batal</flux:button>
                        <flux:button variant="primary" icon="check" wire:click="update">Simpan Perubahan</flux:button>
                    @else
                        <flux:button variant="primary" icon="paper-airplane" wire:click="submit"
                            class="w-full md:w-auto">
                            Ajukan KP
                        </flux:button>
                    @endif
                </div>
            </flux:card>
        </div>

        {{-- KOLOM KANAN: INFO --}}
        <div class="lg:col-span-1 space-y-6">

            {{-- 1. RINGKASAN STATUS --}}
            <flux:card
                class="rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm">
                <div class="mb-5">
                    <div class="flex items-center gap-2">
                        <flux:icon.chart-bar class="size-5 text-zinc-500" />
                        <h3 class="font-semibold text-stone-900 dark:text-stone-100">Status Pengajuan</h3>
                    </div>
                    <p class="mt-1.5 text-xs text-zinc-500 dark:text-zinc-400 pl-7">
                        Pantau progres tahapan KP Anda di sini.
                    </p>
                </div>

                <div class="space-y-3">
                    {{-- Status: Review Komisi --}}
                    <div
                        class="flex items-center justify-between p-2 rounded-lg bg-indigo-50/50 dark:bg-indigo-900/10 border border-indigo-100 dark:border-indigo-800/30">
                        <div class="flex items-center gap-3">
                            <div class="size-2 rounded-full bg-indigo-500"></div>
                            <div>
                                <p class="text-sm font-medium text-stone-700 dark:text-stone-300">Review Komisi</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Menunggu persetujuan</p>
                            </div>
                        </div>
                        <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400">
                            {{ $this->stats['review_komisi'] ?? 0 }}
                        </span>
                    </div>

                    {{-- Status: Review Bapendik --}}
                    <div
                        class="flex items-center justify-between p-2 rounded-lg bg-sky-50/50 dark:bg-sky-900/10 border border-sky-100 dark:border-sky-800/30">
                        <div class="flex items-center gap-3">
                            <div class="size-2 rounded-full bg-sky-500"></div>
                            <div>
                                <p class="text-sm font-medium text-stone-700 dark:text-stone-300">Tunggu SPK</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Proses administrasi</p>
                            </div>
                        </div>
                        <span class="text-lg font-bold text-sky-600 dark:text-sky-400">
                            {{ $this->stats['review_bapendik'] ?? 0 }}
                        </span>
                    </div>

                    {{-- Status: SPK Terbit --}}
                    <div
                        class="flex items-center justify-between p-2 rounded-lg bg-emerald-50/50 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-800/30">
                        <div class="flex items-center gap-3">
                            <div class="size-2 rounded-full bg-emerald-500"></div>
                            <div>
                                <p class="text-sm font-medium text-stone-700 dark:text-stone-300">SPK Terbit</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Siap diunduh</p>
                            </div>
                        </div>
                        <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                            {{ $this->stats['spk_terbit'] ?? 0 }}
                        </span>
                    </div>

                    {{-- Status: Ditolak --}}
                    <div
                        class="flex items-center justify-between p-2 rounded-lg bg-rose-50/50 dark:bg-rose-900/10 border border-rose-100 dark:border-rose-800/30">
                        <div class="flex items-center gap-3">
                            <div class="size-2 rounded-full bg-rose-500"></div>
                            <div>
                                <p class="text-sm font-medium text-stone-700 dark:text-stone-300">Ditolak</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Perlu perbaikan</p>
                            </div>
                        </div>
                        <span class="text-lg font-bold text-rose-600 dark:text-rose-400">
                            {{ $this->stats['ditolak'] ?? 0 }}
                        </span>
                    </div>
                </div>
            </flux:card>

            {{-- 2. PANDUAN --}}
            <flux:card
                class="rounded-xl border bg-sky-50/50 dark:bg-sky-900/10 border-sky-100 dark:border-sky-800/30 shadow-sm">
                <div class="flex items-start gap-3">
                    <flux:icon.information-circle class="mt-0.5 size-5 text-sky-600 dark:text-sky-400" />
                    <div>
                        <h3 class="font-semibold text-sky-900 dark:text-sky-100 text-sm">Panduan Singkat</h3>
                        <ul
                            class="mt-2 text-xs text-sky-800 dark:text-sky-200 list-disc list-outside ml-3 space-y-1.5 leading-relaxed">
                            <li>Pastikan punya SP status <strong>Diterbitkan</strong>.</li>
                            <li>Gunakan fitur "Ambil" untuk isi otomatis.</li>
                            <li>Upload <strong>Proposal</strong> & <strong>Bukti Diterima</strong>.</li>
                            <li>Jika <strong>Ditolak</strong>, cek catatan & perbaiki.</li>
                            <li>Jika <strong>SPK Terbit</strong>, unduh dokumen di tabel.</li>
                        </ul>
                    </div>
                </div>
            </flux:card>
        </div>
    </div>

    {{-- BARIS BAWAH: TABEL --}}
    <flux:card
        class="rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm overflow-hidden">

        <div
            class="flex flex-col gap-4 px-6 py-4 border-b border-zinc-200 dark:border-stone-800 bg-zinc-50/50 dark:bg-stone-900/50 md:flex-row md:items-center md:justify-between">
            <h4 class="text-base font-semibold text-stone-900 dark:text-stone-100">Riwayat Pengajuan KP</h4>

            <div class="flex flex-col gap-3 md:flex-row md:items-center">
                {{-- Search --}}
                <div class="w-full md:w-64">
                    <flux:input icon="magnifying-glass" placeholder="Cari judul / instansi..."
                        wire:model.live.debounce.300ms="search" class="bg-white dark:bg-stone-900" />
                </div>

                {{-- Filter --}}
                <div class="w-full md:w-56">
                    <flux:select wire:model.live="filterStatus" placeholder="Semua Status"
                        class="bg-white dark:bg-stone-900">
                        <flux:select.option value="">Semua Status</flux:select.option>
                        @foreach ($this->statusOptions as $option)
                            <flux:select.option value="{{ $option['value'] }}">
                                {{ $option['label'] }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
            </div>
        </div>

        <flux:table class="[&_thead_th]:bg-zinc-50 [&_thead_th]:dark:bg-stone-900/40" :paginate="$this->orders">
            <flux:table.columns>
                <flux:table.column class="w-12 text-center">No</flux:table.column>

                <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection"
                    wire:click="sort('created_at')">
                    Tanggal
                </flux:table.column>

                <flux:table.column>Judul & Instansi</flux:table.column>

                <flux:table.column sortable :sorted="$sortBy === 'status'" :direction="$sortDirection"
                    wire:click="sort('status')">
                    Status
                </flux:table.column>

                <flux:table.column>Catatan</flux:table.column>
                <flux:table.column class="w-20 text-center">Aksi</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->orders as $i => $row)
                    {{-- Updated Logic Badge with Icon --}}
                    @php
                        $status = $row->status;
                        $badgeTheme = match ($status) {
                            'review_komisi' => ['color' => 'indigo', 'icon' => 'clock'],
                            'review_bapendik' => ['color' => 'sky', 'icon' => 'clock'],
                            'spk_terbit' => ['color' => 'emerald', 'icon' => 'check-circle'],
                            'ditolak' => ['color' => 'rose', 'icon' => 'x-circle'],
                            default => ['color' => 'zinc', 'icon' => 'minus'],
                        };
                    @endphp

                    <flux:table.row :key="$row->id">
                        <flux:table.cell class="text-center text-zinc-500">
                            {{ $this->orders->firstItem() + $i }}
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap">
                            <div class="font-medium text-stone-900 dark:text-stone-100">
                                {{ optional($row->created_at)->translatedFormat('d M Y') ?: '-' }}
                            </div>
                            <div class="text-xs text-zinc-500">
                                {{ optional($row->created_at)->format('H:i') }} WIB
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="font-medium text-stone-900 dark:text-stone-100 line-clamp-2 leading-snug">
                                {{ $row->judul_kp }}
                            </div>
                            <div class="text-xs text-zinc-500 mt-0.5 flex items-center gap-1">
                                <flux:icon.building-office-2 class="size-3" />
                                {{ $row->lokasi_kp }}
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge size="sm" inset="top bottom" :color="$badgeTheme['color']"
                                icon="{{ $badgeTheme['icon'] }}">
                                {{ $this->statusLabel($status) }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell class="max-w-[200px]">
                            @if ($row->status === 'ditolak' && $row->catatan)
                                <div
                                    class="flex items-start gap-1.5 text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/10 p-1.5 rounded-md border border-rose-100 dark:border-rose-900/20">
                                    <flux:icon.exclamation-triangle class="size-4 shrink-0 mt-0.5" />
                                    <span class="text-xs leading-snug line-clamp-2">{{ $row->catatan }}</span>
                                </div>
                            @elseif ($row->status === 'ditolak')
                                <span class="text-xs text-zinc-400 italic">Tanpa catatan</span>
                            @else
                                <span class="text-zinc-300 dark:text-stone-700">—</span>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:dropdown position="bottom" align="end">
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />

                                <flux:menu class="min-w-40">
                                    <flux:modal.trigger name="detail-kp">
                                        <flux:menu.item icon="eye" wire:click="openDetail({{ $row->id }})">
                                            Detail
                                        </flux:menu.item>
                                    </flux:modal.trigger>

                                    @if ($row->status === 'review_komisi')
                                        <flux:menu.item icon="pencil-square" wire:click="edit({{ $row->id }})">
                                            Edit
                                        </flux:menu.item>
                                        <flux:modal.trigger name="delete-kp">
                                            <flux:menu.item icon="trash" variant="danger"
                                                wire:click="markDelete({{ $row->id }})">
                                                Hapus
                                            </flux:menu.item>
                                        </flux:modal.trigger>
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

        {{-- Empty State --}}
        @if ($this->orders->isEmpty())
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="rounded-full bg-zinc-100 p-4 dark:bg-stone-900">
                    <flux:icon.document-text class="size-8 text-zinc-400" />
                </div>
                <h3 class="mt-4 text-base font-semibold text-stone-900 dark:text-stone-100">
                    @if ($search || $filterStatus)
                        Data tidak ditemukan
                    @else
                        Belum ada pengajuan KP
                    @endif
                </h3>
                <p class="mt-1 text-sm text-zinc-500">
                    @if ($search || $filterStatus)
                        Coba ubah kata kunci atau filter.
                    @else
                        Silakan isi formulir di atas untuk mengajukan.
                    @endif
                </p>
            </div>
        @endif
    </flux:card>

    {{-- Modal Hapus --}}
    <flux:modal name="delete-kp" class="md:w-96">
        <div class="space-y-6">
            <div class="text-center">
                <div
                    class="mx-auto mb-4 flex size-12 items-center justify-center rounded-full bg-rose-100 dark:bg-rose-900/30">
                    <flux:icon.trash class="size-6 text-rose-600 dark:text-rose-400" />
                </div>
                <flux:heading size="lg">Hapus Pengajuan?</flux:heading>
                <flux:subheading class="mt-2">
                    Data KP ini akan dihapus permanen.
                </flux:subheading>
            </div>
            <div class="flex gap-3">
                <flux:modal.close>
                    <flux:button variant="ghost" class="w-full">Batal</flux:button>
                </flux:modal.close>
                <flux:button type="button" variant="danger" wire:click="confirmDelete" class="w-full">
                    Ya, Hapus
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Modal Detail --}}
    <flux:modal name="detail-kp" :show="$detailId !== null" class="md:w-[32rem]">
        @php $item = $this->selectedItem; @endphp

        <div class="space-y-6">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg text-indigo-600 dark:text-indigo-400">
                    <flux:icon.document-text class="size-6" />
                </div>
                <div>
                    <flux:heading size="lg">Detail Pengajuan KP</flux:heading>
                    <p class="text-sm text-zinc-500">Informasi lengkap pengajuan.</p>
                </div>
            </div>

            @if ($item)
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div
                            class="p-3 bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-zinc-100 dark:border-zinc-800">
                            <div class="text-xs text-zinc-500 mb-1">Tanggal</div>
                            <div class="font-medium text-stone-900 dark:text-stone-100">
                                {{ optional($item->created_at)->format('d M Y') ?: '—' }}
                            </div>
                        </div>
                        <div
                            class="p-3 bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-zinc-100 dark:border-zinc-800">
                            <div class="text-xs text-zinc-500 mb-1">Status</div>
                            <flux:badge size="sm" :color="$this->badgeColor($item->status)">
                                {{ $this->statusLabel($item->status) }}
                            </flux:badge>
                        </div>
                    </div>

                    <div
                        class="p-3 bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-zinc-100 dark:border-zinc-800">
                        <div class="text-xs text-zinc-500 mb-1">Judul KP</div>
                        <div class="font-medium text-stone-900 dark:text-stone-100">{{ $item->judul_kp }}</div>
                    </div>

                    <div
                        class="p-3 bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-zinc-100 dark:border-zinc-800">
                        <div class="text-xs text-zinc-500 mb-1">Instansi</div>
                        <div class="font-medium text-stone-900 dark:text-stone-100">{{ $item->lokasi_kp }}</div>
                    </div>

                    <div class="space-y-2">
                        <div class="text-sm font-medium text-stone-900 dark:text-stone-100">Dokumen Lampiran</div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <a href="{{ $item->proposal_path ? asset('storage/' . $item->proposal_path) : '#' }}"
                                target="_blank"
                                class="flex items-center gap-2 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition
                                      {{ !$item->proposal_path ? 'opacity-50 pointer-events-none' : '' }}">
                                <flux:icon.document class="size-5 text-rose-500" />
                                <span class="text-sm truncate">Proposal KP</span>
                            </a>

                            <a href="{{ $item->surat_keterangan_path ? asset('storage/' . $item->surat_keterangan_path) : '#' }}"
                                target="_blank"
                                class="flex items-center gap-2 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition
                                      {{ !$item->surat_keterangan_path ? 'opacity-50 pointer-events-none' : '' }}">
                                <flux:icon.photo class="size-5 text-indigo-500" />
                                <span class="text-sm truncate">Bukti Diterima</span>
                            </a>
                        </div>
                    </div>

                    @if ($item->status === 'ditolak' && $item->catatan)
                        <div
                            class="p-4 bg-rose-50 dark:bg-rose-900/10 rounded-lg border border-rose-100 dark:border-rose-800/30">
                            <div class="flex items-start gap-2">
                                <flux:icon.exclamation-triangle class="size-5 text-rose-600 mt-0.5" />
                                <div>
                                    <div class="font-medium text-rose-700 dark:text-rose-400">Catatan Penolakan</div>
                                    <div class="text-sm text-rose-600 dark:text-rose-300 mt-1">{{ $item->catatan }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <div class="text-center py-8 text-zinc-500">Data tidak ditemukan.</div>
            @endif

            <div class="flex justify-end pt-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Tutup</flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</div>
