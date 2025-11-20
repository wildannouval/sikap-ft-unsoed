<div class="space-y-6">
    {{-- Flash messages --}}
    @if (session('ok'))
        <div class="rounded-md border border-emerald-300/60 bg-emerald-50 px-3 py-2 text-emerald-800">
            <div class="font-medium">{{ session('ok') }}</div>
        </div>
    @endif

    <flux:card class="space-y-4">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-semibold">Persetujuan & Riwayat Seminar KP</h3>
                <p class="text-sm text-zinc-500">Kelola pengajuan, lihat jadwal, dan unduh Berita Acara.</p>
            </div>
            <div class="flex items-center gap-2">
                <flux:input class="md:w-64" placeholder="Cari nama / NIM / judul…" wire:model.debounce.400ms="q"
                    icon="magnifying-glass" />
                <flux:select wire:model.live="statusFilter" class="w-44">
                    <option value="diajukan">Menunggu ACC</option>
                    <option value="disetujui_pembimbing">Disetujui Pembimbing</option>
                    <option value="dijadwalkan">Dijadwalkan</option>
                    <option value="ba_terbit">BA Terbit</option>
                    <option value="ditolak">Ditolak</option>
                    <option value="all">Semua Status</option>
                </flux:select>
                <flux:select wire:model.live="perPage" class="w-24">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </flux:select>
            </div>
        </div>

        <flux:table :paginate="$this->items">
            <flux:table.columns>
                <flux:table.column class="w-12">#</flux:table.column>
                <flux:table.column sortable
                    wire:click="$set('sortBy','created_at'); $set('sortDirection', '{{ $sortDirection === 'asc' ? 'desc' : 'asc' }}')">
                    Diajukan
                </flux:table.column>
                <flux:table.column>Mahasiswa</flux:table.column>
                <flux:table.column>Judul</flux:table.column>
                <flux:table.column>Jadwal</flux:table.column>
                <flux:table.column>Ruangan</flux:table.column>
                <flux:table.column>Distribusi</flux:table.column>
                <flux:table.column>Berkas</flux:table.column>
                <flux:table.column class="w-52 text-right">Aksi</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->items as $i => $row)
                    <flux:table.row :key="$row->id">
                        <flux:table.cell>{{ $this->items->firstItem() + $i }}</flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap text-xs text-zinc-500">
                            {{ $row->created_at?->format('d M Y H:i') ?? '—' }}
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap">
                            <div class="font-medium">{{ $row->kp?->mahasiswa?->user?->name ?? '—' }}</div>
                            <div class="text-xs text-zinc-500">{{ $row->kp?->mahasiswa?->nim ?? '' }}</div>
                        </flux:table.cell>

                        <flux:table.cell class="max-w-[360px]">
                            <div class="line-clamp-2">{{ $row->judul_laporan ?? '—' }}</div>
                            <div class="mt-1">
                                <flux:badge size="sm" :color="$this->badgeColor($row->status)">
                                    {{ $this->statusLabel($row->status) }}
                                </flux:badge>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap">
                            @php $tgl = $row->tanggal_seminar?->format('d M Y'); @endphp
                            {{ $tgl ?: '—' }}
                            @if ($row->jam_mulai || $row->jam_selesai)
                                <div class="text-xs text-zinc-500">
                                    {{ $row->jam_mulai ?? '—' }} — {{ $row->jam_selesai ?? '—' }}
                                </div>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap">
                            {{ $row->ruangan_nama ?? '—' }}
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($row->distribusi_proof_path)
                                <a class="text-sm underline"
                                    href="{{ asset('storage/' . $row->distribusi_proof_path) }}" target="_blank">Lihat
                                    Bukti</a>
                            @else
                                <span class="text-xs text-zinc-400">—</span>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($row->berkas_laporan_path)
                                <a class="text-sm underline" href="{{ asset('storage/' . $row->berkas_laporan_path) }}"
                                    target="_blank">Lihat PDF</a>
                            @else
                                <span class="text-xs text-zinc-400">—</span>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="text-right">
                            @if ($row->status === 'diajukan')
                                <flux:button size="sm" variant="ghost" icon="check"
                                    wire:click="approve({{ $row->id }})">
                                    Setujui
                                </flux:button>

                                <flux:modal.trigger name="reject-seminar">
                                    <flux:button size="sm" variant="ghost" icon="x-mark"
                                        wire:click="triggerReject({{ $row->id }})">
                                        Tolak
                                    </flux:button>
                                </flux:modal.trigger>
                            @else
                                @if ($row->status === 'ba_terbit')
                                    <a class="inline-flex items-center text-sm underline"
                                        href="{{ route('dsp.kp.seminar.download.ba', $row->id) }}" target="_blank">
                                        Unduh BA (DOCX)
                                    </a>
                                @else
                                    <span class="text-xs text-zinc-400">—</span>
                                @endif
                            @endif
                        </flux:table.cell>
                    </flux:table.row>

                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="9">
                            <div class="flex items-center justify-center py-6 text-sm text-zinc-500">
                                Tidak ada data untuk filter saat ini.
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

    <flux:modal name="reject-seminar" class="min-w-[26rem]">
        <div class="space-y-4">
            <h3 class="text-base font-semibold">Tolak Pengajuan Seminar</h3>
            <flux:textarea label="Alasan Penolakan" rows="4" wire:model.defer="rejectReason"
                :invalid="$errors->has('rejectReason')" />
            @error('rejectReason')
                <div class="text-sm text-red-600">{{ $message }}</div>
            @enderror
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" icon="x-mark" wire:click="confirmReject">
                    Tolak
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
