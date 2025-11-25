<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1" class="text-stone-900 dark:text-stone-100">
                Persetujuan & Riwayat Seminar (Dosen Pembimbing)
            </flux:heading>
            <flux:subheading class="text-zinc-600 dark:text-zinc-300">
                Tinjau pengajuan seminar mahasiswa bimbingan, setujui/tolak, pantau jadwal, dan unduh Berita Acara (BA)
                jika tersedia.
            </flux:subheading>
        </div>
    </div>
    <flux:separator variant="subtle" />

    {{-- FLASH --}}
    @if (session('ok'))
        <div class="rounded-md border border-emerald-300/60 bg-emerald-50 px-3 py-2 text-emerald-800">
            <div class="font-medium">{{ session('ok') }}</div>
        </div>
    @endif

    {{-- CARD PANDUAN --}}
    <flux:card
        class="space-y-4 rounded-xl border
               bg-white dark:bg-stone-950
               border-zinc-200 dark:border-stone-800
               shadow-xs">
        <div class="flex items-start gap-2 px-1.5 -mt-1">
            <span
                class="inline-flex items-center justify-center rounded-md p-1.5
                       bg-indigo-500 text-white dark:bg-indigo-400">
                <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M12 16v-4" />
                    <path d="M12 8h.01" />
                </svg>
            </span>
            <div>
                <h3 class="text-base font-semibold text-stone-900 dark:text-stone-100">
                    Panduan Persetujuan Seminar KP
                </h3>
                <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-300 space-y-1.5">
                    <div><span class="font-medium">1)</span> Gunakan kolom <em>cari</em> untuk nama/NIM/judul laporan,
                        dan filter status untuk fokus data.</div>
                    <div><span class="font-medium">2)</span> Klik <strong>Setujui</strong> untuk meneruskan ke Bapendik
                        (penjadwalan), atau <strong>Tolak</strong> dengan alasan yang jelas.</div>
                    <div><span class="font-medium">3)</span> Setelah <em>BA Terbit</em>, gunakan tautan <strong>Unduh BA
                            (DOCX)</strong> untuk mengunduh berkas.</div>
                    <div><span class="font-medium">4)</span> Periksa kolom Jadwal & Ruangan ketika status menjadi
                        <em>Dijadwalkan</em>.</div>
                </div>
            </div>
        </div>
    </flux:card>

    {{-- FILTER BAR + TABLE --}}
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
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <h3 class="text-sm font-medium tracking-wide">Daftar Pengajuan Seminar</h3>

                <div class="flex flex-col gap-2 md:flex-row md:items-end">
                    <flux:input class="md:w-72" placeholder="Cari nama / NIM / judul…" wire:model.debounce.400ms="q"
                        icon="magnifying-glass" />

                    <flux:select wire:model.live="statusFilter" class="md:ml-2 w-48">
                        <option value="diajukan">Menunggu ACC</option>
                        <option value="disetujui_pembimbing">Disetujui Pembimbing</option>
                        <option value="dijadwalkan">Dijadwalkan</option>
                        <option value="ba_terbit">BA Terbit</option>
                        <option value="ditolak">Ditolak</option>
                        <option value="all">Semua Status</option>
                    </flux:select>

                    <flux:select wire:model.live="perPage" class="md:ml-2 w-24">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </flux:select>
                </div>

                <div class="flex items-center gap-2">
                    <flux:button size="sm" variant="ghost"
                        :icon="$sortDirection === 'asc' ? 'arrow-up-circle' : 'arrow-down-circle'"
                        wire:click="$set('sortBy','created_at'); $set('sortDirection', '{{ $sortDirection === 'asc' ? 'desc' : 'asc' }}')">
                        Urut: {{ ucfirst(str_replace('_', ' ', $sortBy)) }} ({{ $sortDirection }})
                    </flux:button>
                </div>
            </div>
        </div>

        <div class="p-4">
            <flux:table
                class="[&_thead_th]:bg-zinc-50 [&_thead_th]:dark:bg-stone-900/40
                       [&_thead_th]:text-zinc-600 [&_thead_th]:dark:text-stone-200
                       [&_tbody_tr]:hover:bg-zinc-50/60 [&_tbody_tr]:dark:hover:bg-stone-900/30"
                :paginate="$this->items">

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
                                <div class="font-medium text-stone-900 dark:text-stone-100">
                                    {{ $row->kp?->mahasiswa?->user?->name ?? '—' }}</div>
                                <div class="text-xs text-zinc-500">
                                    {{ $row->kp?->mahasiswa?->nim ?? ($row->kp?->mahasiswa?->mahasiswa_nim ?? '') }}</div>
                            </flux:table.cell>

                            <flux:table.cell class="max-w-[360px]">
                                <div class="line-clamp-2 text-stone-900 dark:text-stone-100">
                                    {{ $row->judul_laporan ?? '—' }}</div>
                                <div class="mt-1">
                                    <flux:badge size="sm" inset="top bottom"
                                        :color="$this->badgeColor($row->status)">
                                        {{ $this->statusLabel($row->status) }}
                                    </flux:badge>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                @php $tgl = $row->tanggal_seminar?->format('d M Y'); @endphp
                                <div class="text-stone-900 dark:text-stone-100">{{ $tgl ?: '—' }}</div>
                                @if ($row->jam_mulai || $row->jam_selesai)
                                    <div class="text-xs text-zinc-500">
                                        {{ $row->jam_mulai ?? '—' }} — {{ $row->jam_selesai ?? '—' }}
                                    </div>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                <span class="text-stone-900 dark:text-stone-100">{{ $row->ruangan_nama ?? '—' }}</span>
                            </flux:table.cell>

                            <flux:table.cell>
                                @if ($row->distribusi_proof_path)
                                    <a class="text-sm underline hover:no-underline"
                                        href="{{ asset('storage/' . $row->distribusi_proof_path) }}" target="_blank">
                                        Lihat Bukti
                                    </a>
                                @else
                                    <span class="text-xs text-zinc-400">—</span>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell>
                                @if ($row->berkas_laporan_path)
                                    <a class="text-sm underline hover:no-underline"
                                        href="{{ asset('storage/' . $row->berkas_laporan_path) }}" target="_blank">
                                        Lihat PDF
                                    </a>
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
                                        <a class="inline-flex items-center text-sm underline hover:no-underline"
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
        </div>
    </flux:card>

    {{-- MODAL TOLAK --}}
    <flux:modal name="reject-seminar" :show="$rejectId !== null" class="min-w-[26rem]">
        <div class="space-y-4">
            <h3 class="text-base font-semibold text-stone-900 dark:text-stone-100">Tolak Pengajuan Seminar</h3>

            <flux:textarea label="Alasan Penolakan" rows="4" wire:model.defer="rejectReason"
                :invalid="$errors->has('rejectReason')" />
            @error('rejectReason')
                <div class="text-sm text-rose-600">{{ $message }}</div>
            @enderror

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>

                {{-- Biarkan modal tertutup otomatis ketika $rejectId diset null di confirmReject --}}
                <flux:button variant="danger" icon="x-mark" wire:click="confirmReject"
                    wire:loading.attr="disabled">
                    Tolak
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
