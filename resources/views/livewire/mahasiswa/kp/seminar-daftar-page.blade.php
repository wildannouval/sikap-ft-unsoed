<div class="space-y-6">
    <flux:toast />

    {{-- HEADER --}}
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <flux:heading size="xl" level="1" class="text-stone-900 dark:text-stone-100">
                Pendaftaran Seminar KP
            </flux:heading>
            <flux:subheading class="text-zinc-600 dark:text-zinc-300">
                Lengkapi data untuk mengajukan jadwal seminar.
            </flux:subheading>
        </div>
    </div>

    <flux:separator variant="subtle" />

    {{-- GRID 7:3 --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-10">

        {{-- KOLOM KIRI: FORM (7) --}}
        <div class="lg:col-span-7 space-y-6">
            <flux:card
                class="space-y-6 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm">

                {{-- Header Kartu --}}
                <div class="flex items-center gap-2 px-1.5 -mt-1">
                    <div
                        class="flex items-center justify-center rounded-lg p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                        <flux:icon.pencil-square class="size-5" />
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-stone-900 dark:text-stone-100">Formulir Pendaftaran</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            Isi data rencana seminar di bawah ini.
                        </p>
                    </div>
                </div>

                <flux:separator />

                {{-- Info KP --}}
                <div
                    class="grid gap-3 md:grid-cols-2 p-3 bg-zinc-50 dark:bg-zinc-900/50 rounded-lg border border-zinc-100 dark:border-zinc-800">
                    <div>
                        <div class="text-xs text-zinc-500 mb-1">Judul Kerja Praktik</div>
                        <div class="font-medium text-sm text-stone-900 dark:text-stone-100 leading-snug">
                            {{ $kp->judul_kp }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-zinc-500 mb-1">Dosen Pembimbing</div>
                        <div class="font-medium text-sm text-stone-900 dark:text-stone-100">
                            {{ $kp->dosenPembimbing->user->name ?? '-' }}
                        </div>
                    </div>
                </div>

                {{-- INPUT FIELDS --}}
                <div class="grid gap-5">
                    <div>
                        <flux:input label="Judul Laporan Final" wire:model.defer="judul_kp_final"
                            placeholder="Judul lengkap laporan akhir KP" :invalid="$errors->has('judul_kp_final')"
                            :disabled="$this->isLocked" />
                        @error('judul_kp_final')
                            <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <flux:input type="date" label="Rencana Tanggal" wire:model.defer="tanggal_seminar"
                                :invalid="$errors->has('tanggal_seminar')" :disabled="$this->isLocked" />
                            @error('tanggal_seminar')
                                <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <flux:select label="Usulan Ruangan" wire:model="ruangan_id"
                                :invalid="$errors->has('ruangan_id')" :disabled="$this->isLocked"
                                placeholder="Pilih Ruangan">
                                <option value="">— Pilih Ruangan —</option>
                                @foreach ($this->rooms as $r)
                                    <option value="{{ $r['id'] }}">{{ $r['label'] }}</option>
                                @endforeach
                            </flux:select>
                            @error('ruangan_id')
                                <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <flux:input type="time" label="Jam Mulai" wire:model.defer="jam_mulai"
                                :invalid="$errors->has('jam_mulai')" :disabled="$this->isLocked" />
                            @error('jam_mulai')
                                <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <flux:input type="time" label="Jam Selesai" wire:model.defer="jam_selesai"
                                :invalid="$errors->has('jam_selesai')" :disabled="$this->isLocked" />
                            @error('jam_selesai')
                                <div class="mt-1 text-xs text-rose-600">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <flux:textarea label="Abstrak (Opsional)" rows="4" wire:model.defer="abstrak"
                            :invalid="$errors->has('abstrak')" placeholder="Ringkasan singkat laporan..."
                            :disabled="$this->isLocked" />
                    </div>

                    <div class="space-y-2">
                        <flux:input type="file" label="Berkas Laporan (PDF, Maks 10MB)" wire:model="berkas_laporan"
                            accept="application/pdf" :invalid="$errors->has('berkas_laporan')"
                            :disabled="$this->isLocked" />

                        <div class="text-xs text-zinc-500" wire:loading wire:target="berkas_laporan">Mengunggah...</div>
                        @error('berkas_laporan')
                            <div class="text-xs text-rose-600">{{ $message }}</div>
                        @enderror

                        @if ($berkas_laporan_path)
                            <div
                                class="mt-2 flex items-center justify-between rounded-lg border bg-zinc-50 p-2 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                                <a class="flex items-center gap-2 truncate text-indigo-600 hover:text-indigo-500 hover:underline"
                                    target="_blank" href="{{ asset('storage/' . $berkas_laporan_path) }}">
                                    <flux:icon.document-text class="size-4" />
                                    <span>Lihat Berkas</span>
                                </a>
                                @unless ($this->isLocked)
                                    <button type="button" wire:click="removeFile"
                                        class="text-rose-500 hover:text-rose-700 p-1">
                                        <flux:icon.trash class="size-4" />
                                    </button>
                                @endunless
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-2">
                    @if (!$this->isLocked)
                        <flux:button variant="ghost" icon="bookmark" wire:click="saveDraft">Simpan Draf</flux:button>
                        <flux:button variant="primary" icon="paper-airplane" wire:click="submitToAdvisor">Ajukan Seminar
                        </flux:button>
                    @else
                        <div
                            class="flex items-center gap-2 text-sm text-zinc-500 italic bg-zinc-100 dark:bg-zinc-900 px-3 py-2 rounded-md">
                            <flux:icon.lock-closed class="size-4" />
                            <span>Formulir terkunci (Sedang diproses)</span>
                        </div>
                    @endif
                </div>
            </flux:card>
        </div>

        {{-- KOLOM KANAN: STATUS & PANDUAN (3) --}}
        <div class="lg:col-span-3 space-y-6">

            {{-- 1. STATUS PENGAJUAN --}}
            <flux:card
                class="rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm">
                <div class="mb-5">
                    <div class="flex items-center gap-2">
                        <flux:icon.chart-bar class="size-5 text-zinc-500" />
                        <h3 class="font-semibold text-stone-900 dark:text-stone-100">Status Pengajuan</h3>
                    </div>
                    <p class="mt-1.5 text-xs text-zinc-500 dark:text-zinc-400 pl-7">
                        Posisi tahapan seminar Anda saat ini.
                    </p>
                </div>

                <div class="space-y-4">
                    {{-- Badge Status Besar --}}
                    @php $badge = $this->statusBadge; @endphp
                    <div
                        class="p-4 rounded-lg border bg-zinc-50/50 dark:bg-zinc-900/20 border-zinc-100 dark:border-zinc-800 text-center">
                        <div class="text-xs text-zinc-500 mb-2 uppercase tracking-wider font-semibold">Status Saat Ini
                        </div>
                        <div class="inline-flex flex-col items-center gap-2">
                            <div
                                class="p-2 rounded-full bg-{{ $badge['color'] }}-100 dark:bg-{{ $badge['color'] }}-900/30 text-{{ $badge['color'] }}-600 dark:text-{{ $badge['color'] }}-400">
                                <flux:icon :name="$badge['icon']" class="size-6" />
                            </div>
                            <div>
                                <div class="font-bold text-lg text-stone-900 dark:text-stone-100">
                                    {{ $this->statusLabel }}</div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $badge['desc'] }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Ringkasan Jadwal (Jika Dijadwalkan/Selesai/BA Terbit) --}}
                    @if (in_array($status, ['dijadwalkan', 'selesai', 'ba_terbit', 'dinilai']))
                        <div class="text-sm space-y-2 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Tanggal</span>
                                <span
                                    class="font-medium">{{ optional($seminar->tanggal_seminar)->format('d M Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Pukul</span>
                                <span class="font-medium">{{ $seminar->jam_mulai }} -
                                    {{ $seminar->jam_selesai }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-zinc-500">Ruangan</span>
                                <span class="font-medium">{{ $seminar->ruangan_nama }}</span>
                            </div>
                        </div>
                    @endif

                    {{-- Tombol Download BA --}}
                    @if ($status === 'ba_terbit')
                        <a href="{{ route('mhs.kp.seminar.download.ba', [$kp->id, $seminar->id]) }}" target="_blank"
                            class="flex items-center justify-center gap-2 w-full p-3 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium transition">
                            <flux:icon.arrow-down-tray class="size-4" />
                            Unduh Berita Acara
                        </a>
                    @endif
                </div>
            </flux:card>

            {{-- 2. PANDUAN --}}
            <flux:card
                class="rounded-xl border bg-amber-50/50 dark:bg-amber-900/10 border-amber-100 dark:border-amber-800/30 shadow-sm">
                <div class="flex items-start gap-3">
                    <flux:icon.information-circle class="mt-0.5 size-5 text-amber-600 dark:text-amber-400" />
                    <div>
                        <h3 class="font-semibold text-amber-900 dark:text-amber-100 text-sm">Alur Pendaftaran</h3>
                        <ul class="mt-3 text-xs text-amber-800 dark:text-amber-200 space-y-3">
                            <li class="flex gap-2">
                                <span
                                    class="flex-none font-bold bg-amber-200 dark:bg-amber-800 rounded-full w-5 h-5 flex items-center justify-center text-[10px]">1</span>
                                <span>Lengkapi formulir (judul, tanggal, ruangan).</span>
                            </li>
                            <li class="flex gap-2">
                                <span
                                    class="flex-none font-bold bg-amber-200 dark:bg-amber-800 rounded-full w-5 h-5 flex items-center justify-center text-[10px]">2</span>
                                <span>Klik <strong>Ajukan Seminar</strong> ke Dosen.</span>
                            </li>
                            <li class="flex gap-2">
                                <span
                                    class="flex-none font-bold bg-amber-200 dark:bg-amber-800 rounded-full w-5 h-5 flex items-center justify-center text-[10px]">3</span>
                                <span>Tunggu jadwal final dari Koordinator.</span>
                            </li>
                            <li class="flex gap-2">
                                <span
                                    class="flex-none font-bold bg-amber-200 dark:bg-amber-800 rounded-full w-5 h-5 flex items-center justify-center text-[10px]">4</span>
                                <span>Laksanakan seminar & revisi (jika ada).</span>
                            </li>
                            <li class="flex gap-2">
                                <span
                                    class="flex-none font-bold bg-amber-200 dark:bg-amber-800 rounded-full w-5 h-5 flex items-center justify-center text-[10px]">5</span>
                                <span>Unduh Berita Acara setelah selesai.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </flux:card>

        </div>
    </div>
</div>
