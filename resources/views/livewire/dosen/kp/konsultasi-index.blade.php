<div class="space-y-6">
    <flux:toast />

    {{-- HEADER --}}
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <flux:heading size="xl" level="1" class="text-stone-900 dark:text-stone-100">
                Bimbingan & Konsultasi
            </flux:heading>
            <flux:subheading class="text-zinc-600 dark:text-zinc-300">
                Pantau perkembangan mahasiswa dan verifikasi log konsultasi.
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
                        Monitoring Mahasiswa
                        <flux:badge size="sm" inset="top bottom" class="ml-2">{{ $this->stats['total_mhs'] }}
                        </flux:badge>
                    </flux:tab>
                    <flux:tab name="log_konsultasi" icon="chat-bubble-left-right">
                        Log Konsultasi (Aktif)
                        @if ($this->stats['pending_verifikasi'] > 0)
                            <flux:badge size="sm" color="amber" inset="top bottom" class="ml-2">
                                {{ $this->stats['pending_verifikasi'] }}</flux:badge>
                        @endif
                    </flux:tab>
                </flux:tabs>

                {{-- PANEL MAHASISWA (MONITORING) --}}
                <flux:tab.panel name="mahasiswa" class="pt-4">
                    <flux:card
                        class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm overflow-hidden">

                        {{-- Header Tabel --}}
                        <div
                            class="flex flex-col gap-4 px-6 py-4 border-b border-zinc-200 dark:border-stone-800 bg-indigo-50/50 dark:bg-indigo-900/10 md:flex-row md:items-center md:justify-between">
                            <h4 class="text-base font-semibold text-stone-900 dark:text-stone-100">Daftar Mahasiswa
                            </h4>
                            <div class="flex items-center gap-3">
                                <flux:input icon="magnifying-glass" placeholder="Cari nama / NIM..."
                                    wire:model.live.debounce.400ms="q"
                                    class="w-full md:w-64 bg-white dark:bg-stone-900" />
                                <flux:select wire:model.live="statusFilter" class="w-40">
                                    <flux:select.option value="all">Semua Status</flux:select.option>
                                    <flux:select.option value="kp_sedang_berjalan">KP Berjalan</flux:select.option>
                                    <flux:select.option value="spk_terbit">SPK Terbit</flux:select.option>
                                    <flux:select.option value="selesai">Selesai / Nilai Terbit</flux:select.option>
                                </flux:select>
                            </div>
                        </div>

                        <flux:table :paginate="$this->mahasiswaItems">
                            <flux:table.columns>
                                <flux:table.column class="w-10">No</flux:table.column>
                                <flux:table.column>Mahasiswa</flux:table.column>
                                <flux:table.column>Judul KP</flux:table.column>
                                <flux:table.column>Status & SPK</flux:table.column>
                                <flux:table.column class="text-center">Progres Bimbingan</flux:table.column>
                            </flux:table.columns>

                            <flux:table.rows>
                                @foreach ($this->mahasiswaItems as $i => $row)
                                    <flux:table.row :key="'mhs-' . $row->id">
                                        <flux:table.cell class="text-center text-zinc-500">
                                            {{ $this->mahasiswaItems->firstItem() + $i }}</flux:table.cell>

                                        <flux:table.cell>
                                            <div class="font-medium text-stone-900 dark:text-stone-100">
                                                {{ $row->mahasiswa?->user?->name }}</div>
                                            <div class="text-xs text-zinc-500">
                                                {{ $row->mahasiswa?->mahasiswa_nim }}
                                            </div>
                                        </flux:table.cell>

                                        <flux:table.cell class="max-w-[280px]">
                                            <div class="truncate text-sm font-medium">{{ $row->judul_kp }}</div>
                                            <div class="text-xs text-zinc-500 truncate">{{ $row->lokasi_kp }}</div>
                                        </flux:table.cell>

                                        <flux:table.cell>
                                            <flux:badge size="sm" :color="$this->badgeColor($row->status)">
                                                {{ $this->statusLabel($row->status) }}
                                            </flux:badge>
                                            @if ($row->tanggal_terbit_spk)
                                                <div class="text-[10px] text-zinc-500 mt-1">
                                                    SPK: {{ $row->tanggal_terbit_spk->format('d/m/y') }}
                                                    <br>
                                                    Exp:
                                                    {{ $row->tanggal_terbit_spk->addYear()->format('d/m/y') }}
                                                </div>
                                            @endif
                                        </flux:table.cell>

                                        <flux:table.cell class="text-center">
                                            <div class="flex flex-col items-center">
                                                <span
                                                    class="font-bold text-lg {{ $row->verified_consultations_count >= 6 ? 'text-emerald-600' : 'text-zinc-600' }}">
                                                    {{ $row->verified_consultations_count }}
                                                </span>
                                                <span class="text-[10px] text-zinc-400">Target: 6</span>
                                            </div>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    </flux:card>
                </flux:tab.panel>

                {{-- PANEL LOG KONSULTASI (LIST MAHASISWA AKTIF) --}}
                <flux:tab.panel name="log_konsultasi" class="pt-4">
                    <flux:card
                        class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm overflow-hidden">

                        {{-- Header --}}
                        <div
                            class="flex flex-col gap-4 px-6 py-4 border-b border-zinc-200 dark:border-stone-800 bg-amber-50/50 dark:bg-amber-900/10 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h4 class="text-base font-semibold text-stone-900 dark:text-stone-100">Log Konsultasi
                                    Masuk</h4>
                                <p class="text-xs text-zinc-500">Pilih mahasiswa untuk melihat detail bimbingan.</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <flux:input icon="magnifying-glass" placeholder="Cari mahasiswa..."
                                    wire:model.live.debounce.400ms="q"
                                    class="w-full md:w-64 bg-white dark:bg-stone-900" />
                            </div>
                        </div>

                        <flux:table :paginate="$this->activeKpItems">
                            <flux:table.columns>
                                <flux:table.column class="w-10">No</flux:table.column>
                                <flux:table.column>Mahasiswa</flux:table.column>
                                <flux:table.column>Info SPK</flux:table.column>
                                <flux:table.column class="text-center">Pending Verifikasi</flux:table.column>
                                <flux:table.column class="text-right">Aksi</flux:table.column>
                            </flux:table.columns>

                            <flux:table.rows>
                                @foreach ($this->activeKpItems as $i => $row)
                                    <flux:table.row :key="'active-' . $row->id">
                                        <flux:table.cell class="text-center text-zinc-500">
                                            {{ $this->activeKpItems->firstItem() + $i }}</flux:table.cell>

                                        <flux:table.cell>
                                            <div class="font-medium text-stone-900 dark:text-stone-100">
                                                {{ $row->mahasiswa?->user?->name }}</div>
                                            <div class="text-xs text-zinc-500">
                                                {{ $row->mahasiswa?->mahasiswa_nim }}</div>
                                        </flux:table.cell>

                                        <flux:table.cell>
                                            @if ($row->tanggal_terbit_spk)
                                                <div class="text-xs text-zinc-600">
                                                    Terbit: {{ $row->tanggal_terbit_spk->format('d M Y') }}
                                                </div>
                                                <div class="text-[10px] text-rose-500 mt-0.5">
                                                    Berlaku s.d:
                                                    {{ $row->tanggal_terbit_spk->addYear()->format('d M Y') }}
                                                </div>
                                            @else
                                                <span class="text-xs text-zinc-400">-</span>
                                            @endif
                                        </flux:table.cell>

                                        <flux:table.cell class="text-center">
                                            @if ($row->pending_count > 0)
                                                <flux:badge color="amber" size="sm" icon="exclamation-circle">
                                                    {{ $row->pending_count }} Baru
                                                </flux:badge>
                                            @else
                                                <span class="text-xs text-zinc-400">Tidak ada</span>
                                            @endif
                                        </flux:table.cell>

                                        <flux:table.cell class="text-right">
                                            <flux:button size="sm" variant="primary" icon="chat-bubble-left-right"
                                                wire:click="openLogModal({{ $row->id }})">
                                                Lihat Log
                                            </flux:button>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>

                        @if ($this->activeKpItems->isEmpty())
                            <div class="p-8 text-center text-sm text-zinc-500">
                                Tidak ada mahasiswa bimbingan aktif saat ini.
                            </div>
                        @endif
                    </flux:card>
                </flux:tab.panel>
            </flux:tab.group>
        </div>

        {{-- KOLOM KANAN: SIDEBAR (1) --}}
        <div class="lg:col-span-1 space-y-6">
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
                            <span class="text-sm font-medium text-stone-700 dark:text-stone-300">Total
                                Mahasiswa</span>
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
        </div>
    </div>

    {{-- MODAL LOG KONSULTASI MAHASISWA --}}
    <flux:modal name="student-logs" class="md:w-[48rem] max-h-[80vh] overflow-y-auto">
        <div class="space-y-6">
            <div class="flex items-start justify-between">
                <div>
                    <flux:heading size="lg">Log Konsultasi</flux:heading>
                    @if ($this->selectedKp)
                        <p class="text-sm text-zinc-500">
                            Mahasiswa: <strong>{{ $this->selectedKp->mahasiswa->user->name }}</strong>
                        </p>
                    @endif
                </div>
            </div>

            @if ($this->selectedKpLogs->isNotEmpty())
                <div class="space-y-4">
                    @foreach ($this->selectedKpLogs as $log)
                        <div
                            class="p-4 rounded-lg border {{ $log->verified_at ? 'border-zinc-200 bg-zinc-50' : 'border-amber-200 bg-amber-50' }} dark:border-zinc-700 dark:bg-zinc-900">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <div class="text-xs text-zinc-500 mb-1">
                                        {{ optional($log->tanggal_konsultasi)->format('d F Y') }}
                                    </div>
                                    <h4 class="font-medium text-stone-900 dark:text-stone-100">
                                        {{ $log->topik_konsultasi }}
                                    </h4>
                                </div>
                                <div>
                                    @if ($log->verified_at)
                                        <flux:badge size="sm" color="emerald" icon="check-circle">Terverifikasi
                                        </flux:badge>
                                    @else
                                        <flux:button size="xs" variant="primary" icon="check"
                                            wire:click="openVerify({{ $log->id }})">Verifikasi</flux:button>
                                    @endif
                                </div>
                            </div>
                            <div class="text-sm text-zinc-700 dark:text-zinc-300 whitespace-pre-line">
                                {{ $log->hasil_konsultasi }}
                            </div>
                            @if ($log->konsultasi_dengan && $log->konsultasi_dengan !== 'Dosen Pembimbing')
                                <div class="mt-2 text-xs text-zinc-500 italic">
                                    Konsultasi dengan: {{ $log->konsultasi_dengan }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-8 text-center text-zinc-500">Belum ada catatan konsultasi.</div>
            @endif

            <div class="flex justify-end">
                <flux:button variant="ghost" wire:click="$set('viewLogKpId', null)" flux:click="close">Tutup
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- MODAL VERIFIKASI (Nested or Separate) --}}
    <flux:modal name="verify-consult" :show="$verifyId !== null" class="md:w-[30rem]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Verifikasi Konsultasi</flux:heading>
                <p class="text-sm text-zinc-500">Berikan catatan tambahan jika perlu.</p>
            </div>

            <flux:textarea label="Catatan Dosen (Opsional)" placeholder="Pesan untuk mahasiswa..."
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
