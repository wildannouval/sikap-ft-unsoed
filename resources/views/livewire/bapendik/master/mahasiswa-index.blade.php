<div class="space-y-6">
    <flux:toast />

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
                <flux:heading size="md">Panduan Data Mahasiswa</flux:heading>
                <ul class="mt-1 text-sm text-zinc-600 dark:text-zinc-300 list-disc ms-4 space-y-1">
                    <li>Cari cepat berdasarkan <em>nama</em> atau <em>NIM</em>.</li>
                    <li>Akun login otomatis memiliki role <strong>Mahasiswa</strong>.</li>
                    <li>Gunakan “Reset Password” jika ada kendala akses.</li>
                </ul>
            </div>
        </div>
    </flux:card>

    {{-- HEADER + AKSI --}}
    <flux:card class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div class="md:w-80">
                <flux:input placeholder="Cari nama / NIM…" wire:model.live.debounce.500ms="q" icon="magnifying-glass" />
            </div>

            <div class="flex items-center gap-2">
                <flux:select wire:model.live="perPage">
                    <option value="10">10 / halaman</option>
                    <option value="25">25 / halaman</option>
                    <option value="50">50 / halaman</option>
                </flux:select>

                {{-- Trigger modal + set state --}}
                <flux:modal.trigger name="mhs-form">
                    <flux:button icon="plus" variant="primary" wire:click="create">Tambah Mahasiswa</flux:button>
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
                <flux:table.column class="whitespace-nowrap">NIM</flux:table.column>
                <flux:table.column>Jurusan</flux:table.column>
                <flux:table.column class="w-24">Angkatan</flux:table.column>
                <flux:table.column>Email</flux:table.column>
                <flux:table.column class="w-28 text-right">Aksi</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($rows as $i => $r)
                    <flux:table.row :key="$r->mahasiswa_id">
                        <flux:table.cell>{{ $rows->firstItem() + $i }}</flux:table.cell>

                        <flux:table.cell class="max-w-[260px]">
                            <div class="font-medium">{{ $r->mahasiswa_name }}</div>
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap">{{ $r->mahasiswa_nim }}</flux:table.cell>

                        <flux:table.cell class="max-w-[220px]">
                            <span class="line-clamp-2">{{ $r->jurusan->nama_jurusan ?? '—' }}</span>
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap">{{ $r->mahasiswa_tahun_angkatan ?? '—' }}
                        </flux:table.cell>

                        <flux:table.cell class="max-w-[260px]">
                            <span class="line-clamp-1">{{ $r->user->email ?? '—' }}</span>
                        </flux:table.cell>

                        <flux:table.cell class="text-right">
                            <flux:dropdown position="bottom" align="end">
                                <flux:button size="sm" variant="ghost" icon="ellipsis-horizontal"
                                    inset="top bottom" />
                                <flux:menu class="min-w-44">
                                    <flux:modal.trigger name="mhs-form">
                                        <flux:menu.item icon="pencil-square" wire:click="edit({{ $r->mahasiswa_id }})">
                                            Edit</flux:menu.item>
                                    </flux:modal.trigger>
                                    <flux:menu.item icon="key"
                                        wire:click="resetUserPassword({{ $r->mahasiswa_id }})">Reset Password
                                    </flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item icon="trash" variant="danger"
                                        wire:click="delete({{ $r->mahasiswa_id }})">Hapus</flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:card>

    {{-- MODAL FORM (Flux) --}}
    <flux:modal name="mhs-form" class="min-w-[36rem]" :show="$showForm">
        <div class="space-y-6">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <flux:heading size="lg">{{ $editingId ? 'Edit Mahasiswa' : 'Tambah Mahasiswa' }}
                    </flux:heading>
                    <flux:subheading class="mt-1">Lengkapi data mahasiswa & akun login.</flux:subheading>
                </div>
                <flux:modal.close>
                    <flux:button variant="ghost" icon="x-mark" wire:click="closeForm"></flux:button>
                </flux:modal.close>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <flux:input label="Nama" wire:model.defer="mahasiswa_name"
                    :invalid="$errors->has('mahasiswa_name')" />
                <flux:input label="NIM" wire:model.defer="mahasiswa_nim" :invalid="$errors->has('mahasiswa_nim')" />

                <div class="md:col-span-2">
                    <flux:select label="Jurusan" wire:model.defer="jurusan_id" :invalid="$errors->has('jurusan_id')">
                        <option value="">— Pilih —</option>
                        @foreach ($jurusans as $j)
                            <option value="{{ $j->id }}">{{ $j->nama_jurusan }}</option>
                        @endforeach
                    </flux:select>
                </div>

                <flux:input type="number" label="Tahun Angkatan" wire:model.defer="mahasiswa_tahun_angkatan"
                    :invalid="$errors->has('mahasiswa_tahun_angkatan')" />
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
