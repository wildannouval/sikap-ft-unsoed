<div class="space-y-6">
    <flux:toast />

    {{-- HEADER --}}
    <div class="flex items-center justify-between">
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

    {{-- PANDUAN --}}
    <flux:card class="space-y-3 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800">
        <div class="flex items-start gap-3">
            <div class="rounded-md p-2 bg-sky-500 text-white dark:bg-sky-400">
                {{-- Icon SVG inline (clipboard-document-list outline) --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 12h6M9 16h6M9 8h6m-3.75-5.25h1.5a2.25 2.25 0 012.25 2.25v.75h1.5A2.25 2.25 0 0120 8.25v10.5A2.25 2.25 0 0117.75 21H6.25A2.25 2.25 0 014 18.75V8.25A2.25 2.25 0 016.25 6.75h1.5V6c0-1.243 1.007-2.25 2.25-2.25z" />
                </svg>
            </div>
            <div>
                <flux:heading size="md">Panduan Data Dosen</flux:heading>
                <ul class="mt-1 text-sm text-zinc-600 dark:text-zinc-300 list-disc ms-4 space-y-1">
                    <li>Pencarian berdasarkan <em>nama</em> atau <em>NIP</em>.</li>
                    <li>Centang <strong>Komisi KP</strong> untuk memberi/menarik role “Dosen Komisi”.</li>
                    <li>Saat tambah, sistem otomatis membuat akun login “Dosen Pembimbing”.</li>
                </ul>
            </div>
        </div>
    </flux:card>

    {{-- HEADER + AKSI --}}
    <flux:card class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div class="md:w-80">
                <flux:input placeholder="Cari nama / NIP…" wire:model.live.debounce.500ms="q" icon="magnifying-glass" />
            </div>

            <div class="flex items-center gap-2">
                <flux:select wire:model.live="perPage">
                    <option value="10">10 / halaman</option>
                    <option value="25">25 / halaman</option>
                    <option value="50">50 / halaman</option>
                </flux:select>

                {{-- Trigger modal + set state --}}
                <flux:modal.trigger name="dosen-form">
                    <flux:button icon="plus" variant="primary" wire:click="create">Tambah Dosen</flux:button>
                </flux:modal.trigger>
            </div>
        </div>

        @if (session('ok'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
                {{ session('ok') }}
            </div>
        @endif

        {{-- TABEL --}}
        <flux:table :paginate="$rows">
            <flux:table.columns>
                <flux:table.column class="w-12">#</flux:table.column>
                <flux:table.column>Nama</flux:table.column>
                <flux:table.column class="whitespace-nowrap">NIP</flux:table.column>
                <flux:table.column>Jurusan</flux:table.column>
                <flux:table.column>Email</flux:table.column>
                <flux:table.column class="w-32">Komisi KP</flux:table.column>
                <flux:table.column class="w-28 text-right">Aksi</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($rows as $i => $r)
                    <flux:table.row :key="$r->dosen_id">
                        <flux:table.cell>{{ $rows->firstItem() + $i }}</flux:table.cell>
                        <flux:table.cell class="max-w-[260px]">
                            <div class="font-medium">{{ $r->dosen_name }}</div>
                        </flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">{{ $r->dosen_nip ?? '—' }}</flux:table.cell>
                        <flux:table.cell class="max-w-[220px]">
                            <span class="line-clamp-2">{{ $r->jurusan->nama_jurusan ?? '—' }}</span>
                        </flux:table.cell>
                        <flux:table.cell class="max-w-[260px]">
                            <span class="line-clamp-1">{{ $r->user->email ?? '—' }}</span>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($r->is_komisi_kp)
                                <flux:badge size="sm" inset="top bottom" color="green">Ya</flux:badge>
                            @else
                                <flux:badge size="sm" inset="top bottom" color="zinc">Tidak</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell class="text-right">
                            <flux:dropdown position="bottom" align="end">
                                <flux:button size="sm" variant="ghost" icon="ellipsis-horizontal"
                                    inset="top bottom" />
                                <flux:menu class="min-w-44">
                                    <flux:modal.trigger name="dosen-form">
                                        <flux:menu.item icon="pencil-square" wire:click="edit({{ $r->dosen_id }})">Edit
                                        </flux:menu.item>
                                    </flux:modal.trigger>
                                    <flux:menu.item icon="key" wire:click="resetUserPassword({{ $r->dosen_id }})">
                                        Reset Password</flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item icon="trash" variant="danger"
                                        wire:click="delete({{ $r->dosen_id }})">Hapus</flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:card>

    {{-- MODAL FORM (Flux) --}}
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
                            <option value="{{ $j->id }}">{{ $j->nama_jurusan }}</option>
                        @endforeach
                    </flux:select>
                </div>

                <div class="md:col-span-2">
                    <flux:checkbox wire:model.defer="is_komisi_kp" label="Tetapkan sebagai Dosen Komisi"
                        help="Menambah/menarik role “Dosen Komisi”." />
                </div>
            </div>

            <flux:separator />

            <div class="grid gap-4 md:grid-cols-2">
                <flux:input type="email" label="Email Login" wire:model.defer="email"
                    :invalid="$errors->has('email')" />
                <flux:input label="{{ $editingId ? 'Password (opsional untuk ubah)' : 'Password' }}"
                    wire:model.defer="password" :invalid="$errors->has('password')" />
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
