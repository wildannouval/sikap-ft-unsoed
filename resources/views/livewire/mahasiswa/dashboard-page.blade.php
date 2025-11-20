<div class="space-y-6">
    <h2 class="text-lg font-semibold">Dashboard Mahasiswa</h2>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <flux:stat label="Total Seminar" :value="$this->seminarStats['total']" />
        <flux:stat label="Menunggu ACC" :value="$this->seminarStats['diajukan']" />
        <flux:stat label="Dijadwalkan" :value="$this->seminarStats['dijadwalkan']" />
        <flux:stat label="BA Terbit" :value="$this->seminarStats['ba_terbit']" />
    </div>

    <flux:card class="space-y-3">
        <div class="flex items-center gap-2">
            <h3 class="text-base font-semibold">Status KP Aktif</h3>
            <flux:spacer />
            @if ($this->activeKp?->verified_consultations_count >= 6)
                <flux:badge size="sm">Siap Daftar Seminar</flux:badge>
            @endif
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="space-y-1">
                <div class="text-sm text-zinc-500">Status KP</div>
                <div class="font-medium">{{ $this->activeKp->status ?? '—' }}</div>
            </div>
            <div class="space-y-1">
                <div class="text-sm text-zinc-500">Konsultasi Terverifikasi</div>
                <div class="font-medium">{{ $this->activeKp->verified_consultations_count ?? 0 }}</div>
            </div>
            <div class="space-y-1">
                <div class="text-sm text-zinc-500">Butuh Upload Distribusi</div>
                <div class="font-medium">{{ $this->needDistribusi }}</div>
            </div>
        </div>
    </flux:card>

    <flux:card>
        <div class="flex items-center justify-between">
            <h3 class="text-base font-semibold">Aktivitas Seminar Terbaru</h3>
            <a class="text-sm underline" href="{{ route('mhs.nilai') }}" wire:navigate>Lihat Nilai</a>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>Judul</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Nilai</flux:table.column>
                <flux:table.column>Diperbarui</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @forelse ($this->recentSeminars as $row)
                    <flux:table.row :key="$row->id">
                        <flux:table.cell class="max-w-[420px]"><span
                                class="line-clamp-2">{{ $row->judul_laporan ?? '—' }}</span></flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" :color="$row::badgeColor($row->status)">
                                {{ $row::statusLabel($row->status) }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($row->distribusi_proof_path && $row->grade)
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
                        <flux:table.cell colspan="4">
                            <div class="py-6 text-center text-sm text-zinc-500">Belum ada aktivitas.</div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>
</div>
