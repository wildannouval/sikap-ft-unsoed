{{-- Komponen ini HANYA berisi CARD FORM (tanpa modal & tanpa expander) --}}
<div class="space-y-4">
    {{-- Ringkasan entitas --}}
    <div class="grid gap-4 sm:grid-cols-3 text-sm">
        <div>
            <div class="text-zinc-500">Mahasiswa</div>
            <div class="font-medium">
                {{ $seminar?->kp?->mahasiswa?->user?->name ?? '—' }}
            </div>
            <div class="text-xs text-zinc-500">
                {{ $seminar?->kp?->mahasiswa?->nim ?? ($seminar?->kp?->mahasiswa?->mahasiswa_nim ?? '') }}
            </div>
        </div>
        <div class="sm:col-span-2">
            <div class="text-zinc-500">Judul</div>
            <div class="font-medium line-clamp-2">
                {{ $seminar?->judul_laporan ?? '—' }}
            </div>
        </div>
    </div>

    {{-- Form Upload --}}
    <div class="space-y-2">
        <flux:input type="file" accept=".pdf,.jpg,.jpeg,.png" label="Berkas (PDF/JPG/PNG, maks 10 MB)"
            wire:model="file" />
        @error('file')
            <div class="text-sm text-red-600">{{ $message }}</div>
        @enderror
        <div wire:loading wire:target="file" class="text-xs text-zinc-500">
            Mengunggah…
        </div>
    </div>

    <div class="flex justify-end gap-2">
        <flux:button type="button" variant="ghost" wire:click="$dispatch('mhs-upload-cancel')">
            Batal
        </flux:button>
        <flux:button type="button" variant="primary" icon="check" wire:click="save" wire:loading.attr="disabled">
            Simpan
        </flux:button>
    </div>
</div>
