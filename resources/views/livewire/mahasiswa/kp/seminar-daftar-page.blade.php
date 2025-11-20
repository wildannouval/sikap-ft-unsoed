<div class="space-y-6">
    {{-- Flash messages --}}
    @if (session('ok'))
        <div class="rounded-md border border-emerald-300/60 bg-emerald-50 px-3 py-2 text-emerald-800">
            <div class="font-medium">{{ session('ok') }}</div>
        </div>
    @endif
    @if (session('err'))
        <div class="rounded-md border border-red-300/60 bg-red-50 px-3 py-2 text-red-800">
            <div class="font-medium">{{ session('err') }}</div>
        </div>
    @endif

    <flux:card class="space-y-4">
        <div>
            <h3 class="text-base font-semibold">Daftar Seminar KP</h3>
            <p class="text-sm text-zinc-500">Lengkapi data berikut lalu kirim untuk persetujuan Dosen Pembimbing.</p>
        </div>

        {{-- Info ringkas KP --}}
        <div class="grid md:grid-cols-3 gap-3">
            <flux:card class="space-y-1">
                <div class="text-xs text-zinc-500">Mahasiswa</div>
                <div class="font-medium">{{ $kp->mahasiswa?->user?->name }} ({{ $kp->mahasiswa?->nim }})</div>
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
            <flux:input label="Judul KP Final" wire:model.defer="judul_kp_final"
                :invalid="$errors->has('judul_kp_final')" placeholder="Masukkan judul KP final"
                :disabled="$this->isLocked()" />
            @error('judul_kp_final')
                <div class="text-sm text-red-600 -mt-2">{{ $message }}</div>
            @enderror

            <div class="grid md:grid-cols-2 gap-4">
                <flux:input type="date" label="Tanggal Seminar" wire:model.defer="tanggal_seminar"
                    :invalid="$errors->has('tanggal_seminar')" :disabled="$this->isLocked()" />
                <flux:select label="Ruangan" wire:model="ruangan_id" :invalid="$errors->has('ruangan_id')"
                    :disabled="$this->isLocked()">
                    <option value="">— Pilih Ruangan —</option>
                    @foreach ($this->rooms as $r)
                        <option value="{{ $r['id'] }}">{{ $r['label'] }}</option>
                    @endforeach
                </flux:select>
            </div>
            @error('tanggal_seminar')
                <div class="text-sm text-red-600 -mt-2">{{ $message }}</div>
            @enderror
            @error('ruangan_id')
                <div class="text-sm text-red-600 -mt-2">{{ $message }}</div>
            @enderror

            <div class="grid md:grid-cols-2 gap-4">
                <flux:input type="time" label="Jam Mulai" wire:model.defer="jam_mulai"
                    :invalid="$errors->has('jam_mulai')" :disabled="$this->isLocked()" />
                <flux:input type="time" label="Jam Selesai" wire:model.defer="jam_selesai"
                    :invalid="$errors->has('jam_selesai')" :disabled="$this->isLocked()" />
            </div>
            @error('jam_mulai')
                <div class="text-sm text-red-600 -mt-2">{{ $message }}</div>
            @enderror
            @error('jam_selesai')
                <div class="text-sm text-red-600 -mt-2">{{ $message }}</div>
            @enderror

            {{-- Opsional: berkas & abstrak --}}
            <flux:textarea label="Abstrak (opsional)" rows="3" wire:model.defer="abstrak"
                :invalid="$errors->has('abstrak')" placeholder="Ringkasan singkat" :disabled="$this->isLocked()" />
            @error('abstrak')
                <div class="text-sm text-red-600 -mt-2">{{ $message }}</div>
            @enderror

            <div>
                <flux:input type="file" label="Berkas Laporan (PDF) — opsional" wire:model="berkas_laporan"
                    accept="application/pdf" :invalid="$errors->has('berkas_laporan')"
                    :disabled="$this->isLocked()" />
                @error('berkas_laporan')
                    <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                @enderror

                @if ($berkas_laporan_path)
                    <div class="mt-2 text-sm">
                        <a class="underline" target="_blank" href="{{ asset('storage/' . $berkas_laporan_path) }}">
                            Lihat berkas saat ini
                        </a>
                        @unless ($this->isLocked())
                            <button class="ml-2 text-red-600 hover:underline" wire:click="removeFile">Hapus</button>
                        @endunless
                    </div>
                @endif

                <div wire:loading wire:target="berkas_laporan" class="text-xs text-zinc-500 mt-1">Mengunggah…</div>
            </div>

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
                    <flux:badge color="blue" inset="top bottom">Terkunci (menunggu proses berikutnya)</flux:badge>
                @endif
            </div>
        </div>
    </flux:card>

    {{-- RINGKASAN & UNDUH BA (jika tersedia) --}}
    @if ($seminar)
        <flux:card class="space-y-3">
            <div class="text-sm text-zinc-600 space-y-1">
                <div><span class="font-semibold">Status:</span> <em>{{ $this->statusLabel }}</em></div>
                <div>Judul: {{ $seminar->judul_laporan ?? '—' }}</div>
                <div>
                    Jadwal:
                    {{ optional($seminar->tanggal_seminar)->format('d M Y') ?: '—' }}
                    @if ($seminar->jam_mulai || $seminar->jam_selesai)
                        • {{ $seminar->jam_mulai ?? '—' }} — {{ $seminar->jam_selesai ?? '—' }}
                    @endif
                </div>
                <div>Ruangan: {{ $seminar->ruangan_nama ?? '—' }}</div>
            </div>

            @if ($seminar->status === 'ba_terbit')
                <div class="flex items-center justify-between gap-3 pt-2">
                    <div class="text-sm text-emerald-700">Berita Acara sudah terbit. Silakan unduh dokumen resmi.</div>
                    <a class="inline-flex items-center rounded-md border px-3 py-1.5 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-800"
                        href="{{ route('mhs.kp.seminar.download.ba', [$kp->id, $seminar->id]) }}" target="_blank">
                        Unduh BA (DOCX)
                    </a>
                </div>
            @endif
        </flux:card>
    @endif
</div>
