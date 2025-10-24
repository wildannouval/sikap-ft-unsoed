<div class="space-y-6">
    <flux:toast />

    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1">Penerbitan SPK (Bapendik)</flux:heading>
            <flux:subheading class="text-zinc-600">
                Terbitkan SPK untuk pengajuan KP yang sudah disetujui Komisi.
            </flux:subheading>
        </div>

        <div class="flex items-center gap-2 w-full md:w-[30rem]">
            <flux:input icon="magnifying-glass" placeholder="Cari judul/instansi/nama/NIM/nomor SPK…"
                wire:model.live.debounce.300ms="search" />
        </div>
    </div>

    <flux:separator variant="subtle" />

    <flux:tab.group wire:model.live="tab">
        <flux:tabs>
            <flux:tab name="pending" icon="inbox-arrow-down">Belum Diterbitkan</flux:tab>
            <flux:tab name="published" icon="check-badge">Sudah Diterbitkan</flux:tab>
        </flux:tabs>

        {{-- PENDING --}}
        <flux:tab.panel name="pending" class="pt-4">
            <flux:card>
                <flux:table :paginate="$this->itemsPending">
                    <flux:table.columns>
                        <flux:table.column class="w-12">#</flux:table.column>
                        <flux:table.column sortable :sorted="$sortBy === 'updated_at'" :direction="$sortDirection"
                            wire:click="sort('updated_at')">Tgl</flux:table.column>
                        <flux:table.column>Mahasiswa</flux:table.column>
                        <flux:table.column>Judul</flux:table.column>
                        <flux:table.column>Instansi</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                        <flux:table.column>Dosen Pembimbing</flux:table.column>
                        <flux:table.column class="w-28 text-right">Aksi</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($this->itemsPending as $i => $row)
                            <flux:table.row :key="'p-'.$row->id">
                                <flux:table.cell>{{ $this->itemsPending->firstItem() + $i }}</flux:table.cell>
                                <flux:table.cell class="whitespace-nowrap">
                                    {{ optional($row->updated_at)->format('d M Y') ?: '—' }}
                                </flux:table.cell>
                                <flux:table.cell class="whitespace-nowrap">
                                    {{ $row->mahasiswa?->user?->name }}
                                    <div class="text-xs text-zinc-500">NIM: {{ $row->mahasiswa?->nim }}</div>
                                </flux:table.cell>
                                <flux:table.cell class="whitespace-nowrap">{{ $row->judul_kp }}</flux:table.cell>
                                <flux:table.cell class="whitespace-nowrap">{{ $row->lokasi_kp }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" inset="top bottom"
                                        :color="$this->badgeColor($row->status)">
                                        {{ $this->statusLabel($row->status) }}
                                    </flux:badge>
                                </flux:table.cell>
                                <flux:table.cell class="whitespace-nowrap">
                                    {{ $row->dosenPembimbing?->nama ?? '—' }}
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:dropdown position="bottom" align="end">
                                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                            inset="top bottom"></flux:button>
                                        <flux:menu class="min-w-52">
                                            <flux:modal.trigger name="detail-spk">
                                                <flux:menu.item icon="eye"
                                                    wire:click="openDetail({{ $row->id }})">
                                                    Detail
                                                </flux:menu.item>
                                            </flux:modal.trigger>
                                            <flux:modal.trigger name="spk-publish">
                                                <flux:menu.item icon="check"
                                                    wire:click="openPublish({{ $row->id }})">
                                                    Terbitkan SPK
                                                </flux:menu.item>
                                            </flux:modal.trigger>
                                        </flux:menu>
                                    </flux:dropdown>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </flux:card>
        </flux:tab.panel>

        {{-- PUBLISHED --}}
        <flux:tab.panel name="published" class="pt-4">
            <flux:card>
                <flux:table :paginate="$this->itemsPublished">
                    <flux:table.columns>
                        <flux:table.column class="w-12">#</flux:table.column>
                        <flux:table.column sortable :sorted="$sortBy === 'tanggal_terbit_spk'"
                            :direction="$sortDirection" wire:click="sort('tanggal_terbit_spk')">Tgl Terbit
                        </flux:table.column>
                        <flux:table.column>Mahasiswa</flux:table.column>
                        <flux:table.column>Nomor SPK</flux:table.column>
                        <flux:table.column>Judul</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                        <flux:table.column>Dosen Pembimbing</flux:table.column>
                        <flux:table.column class="w-36 text-right">Aksi</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($this->itemsPublished as $i => $row)
                            <flux:table.row :key="'pb-'.$row->id">
                                <flux:table.cell>{{ $this->itemsPublished->firstItem() + $i }}</flux:table.cell>
                                <flux:table.cell class="whitespace-nowrap">
                                    {{ optional($row->tanggal_terbit_spk)->format('d M Y') ?: '—' }}
                                </flux:table.cell>
                                <flux:table.cell class="whitespace-nowrap">
                                    {{ $row->mahasiswa?->user?->name }}
                                    <div class="text-xs text-zinc-500">NIM: {{ $row->mahasiswa?->nim }}</div>
                                </flux:table.cell>
                                <flux:table.cell class="whitespace-nowrap">{{ $row->nomor_spk ?: '—' }}
                                </flux:table.cell>
                                <flux:table.cell class="whitespace-nowrap">{{ $row->judul_kp }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" inset="top bottom"
                                        :color="$this->badgeColor($row->status)">
                                        {{ $this->statusLabel($row->status) }}
                                    </flux:badge>
                                </flux:table.cell>
                                <flux:table.cell class="whitespace-nowrap">
                                    {{ $row->dosenPembimbing?->nama ?? '—' }}
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:dropdown position="bottom" align="end">
                                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                            inset="top bottom"></flux:button>
                                        <flux:menu class="min-w-56">
                                            <flux:modal.trigger name="detail-spk">
                                                <flux:menu.item icon="eye"
                                                    wire:click="openDetail({{ $row->id }})">
                                                    Detail
                                                </flux:menu.item>
                                            </flux:modal.trigger>
                                            <flux:menu.item icon="arrow-down-tray"
                                                href="{{ route('bap.kp.download.docx', $row->id) }}" target="_blank">
                                                Unduh SPK (DOCX)
                                            </flux:menu.item>
                                            <flux:modal.trigger name="spk-publish">
                                                <flux:menu.item icon="pencil-square"
                                                    wire:click="openPublish({{ $row->id }})">
                                                    Ubah Nomor SPK
                                                </flux:menu.item>
                                            </flux:modal.trigger>
                                        </flux:menu>
                                    </flux:dropdown>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </flux:card>
        </flux:tab.panel>
    </flux:tab.group>

    {{-- Modal Detail SPK --}}
    <flux:modal name="detail-spk" :show="$detailId !== null">
        @php $item = $this->selectedItem; @endphp

        <div class="space-y-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <flux:heading size="lg">Detail Pengajuan / SPK</flux:heading>
                    <p class="text-sm text-zinc-500">Periksa data & dokumen mahasiswa.</p>
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
                            <div class="text-sm text-zinc-500">NIM: {{ $item->mahasiswa?->nim }}</div>
                        </div>
                    </flux:card>

                    <flux:card class="space-y-2">
                        <div class="text-sm text-zinc-500">Status</div>
                        <flux:badge size="sm" inset="top bottom" :color="$this->badgeColor($item->status)">
                            {{ $this->statusLabel($item->status) }}
                        </flux:badge>
                    </flux:card>

                    <flux:card class="space-y-2">
                        <div class="text-sm text-zinc-500">Dosen Pembimbing</div>
                        <div class="font-medium">{{ $item->dosenPembimbing?->nama ?? '—' }}</div>
                    </flux:card>

                    @if ($item->nomor_spk || $item->tanggal_terbit_spk)
                        <flux:card class="space-y-2">
                            <div class="text-sm text-zinc-500">SPK</div>
                            <div class="text-sm">
                                Nomor: <span class="font-medium">{{ $item->nomor_spk ?: '—' }}</span><br>
                                Tanggal Terbit: <span
                                    class="font-medium">{{ optional($item->tanggal_terbit_spk)->format('d M Y') ?: '—' }}</span>
                            </div>
                        </flux:card>
                    @endif

                    @if ($item->catatan)
                        <flux:card class="space-y-2 md:col-span-2">
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

    {{-- Modal Terbitkan / Ubah Nomor SPK --}}
    <flux:modal name="spk-publish" class="min-w-[32rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Terbitkan / Ubah Nomor SPK</flux:heading>
                <flux:subheading class="mt-1">Masukkan nomor SPK dan pilih penandatangan.</flux:subheading>
            </div>

            <div class="grid gap-4">
                <flux:input label="Nomor SPK" wire:model.defer="nomor_spk"
                    placeholder="cth: 012/UNSOED/FT/SPK/10/2025" />
                <flux:select label="Penandatangan" wire:model="signatory_id">
                    @foreach (\App\Models\Signatory::query()->orderBy('position')->get() as $opt)
                        <flux:select.option :value="$opt->id">
                            {{ $opt->position }} — {{ $opt->name }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" icon="check" wire:click="publishSave" wire:loading.attr="disabled">
                    Simpan & Terbitkan
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
