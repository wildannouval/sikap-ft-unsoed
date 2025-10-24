<x-layouts.app :title="__('Penerbitan SPK')">
    <flux:toast />
    <flux:heading size="xl" level="1">{{ __('Penerbitan SPK') }}</flux:heading>
    <flux:separator variant="subtle" class="mb-4" />
    <livewire:bapendik.kp.spk-page />
</x-layouts.app>
