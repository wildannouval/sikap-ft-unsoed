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

                        {{-- Distribusi --}}
                        <flux:table.cell>
                            <livewire:mahasiswa.kp.distribusi-upload :seminar-id="$row->id" :key="'dist-' . $row->id" />
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
</div>
