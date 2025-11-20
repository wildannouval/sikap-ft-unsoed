<div class="space-y-6">

    {{-- BARIS ATAS: FORM (kiri) + RINGKASAN (kanan) --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-10">
        {{-- FORM --}}
        <div class="lg:col-span-6">
            <flux:card class="space-y-6 bg-zinc-50">
                <div>
                    <h3 class="text-base font-semibold">Pengajuan Surat Pengantar</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-300">Isi data di bawah lalu ajukan ke Bapendik.</p>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <flux:input label="Perusahaan / Instansi" wire:model.defer="lokasi_surat_pengantar"
                            placeholder="Nama perusahaan / instansi" :invalid="$errors->has('lokasi_surat_pengantar')" />
                    </div>

                    <div>
                        <flux:input label="Penerima Surat" wire:model.defer="penerima_surat_pengantar"
                            placeholder="Yth. Bapak/Ibu ..." :invalid="$errors->has('penerima_surat_pengantar')" />
                    </div>

                    <div class="md:col-span-2">
                        <flux:textarea label="Alamat Perusahaan" wire:model.defer="alamat_surat_pengantar"
                            placeholder="Jl. Contoh No. 1, Kota ..." rows="auto"
                            :invalid="$errors->has('alamat_surat_pengantar')" />
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
            <flux:card class="space-y-4 bg-zinc-50">
                <div>
                    <h3 class="text-base font-semibold">Ringkasan Status</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-300">Rekap pengajuan & arti tiap status</p>
                </div>

                <div class="space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <flux:badge size="sm" inset="top bottom" :color="$this->badgeColor('Diajukan')">
                                Diajukan</flux:badge>
                            <p class="text-sm text-left text-zinc-600 dark:text-zinc-300">Menunggu verifikasi &
                                penerbitan oleh Bapendik.</p>
                        </div>
                        <span class="font-semibold">{{ $this->stats['Diajukan'] }}</span>
                    </div>

                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <flux:badge size="sm" inset="top bottom" :color="$this->badgeColor('Diterbitkan')">
                                Diterbitkan</flux:badge>
                            <p class="text-sm text-left text-zinc-600 dark:text-zinc-300">Surat sudah terbit & siap
                                diunduh.</p>
                        </div>
                        <span class="font-semibold">{{ $this->stats['Diterbitkan'] }}</span>
                    </div>

                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <flux:badge size="sm" inset="top bottom" :color="$this->badgeColor('Ditolak')">Ditolak
                            </flux:badge>
                            <p class="text-sm text-left text-zinc-600 dark:text-zinc-300">Silakan perbaiki sesuai
                                catatan.</p>
                        </div>
                        <span class="font-semibold">{{ $this->stats['Ditolak'] }}</span>
                    </div>
                </div>
            </flux:card>
        </div>
    </div>

    {{-- BARIS BAWAH: TABEL (full width) --}}
    <div>
        <flux:card>
            <flux:table :paginate="$this->orders">
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

                            <flux:table.cell class="whitespace-nowrap">{{ $row->lokasi_surat_pengantar }}
                            </flux:table.cell>
                            <flux:table.cell class="whitespace-nowrap">{{ $row->penerima_surat_pengantar }}
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge size="sm" inset="top bottom"
                                    :color="$this->badgeColor($row->status_surat_pengantar)">
                                    {{ $row->status_surat_pengantar }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell class="max-w-[260px]">
                                @if ($row->status_surat_pengantar === 'Ditolak' && $row->catatan_surat)
                                    <span class="text-sm text-zinc-700 line-clamp-2">{{ $row->catatan_surat }}</span>
                                @elseif ($row->status_surat_pengantar === 'Ditolak')
                                    <span class="text-sm text-zinc-500 italic">Tidak ada catatan</span>
                                @else
                                    <span class="text-sm text-zinc-400">—</span>
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

    <flux:modal name="delete-sp" class="min-w-[22rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Hapus pengajuan?</flux:heading>
                <flux:text class="mt-2">
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
