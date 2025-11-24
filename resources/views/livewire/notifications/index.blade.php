<div class="space-y-6" wire:poll.45s>
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Notifikasi</flux:heading>
            <flux:subheading class="text-zinc-600">
                Semua notifikasi untuk akun ini. Belum dibaca:
                <span class="font-semibold">{{ $this->unreadCount }}</span>
            </flux:subheading>
        </div>

        <div class="flex items-center gap-2">
            <flux:button variant="outline" icon="check" wire:click="markAllRead" :disabled="$this->unreadCount === 0">
                Tandai semua terbaca
            </flux:button>

            <flux:button variant="ghost" icon="trash"
                onclick="if (confirm('Hapus semua notifikasi? Tindakan ini tidak bisa dibatalkan.')) { Livewire.dispatch('delete-all-notifs'); }">
                Hapus semua
            </flux:button>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('delete-all-notifs', () => {
                @this.deleteAll();
            });
        });
    </script>

    <flux:tab.group wire:model.live="tab">
        <flux:tabs>
            <flux:tab name="unread" icon="bell-alert">
                Belum dibaca
                <flux:badge size="sm" inset="top bottom" class="ml-2">{{ $this->unreadCount }}</flux:badge>
            </flux:tab>
            <flux:tab name="all" icon="bell">
                Semua
            </flux:tab>
        </flux:tabs>

        <flux:tab.panel name="unread" class="pt-4">
            @include('livewire.notifications.partials-table', ['rows' => $this->rows])
        </flux:tab.panel>

        <flux:tab.panel name="all" class="pt-4">
            @include('livewire.notifications.partials-table', ['rows' => $this->rows])
        </flux:tab.panel>
    </flux:tab.group>
</div>
