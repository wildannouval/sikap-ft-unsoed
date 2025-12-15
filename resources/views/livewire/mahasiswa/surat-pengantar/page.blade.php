<div class="space-y-6">
    {{-- TOAST GLOBAL --}}
    <flux:toast />

    {{-- HEADER HALAMAN --}}
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <flux:heading size="xl" level="1" class="text-stone-900 dark:text-stone-100">
                Pengajuan Surat Pengantar
            </flux:heading>
            <flux:subheading class="text-zinc-600 dark:text-zinc-300">
                Kelola pengajuan surat pengantar KP ke Bapendik.
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
                            Lengkapi data tujuan surat di bawah ini.
                        </p>
                    </div>
                </div>

                <flux:separator />

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    {{-- Perusahaan --}}
                    <div>
                        <flux:input label="Perusahaan / Instansi" wire:model.defer="lokasi_surat_pengantar"
                            placeholder="Contoh: PT. Telkom Indonesia"
                            :invalid="$errors->has('lokasi_surat_pengantar')" />
                        {{-- @error('lokasi_surat_pengantar')
                            <div class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</div>
                        @enderror --}}
                    </div>

                    {{-- Penerima --}}
                    <div>
                        <flux:input label="Penerima Surat" wire:model.defer="penerima_surat_pengantar"
                            placeholder="Contoh: Yth. HRD Manager"
                            :invalid="$errors->has('penerima_surat_pengantar')" />
                        {{--  @error('penerima_surat_pengantar')
                            <div class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</div>
                        @enderror --}}
                    </div>

                    {{-- Alamat (Full Width) --}}
                    <div class="md:col-span-2">
                        <flux:textarea label="Alamat Lengkap Perusahaan" wire:model.defer="alamat_surat_pengantar"
                            placeholder="Jl. Jendral Sudirman No. 1, Jakarta Pusat..." rows="3" resize="none"
                            :invalid="$errors->has('alamat_surat_pengantar')" />
                        {{-- @error('alamat_surat_pengantar')
                            <div class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</div>
                        @enderror --}}
                    </div>

                    {{-- Tembusan --}}
                    <div class="md:col-span-2">
                        <flux:input label="Tembusan (Opsional)" wire:model.defer="tembusan_surat_pengantar"
                            placeholder="Contoh: Dekan Fakultas Teknik / Ketua Jurusan..." />
                        <flux:subheading class="mt-1 text-xs">Kosongkan jika tidak ada.</flux:subheading>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    @if ($editingId)
                        <flux:button variant="subtle" wire:click="cancelEdit">Batal</flux:button>
                        <flux:button variant="primary" icon="check" wire:click="update">Simpan Perubahan</flux:button>
                    @else
                        <flux:button variant="primary" icon="paper-airplane" wire:click="submit"
                            class="w-full md:w-auto">
                            Ajukan Surat
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
                        Ringkasan surat berdasarkan status terkini.
                    </p>
                </div>

                <div class="space-y-3">
                    {{-- Status: Diajukan --}}
                    <div
                        class="flex items-center justify-between p-2 rounded-lg bg-indigo-50/50 dark:bg-indigo-900/10 border border-indigo-100 dark:border-indigo-800/30">
                        <div class="flex items-center gap-3">
                            <div class="size-2 rounded-full bg-indigo-500"></div>
                            <div>
                                <p class="text-sm font-medium text-stone-700 dark:text-stone-300">Diajukan</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Menunggu verifikasi</p>
                            </div>
                        </div>
                        <span class="text-lg font-bold text-indigo-600 dark:text-indigo-400">
                            {{ $this->stats['Diajukan'] ?? 0 }}
                        </span>
                    </div>

                    {{-- Status: Diterbitkan --}}
                    <div
                        class="flex items-center justify-between p-2 rounded-lg bg-emerald-50/50 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-800/30">
                        <div class="flex items-center gap-3">
                            <div class="size-2 rounded-full bg-emerald-500"></div>
                            <div>
                                <p class="text-sm font-medium text-stone-700 dark:text-stone-300">Diterbitkan</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">Siap diunduh</p>
                            </div>
                        </div>
                        <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                            {{ $this->stats['Diterbitkan'] ?? 0 }}
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
                            {{ $this->stats['Ditolak'] ?? 0 }}
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
                            <li>Isi form sesuai tujuan KP.</li>
                            <li>Status <strong>Diajukan</strong>: Menunggu admin.</li>
                            <li>Status <strong>Ditolak</strong>: Cek catatan & perbaiki.</li>
                            <li>Status <strong>Terbit</strong>: Unduh surat (DOCX).</li>
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
            <h4 class="text-base font-semibold text-stone-900 dark:text-stone-100">Riwayat Pengajuan</h4>

            <div class="flex flex-col gap-3 md:flex-row md:items-center">
                {{-- Search --}}
                <div class="w-full md:w-64">
                    <flux:input icon="magnifying-glass" placeholder="Cari Instansi / Penerima..."
                        wire:model.live.debounce.300ms="search" class="bg-white dark:bg-stone-900" />
                </div>

                {{-- Filter --}}
                <div class="w-full md:w-48">
                    <flux:select wire:model.live="filterStatus" placeholder="Semua Status"
                        class="bg-white dark:bg-stone-900">
                        <flux:select.option value="">Semua Status</flux:select.option>
                        <flux:select.option value="Diajukan">Diajukan</flux:select.option>
                        <flux:select.option value="Diterbitkan">Diterbitkan</flux:select.option>
                        <flux:select.option value="Ditolak">Ditolak</flux:select.option>
                    </flux:select>
                </div>
            </div>
        </div>

        <flux:table class="[&_thead_th]:bg-zinc-50 [&_thead_th]:dark:bg-stone-900/40" :paginate="$this->orders">
            <flux:table.columns>
                <flux:table.column class="w-12 text-center">No</flux:table.column>

                <flux:table.column sortable :sorted="$sortBy === 'tanggal_pengajuan_surat_pengantar'"
                    :direction="$sortDirection" wire:click="sort('tanggal_pengajuan_surat_pengantar')">
                    Tanggal
                </flux:table.column>

                <flux:table.column>Perusahaan</flux:table.column>
                <flux:table.column>Penerima</flux:table.column>

                <flux:table.column sortable :sorted="$sortBy === 'status_surat_pengantar'" :direction="$sortDirection"
                    wire:click="sort('status_surat_pengantar')">
                    Status
                </flux:table.column>

                <flux:table.column>Catatan</flux:table.column>
                <flux:table.column class="w-20 text-center">Aksi</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->orders as $i => $row)
                    @php
                        $status = $row->status_surat_pengantar;
                        $badgeTheme = match ($status) {
                            'Diajukan' => ['color' => 'indigo', 'icon' => 'clock'],
                            'Diterbitkan' => ['color' => 'emerald', 'icon' => 'check-circle'],
                            'Ditolak' => ['color' => 'rose', 'icon' => 'x-circle'],
                            default => ['color' => 'zinc', 'icon' => 'minus'],
                        };
                    @endphp

                    <flux:table.row :key="$row->id">
                        <flux:table.cell class="text-center text-zinc-500">
                            {{ $this->orders->firstItem() + $i }}
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap">
                            <div class="font-medium text-stone-900 dark:text-stone-100">
                                {{ optional($row->tanggal_pengajuan_surat_pengantar)->translatedFormat('d M Y') ?: '-' }}
                            </div>
                            <div class="text-xs text-zinc-500">
                                {{ optional($row->created_at)->format('H:i') }} WIB
                            </div>
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap">
                            <span class="text-stone-900 dark:text-stone-100">{{ $row->lokasi_surat_pengantar }}</span>
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap">
                            <span
                                class="text-stone-900 dark:text-stone-100">{{ $row->penerima_surat_pengantar }}</span>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge size="sm" inset="top bottom" :color="$badgeTheme['color']"
                                icon="{{ $badgeTheme['icon'] }}">
                                {{ $status }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell class="max-w-[250px]">
                            @if ($status === 'Ditolak' && $row->catatan_surat)
                                <div
                                    class="flex items-start gap-1.5 text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/10 p-1.5 rounded-md border border-rose-100 dark:border-rose-900/20">
                                    <flux:icon.exclamation-triangle class="size-4 shrink-0 mt-0.5" />
                                    <span class="text-xs leading-snug line-clamp-2">{{ $row->catatan_surat }}</span>
                                </div>
                            @elseif ($status === 'Ditolak')
                                <span class="text-xs text-zinc-400 italic">Tanpa catatan</span>
                            @else
                                <span class="text-zinc-400 dark:text-stone-500">—</span>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:dropdown position="bottom" align="end">
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />

                                <flux:menu class="min-w-40">
                                    <flux:menu.item icon="pencil-square" wire:click="edit({{ $row->id }})">
                                        Edit
                                    </flux:menu.item>

                                    @if ($status === 'Diajukan')
                                        <flux:modal.trigger name="delete-sp">
                                            <flux:menu.item icon="trash" variant="danger"
                                                wire:click="markDelete({{ $row->id }})">
                                                Hapus
                                            </flux:menu.item>
                                        </flux:modal.trigger>
                                    @else
                                        <flux:menu.item icon="trash" disabled>Hapus</flux:menu.item>
                                    @endif

                                    @if ($status === 'Diterbitkan')
                                        <flux:menu.separator />
                                        <flux:menu.item icon="arrow-down-tray"
                                            href="{{ route('mhs.sp.download.docx', $row) }}" target="_blank">
                                            Unduh DOCX
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
                        Belum ada pengajuan
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
    <flux:modal name="delete-sp" class="md:w-96">
        <div class="space-y-6">
            <div class="text-center">
                <div
                    class="mx-auto mb-4 flex size-12 items-center justify-center rounded-full bg-rose-100 dark:bg-rose-900/30">
                    <flux:icon.trash class="size-6 text-rose-600 dark:text-rose-400" />
                </div>
                <flux:heading size="lg">Hapus Pengajuan?</flux:heading>
                <flux:subheading class="mt-2">
                    Data yang dihapus tidak dapat dikembalikan lagi.
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
</div>
