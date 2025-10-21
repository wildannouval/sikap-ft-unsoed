<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Surat Pengantar</h1>
        <flux:button variant="primary" icon="document-plus" wire:click="openForm">Buat Surat Pengantar</flux:button>
    </div>

    @if (session('message'))
        <flux:card class="border border-green-600 text-green-700 dark:text-green-400">
            {{ session('message') }}
        </flux:card>
    @endif

    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <flux:card class="py-3"><div class="text-sm text-zinc-500">Diajukan</div><div class="text-2xl font-semibold">{{ $this->stats['Diajukan'] }}</div></flux:card>
        <flux:card class="py-3"><div class="text-sm text-zinc-500">Disetujui</div><div class="text-2xl font-semibold">{{ $this->stats['Disetujui'] }}</div></flux:card>
        <flux:card class="py-3"><div class="text-sm text-zinc-500">Terbit</div><div class="text-2xl font-semibold">{{ $this->stats['Terbit'] }}</div></flux:card>
        <flux:card class="py-3"><div class="text-sm text-zinc-500">Ditolak</div><div class="text-2xl font-semibold">{{ $this->stats['Ditolak'] }}</div></flux:card>
    </div>

    <flux:card>
        <div class="space-y-1 text-sm">
            <div><flux:badge variant="info">Diajukan</flux:badge> menunggu verifikasi Bapendik.</div>
            <div><flux:badge variant="success">Disetujui</flux:badge> menunggu terbit nomor surat.</div>
            <div><flux:badge variant="success">Terbit</flux:badge> surat siap diambil.</div>
            <div><flux:badge variant="danger">Ditolak</flux:badge> periksa catatan perbaikan.</div>
        </div>
    </flux:card>

    @if($showForm)
        <flux:card>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <flux:input label="Lokasi / Perusahaan" wire:model.defer="lokasi_surat_pengantar" placeholder="PT Contoh Sejahtera" :invalid="$errors->has('lokasi_surat_pengantar')" />
                    @if($errors->has('lokasi_surat_pengantar')) <p class="mt-1 text-sm text-red-600">{{ $errors->first('lokasi_surat_pengantar') }}</p> @endif
                </div>
                <div>
                    <flux:input label="Nama Penerima" wire:model.defer="penerima_surat_pengantar" placeholder="HRD / Bapak/Ibu ..." :invalid="$errors->has('penerima_surat_pengantar')" />
                    @if($errors->has('penerima_surat_pengantar')) <p class="mt-1 text-sm text-red-600">{{ $errors->first('penerima_surat_pengantar') }}</p> @endif
                </div>
                <div class="md:col-span-2">
                    <flux:textarea label="Alamat Perusahaan" wire:model.defer="alamat_surat_pengantar" placeholder="Jl. Contoh No. 1, Kota ..." rows="3" :invalid="$errors->has('alamat_surat_pengantar')" />
                    @if($errors->has('alamat_surat_pengantar')) <p class="mt-1 text-sm text-red-600">{{ $errors->first('alamat_surat_pengantar') }}</p> @endif
                </div>
                <div>
                    <flux:input label="Tembusan (opsional)" wire:model.defer="tembusan_surat_pengantar" placeholder="Dekan / Jurusan ..." />
                </div>
            </div>
            <div class="mt-4 flex gap-3">
                <flux:button variant="primary" wire:click="submit" icon="paper-airplane">Ajukan</flux:button>
                <flux:button variant="ghost" wire:click="closeForm" icon="x-mark">Batal</flux:button>
            </div>
        </flux:card>
    @endif

    <flux:card>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-left">
                <tr class="border-b">
                    <th class="py-2 pe-4">#</th>
                    <th class="py-2 pe-4">Tanggal</th>
                    <th class="py-2 pe-4">Perusahaan</th>
                    <th class="py-2 pe-4">Penerima</th>
                    <th class="py-2 pe-4">Status</th>
                </tr>
                </thead>
                <tbody>
                @forelse($items as $i => $row)
                    <tr class="border-b last:border-0">
                        <td class="py-2 pe-4">{{ $items->firstItem() + $i }}</td>
                        <td class="py-2 pe-4">{{ optional($row->tanggal_pengajuan_surat_pengantar)->format('d M Y') }}</td>
                        <td class="py-2 pe-4">{{ $row->lokasi_surat_pengantar }}</td>
                        <td class="py-2 pe-4">{{ $row->penerima_surat_pengantar }}</td>
                        <td class="py-2 pe-4">
                            @php
                                $status = $row->status_surat_pengantar;
                                $variant = match($status) {
                                    'Diajukan'  => 'info',
                                    'Disetujui' => 'success',
                                    'Terbit'    => 'success',
                                    'Ditolak'   => 'danger',
                                    default     => 'neutral',
                                };
                            @endphp
                            <flux:badge :variant="$variant">{{ $status }}</flux:badge>
                        </td>
                    </tr>
                @empty
                    <tr><td class="py-6 text-center text-zinc-500" colspan="5">Belum ada pengajuan surat pengantar.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $items->links() }}</div>
    </flux:card>
</div>
