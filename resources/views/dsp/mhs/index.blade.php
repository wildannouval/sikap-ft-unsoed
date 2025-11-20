{{-- resources/views/dsp/mhs/index.blade.php --}}
{{-- <x-layouts.app>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold">Mahasiswa Bimbingan</h3>
                <p class="text-sm text-zinc-500">Daftar mahasiswa yang dibimbing.</p>
            </div>
        </div>

        <flux:card class="space-y-4">
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div class="md:w-80">
                    <flux:input placeholder="Cari nama / NIM / judul..." icon="magnifying-glass" disabled />
                </div>
            </div>

            <div class="text-sm text-zinc-500">
                (Placeholder) Halaman ini siap diisi dengan komponen Livewire untuk menampilkan daftar mahasiswa
                bimbingan.
            </div>
        </flux:card>
    </div>
</x-layouts.app> --}}
<x-layouts.app :title="__('Mahasiswa Bimbingan')">
    <flux:toast />
    <flux:heading size="xl" level="1">{{ __('Mahasiswa Bimbingan') }}</flux:heading>
    <flux:subheading size="lg" class="mb-6">{{ __('Daftar mahasiswa yang dibimbing') }}</flux:subheading>
    <flux:separator variant="subtle" />
    <livewire:dosen.kp.konsultasi-index />
</x-layouts.app>
