<div class="space-y-6">
    @if (session('ok'))
        <div class="rounded-md border border-emerald-300/60 bg-emerald-50 px-3 py-2 text-emerald-800">
            <div class="font-medium">{{ session('ok') }}</div>
        </div>
    @endif

    <flux:card class="space-y-4">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-semibold">Nilai KP</h3>
                <p class="text-sm text-zinc-500">Lihat hasil penilaian seminar & dokumen BA scan.</p>
            </div>
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

        <flux:table :paginate="$this->items">
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
                            <div class="line-clamp-2">{{ $row->judul_laporan ?? '—' }}</div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge size="sm" :color="$row::badgeColor($row->status)">
                                {{ $row::statusLabel($row->status) }}
                            </flux:badge>
                        </flux:table.cell>

                        {{-- Distribusi: jika sudah ada => link; jika belum => tombol buka modal --}}
                        <flux:table.cell class="whitespace-nowrap">
                            @if ($row->distribusi_proof_path)
                                <a class="text-sm underline"
                                    href="{{ asset('storage/' . $row->distribusi_proof_path) }}" target="_blank">Lihat
                                    Bukti</a>
                                <div class="text-[11px] text-zinc-500">
                                    {{ $row->distribusi_uploaded_at?->format('d M Y H:i') }}
                                </div>
                            @else
                                @if (in_array($row->status, ['dinilai', 'ba_terbit']))
                                    {{-- Fallback modal attr memastikan modal tetap muncul walau reaktivitas Livewire terblokir --}}
                                    <flux:button size="xs" variant="primary" icon="arrow-up-tray"
                                        modal="mhs-upload-distribusi" wire:click="openUpload({{ $row->id }})">
                                        Upload Bukti
                                    </flux:button>
                                @else
                                    <span class="text-xs text-zinc-400">Menunggu dinilai</span>
                                @endif
                            @endif
                        </flux:table.cell>

                        {{-- Skor Akhir (tampilkan hanya jika distribusi sudah diupload) --}}
                        <flux:table.cell>
                            @if ($row->distribusi_proof_path && $row->grade)
                                <div class="text-sm font-medium">
                                    {{ number_format($row->grade->final_score, 2) }}
                                    ({{ $row->grade->final_letter }})
                                </div>
                            @elseif(!$row->distribusi_proof_path && $row->grade)
                                <span class="text-xs text-zinc-400">Upload bukti distribusi untuk melihat nilai</span>
                            @else
                                <span class="text-xs text-zinc-400">Belum dinilai</span>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="text-sm">
                            @if ($row->distribusi_proof_path && $row->grade)
                                Dospem {{ number_format($row->grade->score_dospem, 2) }}
                                • PL {{ number_format($row->grade->score_pl, 2) }}
                            @else
                                —
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($row->grade?->ba_scan_path)
                                <a class="text-sm underline" href="{{ asset('storage/' . $row->grade->ba_scan_path) }}"
                                    target="_blank">Lihat BA Scan</a>
                            @else
                                <span class="text-xs text-zinc-400">—</span>
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7">
                            <div class="py-6 text-center text-sm text-zinc-500">Belum ada data nilai.</div>
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
                            <h3 class="text-base font-semibold">Upload Bukti Distribusi</h3>
                            <p class="text-sm text-zinc-500">
                                Unggah bukti distribusi setelah status <span class="font-medium">Dinilai</span> atau
                                <span class="font-medium">BA Terbit</span>.
                            </p>
                        </div>
                        <flux:button variant="ghost" icon="x-mark" wire:click="closeUpload"
                            modal="mhs-upload-distribusi">
                            Tutup
                        </flux:button>
                    </div>

                    {{-- Form upload sebagai card form (komponen anak) --}}
                    <livewire:mahasiswa.kp.distribusi-upload :seminar-id="$uploadSeminarId" :key="'modal-upload-' . $uploadSeminarId" />

                </flux:card>
            </div>
        @else
            {{-- Skeleton ringan saat id belum tersetel (klik via fallback modal duluan) --}}
            <div class="p-6 text-sm text-zinc-500">Memuat formulir…</div>
        @endif
    </flux:modal>
</div>
