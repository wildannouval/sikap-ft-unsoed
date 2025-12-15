<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <flux:heading size="xl" level="1" class="text-stone-900 dark:text-stone-100">
                Nilai Kerja Praktik
            </flux:heading>
            <flux:subheading class="text-zinc-600 dark:text-zinc-300">
                Lihat rekap nilai, unggah bukti distribusi, dan akses BA scan.
            </flux:subheading>
        </div>
    </div>
    <flux:separator variant="subtle" />

    {{-- GRID UTAMA 7:3 --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-10">

        {{-- KOLOM KIRI: TABEL (7) --}}
        <div class="lg:col-span-7 space-y-6">
            <flux:card
                class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm overflow-hidden">

                {{-- Header Tabel --}}
                <div
                    class="flex flex-col gap-4 px-6 py-4 border-b border-zinc-200 dark:border-stone-800 bg-zinc-50/50 dark:bg-stone-900/50 md:flex-row md:items-center md:justify-between">
                    <h4 class="text-base font-semibold text-stone-900 dark:text-stone-100">Daftar Nilai</h4>

                    <div class="flex flex-col gap-3 md:flex-row md:items-center">
                        <div class="w-full md:w-56">
                            <flux:input icon="magnifying-glass" placeholder="Cari judul..."
                                wire:model.live.debounce.300ms="q" class="bg-white dark:bg-stone-900" />
                        </div>
                        <div class="w-full md:w-40">
                            <flux:select wire:model.live="filterStatus" placeholder="Semua Status"
                                class="bg-white dark:bg-stone-900">
                                <flux:select.option value="">Semua Status</flux:select.option>
                                @foreach ($this->statusOptions as $option)
                                    <flux:select.option value="{{ $option['value'] }}">
                                        {{ $option['label'] }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>
                    </div>
                </div>

                <flux:table class="[&_thead_th]:bg-zinc-50 [&_thead_th]:dark:bg-stone-900/40" :paginate="$this->items">
                    <flux:table.columns>
                        <flux:table.column class="w-10">No</flux:table.column>
                        <flux:table.column>Judul Laporan</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                        <flux:table.column>Distribusi</flux:table.column>
                        <flux:table.column>Nilai Akhir</flux:table.column>
                        <flux:table.column>Aksi</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @forelse ($this->items as $i => $row)
                            @php
                                $status = $row->status;
                                $badgeTheme = match ($status) {
                                    'dinilai' => ['color' => 'purple', 'icon' => 'star'],
                                    'ba_terbit' => ['color' => 'violet', 'icon' => 'document-text'],
                                    'selesai' => ['color' => 'teal', 'icon' => 'check-badge'],
                                    default => ['color' => 'zinc', 'icon' => 'minus'],
                                };
                            @endphp

                            <flux:table.row :key="$row->id">
                                <flux:table.cell class="text-zinc-500 text-center">{{ $this->items->firstItem() + $i }}
                                </flux:table.cell>

                                <flux:table.cell class="max-w-[240px]">
                                    <div
                                        class="line-clamp-2 text-stone-900 dark:text-stone-100 font-medium leading-snug">
                                        {{ $row->judul_laporan ?? '—' }}
                                    </div>
                                    <div class="text-xs text-zinc-500 mt-0.5">
                                        {{ optional($row->tanggal_seminar)->format('d M Y') }}
                                    </div>
                                </flux:table.cell>

                                <flux:table.cell>
                                    <flux:badge size="sm" :color="$badgeTheme['color']" inset="top bottom"
                                        icon="{{ $badgeTheme['icon'] }}">
                                        {{ $row::statusLabel($status) }}
                                    </flux:badge>
                                </flux:table.cell>

                                <flux:table.cell class="whitespace-nowrap">
                                    @if ($row->distribusi_proof_path)
                                        <a class="flex items-center gap-1.5 text-sm text-emerald-600 hover:text-emerald-700 hover:underline"
                                            href="{{ asset('storage/' . $row->distribusi_proof_path) }}"
                                            target="_blank">
                                            <flux:icon.check-circle class="size-4" />
                                            <span>Lihat Bukti</span>
                                        </a>
                                        <div class="text-[10px] text-zinc-400 mt-0.5">
                                            {{ $row->distribusi_uploaded_at?->format('d/m/y H:i') }}
                                        </div>
                                    @else
                                        @if (in_array($status, ['dinilai', 'ba_terbit']))
                                            {{-- FIX: Tombol ini memicu openUpload --}}
                                            <flux:button size="xs" variant="primary" icon="arrow-up-tray"
                                                wire:click="openUpload({{ $row->id }})">
                                                Upload
                                            </flux:button>
                                        @else
                                            <span class="text-xs text-zinc-400 italic">Menunggu nilai</span>
                                        @endif
                                    @endif
                                </flux:table.cell>

                                <flux:table.cell>
                                    @if ($row->distribusi_proof_path && $row->grade)
                                        <div class="text-sm font-bold text-stone-900 dark:text-stone-100">
                                            {{ number_format($row->grade->final_score, 2) }}
                                            <span
                                                class="ml-1 text-zinc-500 font-normal">({{ $row->grade->final_letter }})</span>
                                        </div>
                                    @elseif(!$row->distribusi_proof_path && $row->grade)
                                        <span class="text-xs text-zinc-400 italic">Upload bukti dulu</span>
                                    @else
                                        <span class="text-xs text-zinc-400">—</span>
                                    @endif
                                </flux:table.cell>

                                <flux:table.cell>
                                    @if ($row->grade?->ba_scan_path)
                                        <a class="flex items-center gap-1.5 text-sm text-indigo-600 hover:text-indigo-700 hover:underline"
                                            href="{{ asset('storage/' . $row->grade->ba_scan_path) }}" target="_blank">
                                            <flux:icon.document-text class="size-4" />
                                            <span>BA Scan</span>
                                        </a>
                                    @else
                                        <span class="text-xs text-zinc-400">—</span>
                                    @endif
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="6">
                                    <div class="py-8 text-center text-sm text-zinc-500">
                                        Belum ada data nilai kerja praktik.
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </flux:card>
        </div>

        {{-- KOLOM KANAN: INFO RINGKAS (3) --}}
        <div class="lg:col-span-3 space-y-6">
            {{-- 1. STATUS SUMMARY (Boxed Style) --}}
            <flux:card
                class="rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm">
                <div class="mb-5">
                    <div class="flex items-center gap-2">
                        <flux:icon.chart-bar class="size-5 text-zinc-500" />
                        <h3 class="font-semibold text-stone-900 dark:text-stone-100">Ringkasan Nilai</h3>
                    </div>
                    <p class="mt-1.5 text-xs text-zinc-500 dark:text-zinc-400 pl-7">
                        Statistik penilaian & penyelesaian KP.
                    </p>
                </div>

                <div class="space-y-3">
                    {{-- Dinilai --}}
                    <div
                        class="flex items-center justify-between p-2 rounded-lg bg-purple-50/50 dark:bg-purple-900/10 border border-purple-100 dark:border-purple-800/30">
                        <div class="flex items-center gap-2">
                            <div class="size-2 rounded-full bg-purple-500"></div>
                            <span class="text-sm font-medium text-stone-700 dark:text-stone-300">Dinilai</span>
                        </div>
                        <span class="text-lg font-bold text-purple-600 dark:text-purple-400">
                            {{ $this->stats['dinilai'] ?? 0 }}
                        </span>
                    </div>

                    {{-- BA Terbit --}}
                    <div
                        class="flex items-center justify-between p-2 rounded-lg bg-violet-50/50 dark:bg-violet-900/10 border border-violet-100 dark:border-violet-800/30">
                        <div class="flex items-center gap-2">
                            <div class="size-2 rounded-full bg-violet-500"></div>
                            <span class="text-sm font-medium text-stone-700 dark:text-stone-300">BA Terbit</span>
                        </div>
                        <span class="text-lg font-bold text-violet-600 dark:text-violet-400">
                            {{ $this->stats['ba_terbit'] ?? 0 }}
                        </span>
                    </div>

                    {{-- Selesai (Distribusi Uploaded) --}}
                    <div
                        class="flex items-center justify-between p-2 rounded-lg bg-teal-50/50 dark:bg-teal-900/10 border border-teal-100 dark:border-teal-800/30">
                        <div class="flex items-center gap-2">
                            <div class="size-2 rounded-full bg-teal-500"></div>
                            <span class="text-sm font-medium text-stone-700 dark:text-stone-300">Selesai</span>
                        </div>
                        <span class="text-lg font-bold text-teal-600 dark:text-teal-400">
                            {{ $this->stats['selesai'] ?? 0 }}
                        </span>
                    </div>
                </div>
            </flux:card>

            {{-- 2. PANDUAN --}}
            <flux:card
                class="rounded-xl border bg-sky-50/50 dark:bg-sky-900/10 border-sky-100 dark:border-sky-800/30 shadow-sm">
                <div class="flex items-start gap-3">
                    <flux:icon.information-circle class="mt-0.5 size-5 text-sky-600 dark:text-sky-400" />
                    <div>
                        <h3 class="font-semibold text-sky-900 dark:text-sky-100 text-sm">Alur Penilaian</h3>
                        <ul class="mt-3 text-xs text-sky-800 dark:text-sky-200 space-y-3">
                            <li class="flex gap-2">
                                <span
                                    class="flex-none font-bold bg-sky-200 dark:bg-sky-800 rounded-full w-5 h-5 flex items-center justify-center text-[10px]">1</span>
                                <span>Nilai tampil setelah Dosen memberi nilai.</span>
                            </li>
                            <li class="flex gap-2">
                                <span
                                    class="flex-none font-bold bg-sky-200 dark:bg-sky-800 rounded-full w-5 h-5 flex items-center justify-center text-[10px]">2</span>
                                <span>Klik <strong>Upload Bukti</strong> saat status <em>Dinilai</em> / <em>BA
                                        Terbit</em>.</span>
                            </li>
                            <li class="flex gap-2">
                                <span
                                    class="flex-none font-bold bg-sky-200 dark:bg-sky-800 rounded-full w-5 h-5 flex items-center justify-center text-[10px]">3</span>
                                <span>Unduh <strong>BA Scan</strong> jika sudah tersedia.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </flux:card>
        </div>
    </div>

    {{-- Modal Upload Distribusi --}}
    {{-- FIX: Gunakan :show="$showUploadModal" dan name yang sesuai --}}
    <flux:modal name="mhs-upload-distribusi" :show="$showUploadModal" class="md:w-[32rem]">
        @if ($uploadSeminarId)
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Upload Bukti Distribusi</flux:heading>
                    <p class="text-sm text-zinc-500">
                        Unggah bukti penyerahan laporan KP untuk melihat nilai.
                    </p>
                </div>

                <div class="space-y-4">
                    {{-- File Input --}}
                    <div class="space-y-2">
                        <flux:input type="file" wire:model="file" accept=".pdf,.jpg,.jpeg,.png"
                            label="Berkas (PDF/JPG/PNG, maks 10 MB)" />

                        <div wire:loading wire:target="file" class="text-xs text-zinc-500">Mengunggah...</div>

                        @error('file')
                            <div class="text-sm text-rose-600">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Tombol Aksi --}}
                    <div class="flex justify-end gap-2 pt-2">
                        {{-- Gunakan wire:click untuk menutup, hindari atribut 'modal' jika menggunakan state Livewire --}}
                        <flux:button variant="ghost" wire:click="closeUpload">Batal</flux:button>

                        <flux:button variant="primary" icon="check" wire:click="saveUpload"
                            wire:loading.attr="disabled">
                            Simpan
                        </flux:button>
                    </div>
                </div>
            </div>
        @else
            <div class="p-6 text-sm text-zinc-500 text-center">Memuat formulir...</div>
        @endif
    </flux:modal>
</div>
