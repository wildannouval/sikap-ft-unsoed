<x-layouts.app :title="__('Master Data Mahasiswa')">
    <flux:toast />
    <flux:heading size="xl" level="1">{{ __('Master Data Mahasiswa') }}</flux:heading>
    <flux:subheading size="lg" class="mb-6">{{ __('Kelola akun & profil mahasiswa (Bapendik)') }}</flux:subheading>
    <flux:separator variant="subtle" />

    <livewire:bapendik.master.mahasiswa-index />
</x-layouts.app>
