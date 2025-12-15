<div class="space-y-6">
    <flux:toast />

    {{-- HEADER --}}
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <div>
            <flux:heading size="xl" level="1" class="text-stone-900 dark:text-stone-100">
                Master Data Dosen
            </flux:heading>
            <flux:subheading class="text-zinc-600 dark:text-zinc-300">
                Kelola akun & profil dosen (Bapendik).
            </flux:subheading>
        </div>
    </div>

    <flux:separator variant="subtle" />

    {{-- GRID UTAMA 3:1 --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-4">

        {{-- KOLOM KIRI: TABEL (3) --}}
        <div class="lg:col-span-3 space-y-6">
            <flux:card
                class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-sm overflow-hidden">

                {{-- Header Tabel --}}
                <div
                    class="flex flex-col gap-4 px-6 py-4 border-b border-zinc-200 dark:border-stone-800 bg-sky-50/50 dark:bg-sky-900/10 md:flex-row md:items-center md:justify-between">
                    <h4 class="text-base font-semibold text-stone-900 dark:text-stone-100">Daftar Dosen</h4>

                    <div class="flex items-center gap-3">
                        <flux:input icon="magnifying-glass" placeholder="Cari nama / NIP..."
                            wire:model.live.debounce.400ms="q" class="w-full md:w-56 bg-white dark:bg-stone-900" />

                        <flux:select wire:model.live="perPage" class="w-20">
                            <flux:select.option :value="10">10</flux:select.option>
                            <flux:select.option :value="25">25</flux:select.option>
                            <flux:select.option :value="50">50</flux:select.option>
                        </flux:select>

                        {{-- Tombol Tambah di Kanan --}}
                        <flux:button icon="plus" variant="primary" size="sm" wire:click="create">Tambah
                        </flux:button>
                    </div>
                </div>

                <flux:table :paginate="$this->items">
                    <flux:table.columns>
                        <flux:table.column class="w-12 text-center">No</flux:table.column>
                        <flux:table.column sortable :sorted="$sortBy === 'dosen_name'" :direction="$sortDirection"
                            wire:click="sort('dosen_name')">Nama</flux:table.column>
                        <flux:table.column>NIP</flux:table.column>
                        <flux:table.column>Jurusan</flux:table.column>
                        <flux:table.column>Email</flux:table.column>
                        <flux:table.column class="w-32 text-center">Komisi KP</flux:table.column>
                        <flux:table.column class="text-right">Aksi</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($this->items as $i => $r)
                            <flux:table.row :key="$r->dosen_id">
                                <flux:table.cell class="text-center text-zinc-500">
                                    {{ $this->items->firstItem() + $i }}
                                </flux:table.cell>

                                <flux:table.cell class="max-w-[240px]">
                                    <div class="font-medium text-stone-900 dark:text-stone-100 truncate">
                                        {{ $r->dosen_name }}
                                    </div>
                                </flux:table.cell>

                                <flux:table.cell class="whitespace-nowrap font-mono text-xs">
                                    {{ $r->dosen_nip ?? '—' }}
                                </flux:table.cell>

                                <flux:table.cell class="max-w-[200px]">
                                    <span class="line-clamp-1">{{ $r->jurusan->nama_jurusan ?? '—' }}</span>
                                </flux:table.cell>

                                <flux:table.cell class="max-w-[220px]">
                                    <span class="line-clamp-1 text-xs">{{ $r->user->email ?? '—' }}</span>
                                </flux:table.cell>

                                <flux:table.cell class="text-center">
                                    @if ($r->is_komisi_kp)
                                        <flux:badge size="sm" color="emerald" icon="check-circle"
                                            inset="top bottom">Ya</flux:badge>
                                    @else
                                        <flux:badge size="sm" color="zinc" icon="minus" inset="top bottom">
                                            Tidak</flux:badge>
                                    @endif
                                </flux:table.cell>

                                <flux:table.cell class="text-right">
                                    <flux:dropdown position="bottom" align="end">
                                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                        <flux:menu class="min-w-44">
                                            <flux:menu.item icon="pencil-square" wire:click="edit({{ $r->dosen_id }})">
                                                Edit
                                            </flux:menu.item>
                                            <flux:menu.item icon="key"
                                                wire:click="resetUserPassword({{ $r->dosen_id }})">
                                                Reset Password
                                            </flux:menu.item>
                                            <flux:menu.separator />
                                            <flux:menu.item icon="trash" variant="danger"
                                                wire:click="delete({{ $r->dosen_id }})">Hapus
                                            </flux:menu.item>
                                        </flux:menu>
                                    </flux:dropdown>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>

                {{-- Empty State --}}
                @if ($this->items->isEmpty())
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <div class="rounded-full bg-zinc-100 p-4 dark:bg-stone-900">
                            <flux:icon.users class="size-8 text-zinc-400" />
                        </div>
                        <h3 class="mt-4 text-base font-semibold text-stone-900 dark:text-stone-100">
                            Belum ada data
                        </h3>
                        <p class="mt-1 text-sm text-zinc-500">
                            @if ($q)
                                Tidak ditemukan dosen dengan kata kunci "{{ $q }}".
                            @else
                                Belum ada data dosen yang ditambahkan.
                            @endif
                        </p>
                    </div>
                @endif
            </flux:card>
        </div>

        {{-- KOLOM KANAN: SIDEBAR (1) --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- PANDUAN --}}
            <flux:card
                class="rounded-xl border bg-sky-50/50 dark:bg-sky-900/10 border-sky-100 dark:border-sky-800/30 shadow-sm">
                <div class="flex items-start gap-3">
                    <flux:icon.information-circle class="mt-0.5 size-5 text-sky-600 dark:text-sky-400" />
                    <div>
                        <h3 class="font-semibold text-sky-900 dark:text-sky-100 text-sm">Panduan Data Dosen</h3>
                        <ul class="mt-3 text-xs text-sky-800 dark:text-sky-200 space-y-2 list-disc list-inside">
                            <li>Pencarian berdasarkan <strong>nama</strong> atau <strong>NIP</strong>.</li>
                            <li>Centang <strong>Komisi KP</strong> untuk memberi/menarik role “Dosen Komisi”.</li>
                            <li>Saat tambah, sistem otomatis membuat akun login “Dosen Pembimbing”.</li>
                            <li>Gunakan menu aksi untuk reset password jika dosen lupa.</li>
                        </ul>
                    </div>
                </div>
            </flux:card>
        </div>
    </div>

    {{-- MODAL FORM --}}
    <flux:modal name="dosen-form" class="min-w-[36rem]" :show="$showForm">
        <div class="space-y-6">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <flux:heading size="lg">{{ $editingId ? 'Edit Dosen' : 'Tambah Dosen' }}</flux:heading>
                    <flux:subheading class="mt-1">Lengkapi data dosen & akun login.</flux:subheading>
                </div>
                <flux:modal.close>
                    <flux:button variant="ghost" icon="x-mark" wire:click="closeForm"></flux:button>
                </flux:modal.close>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <flux:input label="Nama" wire:model.defer="dosen_name" :invalid="$errors->has('dosen_name')" />
                <flux:input label="NIP" wire:model.defer="dosen_nip" :invalid="$errors->has('dosen_nip')" />

                <div class="md:col-span-2">
                    <flux:select label="Jurusan" wire:model.defer="jurusan_id" :invalid="$errors->has('jurusan_id')">
                        <option value="">— Pilih —</option>
                        @foreach ($jurusans as $j)
                            <flux:select.option value="{{ $j->id }}">{{ $j->nama_jurusan }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <div class="md:col-span-2">
                    <flux:checkbox wire:model.defer="is_komisi_kp" label="Tetapkan sebagai Dosen Komisi"
                        description="Menambah/menarik role “Dosen Komisi”." />
                </div>
            </div>

            <flux:separator />

            <div class="grid gap-4 md:grid-cols-2">
                <flux:input type="email" label="Email Login" wire:model.defer="email"
                    :invalid="$errors->has('email')" />
                <flux:input label="{{ $editingId ? 'Password (opsional)' : 'Password' }}" wire:model.defer="password"
                    :invalid="$errors->has('password')" />
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost" wire:click="closeForm">Batal</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" icon="check" wire:click="save">Simpan</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
