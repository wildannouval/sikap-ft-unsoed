<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1" class="text-stone-900 dark:text-stone-100">
                Mahasiswa Bimbingan
            </flux:heading>
            <flux:subheading class="text-zinc-600 dark:text-zinc-300">
                Lihat daftar mahasiswa yang Anda bimbing, status KP, dan ringkasan konsultasi.
            </flux:subheading>
        </div>
    </div>
    <flux:separator variant="subtle" />

    {{-- PANDUAN --}}
    <flux:card
        class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-xs">
        <div class="flex items-start gap-2 px-1.5 -mt-1">
            <span
                class="inline-flex items-center justify-center rounded-md p-1.5 bg-indigo-500 text-white dark:bg-indigo-400">
                <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M12 16v-4" />
                    <path d="M12 8h.01" />
                </svg>
            </span>
            <div>
                <h3 class="text-base font-semibold text-stone-900 dark:text-stone-100">Panduan Mahasiswa Bimbingan</h3>
                <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-300 space-y-1.5">
                    <div><span class="font-medium">1)</span> Gunakan kolom <em>cari</em> untuk nama, NIM, judul KP, atau
                        instansi.</div>
                    <div><span class="font-medium">2)</span> Lihat <strong>jumlah konsultasi terverifikasi</strong>
                        untuk mengukur kelayakan daftar seminar (target ≥ 6).</div>
                    <div><span class="font-medium">3)</span> Klik <strong>Lihat Konsultasi</strong> untuk
                        memverifikasi/meninjau detail konsultasi.</div>
                </div>
            </div>
        </div>
    </flux:card>

    {{-- FILTER BAR --}}
    <flux:card class="rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-xs">
        <div
            class="px-4 py-3 border-b
                   bg-indigo-50 text-indigo-700
                   dark:bg-indigo-900/20 dark:text-indigo-300
                   border-indigo-100 dark:border-indigo-900/40
                   rounded-t-xl">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <h3 class="text-sm font-medium tracking-wide">Daftar Mahasiswa Bimbingan</h3>

                <div class="flex flex-col gap-2 md:flex-row md:items-end">
                    <div class="md:w-96">
                        <flux:input placeholder="Cari nama / NIM / judul KP / instansi…"
                            wire:model.live.debounce.400ms="q" icon="magnifying-glass" />
                    </div>

                    <flux:select wire:model.live="status" class="md:ml-2">
                        <option value="all">Semua Status</option>
                        <option value="review_komisi">Menunggu Review Komisi</option>
                        <option value="review_bapendik">Menunggu Terbit SPK</option>
                        <option value="spk_terbit">SPK Terbit</option>
                        <option value="kp_berjalan">KP Berjalan</option>
                        <option value="selesai">Selesai</option>
                        <option value="ditolak">Ditolak</option>
                    </flux:select>
                </div>

                <div class="flex items-center gap-2">
                    <flux:button size="sm" variant="ghost"
                        :icon="$sortDirection === 'asc' ? 'arrow-up-circle' : 'arrow-down-circle'"
                        wire:click="sort('updated_at')">
                        Urut: {{ ucfirst(str_replace('_', ' ', $sortBy)) }} ({{ $sortDirection }})
                    </flux:button>
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
                    <flux:table.column class="w-10">#</flux:table.column>
                    <flux:table.column>Mahasiswa</flux:table.column>
                    <flux:table.column>Judul KP</flux:table.column>
                    <flux:table.column>Instansi</flux:table.column>
                    <flux:table.column>Status KP</flux:table.column>
                    <flux:table.column class="text-center">Konsultasi (ver/total)</flux:table.column>
                    <flux:table.column class="whitespace-nowrap">Terakhir Konsultasi</flux:table.column>
                    <flux:table.column class="w-48 text-center">Aksi</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->items as $i => $row)
                        <flux:table.row :key="$row->id">
                            <flux:table.cell>{{ $this->items->firstItem() + $i }}</flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                <div class="text-stone-900 dark:text-stone-100">
                                    {{ $row->mahasiswa?->user?->name ?? '—' }}
                                </div>
                                <div class="text-xs text-zinc-500">
                                    {{ $row->mahasiswa?->mahasiswa_nim ?? $row->mahasiswa?->nim }}
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="max-w-[360px]">
                                <span
                                    class="line-clamp-2 text-stone-900 dark:text-stone-100">{{ $row->judul_kp }}</span>
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                <span class="text-stone-900 dark:text-stone-100">{{ $row->lokasi_kp }}</span>
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:badge size="sm" inset="top bottom" :color="$this->badgeColor($row->status)">
                                    {{ $this->statusLabel($row->status) }}
                                </flux:badge>
                            </flux:table.cell>

                            <flux:table.cell class="text-center">
                                <span class="text-sm font-medium text-stone-900 dark:text-stone-100">
                                    {{ $row->verified_consultations_count ?? 0 }} / {{ $row->consultations_count ?? 0 }}
                                </span>
                            </flux:table.cell>

                            <flux:table.cell class="whitespace-nowrap">
                                {{ optional($row->last_consultation_at)->format('d M Y') ?? '—' }}
                            </flux:table.cell>

                            <flux:table.cell class="text-center">
                                <div class="inline-flex items-center gap-2">
                                    {{-- Arahkan ke halaman verifikasi konsultasi (jika route ada), fallback tetap ke halaman ini --}}
                                    @if (Route::has('dsp.kp.konsultasi.review'))
                                        <flux:button size="xs" variant="primary" icon="chat-bubble-left-right"
                                            :href="route('dsp.kp.konsultasi.review', ['kp' => $row->id])" wire:navigate>
                                            Lihat Konsultasi
                                        </flux:button>
                                    @else
                                        <flux:button size="xs" variant="primary" icon="chat-bubble-left-right"
                                            :href="route('dsp.kp.konsultasi')" wire:navigate>
                                            Lihat Konsultasi
                                        </flux:button>
                                    @endif

                                    @if (Route::has('dsp.kp.seminar.approval'))
                                        <flux:button size="xs" variant="outline" icon="check-circle"
                                            :href="route('dsp.kp.seminar.approval')" wire:navigate>
                                            Persetujuan Seminar
                                        </flux:button>
                                    @endif
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>
    </flux:card>
</div>
