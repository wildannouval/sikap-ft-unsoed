<div class="space-y-6">
    {{-- HEADER + AKSI --}}
    <flux:card class="space-y-4">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <div class="md:w-80">
                <flux:input placeholder="Cari nama / NIM…" wire:model.debounce.500ms="q" icon="magnifying-glass" />
            </div>

            <div class="flex items-center gap-2">
                <flux:select wire:model.live="perPage">
                    <option value="10">10 / halaman</option>
                    <option value="25">25 / halaman</option>
                    <option value="50">50 / halaman</option>
                </flux:select>

                <flux:button icon="plus" variant="primary" wire:click="create">Tambah Mahasiswa</flux:button>
            </div>
        </div>

        @if (session('ok'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-700">
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

                        <flux:table.cell class="whitespace-nowrap">
                            {{ $r->mahasiswa_nim }}
                        </flux:table.cell>

                        <flux:table.cell class="max-w-[220px]">
                            <span class="line-clamp-2">{{ $r->jurusan->nama_jurusan ?? '—' }}</span>
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap">
                            {{ $r->mahasiswa_tahun_angkatan ?? '—' }}
                        </flux:table.cell>

                        <flux:table.cell class="max-w-[260px]">
                            <span class="line-clamp-1">{{ $r->user->email ?? '—' }}</span>
                        </flux:table.cell>

                        <flux:table.cell class="text-right">
                            <flux:dropdown position="bottom" align="end">
                                <flux:button size="sm" variant="ghost" icon="ellipsis-horizontal"
                                    inset="top bottom" />
                                <flux:menu class="min-w-44">
                                    <flux:menu.item icon="pencil-square" wire:click="edit({{ $r->mahasiswa_id }})">
                                        Edit
                                    </flux:menu.item>
                                    <flux:menu.item icon="key"
                                        wire:click="resetUserPassword({{ $r->mahasiswa_id }})">
                                        Reset Password
                                    </flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item icon="trash" variant="danger"
                                        wire:click="delete({{ $r->mahasiswa_id }})">
                                        Hapus
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    </flux:card>

    {{-- MODAL: Alpine + Livewire entangle (tanpa flux:modal) --}}
    <div x-data="{ open: @entangle('showForm').live }" x-show="open" x-cloak
        class="fixed inset-0 z-[60] flex items-center justify-center p-4" aria-modal="true" role="dialog">

        {{-- Overlay --}}
        <div class="fixed inset-0 bg-black/40" x-show="open" x-transition.opacity></div>

        {{-- Panel --}}
        <div class="relative z-[61] w-full max-w-2xl rounded-2xl border border-zinc-200 bg-white p-6 shadow-2xl dark:border-zinc-700 dark:bg-zinc-900"
            x-show="open" x-transition.scale.origin.center>

            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold">
                        {{ $editingId ? 'Edit Mahasiswa' : 'Tambah Mahasiswa' }}
                    </h2>
                    <p class="mt-1 text-sm text-zinc-500">Lengkapi data mahasiswa & akun login.</p>
                </div>
                <button
                    class="rounded-lg p-1 text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-800"
                    @click="open=false">✕</button>
            </div>

            <div class="mt-5 space-y-6">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm">Nama</label>
                        <input type="text" wire:model.defer="mahasiswa_name"
                            class="w-full rounded-md border px-3 py-2 dark:bg-zinc-800 dark:border-zinc-700">
                        @error('mahasiswa_name')
                            <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm">NIM</label>
                        <input type="text" wire:model.defer="mahasiswa_nim"
                            class="w-full rounded-md border px-3 py-2 dark:bg-zinc-800 dark:border-zinc-700">
                        @error('mahasiswa_nim')
                            <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm">Jurusan</label>
                        <select wire:model.defer="jurusan_id"
                            class="w-full rounded-md border px-3 py-2 dark:bg-zinc-800 dark:border-zinc-700">
                            <option value="">— Pilih —</option>
                            @foreach ($jurusans as $j)
                                <option value="{{ $j->id }}">{{ $j->nama_jurusan }}</option>
                            @endforeach
                        </select>
                        @error('jurusan_id')
                            <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm">Tahun Angkatan</label>
                        <input type="number" wire:model.defer="mahasiswa_tahun_angkatan"
                            class="w-full rounded-md border px-3 py-2 dark:bg-zinc-800 dark:border-zinc-700">
                        @error('mahasiswa_tahun_angkatan')
                            <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm">Email Login</label>
                        <input type="email" wire:model.defer="email"
                            class="w-full rounded-md border px-3 py-2 dark:bg-zinc-800 dark:border-zinc-700">
                        @error('email')
                            <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm">
                            {{ $editingId ? 'Password (opsional untuk ubah)' : 'Password' }}
                        </label>
                        <input type="text" wire:model.defer="password"
                            class="w-full rounded-md border px-3 py-2 dark:bg-zinc-800 dark:border-zinc-700">
                        @error('password')
                            <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <div class="flex-1"></div>
                    <button
                        class="rounded-md px-4 py-2 text-zinc-700 hover:bg-zinc-100 dark:text-zinc-200 dark:hover:bg-zinc-800"
                        @click="open=false">Batal</button>
                    <button class="rounded-md bg-blue-600 px-4 py-2 font-medium text-white hover:bg-blue-700"
                        wire:click="save">Simpan</button>
                </div>
            </div>
        </div>
    </div>
</div>
