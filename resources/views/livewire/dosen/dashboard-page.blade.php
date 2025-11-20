<div class="space-y-6">
    <h2 class="text-lg font-semibold">Dashboard Dosen Pembimbing</h2>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <flux:stat label="Menunggu ACC" :value="$this->stats['menungguAcc']" />
        <flux:stat label="Dijadwalkan" :value="$this->stats['dijadwalkan']" />
        <flux:stat label="BA Terbit" :value="$this->stats['baTerbit']" />
        <flux:stat label="Perlu Dinilai" :value="$this->stats['perluNilai']" />
        <flux:stat label="Bimbingan Aktif" :value="$this->bimbinganAktif" />
    </div>

    <flux:card>
        <div class="flex items-center justify-between">
            <h3 class="text-base font-semibold">Aktivitas Terbaru</h3>
            <div class="flex gap-3">
                <a class="text-sm underline" href="{{ route('dsp.kp.seminar.approval') }}" wire:navigate>Persetujuan
                    Seminar</a>
                <a class="text-sm underline" href="{{ route('dsp.nilai') }}" wire:navigate>Penilaian KP</a>
            </div>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>Mahasiswa</flux:table.column>
                <flux:table.column>Judul</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Nilai</flux:table.column>
                <flux:table.column>Diperbarui</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse ($this->recent as $row)
                    <flux:table.row :key="$row->id">
                        <flux:table.cell class="whitespace-nowrap">
                            {{ $row->kp?->mahasiswa?->user?->name ?? '—' }}
                            <div class="text-xs text-zinc-500">{{ $row->kp?->mahasiswa?->nim ?? '' }}</div>
                        </flux:table.cell>
                        <flux:table.cell class="max-w-[420px]"><span
                                class="line-clamp-2">{{ $row->judul_laporan ?? '—' }}</span></flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" :color="$row::badgeColor($row->status)">
                                {{ $row::statusLabel($row->status) }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($row->grade)
                                {{ number_format($row->grade->final_score, 2) }} ({{ $row->grade->final_letter }})
                            @else
                                <span class="text-xs text-zinc-400">—</span>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell class="text-xs text-zinc-500">
                            {{ $row->updated_at?->format('d M Y H:i') ?? '—' }}</flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="5">
                            <div class="py-6 text-center text-sm text-zinc-500">Belum ada aktivitas.</div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>
</div>
