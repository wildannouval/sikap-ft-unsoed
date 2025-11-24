<div class="space-y-6">

    {{-- Flash alert sederhana --}}
    @if (session('ok'))
        <div
            class="rounded-md border border-emerald-300/60 bg-emerald-50 px-3 py-2 text-emerald-800 dark:bg-emerald-900/20 dark:border-emerald-900/40 dark:text-emerald-200">
            <div class="font-medium">{{ session('ok') }}</div>
        </div>
    @endif
    @if (session('err'))
        <div
            class="rounded-md border border-red-300/60 bg-red-50 px-3 py-2 text-red-800 dark:bg-red-900/20 dark:border-red-900/40 dark:text-red-200">
            <div class="font-medium">{{ session('err') }}</div>
        </div>
    @endif

    {{-- PANDUAN (aksen sky) --}}
    <flux:card
        class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-xs">
        <div class="flex items-start gap-2 px-1.5 -mt-1">
            <span class="inline-flex items-center justify-center rounded-md p-1.5 bg-sky-500 text-white dark:bg-sky-400">
                <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="16" rx="2" />
                    <path d="M7 8h10M7 12h8M7 16h6" />
                </svg>
            </span>
            <div>
                <h3 class="text-base font-semibold text-stone-900 dark:text-stone-100">Panduan Jadwal & BA Seminar</h3>
                <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-300 space-y-1.5">
                    <div><span class="font-medium">1)</span> Status <strong>Disetujui Pembimbing</strong> → lakukan
                        penjadwalan.</div>
                    <div><span class="font-medium">2)</span> Klik menu <strong>Jadwalkan / Ubah Jadwal</strong> untuk
                        atur tanggal & ruangan.</div>
                    <div><span class="font-medium">3)</span> Setelah seminar selesai, isi <strong>Nomor BA</strong> &
                        <strong>Tanggal BA</strong> lalu <strong>Terbitkan BA</strong>.</div>
                    <div><span class="font-medium">4)</span> Jika <strong>BA Terbit</strong>, tersedia tombol <em>Unduh
                            BA (DOCX)</em>.</div>
                </div>
            </div>
        </div>
    </flux:card>

    {{-- FILTER BAR --}}
    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
            <h3 class="text-base font-semibold">Jadwal & BA Seminar KP</h3>
            <p class="text-sm text-zinc-500 dark:text-zinc-300">Atur jadwal seminar, lalu terbitkan Berita Acara.</p>
        </div>

        <div class="flex flex-col gap-2 md:flex-row md:items-end">
            <div class="md:w-80">
                <flux:input placeholder="Cari nama / NIM / judul / nomor BA…" wire:model.live.debounce.400ms="q"
                    icon="magnifying-glass" />
            </div>

            <flux:select wire:model.live="statusFilter" class="md:ml-2">
                <option value="all">Semua Status</option>
                <option value="diajukan">Diajukan</option>
                <option value="disetujui_pembimbing">Disetujui Pembimbing</option>
                <option value="dijadwalkan">Dijadwalkan</option>
                <option value="ba_terbit">BA Terbit</option>
                <option value="ditolak">Ditolak</option>
            </flux:select>

            <flux:select wire:model.live="perPage" class="md:ml-2 w-32">
                <option value="10">10 / halaman</option>
                <option value="25">25 / halaman</option>
                <option value="50">50 / halaman</option>
            </flux:select>
        </div>
    </div>

    {{-- TABEL LIST --}}
    <flux:card class="rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-xs">
        {{-- Header beraksen sky --}}
        <div
            class="px-4 py-3 border-b bg-sky-50 text-sky-700 dark:bg-sky-900/20 dark:text-sky-300 border-sky-100 dark:border-sky-900/40 rounded-t-xl">
            <div class="text-sm font-medium tracking-wide">Daftar Seminar</div>
        </div>

        <div class="p-4">
            <flux:table
                class="[&_thead_th]:bg-zinc-50 [&_thead_th]:dark:bg-stone-900/40
                       [&_thead_th]:text-zinc-600 [&_thead_th]:dark:text-stone-200
                       [&_tbody_tr]:hover:bg-zinc-50/60 [&_tbody_tr]:dark:hover:bg-stone-900/30"
                :paginate="$this->items">

                <flux:table.columns>
                    <flux:table.column class="w-12">#</flux:table.column>

                    <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection"
                        wire:click="sort('created_at')">
                        Dibuat
                    </flux:table.column>

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
                                {{ optional($row->created_at)->format('d M Y') ?: '—' }}
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                {{ $row->kp?->mahasiswa?->user?->name ?? '—' }}
                                <div class="text-xs text-zinc-500">
                                    {{ $row->kp?->mahasiswa?->nim ?? ($row->kp?->mahasiswa?->mahasiswa_nim ?? '') }}
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="max-w-[360px]">
                                <span
                                    class="line-clamp-2">{{ $row->judul_laporan ?? ($row->kp?->judul_kp ?? '—') }}</span>
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge size="sm" inset="top bottom" :color="$this->badgeColor($row->status)">
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
                                    <span class="text-xs text-zinc-400">Belum dijadwalkan</span>
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
                                        href="{{ asset('storage/' . $row->distribusi_proof_path) }}"
                                        target="_blank">Lihat Bukti</a>
                                    <div class="text-[11px] text-zinc-500">
                                        {{ $row->distribusi_uploaded_at?->format('d M Y H:i') }}
                                    </div>
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
                                                Jadwalkan / Ubah Jadwal & Terbitkan BA
                                            </flux:menu.item>
                                        </flux:modal.trigger>

                                        @if ($row->status === 'ba_terbit')
                                            <flux:menu.item icon="arrow-down-tray"
                                                href="{{ route('bap.kp.seminar.download.ba', $row->id) }}"
                                                target="_blank">
                                                Unduh BA (DOCX)
                                            </flux:menu.item>
                                        @else
                                            <flux:menu.item icon="arrow-down-tray" disabled>Unduh BA (DOCX)
                                            </flux:menu.item>
                                        @endif
                                    </flux:menu>
                                </flux:dropdown>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>
    </flux:card>

    {{-- MODAL: Jadwal & BA --}}
    <flux:modal name="edit-seminar" class="min-w-[34rem]" :show="$editId !== null">
        <div class="space-y-6">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 class="text-base font-semibold">Jadwalkan & Terbitkan BA</h3>
                    <p class="text-sm text-zinc-500 dark:text-zinc-300">Lengkapi data di bawah, simpan jadwal, lalu
                        terbitkan BA.</p>
                </div>
                <flux:modal.close>
                    <flux:button variant="ghost" icon="x-mark" wire:click="$set('editId', null)"></flux:button>
                </flux:modal.close>
            </div>

            {{-- Jadwal --}}
            <div class="grid gap-4 md:grid-cols-2">
                <flux:input type="datetime-local" label="Tanggal & Jam Seminar" wire:model.defer="tanggal_seminar"
                    :invalid="$errors->has('tanggal_seminar')" />
                <flux:input label="Ruangan (nama)" wire:model.defer="ruangan_nama"
                    :invalid="$errors->has('ruangan_nama')" />
            </div>
            @error('tanggal_seminar')
                <div class="text-sm text-red-600 -mt-2">{{ $message }}</div>
            @enderror
            @error('ruangan_nama')
                <div class="text-sm text-red-600 -mt-2">{{ $message }}</div>
            @enderror

            <div class="flex justify-end gap-2">
                <flux:button variant="primary" icon="calendar" wire:click="saveSchedule"
                    wire:loading.attr="disabled">
                    Simpan Jadwal
                </flux:button>
            </div>

            <flux:separator />

            {{-- BA --}}
            <div class="grid gap-4 md:grid-cols-2">
                <flux:input label="Nomor BA" wire:model.defer="nomor_ba" :invalid="$errors->has('nomor_ba')" />
                <flux:input type="date" label="Tanggal BA" wire:model.defer="tanggal_ba"
                    :invalid="$errors->has('tanggal_ba')" />
                <flux:input label="Signatory ID (opsional)" wire:model.defer="signatory_id" />
            </div>
            @error('nomor_ba')
                <div class="text-sm text-red-600 -mt-2">{{ $message }}</div>
            @enderror
            @error('tanggal_ba')
                <div class="text-sm text-red-600 -mt-2">{{ $message }}</div>
            @enderror

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Tutup</flux:button>
                </flux:modal.close>

                <flux:button variant="primary" icon="check" wire:click="publishBA" wire:loading.attr="disabled">
                    Terbitkan BA
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
