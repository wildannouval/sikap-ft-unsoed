<div class="space-y-6">

    {{-- BARIS ATAS: FORM (kiri) + RINGKASAN (kanan) --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-10">

        {{-- FORM --}}
        <div class="lg:col-span-6">
            <flux:card
                class="space-y-6 rounded-xl border
                              bg-white dark:bg-stone-950
                              border-zinc-200 dark:border-stone-800
                              shadow-xs">
                {{-- Header kartu dengan aksen indigo (seragam dgn dashboard) --}}
                <div class="flex items-center gap-2 px-1.5 -mt-1">
                    <span
                        class="inline-flex items-center justify-center rounded-md p-1.5
                                 bg-indigo-500 text-white dark:bg-indigo-400">
                        <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <path d="M14 2v6h6" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-base font-semibold text-stone-900 dark:text-stone-100">
                            Pengajuan Surat Pengantar
                        </h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-300">
                            Isi data di bawah lalu ajukan ke Bapendik.
                        </p>
                    </div>
                </div>

                <flux:separator />

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <flux:input label="Perusahaan / Instansi" wire:model.defer="lokasi_surat_pengantar"
                            placeholder="Nama perusahaan / instansi"
                            :invalid="$errors->has('lokasi_surat_pengantar')" />
                        @error('lokasi_surat_pengantar')
                            <div class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <flux:input label="Penerima Surat" wire:model.defer="penerima_surat_pengantar"
                            placeholder="Yth. Bapak/Ibu ..." :invalid="$errors->has('penerima_surat_pengantar')" />
                        @error('penerima_surat_pengantar')
                            <div class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <flux:textarea label="Alamat Perusahaan" wire:model.defer="alamat_surat_pengantar"
                            placeholder="Jl. Contoh No. 1, Kota ..." rows="auto"
                            :invalid="$errors->has('alamat_surat_pengantar')" />
                        @error('alamat_surat_pengantar')
                            <div class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <flux:input label="Tembusan (opsional)" wire:model.defer="tembusan_surat_pengantar"
                            placeholder="Dekan / Jurusan ..." />
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
                            <flux:badge size="sm" inset="top bottom" :color="$this->badgeColor('Diajukan')"
                                class="border border-indigo-200 dark:border-indigo-900/40
                                       bg-indigo-50 text-indigo-700
                                       dark:bg-indigo-900/20 dark:text-indigo-300">
                                Diajukan
                            </flux:badge>
                            <p class="text-sm text-left text-zinc-600 dark:text-zinc-300">
                                Menunggu verifikasi & penerbitan oleh Bapendik.
                            </p>
                        </div>
                        <span
                            class="font-semibold text-stone-900 dark:text-stone-100">{{ $this->stats['Diajukan'] }}</span>
                    </div>

                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <flux:badge size="sm" inset="top bottom" :color="$this->badgeColor('Diterbitkan')"
                                class="border border-emerald-200 dark:border-emerald-900/40
                                       bg-emerald-50 text-emerald-700
                                       dark:bg-emerald-900/20 dark:text-emerald-300">
                                Diterbitkan
                            </flux:badge>
                            <p class="text-sm text-left text-zinc-600 dark:text-zinc-300">
                                Surat sudah terbit & siap diunduh.
                            </p>
                        </div>
                        <span
                            class="font-semibold text-stone-900 dark:text-stone-100">{{ $this->stats['Diterbitkan'] }}</span>
                    </div>

                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <flux:badge size="sm" inset="top bottom" :color="$this->badgeColor('Ditolak')"
                                class="border border-rose-200 dark:border-rose-900/40
                                       bg-rose-50 text-rose-700
                                       dark:bg-rose-900/20 dark:text-rose-300">
                                Ditolak
                            </flux:badge>
                            <p class="text-sm text-left text-zinc-600 dark:text-zinc-300">
                                Silakan perbaiki sesuai catatan.
                            </p>
                        </div>
                        <span
                            class="font-semibold text-stone-900 dark:text-stone-100">{{ $this->stats['Ditolak'] }}</span>
                    </div>
                </div>
            </flux:card>
        </div>
    </div>

    {{-- BARIS BAWAH: TABEL --}}
    <div>
        <flux:card
            class="rounded-xl border
                         bg-white dark:bg-stone-950
                         border-zinc-200 dark:border-stone-800
                         shadow-xs">
            <div
                class="px-4 py-3 border-b
                        bg-indigo-50 text-indigo-700
                        dark:bg-indigo-900/20 dark:text-indigo-300
                        border-indigo-100 dark:border-indigo-900/40
                        rounded-t-xl">
                <h4 class="text-sm font-medium tracking-wide">Daftar Pengajuan</h4>
            </div>

            <flux:table
                class="[&_thead_th]:bg-zinc-50 [&_thead_th]:dark:bg-stone-900/40
                       [&_thead_th]:text-zinc-600 [&_thead_th]:dark:text-stone-200
                       [&_tbody_tr]:hover:bg-zinc-50/60 [&_tbody_tr]:dark:hover:bg-stone-900/30"
                :paginate="$this->orders">
                <flux:table.columns>
                    <flux:table.column class="w-12">#</flux:table.column>

                    <flux:table.column sortable :sorted="$sortBy === 'tanggal_pengajuan_surat_pengantar'"
                        :direction="$sortDirection" wire:click="sort('tanggal_pengajuan_surat_pengantar')">
                        Tanggal
                    </flux:table.column>

                    <flux:table.column>Perusahaan</flux:table.column>
                    <flux:table.column>Penerima</flux:table.column>

                    <flux:table.column sortable :sorted="$sortBy === 'status_surat_pengantar'"
                        :direction="$sortDirection" wire:click="sort('status_surat_pengantar')">
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
                                {{ optional($row->tanggal_pengajuan_surat_pengantar)->format('d M Y') ?: '—' }}
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                <span
                                    class="text-stone-900 dark:text-stone-100">{{ $row->lokasi_surat_pengantar }}</span>
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                <span
                                    class="text-stone-900 dark:text-stone-100">{{ $row->penerima_surat_pengantar }}</span>
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge size="sm" inset="top bottom"
                                    :color="$this->badgeColor($row->status_surat_pengantar)"
                                    class="border
                                           dark:border-opacity-40
                                           @if ($row->status_surat_pengantar === 'Diajukan')
border-indigo-200 dark:border-indigo-900/40 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-300
@elseif($row->status_surat_pengantar === 'Diterbitkan')
border-emerald-200 dark:border-emerald-900/40 bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300
@elseif($row->status_surat_pengantar === 'Ditolak')
border-rose-200 dark:border-rose-900/40 bg-rose-50 text-rose-700 dark:bg-rose-900/20 dark:text-rose-300
@else
border-zinc-200 dark:border-stone-700 bg-zinc-100 text-zinc-700 dark:bg-stone-900/30 dark:text-stone-200
@endif">
                                    {{ $row->status_surat_pengantar }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell class="max-w-[320px]">
                                @if ($row->status_surat_pengantar === 'Ditolak' && $row->catatan_surat)
                                    <span
                                        class="text-sm text-zinc-700 dark:text-stone-200 line-clamp-2">{{ $row->catatan_surat }}</span>
                                @elseif ($row->status_surat_pengantar === 'Ditolak')
                                    <span class="text-sm text-zinc-500 dark:text-stone-400 italic">Tidak ada
                                        catatan</span>
                                @else
                                    <span class="text-sm text-zinc-400 dark:text-stone-500">—</span>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:dropdown position="bottom" align="end">
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                        inset="top bottom"></flux:button>
                                    <flux:menu class="min-w-40">
                                        <flux:menu.item icon="pencil-square" wire:click="edit({{ $row->id }})">
                                            Edit
                                        </flux:menu.item>

                                        @if ($row->status_surat_pengantar === 'Diajukan')
                                            <flux:modal.trigger name="delete-sp">
                                                <flux:menu.item icon="trash"
                                                    wire:click="markDelete({{ $row->id }})">
                                                    Hapus
                                                </flux:menu.item>
                                            </flux:modal.trigger>
                                        @else
                                            <flux:menu.item icon="trash" disabled>Hapus</flux:menu.item>
                                        @endif

                                        @if ($row->status_surat_pengantar === 'Diterbitkan')
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
        </flux:card>
    </div>

    {{-- Modal konfirmasi hapus --}}
    <flux:modal name="delete-sp" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg" class="text-stone-900 dark:text-stone-100">Hapus pengajuan?
                </flux:heading>
                <flux:text class="mt-2 text-zinc-700 dark:text-stone-300">
                    Anda akan menghapus pengajuan ini.<br>
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

</div>
