<div class="space-y-6">

    {{-- FLASH --}}
    @if (session('ok'))
        <div class="rounded-md border border-emerald-300/60 bg-emerald-50 px-3 py-2 text-emerald-800">
            <div class="font-medium">{{ session('ok') }}</div>
        </div>
    @endif
    @if (session('err'))
        <div class="rounded-md border border-rose-300/60 bg-rose-50 px-3 py-2 text-rose-800">
            <div class="font-medium">{{ session('err') }}</div>
        </div>
    @endif

    {{-- FORM CARD --}}
    <flux:card
        class="space-y-6 rounded-xl border
               bg-white dark:bg-stone-950
               border-zinc-200 dark:border-stone-800
               shadow-xs">

        {{-- Header kartu (aksen indigo) --}}
        <div class="flex items-center gap-2 px-1.5 -mt-1">
            <span
                class="inline-flex items-center justify-center rounded-md p-1.5
                       bg-indigo-500 text-white dark:bg-indigo-400">
                <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="16" rx="2" />
                    <path d="M7 8h10M7 12h7M7 16h5" />
                </svg>
            </span>
            <div>
                <h3 class="text-base font-semibold text-stone-900 dark:text-stone-100">Daftar Seminar KP</h3>
                <p class="text-sm text-zinc-500 dark:text-zinc-300">
                    Lengkapi data berikut lalu kirim untuk persetujuan Dosen Pembimbing.
                </p>
            </div>
        </div>

        <flux:separator />

        {{-- Info ringkas KP --}}
        <div class="grid gap-3 md:grid-cols-3">
            <flux:card class="space-y-1">
                <div class="text-xs text-zinc-500">Mahasiswa</div>
                <div class="font-medium">
                    {{ $kp->mahasiswa?->user?->name }} ({{ $kp->mahasiswa?->nim }})
                </div>
            </flux:card>

            <flux:card class="space-y-1">
                <div class="text-xs text-zinc-500">Judul KP</div>
                <div class="font-medium">{{ $kp->judul_kp }}</div>
            </flux:card>

            <flux:card class="space-y-1">
                <div class="text-xs text-zinc-500">Status Registrasi</div>
                <div class="font-medium">{{ $this->statusLabel }}</div>
            </flux:card>
        </div>

        {{-- FORM --}}
        <div class="grid gap-4">
            {{-- Judul final --}}
            <div>
                <flux:input label="Judul KP Final" wire:model.defer="judul_kp_final"
                    placeholder="Masukkan judul KP final" :invalid="$errors->has('judul_kp_final')"
                    :disabled="$this->isLocked()" />
                @error('judul_kp_final')
                    <div class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</div>
                @enderror
            </div>

            {{-- Tanggal & Ruangan --}}
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <flux:input type="date" label="Tanggal Seminar" wire:model.defer="tanggal_seminar"
                        :invalid="$errors->has('tanggal_seminar')" :disabled="$this->isLocked()" />
                    @error('tanggal_seminar')
                        <div class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <flux:select label="Ruangan" wire:model="ruangan_id" :invalid="$errors->has('ruangan_id')"
                        :disabled="$this->isLocked()">
                        <option value="">— Pilih Ruangan —</option>
                        @foreach ($this->rooms as $r)
                            <option value="{{ $r['id'] }}">{{ $r['label'] }}</option>
                        @endforeach
                    </flux:select>
                    @error('ruangan_id')
                        <div class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Jam --}}
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <flux:input type="time" label="Jam Mulai" wire:model.defer="jam_mulai"
                        :invalid="$errors->has('jam_mulai')" :disabled="$this->isLocked()" />
                    @error('jam_mulai')
                        <div class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <flux:input type="time" label="Jam Selesai" wire:model.defer="jam_selesai"
                        :invalid="$errors->has('jam_selesai')" :disabled="$this->isLocked()" />
                    @error('jam_selesai')
                        <div class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            {{-- Abstrak (opsional) --}}
            <div>
                <flux:textarea label="Abstrak (opsional)" rows="3" wire:model.defer="abstrak"
                    :invalid="$errors->has('abstrak')" placeholder="Ringkasan singkat"
                    :disabled="$this->isLocked()" />
                @error('abstrak')
                    <div class="mt-1 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</div>
                @enderror
            </div>

            {{-- Berkas laporan (PDF opsional) --}}
            <div class="space-y-1">
                <flux:input type="file" label="Berkas Laporan (PDF) — opsional" wire:model="berkas_laporan"
                    accept="application/pdf" :invalid="$errors->has('berkas_laporan')"
                    :disabled="$this->isLocked()" />
                <div class="text-xs text-zinc-500" wire:loading wire:target="berkas_laporan">Mengunggah…</div>
                @error('berkas_laporan')
                    <div class="text-xs text-rose-600 dark:text-rose-400">{{ $message }}</div>
                @enderror

                @if ($berkas_laporan_path)
                    <div
                        class="mt-1 flex items-center justify-between rounded-lg border bg-zinc-50 p-2 text-sm
                               dark:border-zinc-700 dark:bg-zinc-800">
                        <a class="truncate underline hover:no-underline" target="_blank"
                            href="{{ asset('storage/' . $berkas_laporan_path) }}">
                            Lihat berkas saat ini
                        </a>
                        @unless ($this->isLocked())
                            <button type="button" wire:click="removeFile"
                                class="text-rose-500 hover:text-rose-700 font-bold text-lg flex-shrink-0 ml-2">&times;</button>
                        @endunless
                    </div>
                @endif
            </div>

            {{-- Aksi --}}
            <div class="flex justify-end gap-2">
                @if (!$this->isLocked())
                    <flux:button variant="ghost" icon="bookmark" wire:click="saveDraft" wire:loading.attr="disabled"
                        wire:target="saveDraft,berkas_laporan">
                        Simpan Draf
                    </flux:button>

                    <flux:button variant="primary" icon="paper-airplane" wire:click="submitToAdvisor"
                        wire:loading.attr="disabled" wire:target="submitToAdvisor,saveDraft,berkas_laporan">
                        Kirim ke Dosen Pembimbing
                    </flux:button>
                @else
                    <flux:badge
                        class="border border-sky-200 dark:border-sky-900/40
                               bg-sky-50 text-sky-700
                               dark:bg-sky-900/20 dark:text-sky-300"
                        inset="top bottom">
                        Terkunci (menunggu proses berikutnya)
                    </flux:badge>
                @endif
            </div>
        </div>
    </flux:card>

    {{-- RINGKASAN & UNDUH BA (jika tersedia) --}}
    @if ($seminar)
        <flux:card
            class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-xs">
            <div class="flex items-center gap-2 px-1.5 -mt-1">
                <span
                    class="inline-flex items-center justify-center rounded-md p-1.5
                           bg-indigo-500 text-white dark:bg-indigo-400">
                    <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 3v18h18" />
                        <path d="M7 13v5" />
                        <path d="M12 9v9" />
                        <path d="M17 5v13" />
                    </svg>
                </span>
                <div>
                    <h4 class="text-sm font-semibold text-stone-900 dark:text-stone-100">Ringkasan Pengajuan</h4>
                    <p class="text-xs text-zinc-500 dark:text-zinc-300">Status & jadwal seminar KP.</p>
                </div>
            </div>

            <flux:separator />

            <div class="text-sm text-zinc-700 dark:text-stone-300 space-y-1">
                <div><span class="font-semibold text-stone-900 dark:text-stone-100">Status:</span>
                    <em>{{ $this->statusLabel }}</em></div>
                <div><span class="font-semibold text-stone-900 dark:text-stone-100">Judul:</span>
                    {{ $seminar->judul_laporan ?? '—' }}</div>
                <div>
                    <span class="font-semibold text-stone-900 dark:text-stone-100">Jadwal:</span>
                    {{ optional($seminar->tanggal_seminar)->format('d M Y') ?: '—' }}
                    @if ($seminar->jam_mulai || $seminar->jam_selesai)
                        • {{ $seminar->jam_mulai ?? '—' }} — {{ $seminar->jam_selesai ?? '—' }}
                    @endif
                </div>
                <div><span class="font-semibold text-stone-900 dark:text-stone-100">Ruangan:</span>
                    {{ $seminar->ruangan_nama ?? '—' }}</div>
            </div>

            @if ($seminar->status === 'ba_terbit')
                <div class="flex items-center justify-between gap-3 pt-2">
                    <div class="text-sm text-emerald-700 dark:text-emerald-300">
                        Berita Acara sudah terbit. Silakan unduh dokumen resmi.
                    </div>
                    <a class="inline-flex items-center rounded-md border px-3 py-1.5 text-sm
                              hover:bg-zinc-50 dark:hover:bg-zinc-800
                              border-zinc-200 dark:border-stone-700"
                        href="{{ route('mhs.kp.seminar.download.ba', [$kp->id, $seminar->id]) }}" target="_blank">
                        Unduh BA (DOCX)
                    </a>
                </div>
            @endif
        </flux:card>
    @endif
</div>
