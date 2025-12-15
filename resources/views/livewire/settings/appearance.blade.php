<x-settings.layout :heading="__('Appearance')" :subheading="__('Customize how the application looks for you.')">
    <flux:card class="space-y-6">
        <div>
            <flux:heading size="lg" class="mb-2">Theme Preference</flux:heading>
            <p class="text-sm text-zinc-500 mb-4">Choose a theme that suits your preference.</p>

            <flux:radio.group x-data variant="segmented" x-model="$flux.appearance"
                class="flex flex-col sm:flex-row w-full">
                <flux:radio value="light" icon="sun" class="flex-1 justify-center">{{ __('Light') }}</flux:radio>
                <flux:radio value="dark" icon="moon" class="flex-1 justify-center">{{ __('Dark') }}</flux:radio>
                <flux:radio value="system" icon="computer-desktop" class="flex-1 justify-center">{{ __('System') }}
                </flux:radio>
            </flux:radio.group>
        </div>
    </flux:card>
</x-settings.layout>
