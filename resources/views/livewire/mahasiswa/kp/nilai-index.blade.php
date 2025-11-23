<div class="space-y-6">

    {{-- FLASH OK --}}
    @if (session('ok'))
        <div class="rounded-md border border-emerald-300/60 bg-emerald-50 px-3 py-2 text-emerald-800">
            <div class="font-medium">{{ session('ok') }}</div>
        </div>
    @endif

    {{-- PANDUAN HALAMAN --}}
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
                <h3 class="text-base font-semibold text-stone-900 dark:text-stone-100">Panduan Nilai KP</h3>
                <p class="text-sm text-zinc-600 dark:text-zinc-300">
                    1) Nilai hanya terlihat <em>setelah</em> bukti distribusi diunggah. 2) Klik <strong>Upload
                        Bukti</strong> pada baris seminar yang berstatus
                    <span class="font-medium">Dinilai</span> atau <span class="font-medium">BA Terbit</span>.
                    3) Jika sudah mengunggah, Anda bisa membuka <strong>BA Scan</strong> bila tersedia.
                </p>
            </div>
        </div>
    </flux:card>

    {{-- TABEL NILAI --}}
    <flux:card
        class="space-y-4 rounded-xl border
               bg-white dark:bg-stone-950
               border-zinc-200 dark:border-stone-800
               shadow-xs">

        {{-- Header card dengan aksen indigo (seragam modul lain) --}}
        <div
            class="px-4 py-3 border-b
                   bg-indigo-50 text-indigo-700
                   dark:bg-indigo-900/20 dark:text-indigo-300
                   border-indigo-100 dark:border-indigo-900/40
                   rounded-t-xl">
            <div class="flex items-center justify-between gap-3">
                <h3 class="text-sm font-medium tracking-wide">Nilai KP</h3>
                <div class="flex items-center gap-2">
                    <flux:input class="md:w-72" placeholder="Cari judul…" wire:model.debounce.400ms="q"
                        icon="magnifying-glass" />
                    <flux:select wire:model.live="perPage" class="w-24">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </flux:select>
                </div>
            </div>
        </div>

        <flux:table
            class="[&_thead_th]:bg-zinc-50 [&_thead_th]:dark:bg-stone-900/40
                   [&_thead_th]:text-zinc-600 [&_thead_th]:dark:text-stone-200
                   [&_tbody_tr]:hover:bg-zinc-50/60 [&_tbody_tr]:dark:hover:bg-stone-900/30"
            :paginate="$this->items">
            <flux:table.columns>
                <flux:table.column class="w-10">#</flux:table.column>
                <flux:table.column>Judul</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Distribusi</flux:table.column>
                <flux:table.column>Skor Akhir</flux:table.column>
                <flux:table.column>Rincian</flux:table.column>
                <flux:table.column>BA Scan</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->items as $i => $row)
                    <flux:table.row :key="$row->id">
                        <flux:table.cell>{{ $this->items->firstItem() + $i }}</flux:table.cell>

                        <flux:table.cell class="max-w-[420px]">
                            <div class="line-clamp-2 text-stone-900 dark:text-stone-100">
                                {{ $row->judul_laporan ?? '—' }}
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge size="sm" :color="$row::badgeColor($row->status)">
                                {{ $row::statusLabel($row->status) }}
                            </flux:badge>
                        </flux:table.cell>

                        {{-- Distribusi: jika sudah ada => link; jika belum => tombol buka modal --}}
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
                                @if (in_array($row->status, ['dinilai', 'ba_terbit']))
                                    {{-- Fallback modal attr memastikan modal muncul walau reaktivitas sempat delay --}}
                                    <flux:button size="xs" variant="primary" icon="arrow-up-tray"
                                        modal="mhs-upload-distribusi" wire:click="openUpload({{ $row->id }})">
                                        Upload Bukti
                                    </flux:button>
                                @else
                                    <span class="text-xs text-zinc-400 dark:text-stone-500">Menunggu dinilai</span>
                                @endif
                            @endif
                        </flux:table.cell>

                        {{-- Skor Akhir (tampil setelah distribusi diunggah) --}}
                        <flux:table.cell>
                            @if ($row->distribusi_proof_path && $row->grade)
                                <div class="text-sm font-medium text-stone-900 dark:text-stone-100">
                                    {{ number_format($row->grade->final_score, 2) }}
                                    ({{ $row->grade->final_letter }})
                                </div>
                            @elseif(!$row->distribusi_proof_path && $row->grade)
                                <span class="text-xs text-zinc-400 dark:text-stone-500">
                                    Upload bukti distribusi untuk melihat nilai
                                </span>
                            @else
                                <span class="text-xs text-zinc-400 dark:text-stone-500">Belum dinilai</span>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="text-sm">
                            @if ($row->distribusi_proof_path && $row->grade)
                                Dospem {{ number_format($row->grade->score_dospem, 2) }}
                                • PL {{ number_format($row->grade->score_pl, 2) }}
                            @else
                                <span class="text-xs text-zinc-400 dark:text-stone-500">—</span>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($row->grade?->ba_scan_path)
                                <a class="text-sm underline hover:no-underline"
                                    href="{{ asset('storage/' . $row->grade->ba_scan_path) }}" target="_blank">Lihat BA
                                    Scan</a>
                            @else
                                <span class="text-xs text-zinc-400 dark:text-stone-500">—</span>
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7">
                            <div class="py-6 text-center text-sm text-zinc-500 dark:text-stone-400">
                                Belum ada data nilai.
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>

    {{-- Modal Global: kombinasi :show (Livewire) + name (fallback modal attr) --}}
    <flux:modal name="mhs-upload-distribusi" :show="$showUploadModal" dismissable class="min-w-[34rem]">
        {{-- Saat tombol diklik, modal bisa muncul dulu via "modal" attr; konten akan mengisi saat $uploadSeminarId sudah diset --}}
        @if ($uploadSeminarId)
            <div class="p-1">
                <flux:card class="space-y-4 border-indigo-200/70 dark:border-indigo-900/40">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-stone-900 dark:text-stone-100">
                                Upload Bukti Distribusi
                            </h3>
                            <p class="text-sm text-zinc-600 dark:text-zinc-300">
                                Unggah bukti distribusi setelah status
                                <span class="font-medium">Dinilai</span> atau
                                <span class="font-medium">BA Terbit</span>.
                            </p>
                        </div>
                        <flux:button variant="ghost" icon="x-mark" wire:click="closeUpload"
                            modal="mhs-upload-distribusi">
                            Tutup
                        </flux:button>
                    </div>

                    {{-- Form upload sebagai komponen anak --}}
                    <livewire:mahasiswa.kp.distribusi-upload :seminar-id="$uploadSeminarId" :key="'modal-upload-' . $uploadSeminarId" />
                </flux:card>
            </div>
        @else
            {{-- Skeleton ringan saat id belum tersetel (klik via fallback modal duluan) --}}
            <div class="p-6 text-sm text-zinc-500 dark:text-stone-400">Memuat formulir…</div>
        @endif
    </flux:modal>
</div>
