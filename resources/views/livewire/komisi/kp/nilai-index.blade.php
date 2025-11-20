<div class="space-y-6">
    <flux:card class="space-y-4">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-semibold">Status KP Dinilai</h3>
                <p class="text-sm text-zinc-500">Pantau nilai akhir dan status penyelesaian KP.</p>
            </div>
            <div class="flex items-center gap-2">
                <flux:input class="md:w-72" placeholder="Cari nama / NIM / judul…" wire:model.debounce.400ms="q"
                    icon="magnifying-glass" />
                <flux:select wire:model="statusFilter" class="w-44">
                    <option value="all">Semua Status</option>
                    <option value="ba_terbit">BA Terbit</option>
                    <option value="dinilai">Dinilai</option>
                </flux:select>
                <flux:select wire:model="perPage" class="w-24">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </flux:select>
            </div>
        </div>

        <flux:table :paginate="$items">
            <flux:table.columns>
                <flux:table.column class="w-10">#</flux:table.column>
                <flux:table.column>Mahasiswa</flux:table.column>
                <flux:table.column>Judul</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Nilai Akhir</flux:table.column>
                <flux:table.column>Rincian</flux:table.column>
                <flux:table.column>Distribusi</flux:table.column>
                <flux:table.column>Diperbarui</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($items as $i => $row)
                    <flux:table.row :key="$row->id">
                        <flux:table.cell>{{ $items->firstItem() + $i }}</flux:table.cell>

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
                            <flux:badge size="sm" :color="$row::badgeColor($row->status)">
                                {{ $row::statusLabel($row->status) }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($row->grade)
                                <div class="text-sm font-medium">
                                    {{ number_format($row->grade->final_score, 2) }} ({{ $row->grade->final_letter }})
                                </div>
                            @else
                                <span class="text-xs text-zinc-400">—</span>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="text-sm">
                            @if ($row->grade)
                                Dospem {{ number_format($row->grade->score_dospem, 2) }}
                                • PL {{ number_format($row->grade->score_pl, 2) }}
                            @else
                                —
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            @if ($row->distribusi_proof_path)
                                <a class="text-sm underline" href="{{ asset('storage/' . $row->distribusi_proof_path) }}"
                                    target="_blank">Lihat Bukti</a>
                            @else
                                <span class="text-xs text-zinc-400">Belum diupload</span>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell class="text-xs text-zinc-500">
                            {{ $row->updated_at?->format('d M Y H:i') ?? '—' }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:card>
</div>
