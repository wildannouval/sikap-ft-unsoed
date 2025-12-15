<div class="flex flex-col md:flex-row items-start gap-8 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- SIDEBAR MENU --}}
    <div class="w-full md:w-64 flex-shrink-0">
        <flux:navlist>
            <flux:navlist.item :href="route('profile.edit')" wire:navigate icon="user-circle">
                {{ __('Profile') }}
            </flux:navlist.item>
            <flux:navlist.item :href="route('user-password.edit')" wire:navigate icon="key">
                {{ __('Password') }}
            </flux:navlist.item>
            <flux:navlist.item :href="route('appearance.edit')" wire:navigate icon="paint-brush">
                {{ __('Appearance') }}
            </flux:navlist.item>
        </flux:navlist>
    </div>

    {{-- KONTEN UTAMA --}}
    <div class="flex-1 w-full min-w-0">
        <div class="mb-6">
            <flux:heading size="xl" level="1">{{ $heading ?? '' }}</flux:heading>
            <flux:subheading class="text-zinc-500">{{ $subheading ?? '' }}</flux:subheading>
        </div>

        <flux:separator variant="subtle" class="mb-6" />

        <div class="space-y-6">
            {{ $slot }}
        </div>
    </div>
</div>
