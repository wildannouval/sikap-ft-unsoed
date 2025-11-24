<x-settings.layout>
    <x-slot name="heading">{{ __('Profile') }}</x-slot>
    <x-slot name="subheading">{{ __('Manage your account settings') }}</x-slot>

    {{-- ===== Foto Profil ===== --}}
    <flux:card class="mb-6">
        <flux:heading size="sm">{{ __('Profile Photo') }}</flux:heading>
        <div class="mt-4 flex items-center gap-4">
            <div
                class="h-16 w-16 overflow-hidden rounded-xl bg-neutral-200 dark:bg-neutral-700 flex items-center justify-center">
                @if ($photo_url)
                    <img src="{{ $photo_url }}" alt="Profile Photo" class="h-16 w-16 object-cover" id="profile-preview">
                @else
                    <span class="text-sm font-semibold" id="profile-initials">{{ auth()->user()->initials() }}</span>
                @endif
            </div>

            <div class="flex flex-col gap-2">
                <flux:input type="file" wire:model="photo" accept="image/png,image/jpeg,image/webp" />
                @error('photo')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror

                <div class="flex gap-2">
                    <flux:button size="sm" variant="primary" wire:click="updateProfilePhoto"
                        :disabled="!$photo">
                        {{ __('Upload / Update') }}
                    </flux:button>

                    <flux:button size="sm" variant="ghost" wire:click="removeProfilePhoto" :disabled="!$photo_url">
                        {{ __('Remove Photo') }}
                    </flux:button>
                </div>
                <p class="text-xs text-zinc-500">{{ __('Max 2 MB. JPG, JPEG, PNG, WEBP.') }}</p>
            </div>
        </div>
    </flux:card>

    {{-- ===== Data Profil (Name/Email) — HANYA BAPENDIK BOLEH EDIT ===== --}}
    <flux:card>
        <flux:heading size="sm">{{ __('Profile Information') }}</flux:heading>
        <div class="mt-4 grid gap-4">
            <flux:input wire:model.defer="name" :label="__('Name')" :disabled="!$canEditProfile"
                placeholder="Nama lengkap" />
            <flux:input wire:model.defer="email" :label="__('Email')" type="email" :disabled="!$canEditProfile"
                placeholder="email@example.com" />

            <div class="flex items-center gap-2">
                <flux:button variant="primary" wire:click="updateProfileInformation" :disabled="!$canEditProfile">
                    {{ __('Save changes') }}
                </flux:button>

                @unless ($canEditProfile)
                    <flux:badge size="sm" color="zinc" inset="top bottom">
                        {{ __('Hanya Bapendik yang dapat mengubah nama & email') }}
                    </flux:badge>
                @endunless
            </div>
        </div>
    </flux:card>

    {{-- ===== Delete Account (opsional, hanya Bapendik) ===== --}}
    @role('Bapendik')
        @if (View::exists('livewire.settings.delete-user'))
            <div class="mt-6">
                @include('livewire.settings.delete-user')
            </div>
        @endif
    @endrole

    {{-- Sinkronisasi preview lokal (opsional) --}}
    <script>
        // Jika Livewire memancarkan event dengan URL baru, perbarui preview kecil di halaman Profile juga
        window.addEventListener('profile-photo-updated', (e) => {
            const url = e?.detail?.url;
            const img = document.getElementById('profile-preview');
            const initials = document.getElementById('profile-initials');

            if (url) {
                if (img) {
                    img.src = url + (url.includes('?') ? '&' : '?') + 't=' + Date.now();
                } else {
                    const box = initials?.parentElement;
                    if (box) {
                        const el = document.createElement('img');
                        el.id = 'profile-preview';
                        el.className = 'h-16 w-16 object-cover';
                        el.alt = 'Profile Photo';
                        el.src = url + '?t=' + Date.now();
                        box.innerHTML = '';
                        box.appendChild(el);
                    }
                }
            } else {
                // tanpa payload URL, biar sidebar yang handle reload
            }
        });

        window.addEventListener('profile-photo-removed', () => {
            const img = document.getElementById('profile-preview');
            const box = img?.parentElement;
            if (box) {
                box.innerHTML =
                    `<span class="text-sm font-semibold" id="profile-initials">{{ auth()->user()->initials() }}</span>`;
            }
        });
    </script>
</x-settings.layout>
