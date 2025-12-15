<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1" class="text-stone-900 dark:text-stone-100">
                Penilaian KP
            </flux:heading>
            <flux:subheading class="text-zinc-600 dark:text-zinc-300">
                Input nilai seminar mahasiswa bimbingan.
            </flux:subheading>
        </div>
    </div>
    <flux:separator variant="subtle" />

    {{-- GRID UTAMA 3:1 --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">

        {{-- KOLOM KIRI: FORM / TABEL (3) --}}
        <div class="lg:col-span-3 space-y-6">

            {{-- FORM PENILAIAN (Jika ada yg dipilih) --}}
            @if ($editingId && $seminarSelected)
                <flux:card
                    class="space-y-6 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm">
                    <div class="flex items-center gap-2 border-b border-zinc-100 dark:border-zinc-800 pb-4 mb-4">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-stone-900 dark:text-stone-100">Form Penilaian</h3>
                            <p class="text-sm text-zinc-500">
                                Mahasiswa: <span
                                    class="font-medium text-stone-900 dark:text-stone-100">{{ $seminarSelected->kp->mahasiswa->user->name }}</span>
                            </p>
                        </div>
                        <flux:badge size="sm">{{ $seminarSelected->kp->mahasiswa->mahasiswa_nim }}</flux:badge>
                        <flux:button size="sm" variant="ghost" icon="x-mark" wire:click="$set('editingId', null)">
                            Tutup
                        </flux:button>
                    </div>

                    <div class="grid md:grid-cols-2 gap-8">
                        {{-- Komponen Dosen --}}
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 mb-2">
                                <div
                                    class="size-6 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold">
                                    D</div>
                                <h4 class="font-medium text-stone-900 dark:text-stone-100">Komponen Dospem (60%)</h4>
                            </div>

                            <flux:input type="number" label="Sistematika Laporan"
                                wire:model.defer="dospem_sistematika_laporan" min="0" max="100" />
                            <flux:input type="number" label="Tata Bahasa" wire:model.defer="dospem_tata_bahasa"
                                min="0" max="100" />
                            <flux:input type="number" label="Sistematika Seminar"
                                wire:model.defer="dospem_sistematika_seminar" min="0" max="100" />
                            <flux:input type="number" label="Kecocokan Isi" wire:model.defer="dospem_kecocokan_isi"
                                min="0" max="100" />
                            <flux:input type="number" label="Materi KP" wire:model.defer="dospem_materi_kp"
                                min="0" max="100" />
                            <flux:input type="number" label="Penguasaan Masalah"
                                wire:model.defer="dospem_penguasaan_masalah" min="0" max="100" />
                            <flux:input type="number" label="Diskusi" wire:model.defer="dospem_diskusi" min="0"
                                max="100" />
                        </div>

                        {{-- Komponen PL --}}
                        <div class="space-y-4">
                            <div class="flex items-center gap-2 mb-2">
                                <div
                                    class="size-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs font-bold">
                                    P</div>
                                <h4 class="font-medium text-stone-900 dark:text-stone-100">Komponen Lapangan (40%)</h4>
                            </div>

                            <flux:input type="number" label="Kesesuaian" wire:model.defer="pl_kesesuaian"
                                min="0" max="100" />
                            <flux:input type="number" label="Kehadiran" wire:model.defer="pl_kehadiran" min="0"
                                max="100" />
                            <flux:input type="number" label="Kedisiplinan" wire:model.defer="pl_kedisiplinan"
                                min="0" max="100" />
                            <flux:input type="number" label="Keaktifan" wire:model.defer="pl_keaktifan" min="0"
                                max="100" />
                            <flux:input type="number" label="Kecermatan" wire:model.defer="pl_kecermatan"
                                min="0" max="100" />
                            <flux:input type="number" label="Tanggung Jawab" wire:model.defer="pl_tanggung_jawab"
                                min="0" max="100" />
                        </div>
                    </div>

                    <flux:separator />

                    {{-- Upload BA --}}
                    <div class="space-y-3">
                        <h4 class="font-medium text-stone-900 dark:text-stone-100">Unggah Berita Acara (Scan)</h4>
                        <div class="grid md:grid-cols-2 gap-4">
                            <flux:input type="file" accept=".pdf,.jpg,.jpeg,.png" wire:model="ba_scan"
                                label="File BA (PDF/Img, Max 10MB)" />

                            @if ($ba_scan_path)
                                <div class="flex items-end pb-2">
                                    <a class="text-sm flex items-center gap-2 text-indigo-600 hover:underline"
                                        target="_blank" href="{{ asset('storage/' . $ba_scan_path) }}">
                                        <flux:icon.document-text class="size-4" /> Lihat Berkas Saat Ini
                                    </a>
                                </div>
                            @endif
                        </div>
                        <div wire:loading wire:target="ba_scan" class="text-xs text-zinc-500">Mengunggah...</div>
                        @error('ba_scan')
                            <div class="text-sm text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="flex justify-end pt-4">
                        <flux:button variant="primary" icon="check" wire:click="save"
                            wire:loading.attr="disabled">
                            Simpan Nilai
                        </flux:button>
                    </div>
                </flux:card>
            @else
                {{-- TABEL DAFTAR SEMINAR --}}
                <flux:card
                    class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm overflow-hidden">
                    <div
                        class="flex flex-col gap-4 px-6 py-4 border-b border-zinc-200 dark:border-stone-800 bg-indigo-50/50 dark:bg-indigo-900/10 md:flex-row md:items-center md:justify-between">
                        <h4 class="text-base font-semibold text-stone-900 dark:text-stone-100">Antrean Penilaian</h4>
                        <div class="flex items-center gap-3">
                            <flux:input icon="magnifying-glass" placeholder="Cari mahasiswa..."
                                wire:model.live.debounce.400ms="q"
                                class="w-full md:w-64 bg-white dark:bg-stone-900" />
                        </div>
                    </div>

                    <flux:table :paginate="$this->items">
                        <flux:table.columns>
                            <flux:table.column class="w-10">No</flux:table.column>
                            <flux:table.column>Mahasiswa</flux:table.column>
                            <flux:table.column>Judul Laporan</flux:table.column>
                            <flux:table.column>Status</flux:table.column>
                            <flux:table.column>Nilai</flux:table.column>
                            <flux:table.column class="text-right">Aksi</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @foreach ($this->items as $i => $row)
                                <flux:table.row :key="$row->id">
                                    <flux:table.cell class="text-center text-zinc-500">
                                        {{ $this->items->firstItem() + $i }}</flux:table.cell>

                                    <flux:table.cell>
                                        <div class="font-medium text-stone-900 dark:text-stone-100">
                                            {{ $row->kp?->mahasiswa?->user?->name }}</div>
                                        <div class="text-xs text-zinc-500">{{ $row->kp?->mahasiswa?->mahasiswa_nim }}
                                        </div>
                                    </flux:table.cell>

                                    <flux:table.cell class="max-w-[280px]">
                                        <span class="line-clamp-2 text-sm">{{ $row->judul_laporan }}</span>
                                    </flux:table.cell>

                                    <flux:table.cell>
                                        <flux:badge size="sm" :color="$this->badgeColor($row->status)"
                                            inset="top bottom">
                                            {{ $this->statusLabel($row->status) }}
                                        </flux:badge>
                                    </flux:table.cell>

                                    <flux:table.cell>
                                        @if ($row->grade)
                                            <span
                                                class="font-bold text-stone-900 dark:text-stone-100">{{ $row->grade->final_score }}</span>
                                            <span
                                                class="text-zinc-500 text-xs">({{ $row->grade->final_letter }})</span>
                                        @else
                                            <span class="text-zinc-400 text-xs">—</span>
                                        @endif
                                    </flux:table.cell>

                                    <flux:table.cell class="text-right">
                                        <flux:button size="xs" variant="primary" icon="pencil-square"
                                            wire:click="open({{ $row->id }})">
                                            Nilai
                                        </flux:button>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>

                    @if ($this->items->isEmpty())
                        <div class="flex flex-col items-center justify-center py-12 text-center">
                            <div class="rounded-full bg-zinc-100 p-4 dark:bg-stone-900">
                                <flux:icon.clipboard-document-check class="size-8 text-zinc-400" />
                            </div>
                            <h3 class="mt-4 text-base font-semibold text-stone-900 dark:text-stone-100">Tidak ada
                                antrean</h3>
                            <p class="mt-1 text-sm text-zinc-500">
                                @if ($q)
                                    Tidak ditemukan data dengan kata kunci "{{ $q }}".
                                @else
                                    Belum ada seminar yang siap dinilai (Status: BA Terbit).
                                @endif
                            </p>
                        </div>
                    @endif
                </flux:card>
            @endif
        </div>

        {{-- KOLOM KANAN: SIDEBAR (1) --}}
        <div class="lg:col-span-1 space-y-6">

            {{-- 1. RINGKASAN --}}
            <flux:card
                class="rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm">
                <div class="mb-4 flex items-center gap-2">
                    <flux:icon.chart-bar class="size-5 text-zinc-500" />
                    <h3 class="font-semibold text-stone-900 dark:text-stone-100">Ringkasan</h3>
                </div>

                <div class="space-y-3">
                    <div
                        class="flex items-center justify-between p-2 rounded-lg bg-violet-50/50 dark:bg-violet-900/10 border border-violet-100 dark:border-violet-800/30">
                        <div class="flex items-center gap-2">
                            <div class="size-2 rounded-full bg-violet-500 animate-pulse"></div>
                            <span class="text-sm font-medium text-stone-700 dark:text-stone-300">Perlu Dinilai</span>
                        </div>
                        <span
                            class="text-lg font-bold text-violet-600 dark:text-violet-400">{{ $this->stats['pending'] }}</span>
                    </div>

                    <div
                        class="flex items-center justify-between p-2 rounded-lg bg-purple-50/50 dark:bg-purple-900/10 border border-purple-100 dark:border-purple-800/30">
                        <div class="flex items-center gap-2">
                            <div class="size-2 rounded-full bg-purple-500"></div>
                            <span class="text-sm font-medium text-stone-700 dark:text-stone-300">Selesai Dinilai</span>
                        </div>
                        <span
                            class="text-lg font-bold text-purple-600 dark:text-purple-400">{{ $this->stats['completed'] }}</span>
                    </div>
                </div>
            </flux:card>

            {{-- 2. PANDUAN --}}
            <flux:card
                class="rounded-xl border bg-sky-50/50 dark:bg-sky-900/10 border-sky-100 dark:border-sky-800/30 shadow-sm">
                <div class="flex items-start gap-3">
                    <flux:icon.information-circle class="mt-0.5 size-5 text-sky-600 dark:text-sky-400" />
                    <div>
                        <h3 class="font-semibold text-sky-900 dark:text-sky-100 text-sm">Panduan Penilaian</h3>
                        <ul class="mt-3 text-xs text-sky-800 dark:text-sky-200 space-y-2 list-disc list-inside">
                            <li>Data yang tampil adalah seminar dengan status <strong>BA Terbit</strong>.</li>
                            <li>Komponen nilai: <strong>Dosen (60%)</strong> dan <strong>Lapangan (40%)</strong>.</li>
                            <li>Unggah <strong>Scan BA</strong> sebagai bukti fisik penilaian.</li>
                            <li>Setelah disimpan, status berubah menjadi <strong>Dinilai</strong>.</li>
                        </ul>
                    </div>
                </div>
            </flux:card>
        </div>
    </div>
</div>
