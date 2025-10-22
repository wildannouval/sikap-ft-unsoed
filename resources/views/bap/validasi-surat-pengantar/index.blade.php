<x-layouts.app :title="__('Validasi & Riwayat Surat Pengantar')">
    <flux:toast />
{{-- 
    <flux:heading size="xl" level="1">{{ __('Validasi & Riwayat Surat Pengantar') }}</flux:heading>
    <flux:subheading size="lg" class="mb-6">
        {{ __('Kelola pengajuan, terbitkan nomor, dan lihat riwayat surat pengantar') }}
    </flux:subheading>
    <flux:separator variant="subtle" /> --}}

    {{-- Komponen Livewire --}}
    <livewire:bapendik.surat-pengantar.validasi-page />
</x-layouts.app>
