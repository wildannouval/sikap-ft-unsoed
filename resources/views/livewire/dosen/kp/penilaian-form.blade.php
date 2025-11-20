<div class="space-y-6">
    @if (session('ok'))
        <div class="rounded-md border border-emerald-300/60 bg-emerald-50 px-3 py-2 text-emerald-800">
            <div class="font-medium">{{ session('ok') }}</div>
        </div>
    @endif

    <flux:card class="space-y-4">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-semibold">Penilaian KP</h3>
                <p class="text-sm text-zinc-500">Cari mahasiswa, buka form, isi komponen, dan simpan.</p>
            </div>
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

        <flux:table :paginate="$this->items">
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
                            <div class="font-medium">{{ $row->kp?->mahasiswa?->user?->name ?? '—' }}</div>
                            <div class="text-xs text-zinc-500">
                                {{ $row->kp?->mahasiswa?->nim ?? ($row->kp?->mahasiswa?->mahasiswa_nim ?? '') }}
                            </div>
                        </flux:table.cell>

                        <flux:table.cell class="max-w-[420px]">
                            <div class="line-clamp-2">{{ $row->judul_laporan ?? '—' }}</div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge size="sm" :color="\App\Models\KpSeminar::badgeColor($row->status)">
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
                                <a class="text-sm underline"
                                    href="{{ asset('storage/' . $row->distribusi_proof_path) }}" target="_blank">
                                    Lihat Bukti
                                </a>
                                <div class="text-[11px] text-zinc-500">
                                    {{ $row->distribusi_uploaded_at?->format('d M Y H:i') }}</div>
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
    </flux:card>

    {{-- Modal Form Penilaian --}}
    @if ($editingId)
        <flux:card class="space-y-4">
            <div class="flex items-center gap-2">
                <h3 class="text-base font-semibold">Form Penilaian</h3>
                <flux:badge>KP Seminar #{{ $editingId }}</flux:badge>
                <flux:spacer />
                <flux:button variant="ghost" icon="x-mark" wire:click="$set('editingId', null)">
                    Tutup
                </flux:button>
            </div>

            {{-- Ringkasan & Bukti Distribusi --}}
            <div class="grid md:grid-cols-3 gap-5 text-sm">
                <div>
                    <div class="text-zinc-500">Mahasiswa</div>
                    <div class="font-medium">
                        {{ $seminar?->kp?->mahasiswa?->user?->name ?? '—' }}
                    </div>
                    <div class="text-xs text-zinc-500">
                        {{ $seminar?->kp?->mahasiswa?->nim ?? ($seminar?->kp?->mahasiswa?->mahasiswa_nim ?? '') }}
                    </div>
                </div>
                <div>
                    <div class="text-zinc-500">Judul</div>
                    <div class="font-medium line-clamp-2">{{ $seminar?->judul_laporan ?? '—' }}</div>
                </div>
                <div>
                    <div class="text-zinc-500">Bukti Distribusi</div>
                    @if ($seminar?->distribusi_proof_path)
                        <a class="underline" href="{{ asset('storage/' . $seminar->distribusi_proof_path) }}"
                            target="_blank">Lihat Bukti</a>
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
                    <h4 class="font-medium">Komponen Dosen Pembimbing (60%)</h4>
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
                    <h4 class="font-medium">Komponen Pembimbing Lapangan (40%)</h4>
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
                    <h4 class="font-medium">Unggah Berita Acara (Scan)</h4>
                    <flux:input type="file" accept=".pdf,.jpg,.jpeg,.png" wire:model="ba_scan"
                        label="BA (PDF/JPG/PNG, maks 10 MB)" />
                    @error('ba_scan')
                        <div class="text-sm text-red-600">{{ $message }}</div>
                    @enderror

                    @if ($ba_scan_path)
                        <div class="text-sm">
                            Berkas saat ini:
                            <a class="underline" target="_blank"
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
