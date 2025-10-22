<x-layouts.app :title="__('Pengajuan Surat Pengantar')">
    <flux:toast />
    {{-- Komponen toast global di halaman ini --}}
        <flux:heading size="xl" level="1">{{ __('Pengajuan Surat Pengantar') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Kelola Pengajuan Surat Pengantar KP Mahasiswa') }}</flux:subheading>
        <flux:separator variant="subtle" />

        <livewire:mahasiswa.surat-pengantar.page />
</x-layouts.app>
