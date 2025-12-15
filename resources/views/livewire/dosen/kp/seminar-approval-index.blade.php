<div class="space-y-6">
    <flux:toast />

    {{-- HEADER --}}
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <flux:heading size="xl" level="1" class="text-stone-900 dark:text-stone-100">
                Persetujuan Seminar
            </flux:heading>
            <flux:subheading class="text-zinc-600 dark:text-zinc-300">
                Tinjau pengajuan seminar mahasiswa bimbingan.
            </flux:subheading>
        </div>
    </div>

    <flux:separator variant="subtle" />

    {{-- GRID UTAMA 3:1 --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">

        {{-- KOLOM KIRI: TABEL (3/4) --}}
        <div class="lg:col-span-3 space-y-6">
            <flux:card
                class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm overflow-hidden">

                {{-- Header Tabel --}}
                <div
                    class="flex flex-col gap-4 px-6 py-4 border-b border-zinc-200 dark:border-stone-800 bg-indigo-50/50 dark:bg-indigo-900/10 md:flex-row md:items-center md:justify-between">
                    <h4 class="text-base font-semibold text-stone-900 dark:text-stone-100">Daftar Pengajuan</h4>

                    <div class="flex items-center gap-3">
                        <flux:input icon="magnifying-glass" placeholder="Cari nama / judul..."
                            wire:model.live.debounce.300ms="q" class="w-full md:w-64 bg-white dark:bg-stone-900" />

                        <flux:select wire:model.live="statusFilter" class="w-40">
                            <flux:select.option value="diajukan">Menunggu</flux:select.option>
                            <flux:select.option value="disetujui_pembimbing">Disetujui</flux:select.option>
                            <flux:select.option value="dijadwalkan">Dijadwalkan</flux:select.option>
                            <flux:select.option value="all">Semua</flux:select.option>
                        </flux:select>
                    </div>
                </div>

                <flux:table :paginate="$this->items">
                    <flux:table.columns>
                        <flux:table.column class="w-10 text-center">No</flux:table.column>
                        <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection"
                            wire:click="$set('sortBy', 'created_at')">Tanggal</flux:table.column>
                        <flux:table.column>Mahasiswa</flux:table.column>
                        <flux:table.column>Judul Laporan</flux:table.column>
                        <flux:table.column>Jadwal</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                        <flux:table.column class="text-right">Aksi</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($this->items as $i => $row)
                            <flux:table.row :key="$row->id">
                                <flux:table.cell class="text-center text-zinc-500">{{ $this->items->firstItem() + $i }}
                                </flux:table.cell>

                                <flux:table.cell class="whitespace-nowrap">
                                    {{ optional($row->created_at)->format('d M Y') }}
                                </flux:table.cell>

                                <flux:table.cell>
                                    <div class="font-medium text-stone-900 dark:text-stone-100">
                                        {{ $row->kp?->mahasiswa?->user?->name ?? '—' }}
                                    </div>
                                    <div class="text-xs text-zinc-500">
                                        {{ $row->kp?->mahasiswa?->mahasiswa_nim ?? '' }}
                                    </div>
                                </flux:table.cell>

                                <flux:table.cell class="max-w-[250px]">
                                    <span class="line-clamp-2 text-sm">{{ $row->judul_laporan }}</span>
                                </flux:table.cell>

                                <flux:table.cell class="whitespace-nowrap">
                                    @if ($row->tanggal_seminar)
                                        <div class="text-sm font-medium">
                                            {{ $row->tanggal_seminar->format('d M Y H:i') }}
                                        </div>
                                        <div class="text-xs text-zinc-500">{{ $row->ruangan_nama }}</div>
                                    @else
                                        <span class="text-xs text-zinc-400 italic">Belum dijadwalkan</span>
                                    @endif
                                </flux:table.cell>

                                <flux:table.cell>
                                    <flux:badge size="sm" :color="$this->badgeColor($row->status)"
                                        :icon="$this->badgeIcon($row->status)">
                                        {{ $this->statusLabel($row->status) }}
                                    </flux:badge>
                                </flux:table.cell>

                                <flux:table.cell class="text-right">
                                    <flux:dropdown position="bottom" align="end">
                                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                            inset="top bottom" />
                                        <flux:menu>
                                            @if ($row->status === 'diajukan')
                                                <flux:menu.item icon="check"
                                                    wire:click="approve({{ $row->id }})">
                                                    Setujui
                                                </flux:menu.item>
                                                <flux:menu.item icon="x-mark" variant="danger"
                                                    wire:click="triggerReject({{ $row->id }})">
                                                    Tolak
                                                </flux:menu.item>
                                            @endif

                                            @if ($row->berkas_laporan_path)
                                                <flux:menu.separator />
                                                <flux:menu.item icon="document-text"
                                                    href="{{ asset('storage/' . $row->berkas_laporan_path) }}"
                                                    target="_blank">
                                                    Lihat Laporan
                                                </flux:menu.item>
                                            @endif

                                            @if ($row->status === 'ba_terbit')
                                                <flux:menu.separator />
                                                <flux:menu.item icon="arrow-down-tray"
                                                    href="{{ route('dsp.kp.seminar.download.ba', $row->id) }}"
                                                    target="_blank">
                                                    Unduh BA
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
                @if ($this->items->isEmpty())
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <div class="rounded-full bg-zinc-100 p-4 dark:bg-stone-900">
                            <flux:icon.check-badge class="size-8 text-zinc-400" />
                        </div>
                        <h3 class="mt-4 text-base font-semibold text-stone-900 dark:text-stone-100">
                            Tidak ada pengajuan
                        </h3>
                        <p class="mt-1 text-sm text-zinc-500">
                            @if ($q)
                                Tidak ditemukan data yang cocok dengan pencarian "{{ $q }}".
                            @else
                                Belum ada pengajuan seminar dengan status ini.
                            @endif
                        </p>
                    </div>
                @endif
            </flux:card>
        </div>

        {{-- KOLOM KANAN: SIDEBAR (1/4) --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- PANDUAN --}}
            <flux:card
                class="rounded-xl border bg-indigo-50/50 dark:bg-indigo-900/10 border-indigo-100 dark:border-indigo-800/30 shadow-sm">
                <div class="flex items-start gap-3">
                    <flux:icon.information-circle class="mt-0.5 size-5 text-indigo-600 dark:text-indigo-400" />
                    <div>
                        <h3 class="font-semibold text-indigo-900 dark:text-indigo-100 text-sm">Alur Persetujuan</h3>
                        <ul class="mt-3 text-xs text-indigo-800 dark:text-indigo-200 space-y-2 list-disc list-inside">
                            <li>Cek dokumen laporan mahasiswa melalui menu aksi.</li>
                            <li>Klik <strong>Setujui</strong> jika laporan layak seminar.</li>
                            <li>Setelah disetujui, Bapendik akan mengatur jadwal.</li>
                            <li>Pantau kolom <strong>Jadwal</strong> untuk info waktu & ruangan.</li>
                        </ul>
                    </div>
                </div>
            </flux:card>
        </div>
    </div>

    {{-- MODAL TOLAK --}}
    <flux:modal name="reject-seminar" :show="$rejectId !== null" class="md:w-96">
        <div class="space-y-4">
            <flux:heading size="lg">Tolak Pengajuan?</flux:heading>
            <p class="text-sm text-zinc-500">Berikan alasan penolakan agar mahasiswa dapat memperbaiki.</p>

            <flux:textarea label="Alasan Penolakan" wire:model.defer="rejectReason" />

            <div class="flex justify-end gap-2 pt-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" wire:click="confirmReject">Tolak Pengajuan</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
