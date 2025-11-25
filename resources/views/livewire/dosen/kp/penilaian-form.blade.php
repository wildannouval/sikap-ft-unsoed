<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1" class="text-stone-900 dark:text-stone-100">
                Penilaian Kerja Praktik (Dosen Pembimbing)
            </flux:heading>
            <flux:subheading class="text-zinc-600 dark:text-zinc-300">
                Cari mahasiswa bimbingan, buka form penilaian, isi komponen (60% dospem + 40% pembimbing lapangan), lalu
                simpan.
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
                    Panduan Penilaian KP
                </h3>
                <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-300 space-y-1.5">
                    <div><span class="font-medium">1)</span> Gunakan kolom <em>cari</em> untuk nama/NIM/judul laporan.
                        Data yang tampil adalah seminar berstatus <em>BA Terbit</em> atau <em>Dinilai</em>.</div>
                    <div><span class="font-medium">2)</span> Komponen <strong>Dosen Pembimbing 60%</strong> dan
                        <strong>Pembimbing Lapangan 40%</strong>. Rentang skor 0–100.</div>
                    <div><span class="font-medium">3)</span> Opsional: unggah <strong>scan BA</strong> (PDF/JPG/PNG,
                        maks 10 MB) sebagai arsip saat menyimpan nilai.</div>
                    <div><span class="font-medium">4)</span> Setelah tersimpan, status seminar menjadi <em>Dinilai</em>
                        dan mahasiswa mendapat notifikasi untuk unggah bukti distribusi.</div>
                    <div><span class="font-medium">5)</span> Nilai akhir & huruf (A–D) akan muncul pada tabel jika sudah
                        ada rekaman nilai.</div>
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
                <h3 class="text-sm font-medium tracking-wide">Daftar Seminar untuk Dinilai</h3>

                <div class="flex items-center gap-2">
                    <flux:input class="md:w-72" placeholder="Cari nama / NIM / judul…" wire:model.debounce.400ms="q"
                        icon="magnifying-glass" />
                    <flux:select wire:model="perPage" class="w-24">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </flux:select>
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
                    <flux:table.column>Diperbarui</flux:table.column>
                    <flux:table.column>Mahasiswa</flux:table.column>
                    <flux:table.column>Judul</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Distribusi</flux:table.column>
                    <flux:table.column class="w-28 text-right">Aksi</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->items as $i => $row)
                        <flux:table.row :key="$row->id">
                            <flux:table.cell>{{ $this->items->firstItem() + $i }}</flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap text-xs text-zinc-500">
                                {{ $row->updated_at?->format('d M Y H:i') ?? '—' }}
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                <div class="font-medium text-stone-900 dark:text-stone-100">
                                    {{ $row->kp?->mahasiswa?->user?->name ?? '—' }}
                                </div>
                                <div class="text-xs text-zinc-500">
                                    {{ $row->kp?->mahasiswa?->nim ?? ($row->kp?->mahasiswa?->mahasiswa_nim ?? '') }}
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="max-w-[420px]">
                                <div class="line-clamp-2 text-stone-900 dark:text-stone-100">
                                    {{ $row->judul_laporan ?? '—' }}
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge size="sm" inset="top bottom"
                                    :color="\App\Models\KpSeminar::badgeColor($row->status)">
                                    {{ \App\Models\KpSeminar::statusLabel($row->status) }}
                                </flux:badge>
                                @if ($row->grade)
                                    <div class="text-xs text-zinc-500 mt-1">
                                        Nilai akhir: <span class="font-medium">{{ $row->grade->final_score }}</span>
                                        ({{ $row->grade->final_letter }})
                                    </div>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                @if ($row->distribusi_proof_path)
                                    <a class="text-sm underline hover:no-underline"
                                        href="{{ asset('storage/' . $row->distribusi_proof_path) }}" target="_blank">
                                        Lihat Bukti
                                    </a>
                                    <div class="text-[11px] text-zinc-500">
                                        {{ $row->distribusi_uploaded_at?->format('d M Y H:i') }}
                                    </div>
                                @else
                                    <span class="text-xs text-zinc-400">Belum diupload</span>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell class="text-right">
                                <flux:button size="sm" variant="ghost" icon="pencil-square"
                                    wire:click="open({{ $row->id }})">
                                    Nilai
                                </flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>
    </flux:card>

    {{-- FORM PENILAIAN (expanded card) --}}
    @if ($editingId)
        <flux:card
            class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-xs">
            <div class="flex items-center gap-2">
                <h3 class="text-base font-semibold text-stone-900 dark:text-stone-100">Form Penilaian</h3>
                <flux:badge>KP Seminar #{{ $editingId }}</flux:badge>
                <flux:spacer />
                <flux:button variant="ghost" icon="x-mark" wire:click="$set('editingId', null)">
                    Tutup
                </flux:button>
            </div>

            {{-- Ringkasan --}}
            <div class="grid md:grid-cols-3 gap-5 text-sm">
                <div>
                    <div class="text-zinc-500">Mahasiswa</div>
                    <div class="font-medium text-stone-900 dark:text-stone-100">
                        {{ $seminar?->kp?->mahasiswa?->user?->name ?? '—' }}
                    </div>
                    <div class="text-xs text-zinc-500">
                        {{ $seminar?->kp?->mahasiswa?->nim ?? ($seminar?->kp?->mahasiswa?->mahasiswa_nim ?? '') }}
                    </div>
                </div>
                <div>
                    <div class="text-zinc-500">Judul</div>
                    <div class="font-medium line-clamp-2 text-stone-900 dark:text-stone-100">
                        {{ $seminar?->judul_laporan ?? '—' }}
                    </div>
                </div>
                <div>
                    <div class="text-zinc-500">Bukti Distribusi</div>
                    @if ($seminar?->distribusi_proof_path)
                        <a class="underline hover:no-underline"
                            href="{{ asset('storage/' . $seminar->distribusi_proof_path) }}" target="_blank">Lihat
                            Bukti</a>
                        <div class="text-xs text-zinc-500">
                            {{ $seminar->distribusi_uploaded_at?->format('d M Y H:i') }}
                        </div>
                    @else
                        <span class="text-xs text-zinc-400">Belum diupload mahasiswa</span>
                    @endif
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-5">
                <div class="space-y-3">
                    <h4 class="font-medium text-stone-900 dark:text-stone-100">Komponen Dosen Pembimbing (60%)</h4>
                    <flux:input type="number" label="Sistematika Laporan" wire:model.live="dospem_sistematika_laporan"
                        min="0" max="100" />
                    <flux:input type="number" label="Tata Bahasa" wire:model.live="dospem_tata_bahasa" min="0"
                        max="100" />
                    <flux:input type="number" label="Sistematika Seminar" wire:model.live="dospem_sistematika_seminar"
                        min="0" max="100" />
                    <flux:input type="number" label="Kecocokan Isi" wire:model.live="dospem_kecocokan_isi"
                        min="0" max="100" />
                    <flux:input type="number" label="Materi KP" wire:model.live="dospem_materi_kp" min="0"
                        max="100" />
                    <flux:input type="number" label="Penguasaan Masalah" wire:model.live="dospem_penguasaan_masalah"
                        min="0" max="100" />
                    <flux:input type="number" label="Diskusi" wire:model.live="dospem_diskusi" min="0"
                        max="100" />
                </div>

                <div class="space-y-3">
                    <h4 class="font-medium text-stone-900 dark:text-stone-100">Komponen Pembimbing Lapangan (40%)</h4>
                    <flux:input type="number" label="Kesesuaian" wire:model.live="pl_kesesuaian" min="0"
                        max="100" />
                    <flux:input type="number" label="Kehadiran" wire:model.live="pl_kehadiran" min="0"
                        max="100" />
                    <flux:input type="number" label="Kedisiplinan" wire:model.live="pl_kedisiplinan" min="0"
                        max="100" />
                    <flux:input type="number" label="Keaktifan" wire:model.live="pl_keaktifan" min="0"
                        max="100" />
                    <flux:input type="number" label="Kecermatan" wire:model.live="pl_kecermatan" min="0"
                        max="100" />
                    <flux:input type="number" label="Tanggung Jawab" wire:model.live="pl_tanggung_jawab"
                        min="0" max="100" />
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-5">
                <div class="space-y-3">
                    <h4 class="font-medium text-stone-900 dark:text-stone-100">Unggah Berita Acara (Scan)</h4>
                    <flux:input type="file" accept=".pdf,.jpg,.jpeg,.png" wire:model="ba_scan"
                        label="BA (PDF/JPG/PNG, maks 10 MB)" />
                    @error('ba_scan')
                        <div class="text-sm text-rose-600">{{ $message }}</div>
                    @enderror

                    @if ($ba_scan_path)
                        <div class="text-sm">
                            Berkas saat ini:
                            <a class="underline hover:no-underline" target="_blank"
                                href="{{ asset('storage/' . $ba_scan_path) }}">Lihat</a>
                        </div>
                    @endif

                    <div wire:loading wire:target="ba_scan" class="text-xs text-zinc-500">Mengunggah…</div>
                </div>
            </div>

            <div class="flex justify-end">
                <flux:button variant="primary" icon="check" wire:click="save" wire:loading.attr="disabled">
                    Simpan Nilai
                </flux:button>
            </div>
        </flux:card>
    @endif
</div>
