<x-layouts.app :title="__('Pengajuan Kerja Praktik')">
    <flux:toast />
    <flux:heading size="xl" level="1">{{ __('Pengajuan Kerja Praktik') }}</flux:heading>
    <flux:subheading size="lg" class="mb-6">{{ __('Kelola pengajuan KP mahasiswa') }}</flux:subheading>
    <flux:separator variant="subtle" />

    <livewire:mahasiswa.kp.page />
</x-layouts.app>
