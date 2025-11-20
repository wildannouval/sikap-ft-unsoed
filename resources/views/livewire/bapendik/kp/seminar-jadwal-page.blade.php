<div class="space-y-6">
    <div class="flex items-end justify-between gap-3">
        <div>
            <h3 class="text-base font-semibold">Jadwal & BA Seminar KP</h3>
            <p class="text-sm text-zinc-500">Atur jadwal, lalu terbitkan Berita Acara.</p>
        </div>
        <div class="flex gap-2">
            <flux:input class="md:w-80" placeholder="Cari judul/nomor BA…" wire:model.debounce.400ms="q"
                icon="magnifying-glass" />
            <flux:select wire:model.live="statusFilter">
                <option value="all">Semua Status</option>
                <option value="disetujui_pembimbing">Disetujui Pembimbing</option>
                <option value="dijadwalkan">Dijadwalkan</option>
                <option value="ba_terbit">BA Terbit</option>
                <option value="ditolak">Ditolak</option>
                <option value="diajukan">Diajukan</option>
            </flux:select>
        </div>
    </div>

    <flux:card>
        <flux:table :paginate="$this->items">
            <flux:table.columns>
                <flux:table.column class="w-12">#</flux:table.column>
                <flux:table.column>Mahasiswa</flux:table.column>
                <flux:table.column>Judul</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Jadwal</flux:table.column>
                <flux:table.column>BA Scan</flux:table.column>
                <flux:table.column>Distribusi</flux:table.column>
                <flux:table.column class="w-48 text-right">Aksi</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->items as $i => $row)
                    <flux:table.row :key="$row->id">
                        <flux:table.cell>{{ $this->items->firstItem() + $i }}</flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap">
                            {{ $row->kp?->mahasiswa?->user?->name }}
                            <div class="text-xs text-zinc-500">{{ $row->kp?->mahasiswa?->nim }}</div>
                        </flux:table.cell>

                        <flux:table.cell class="max-w-[320px]">
                            <span class="line-clamp-2">{{ $row->judul_laporan ?? $row->kp?->judul_kp }}</span>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge size="sm" :color="$this->badgeColor($row->status)">
                                {{ $this->statusLabel($row->status) }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap">
                            @if ($row->tanggal_seminar)
                                {{ $row->tanggal_seminar->format('d M Y H:i') }}
                                @if ($row->ruangan_nama)
                                    • {{ $row->ruangan_nama }}
                                @endif
                            @else
                                —
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($row->ba_scan_path)
                                <a class="text-sm underline" href="{{ asset('storage/' . $row->ba_scan_path) }}"
                                    target="_blank">Lihat</a>
                            @else
                                <span class="text-xs text-zinc-400">—</span>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($row->distribusi_proof_path)
                                <a class="text-sm underline"
                                    href="{{ asset('storage/' . $row->distribusi_proof_path) }}" target="_blank">Lihat
                                    Bukti</a>
                                <div class="text-[11px] text-zinc-500">
                                    {{ $row->distribusi_uploaded_at?->format('d M Y H:i') }}</div>
                            @else
                                <span class="text-xs text-zinc-400">Belum diupload</span>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="text-right">
                            <flux:dropdown position="bottom" align="end">
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                    inset="top bottom" />
                                <flux:menu class="min-w-56">
                                    <flux:modal.trigger name="edit-seminar">
                                        <flux:menu.item icon="calendar" wire:click="openEdit({{ $row->id }})">
                                            Jadwalkan / Ubah Jadwal
                                        </flux:menu.item>
                                    </flux:modal.trigger>

                                    @if ($row->status === 'ba_terbit')
                                        <flux:menu.item icon="arrow-down-tray"
                                            href="{{ route('bap.kp.seminar.download.ba', $row->id) }}" target="_blank">
                                            Unduh BA (DOCX)
                                        </flux:menu.item>
                                    @else
                                        <flux:menu.item icon="arrow-down-tray" disabled>Unduh BA (DOCX)</flux:menu.item>
                                    @endif
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:card>

    <flux:modal name="edit-seminar" class="min-w-[34rem]" :show="$editId !== null">
        <div class="space-y-6">
            <h3 class="text-base font-semibold">Jadwalkan & Terbitkan BA</h3>

            <div class="grid gap-4 md:grid-cols-2">
                <flux:input type="datetime-local" label="Tanggal & Jam Seminar" wire:model.defer="tanggal_seminar" />
                <flux:input label="Ruangan (nama)" wire:model.defer="ruangan_nama" />
            </div>

            <div class="flex justify-end gap-2">
                <flux:button variant="primary" icon="calendar" wire:click="saveSchedule">Simpan Jadwal</flux:button>
            </div>

            <flux:separator />

            <div class="grid gap-4 md:grid-cols-2">
                <flux:input label="Nomor BA" wire:model.defer="nomor_ba" />
                <flux:input type="date" label="Tanggal BA" wire:model.defer="tanggal_ba" />
                <flux:input label="Signatory ID (opsional)" wire:model.defer="signatory_id" />
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Tutup</flux:button>
                </flux:modal.close>

                <flux:button variant="primary" icon="check" wire:click="publishBA">
                    Terbitkan BA
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
