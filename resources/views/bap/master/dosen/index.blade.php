<x-layouts.app :title="__('Master Data Dosen')">
    <flux:toast />
    <flux:heading size="xl" level="1">{{ __('Master Data Dosen') }}</flux:heading>
    <flux:subheading size="lg" class="mb-6">{{ __('Kelola akun & profil dosen (Bapendik)') }}</flux:subheading>
    <flux:separator variant="subtle" />

    <livewire:bapendik.master.dosen-index />
</x-layouts.app>
