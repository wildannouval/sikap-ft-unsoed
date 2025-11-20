<div class="space-y-6">
    <h2 class="text-lg font-semibold">Dashboard Dosen Komisi</h2>

    {{-- Metric cards (pengganti <flux:stat>) --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500">Menunggu Review</div>
            <div class="mt-2 text-2xl font-semibold">{{ $this->stats['menungguReview'] }}</div>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500">Disetujui Pembimbing</div>
            <div class="mt-2 text-2xl font-semibold">{{ $this->stats['disetujuiPemb'] }}</div>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500">Dijadwalkan</div>
            <div class="mt-2 text-2xl font-semibold">{{ $this->stats['dijadwalkan'] }}</div>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <div class="text-xs uppercase tracking-wide text-zinc-500">BA Terbit</div>
            <div class="mt-2 text-2xl font-semibold">{{ $this->stats['baTerbit'] }}</div>
        </div>
    </div>

    <flux:card>
        <div class="flex items-center justify-between">
            <h3 class="text-base font-semibold">Seminar Terbaru</h3>
            <a class="text-sm underline" href="{{ route('komisi.kp.review') }}" wire:navigate>
                Review Pengajuan KP
            </a>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>Mahasiswa</flux:table.column>
                <flux:table.column>Judul</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Diperbarui</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->recent as $row)
                    <flux:table.row :key="$row->id">
                        <flux:table.cell class="whitespace-nowrap">
                            {{ $row->kp?->mahasiswa?->user?->name ?? '—' }}
                            <div class="text-xs text-zinc-500">{{ $row->kp?->mahasiswa?->nim ?? '' }}</div>
                        </flux:table.cell>

                        <flux:table.cell class="max-w-[420px]">
                            <span class="line-clamp-2">{{ $row->judul_laporan ?? '—' }}</span>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge size="sm" :color="$row::badgeColor($row->status)">
                                {{ $row::statusLabel($row->status) }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell class="text-xs text-zinc-500">
                            {{ $row->updated_at?->format('d M Y H:i') ?? '—' }}
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4">
                            <div class="py-6 text-center text-sm text-zinc-500">Belum ada data.</div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>
</div>
