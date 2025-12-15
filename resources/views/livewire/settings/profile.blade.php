<x-settings.layout :heading="__('Profile')" :subheading="__('Manage your account information and photo.')">

    {{-- ===== FOTO PROFIL ===== --}}
    <flux:card>
        <div class="flex flex-col sm:flex-row sm:items-center gap-6">
            <div class="shrink-0">
                <div
                    class="h-24 w-24 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800 ring-1 ring-zinc-200 dark:ring-zinc-700 flex items-center justify-center relative">
                    @if ($photo_url)
                        <img src="{{ $photo_url }}" alt="Profile Photo" class="h-full w-full object-cover"
                            id="profile-preview">
                    @else
                        <span class="text-xl font-bold text-zinc-500"
                            id="profile-initials">{{ auth()->user()->initials() }}</span>
                    @endif

                    {{-- Overlay loading saat upload --}}
                    <div wire:loading wire:target="photo"
                        class="absolute inset-0 bg-black/50 flex items-center justify-center">
                        <flux:icon.arrow-path class="animate-spin text-white size-6" />
                    </div>
                </div>
            </div>

            <div class="flex-1 space-y-3">
                <flux:heading size="lg">Profile Photo</flux:heading>
                <div class="flex flex-wrap gap-3">
                    <flux:button size="sm" variant="primary"
                        onclick="document.getElementById('photo-input').click()">
                        Upload New Photo
                    </flux:button>

                    @if ($photo_url)
                        <flux:button size="sm" variant="ghost"
                            class="text-rose-600 hover:bg-rose-50 hover:text-rose-700" wire:click="removeProfilePhoto">
                            Remove
                        </flux:button>
                    @endif
                </div>
                <p class="text-xs text-zinc-500">
                    JPG, JPEG, PNG, or WEBP. Max 2MB.
                </p>

                {{-- Hidden Input --}}
                <input type="file" id="photo-input" wire:model="photo" accept="image/*" class="hidden" />
                @error('photo')
                    <p class="text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </flux:card>

    {{-- ===== DATA PROFIL ===== --}}
    <flux:card>
        <div class="space-y-5 max-w-xl">
            <div>
                <flux:heading size="lg" class="mb-1">Personal Information</flux:heading>
                <p class="text-sm text-zinc-500">Update your account's profile information and email address.</p>
            </div>

            <flux:input wire:model.defer="name" :label="__('Name')" :disabled="!$canEditProfile" />

            <flux:input wire:model.defer="email" :label="__('Email')" type="email" :disabled="!$canEditProfile" />

            <div class="flex items-center justify-between pt-2">
                @unless ($canEditProfile)
                    <div
                        class="flex items-center gap-2 text-xs text-amber-600 bg-amber-50 px-2 py-1 rounded-md border border-amber-200">
                        <flux:icon.lock-closed class="size-3" />
                        <span>Contact administrator to change info</span>
                    </div>
                @else
                    <div></div> {{-- Spacer --}}
                @endunless

                <div class="flex items-center gap-3">
                    <x-action-message on="profile-updated">
                        <flux:badge color="green" size="sm" icon="check">{{ __('Saved.') }}</flux:badge>
                    </x-action-message>

                    <flux:button variant="primary" wire:click="updateProfileInformation" :disabled="!$canEditProfile">
                        {{ __('Save Changes') }}
                    </flux:button>
                </div>
            </div>
        </div>
    </flux:card>

    {{-- ===== DELETE ACCOUNT (Optional) ===== --}}
    @role('Bapendik')
        <div class="pt-6 border-t border-zinc-200 dark:border-zinc-800">
            <div class="max-w-xl">
                @if (View::exists('livewire.settings.delete-user'))
                    @include('livewire.settings.delete-user')
                @endif
            </div>
        </div>
    @endrole

    {{-- Script untuk preview lokal --}}
    <script>
        window.addEventListener('profile-photo-updated', (e) => {
            const url = e.detail[0]?.url; // Livewire v3 event detail array
            const img = document.getElementById('profile-preview');
            const initials = document.getElementById('profile-initials');

            if (url) {
                if (img) {
                    img.src = url;
                } else if (initials && initials.parentElement) {
                    // Replace initials with image
                    const newImg = document.createElement('img');
                    newImg.src = url;
                    newImg.id = 'profile-preview';
                    newImg.className = 'h-full w-full object-cover';
                    initials.replaceWith(newImg);
                }
            }
        });

        window.addEventListener('profile-photo-removed', () => {
            // Reload page or handle manually, usually reload is safer for avatar consistency in sidebar
            window.location.reload();
        });
    </script>
</x-settings.layout>
