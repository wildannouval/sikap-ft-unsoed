<div class="relative">
    {{-- Jika sudah ada bukti: tampilkan link & timestamp kecil --}}
    @if ($seminar?->distribusi_proof_path)
        <div class="space-y-1">
            <a class="text-sm underline" href="{{ asset('storage/' . $seminar->distribusi_proof_path) }}" target="_blank">
                Lihat Bukti
            </a>
            <div class="text-[11px] text-zinc-500">
                {{ $seminar->distribusi_uploaded_at?->format('d M Y H:i') }}
            </div>
        </div>
    @else
        {{-- Tombol toggle expander (stabil di dalam row tabel) --}}
        <div class="flex items-center gap-2">
            <flux:button size="xs" variant="ghost" icon="arrow-up-tray" wire:click="toggle"
                class="text-indigo-700 hover:text-indigo-900 dark:text-indigo-300 dark:hover:text-indigo-200">
                Upload Bukti
            </flux:button>

            @error('file')
                <span class="text-[11px] text-red-600">{{ $message }}</span>
            @enderror
        </div>

        {{-- Panel inline (expander) --}}
        @if ($open)
            <div
                class="mt-3 rounded-lg border border-zinc-200 bg-white p-4 shadow-sm
                       dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-3 text-sm">
                    <div class="text-zinc-500">Mahasiswa</div>
                    <div class="font-medium">
                        {{ $seminar?->kp?->mahasiswa?->user?->name ?? '—' }}
                    </div>
                    <div class="text-xs text-zinc-500">
                        {{ $seminar?->kp?->mahasiswa?->nim ?? ($seminar?->kp?->mahasiswa?->mahasiswa_nim ?? '') }}
                    </div>
                </div>

                <div class="space-y-2">
                    <flux:input type="file" accept=".pdf,.jpg,.jpeg,.png" label="Berkas (PDF/JPG/PNG, maks 10 MB)"
                        wire:model="file" />
                    <div wire:loading wire:target="file" class="text-xs text-zinc-500">
                        Mengunggah…
                    </div>
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <flux:button type="button" variant="ghost" wire:click="toggle">
                        Batal
                    </flux:button>
                    <flux:button type="button" variant="primary" icon="check" wire:click="save"
                        wire:loading.attr="disabled">
                        Simpan
                    </flux:button>
                </div>
            </div>
        @endif
    @endif
</div>
