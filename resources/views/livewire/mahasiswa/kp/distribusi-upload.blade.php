<div>
    {{-- Status ringkas di tabel --}}
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
        {{-- (Opsional) Nonaktifkan tombol jika BA belum terbit
        @php $canUpload = in_array($seminar?->status, ['ba_terbit','dinilai']); @endphp --}}
        <flux:button size="xs" variant="ghost" icon="arrow-up-tray" {{-- :disabled="!$canUpload" --}}
            wire:click="$set('showModal', true)">
            Upload Bukti
        </flux:button>
        {{-- @unless ($canUpload)
            <div class="mt-1 text-[11px] text-zinc-400">Menunggu BA terbit/dinilai</div>
        @endunless --}}
    @endif

    {{-- Modal upload --}}
    <flux:modal name="upload-distribusi-{{ $seminarId }}" :show="$showModal" class="min-w-[28rem]">
        <form wire:submit.prevent="save" class="space-y-4">
            <h3 class="text-base font-semibold">Upload Bukti Distribusi</h3>

            <div class="text-sm">
                <div class="text-zinc-500">Mahasiswa</div>
                <div class="font-medium">
                    {{ $seminar?->kp?->mahasiswa?->user?->name ?? '—' }}
                </div>
                <div class="text-xs text-zinc-500">
                    {{ $seminar?->kp?->mahasiswa?->nim ?? ($seminar?->kp?->mahasiswa?->mahasiswa_nim ?? '') }}
                </div>
            </div>

            <div>
                <flux:input type="file" accept=".pdf,.jpg,.jpeg,.png" label="Berkas (PDF/JPG/PNG, maks 10 MB)"
                    wire:model="file" />
                @error('file')
                    <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                @enderror>
                <div wire:loading wire:target="file" class="mt-1 text-xs text-zinc-500">
                    Mengunggah…
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <flux:button type="button" variant="ghost" wire:click="$set('showModal', false)">Batal</flux:button>
                <flux:button type="submit" variant="primary" icon="check" wire:loading.attr="disabled">
                    Simpan
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
