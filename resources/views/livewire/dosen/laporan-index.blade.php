<div class="space-y-6">
    <flux:toast />

    {{-- HEADER --}}
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <flux:heading size="xl" level="1" class="text-stone-900 dark:text-stone-100">
                Laporan & Arsip Bimbingan
            </flux:heading>
            <flux:subheading class="text-zinc-600 dark:text-zinc-300">
                Akses dokumen laporan KP, Berita Acara, dan bukti distribusi mahasiswa bimbingan.
            </flux:subheading>
        </div>
    </div>

    <flux:separator variant="subtle" />

    {{-- GRID UTAMA 3:1 --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">

        {{-- KOLOM KIRI: TABEL (3) --}}
        <div class="lg:col-span-3 space-y-6">
            <flux:card
                class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm overflow-hidden">

                {{-- Header Tabel --}}
                <div
                    class="flex flex-col gap-4 px-6 py-4 border-b border-zinc-200 dark:border-stone-800 bg-indigo-50/50 dark:bg-indigo-900/10 md:flex-row md:items-center md:justify-between">
                    <h4 class="text-base font-semibold text-stone-900 dark:text-stone-100">Daftar Dokumen</h4>

                    <div class="flex items-center gap-3">
                        <flux:input icon="magnifying-glass" placeholder="Cari nama / judul..."
                            wire:model.live.debounce.400ms="q" class="w-full md:w-64 bg-white dark:bg-stone-900" />

                        <flux:select wire:model.live="perPage" class="w-20">
                            <flux:select.option :value="10">10</flux:select.option>
                            <flux:select.option :value="25">25</flux:select.option>
                            <flux:select.option :value="50">50</flux:select.option>
                        </flux:select>
                    </div>
                </div>

                <flux:table :paginate="$this->items">
                    <flux:table.columns>
                        <flux:table.column class="w-10">No</flux:table.column>
                        <flux:table.column>Mahasiswa</flux:table.column>
                        <flux:table.column>Laporan KP</flux:table.column>
                        <flux:table.column>Berita Acara</flux:table.column>
                        <flux:table.column>Distribusi</flux:table.column>
                        <flux:table.column class="text-right">Aksi</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($this->items as $i => $row)
                            <flux:table.row :key="$row->id">
                                <flux:table.cell class="text-center text-zinc-500">
                                    {{ $this->items->firstItem() + $i }}
                                </flux:table.cell>

                                <flux:table.cell class="whitespace-nowrap">
                                    <div class="font-medium text-stone-900 dark:text-stone-100">
                                        {{ $row->kp?->mahasiswa?->user?->name ?? '—' }}
                                    </div>
                                    <div class="text-xs text-zinc-500">
                                        {{ $row->kp?->mahasiswa?->mahasiswa_nim ?? '—' }}
                                    </div>
                                </flux:table.cell>

                                {{-- File Laporan --}}
                                <flux:table.cell>
                                    @if ($row->berkas_laporan_path)
                                        <a href="{{ asset('storage/' . $row->berkas_laporan_path) }}" target="_blank"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 dark:bg-red-900/20 dark:text-red-300 dark:border-red-800">
                                            <flux:icon.document-text class="size-3.5" />
                                            PDF
                                        </a>
                                    @else
                                        <span class="text-xs text-zinc-400 italic">Belum ada</span>
                                    @endif
                                </flux:table.cell>

                                {{-- BA Scan --}}
                                <flux:table.cell>
                                    <div class="flex flex-col gap-1 items-start">
                                        {{-- 1. Jika Scan Ada --}}
                                        @if ($row->grade?->ba_scan_path)
                                            <a href="{{ asset('storage/' . $row->grade->ba_scan_path) }}"
                                                target="_blank"
                                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-violet-50 text-violet-700 hover:bg-violet-100 border border-violet-200 dark:bg-violet-900/20 dark:text-violet-300 dark:border-violet-800">
                                                <flux:icon.clipboard-document-check class="size-3.5" />
                                                Scan
                                            </a>

                                            {{-- 2. Jika Scan Kosong, TAPI status sudah BA Terbit/Dinilai/Selesai --}}
                                        @elseif (in_array($row->status, ['ba_terbit', 'dinilai', 'selesai']))
                                            <a href="{{ route('dsp.kp.seminar.download.ba', $row->id) }}"
                                                target="_blank"
                                                class="inline-flex items-center gap-1.5 text-xs text-indigo-600 hover:underline">
                                                <flux:icon.arrow-down-tray class="size-3.5" />
                                                Draft BA
                                            </a>
                                            <span class="text-[10px] text-zinc-400 italic mt-0.5">Menunggu nilai</span>

                                            {{-- 3. Belum masuk tahap BA --}}
                                        @else
                                            <span class="text-xs text-zinc-400 italic">Belum terbit</span>
                                        @endif
                                    </div>
                                </flux:table.cell>

                                {{-- Bukti Distribusi --}}
                                <flux:table.cell>
                                    @if ($row->distribusi_proof_path)
                                        <a href="{{ asset('storage/' . $row->distribusi_proof_path) }}" target="_blank"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-teal-50 text-teal-700 hover:bg-teal-100 border border-teal-200 dark:bg-teal-900/20 dark:text-teal-300 dark:border-teal-800">
                                            <flux:icon.check-circle class="size-3.5" />
                                            Bukti
                                        </a>
                                        <div class="text-[10px] text-zinc-500 mt-1">
                                            {{ $row->distribusi_uploaded_at?->format('d/m/y') }}
                                        </div>
                                    @else
                                        <span class="text-xs text-zinc-400 italic">Menunggu Mhs</span>
                                    @endif
                                </flux:table.cell>

                                <flux:table.cell class="text-right">
                                    <flux:dropdown position="bottom" align="end">
                                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                        <flux:menu>
                                            @if ($row->berkas_laporan_path)
                                                <flux:menu.item icon="arrow-down-tray"
                                                    href="{{ asset('storage/' . $row->berkas_laporan_path) }}"
                                                    target="_blank">
                                                    Unduh Laporan
                                                </flux:menu.item>
                                            @endif

                                            @if ($row->grade?->ba_scan_path)
                                                <flux:menu.item icon="document-duplicate"
                                                    href="{{ asset('storage/' . $row->grade->ba_scan_path) }}"
                                                    target="_blank">
                                                    Unduh BA Scan
                                                </flux:menu.item>
                                            @endif

                                            @if (in_array($row->status, ['ba_terbit', 'dinilai', 'selesai']))
                                                <flux:menu.item icon="document-text"
                                                    href="{{ route('dsp.kp.seminar.download.ba', $row->id) }}"
                                                    target="_blank">
                                                    Unduh BA Asli (DOCX)
                                                </flux:menu.item>
                                            @endif

                                            <flux:menu.separator />

                                            <flux:menu.item icon="eye"
                                                href="{{ route('dsp.kp.seminar.approval') }}" wire:navigate>
                                                Lihat Detail Seminar
                                            </flux:menu.item>
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
                            <flux:icon.archive-box class="size-8 text-zinc-400" />
                        </div>
                        <h3 class="mt-4 text-base font-semibold text-stone-900 dark:text-stone-100">
                            Belum ada arsip
                        </h3>
                        <p class="mt-1 text-sm text-zinc-500">
                            @if ($q)
                                Tidak ditemukan data dengan kata kunci "{{ $q }}".
                            @else
                                Belum ada seminar yang selesai atau memiliki dokumen.
                            @endif
                        </p>
                    </div>
                @endif
            </flux:card>
        </div>

        {{-- KOLOM KANAN: SIDEBAR (1) --}}
        <div class="lg:col-span-1 space-y-6">

            {{-- 1. RINGKASAN --}}
            <flux:card
                class="rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm">
                <div class="mb-4 flex items-center gap-2">
                    <flux:icon.chart-bar class="size-5 text-zinc-500" />
                    <h3 class="font-semibold text-stone-900 dark:text-stone-100">Statistik Arsip</h3>
                </div>

                <div class="space-y-3">
                    <div
                        class="flex items-center justify-between p-2 rounded-lg bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700">
                        <div class="flex items-center gap-2">
                            <flux:icon.document-text class="size-4 text-zinc-500" />
                            <span class="text-sm font-medium text-stone-700 dark:text-stone-300">Laporan Masuk</span>
                        </div>
                        <span
                            class="font-bold text-stone-900 dark:text-stone-100">{{ $this->stats['total_laporan'] }}</span>
                    </div>

                    <div
                        class="flex items-center justify-between p-2 rounded-lg bg-violet-50 dark:bg-violet-900/10 border border-violet-100 dark:border-violet-800">
                        <div class="flex items-center gap-2">
                            <flux:icon.clipboard-document-check class="size-4 text-violet-500" />
                            <span class="text-sm font-medium text-violet-700 dark:text-violet-300">BA Scan</span>
                        </div>
                        <span
                            class="font-bold text-violet-700 dark:text-violet-300">{{ $this->stats['total_ba'] }}</span>
                    </div>

                    <div
                        class="flex items-center justify-between p-2 rounded-lg bg-teal-50 dark:bg-teal-900/10 border border-teal-100 dark:border-teal-800">
                        <div class="flex items-center gap-2">
                            <flux:icon.check-badge class="size-4 text-teal-500" />
                            <span class="text-sm font-medium text-teal-700 dark:text-teal-300">Selesai Distribusi</span>
                        </div>
                        <span
                            class="font-bold text-teal-700 dark:text-teal-300">{{ $this->stats['total_selesai'] }}</span>
                    </div>
                </div>
            </flux:card>

            {{-- 2. PANDUAN --}}
            <flux:card
                class="rounded-xl border bg-indigo-50/50 dark:bg-indigo-900/10 border-indigo-100 dark:border-indigo-800/30 shadow-sm">
                <div class="flex items-start gap-3">
                    <flux:icon.information-circle class="mt-0.5 size-5 text-indigo-600 dark:text-indigo-400" />
                    <div>
                        <h3 class="font-semibold text-indigo-900 dark:text-indigo-100 text-sm">Informasi Arsip</h3>
                        <ul class="mt-3 text-xs text-indigo-800 dark:text-indigo-200 space-y-2 list-disc list-inside">
                            <li>Halaman ini memuat seluruh dokumen terkait KP mahasiswa bimbingan Anda.</li>
                            <li><strong>Laporan KP:</strong> Diunggah mahasiswa sebelum seminar.</li>
                            <li><strong>BA Scan:</strong> Anda unggah saat input nilai.</li>
                            <li><strong>Bukti Distribusi:</strong> Diunggah mahasiswa setelah revisi selesai.</li>
                        </ul>
                    </div>
                </div>
            </flux:card>
        </div>
    </div>
</div>
