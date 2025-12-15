<div class="space-y-6" wire:poll.45s>
    {{-- HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1" class="text-stone-900 dark:text-stone-100">
                Notifikasi
            </flux:heading>
            <flux:subheading class="text-zinc-600 dark:text-zinc-300">
                Semua pemberitahuan aktivitas akun Anda.
            </flux:subheading>
        </div>
    </div>

    <flux:separator variant="subtle" />

    {{-- GRID UTAMA 3:1 --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">

        {{-- KOLOM KIRI: LIST NOTIFIKASI (3) --}}
        <div class="lg:col-span-3 space-y-6">
            <flux:tab.group wire:model.live="tab">
                <flux:tabs>
                    <flux:tab name="unread" icon="bell-alert">
                        Belum Dibaca
                        @if ($this->unreadCount > 0)
                            <flux:badge size="sm" color="amber" inset="top bottom" class="ml-2">
                                {{ $this->unreadCount }}</flux:badge>
                        @endif
                    </flux:tab>
                    <flux:tab name="all" icon="inbox-stack">
                        Semua
                        <flux:badge size="sm" inset="top bottom" class="ml-2">{{ $this->allCount }}</flux:badge>
                    </flux:tab>
                </flux:tabs>

                <flux:tab.panel name="unread" class="pt-4">
                    @include('livewire.notifications.partials.table', ['rows' => $this->rows])
                </flux:tab.panel>

                <flux:tab.panel name="all" class="pt-4">
                    @include('livewire.notifications.partials.table', ['rows' => $this->rows])
                </flux:tab.panel>
            </flux:tab.group>
        </div>

        {{-- KOLOM KANAN: SIDEBAR (1) --}}
        <div class="lg:col-span-1 space-y-6">

            {{-- AKSI CEPAT --}}
            <flux:card
                class="rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm">
                <div class="mb-4 flex items-center gap-2">
                    <flux:icon.adjustments-horizontal class="size-5 text-zinc-500" />
                    <h3 class="font-semibold text-stone-900 dark:text-stone-100">Aksi</h3>
                </div>

                <div class="space-y-2">
                    <flux:button class="w-full justify-start" variant="ghost" icon="check-circle"
                        wire:click="markAllRead" :disabled="$this->unreadCount === 0">
                        Tandai Semua Dibaca
                    </flux:button>

                    <flux:button class="w-full justify-start text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20"
                        variant="ghost" icon="trash"
                        onclick="if (confirm('Hapus semua notifikasi?')) { @this.deleteAll(); }">
                        Hapus Semua
                    </flux:button>
                </div>
            </flux:card>

            {{-- INFO --}}
            <flux:card
                class="rounded-xl border bg-sky-50/50 dark:bg-sky-900/10 border-sky-100 dark:border-sky-800/30 shadow-sm">
                <div class="flex items-start gap-3">
                    <flux:icon.information-circle class="mt-0.5 size-5 text-sky-600 dark:text-sky-400" />
                    <div>
                        <h3 class="font-semibold text-sky-900 dark:text-sky-100 text-sm">Info Notifikasi</h3>
                        <p class="mt-2 text-xs text-sky-800 dark:text-sky-200 leading-relaxed">
                            Notifikasi yang belum dibaca ditandai dengan titik hijau. Klik tombol aksi untuk membuka
                            tautan terkait.
                        </p>
                    </div>
                </div>
            </flux:card>
        </div>
    </div>
</div>
