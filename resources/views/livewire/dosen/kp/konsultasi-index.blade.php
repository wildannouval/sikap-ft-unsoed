<div class="space-y-6">
    <flux:toast />

    {{-- HEADER --}}
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <flux:heading size="xl" level="1" class="text-stone-900 dark:text-stone-100">
                Bimbingan & Konsultasi
            </flux:heading>
            <flux:subheading class="text-zinc-600 dark:text-zinc-300">
                Pantau perkembangan mahasiswa bimbingan dan verifikasi log konsultasi.
            </flux:subheading>
        </div>
    </div>

    <flux:separator variant="subtle" />

    {{-- GRID UTAMA 3:1 --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">

        {{-- KOLOM KIRI: TAB UTAMA (3) --}}
        <div class="lg:col-span-3 space-y-6">
            <flux:tab.group wire:model.live="tab">
                <flux:tabs>
                    <flux:tab name="mahasiswa" icon="users">
                        Mahasiswa Bimbingan
                        <flux:badge size="sm" inset="top bottom" class="ml-2">{{ $this->stats['total_mhs'] }}
                        </flux:badge>
                    </flux:tab>
                    <flux:tab name="konsultasi" icon="chat-bubble-left-right">
                        Log Konsultasi
                        @if ($this->stats['pending_verifikasi'] > 0)
                            <flux:badge size="sm" color="amber" inset="top bottom" class="ml-2">
                                {{ $this->stats['pending_verifikasi'] }}</flux:badge>
                        @endif
                    </flux:tab>
                </flux:tabs>

                {{-- PANEL MAHASISWA --}}
                <flux:tab.panel name="mahasiswa" class="pt-4">
                    <flux:card
                        class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm overflow-hidden">

                        {{-- Header Tabel --}}
                        <div
                            class="flex flex-col gap-4 px-6 py-4 border-b border-zinc-200 dark:border-stone-800 bg-indigo-50/50 dark:bg-indigo-900/10 md:flex-row md:items-center md:justify-between">
                            <h4 class="text-base font-semibold text-stone-900 dark:text-stone-100">Daftar Mahasiswa</h4>
                            <div class="flex items-center gap-3">
                                <flux:input icon="magnifying-glass" placeholder="Cari nama / NIM..."
                                    wire:model.live.debounce.400ms="q"
                                    class="w-full md:w-64 bg-white dark:bg-stone-900" />
                                <flux:select wire:model.live="statusFilter" class="w-40">
                                    <flux:select.option value="all">Semua Status</flux:select.option>
                                    <flux:select.option value="kp_berjalan">KP Berjalan</flux:select.option>
                                    <flux:select.option value="spk_terbit">SPK Terbit</flux:select.option>
                                    <flux:select.option value="selesai">Selesai</flux:select.option>
                                </flux:select>
                            </div>
                        </div>

                        <flux:table :paginate="$this->mahasiswaItems">
                            <flux:table.columns>
                                <flux:table.column class="w-10">No</flux:table.column>
                                <flux:table.column>Mahasiswa</flux:table.column>
                                <flux:table.column>Judul KP</flux:table.column>
                                <flux:table.column>Status KP</flux:table.column>
                                <flux:table.column class="text-center">Konsultasi</flux:table.column>
                                <flux:table.column class="whitespace-nowrap">Terakhir Bimbingan</flux:table.column>
                            </flux:table.columns>

                            <flux:table.rows>
                                @foreach ($this->mahasiswaItems as $i => $row)
                                    <flux:table.row :key="'mhs-' . $row->id">
                                        <flux:table.cell class="text-center text-zinc-500">
                                            {{ $this->mahasiswaItems->firstItem() + $i }}</flux:table.cell>

                                        <flux:table.cell>
                                            <div class="font-medium text-stone-900 dark:text-stone-100">
                                                {{ $row->mahasiswa?->user?->name }}</div>
                                            <div class="text-xs text-zinc-500">{{ $row->mahasiswa?->mahasiswa_nim }}
                                            </div>
                                        </flux:table.cell>

                                        <flux:table.cell class="max-w-[280px]">
                                            <div class="truncate text-sm font-medium">{{ $row->judul_kp }}</div>
                                            <div class="text-xs text-zinc-500 truncate">{{ $row->lokasi_kp }}</div>
                                        </flux:table.cell>

                                        <flux:table.cell>
                                            <flux:badge size="sm" :color="$this->badgeColor($row->status)"
                                                :icon="$this->badgeIcon($row->status)">
                                                {{ $this->statusLabel($row->status) }}
                                            </flux:badge>
                                        </flux:table.cell>

                                        <flux:table.cell class="text-center">
                                            <span
                                                class="font-medium text-emerald-600">{{ $row->verified_consultations_count }}</span>
                                            <span class="text-zinc-400">/ {{ $row->consultations_count }}</span>
                                        </flux:table.cell>

                                        <flux:table.cell class="whitespace-nowrap text-zinc-500 text-xs">
                                            {{ optional($row->last_consultation_at)->format('d M Y') ?? '—' }}
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>

                        {{-- Empty State Mahasiswa --}}
                        @if ($this->mahasiswaItems->isEmpty())
                            <div class="flex flex-col items-center justify-center py-12 text-center">
                                <div class="rounded-full bg-zinc-100 p-4 dark:bg-stone-900">
                                    <flux:icon.users class="size-8 text-zinc-400" />
                                </div>
                                <h3 class="mt-4 text-base font-semibold text-stone-900 dark:text-stone-100">Tidak ada
                                    mahasiswa</h3>
                                <p class="mt-1 text-sm text-zinc-500">Belum ada mahasiswa bimbingan yang sesuai filter.
                                </p>
                            </div>
                        @endif
                    </flux:card>
                </flux:tab.panel>

                {{-- PANEL KONSULTASI --}}
                <flux:tab.panel name="konsultasi" class="pt-4">
                    <flux:card
                        class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm overflow-hidden">

                        {{-- Header Tabel --}}
                        <div
                            class="flex flex-col gap-4 px-6 py-4 border-b border-zinc-200 dark:border-stone-800 bg-amber-50/50 dark:bg-amber-900/10 md:flex-row md:items-center md:justify-between">
                            <h4 class="text-base font-semibold text-stone-900 dark:text-stone-100">Log Konsultasi Masuk
                            </h4>
                            <div class="flex items-center gap-3">
                                <flux:input icon="magnifying-glass" placeholder="Cari topik..."
                                    wire:model.live.debounce.400ms="q"
                                    class="w-full md:w-64 bg-white dark:bg-stone-900" />
                            </div>
                        </div>

                        <flux:table :paginate="$this->konsultasiItems">
                            <flux:table.columns>
                                <flux:table.column class="w-10">No</flux:table.column>
                                <flux:table.column>Tanggal</flux:table.column>
                                <flux:table.column>Mahasiswa</flux:table.column>
                                <flux:table.column>Topik</flux:table.column>
                                <flux:table.column>Hasil</flux:table.column>
                                <flux:table.column class="text-right">Aksi</flux:table.column>
                            </flux:table.columns>

                            <flux:table.rows>
                                @foreach ($this->konsultasiItems as $i => $row)
                                    <flux:table.row :key="'log-' . $row->id">
                                        <flux:table.cell class="text-center text-zinc-500">
                                            {{ $this->konsultasiItems->firstItem() + $i }}</flux:table.cell>

                                        <flux:table.cell class="whitespace-nowrap">
                                            {{ optional($row->tanggal_konsultasi)->format('d M Y') }}
                                        </flux:table.cell>

                                        <flux:table.cell>
                                            <div class="font-medium text-stone-900 dark:text-stone-100">
                                                {{ $row->kp?->mahasiswa?->user?->name }}</div>
                                            <div class="text-xs text-zinc-500">
                                                {{ $row->kp?->mahasiswa?->mahasiswa_nim }}</div>
                                        </flux:table.cell>

                                        <flux:table.cell class="max-w-[200px]">
                                            <span class="line-clamp-2 text-sm">{{ $row->topik_konsultasi }}</span>
                                        </flux:table.cell>

                                        <flux:table.cell class="max-w-[250px]">
                                            <span
                                                class="line-clamp-2 text-sm text-zinc-500">{{ $row->hasil_konsultasi }}</span>
                                        </flux:table.cell>

                                        <flux:table.cell class="text-right">
                                            @if ($row->verified_at)
                                                <flux:badge size="sm" color="emerald" icon="check-circle">
                                                    Terverifikasi</flux:badge>
                                            @else
                                                <flux:modal.trigger name="verify-consult">
                                                    <flux:button size="xs" variant="primary" icon="check"
                                                        wire:click="openVerify({{ $row->id }})">Verifikasi
                                                    </flux:button>
                                                </flux:modal.trigger>
                                            @endif
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>

                        {{-- Empty State Konsultasi --}}
                        @if ($this->konsultasiItems->isEmpty())
                            <div class="flex flex-col items-center justify-center py-12 text-center">
                                <div class="rounded-full bg-zinc-100 p-4 dark:bg-stone-900">
                                    <flux:icon.chat-bubble-left-right class="size-8 text-zinc-400" />
                                </div>
                                <h3 class="mt-4 text-base font-semibold text-stone-900 dark:text-stone-100">Tidak ada
                                    log</h3>
                                <p class="mt-1 text-sm text-zinc-500">Belum ada catatan konsultasi yang masuk.</p>
                            </div>
                        @endif
                    </flux:card>
                </flux:tab.panel>
            </flux:tab.group>
        </div>

        {{-- KOLOM KANAN: SIDEBAR (1) --}}
        <div class="lg:col-span-1 space-y-6">

            {{-- 1. RINGKASAN --}}
            <flux:card
                class="rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm">
                <div class="mb-4 flex items-center gap-2">
                    <flux:icon.chart-bar class="size-5 text-zinc-500" />
                    <h3 class="font-semibold text-stone-900 dark:text-stone-100">Ringkasan</h3>
                </div>

                <div class="space-y-3">
                    <div
                        class="flex items-center justify-between p-2 rounded-lg bg-indigo-50/50 dark:bg-indigo-900/10 border border-indigo-100 dark:border-indigo-800/30">
                        <div class="flex items-center gap-2">
                            <div class="size-2 rounded-full bg-indigo-500"></div>
                            <span class="text-sm font-medium text-stone-700 dark:text-stone-300">Total Mahasiswa</span>
                        </div>
                        <span
                            class="text-lg font-bold text-indigo-600 dark:text-indigo-400">{{ $this->stats['total_mhs'] }}</span>
                    </div>

                    <div
                        class="flex items-center justify-between p-2 rounded-lg bg-amber-50/50 dark:bg-amber-900/10 border border-amber-100 dark:border-amber-800/30">
                        <div class="flex items-center gap-2">
                            <div class="size-2 rounded-full bg-amber-500 animate-pulse"></div>
                            <span class="text-sm font-medium text-stone-700 dark:text-stone-300">Perlu
                                Verifikasi</span>
                        </div>
                        <span
                            class="text-lg font-bold text-amber-600 dark:text-amber-400">{{ $this->stats['pending_verifikasi'] }}</span>
                    </div>
                </div>
            </flux:card>

            {{-- 2. PANDUAN --}}
            <flux:card
                class="rounded-xl border bg-sky-50/50 dark:bg-sky-900/10 border-sky-100 dark:border-sky-800/30 shadow-sm">
                <div class="flex items-start gap-3">
                    <flux:icon.information-circle class="mt-0.5 size-5 text-sky-600 dark:text-sky-400" />
                    <div>
                        <h3 class="font-semibold text-sky-900 dark:text-sky-100 text-sm">Panduan Dosen</h3>
                        <ul class="mt-3 text-xs text-sky-800 dark:text-sky-200 space-y-2 list-disc list-inside">
                            <li>Tab <strong>Mahasiswa</strong>: Memantau progres bimbingan mahasiswa.</li>
                            <li>Tab <strong>Log Konsultasi</strong>: Memverifikasi catatan bimbingan yang diinput
                                mahasiswa.</li>
                            <li>Klik tombol <strong>Verifikasi</strong> untuk menyetujui log konsultasi.</li>
                        </ul>
                    </div>
                </div>
            </flux:card>
        </div>
    </div>

    {{-- MODAL VERIFIKASI --}}
    <flux:modal name="verify-consult" :show="$verifyId !== null" class="md:w-[30rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Verifikasi Konsultasi</flux:heading>
                <p class="text-sm text-zinc-500">Setujui catatan konsultasi ini?</p>
            </div>

            <flux:textarea label="Catatan Tambahan (Opsional)" placeholder="Tambahkan pesan untuk mahasiswa..."
                wire:model.defer="verifier_note" />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" icon="check" wire:click="confirmVerify">Ya, Verifikasi</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
