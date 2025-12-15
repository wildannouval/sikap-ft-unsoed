<x-settings.layout :heading="__('Update Password')" :subheading="__('Ensure your account is using a long, random password to stay secure.')">
    <flux:card>
        <form method="POST" wire:submit="updatePassword" class="space-y-5 max-w-xl">
            <flux:input wire:model="current_password" :label="__('Current Password')" type="password" required
                autocomplete="current-password" />

            <flux:input wire:model="password" :label="__('New Password')" type="password" required
                autocomplete="new-password" />

            <flux:input wire:model="password_confirmation" :label="__('Confirm Password')" type="password" required
                autocomplete="new-password" />

            <div class="flex items-center justify-end gap-4 pt-2">
                <x-action-message on="password-updated">
                    <flux:badge color="green" size="sm" icon="check">{{ __('Saved.') }}</flux:badge>
                </x-action-message>

                <flux:button variant="primary" type="submit">{{ __('Save Password') }}</flux:button>
            </div>
        </form>
    </flux:card>
</x-settings.layout>
