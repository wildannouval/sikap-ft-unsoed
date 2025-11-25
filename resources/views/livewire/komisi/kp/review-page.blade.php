<div class="space-y-6">

    {{-- HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" level="1" class="text-stone-900 dark:text-stone-100">
                Review Pengajuan KP (Komisi)
            </flux:heading>
            <flux:subheading class="text-zinc-600 dark:text-zinc-300">
                Tinjau pengajuan mahasiswa, tetapkan Dosen Pembimbing, setujui untuk diteruskan ke Bapendik (SPK),
                atau tolak dengan catatan. Unduh SPK saat sudah terbit.
            </flux:subheading>
        </div>
    </div>
    <flux:separator variant="subtle" />

    {{-- FLASH --}}
    @if (session('ok'))
        <div class="rounded-md border border-emerald-300/60 bg-emerald-50 px-3 py-2 text-emerald-800">
            <div class="font-medium">{{ session('ok') }}</div>
        </div>
    @endif

    @if (session('err'))
        <div class="rounded-md border border-rose-300/60 bg-rose-50 px-3 py-2 text-rose-800">
            <div class="font-medium">{{ session('err') }}</div>
        </div>
    @endif

    {{-- PANDUAN (aksen violet) --}}
    <flux:card
        class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-xs">
        <div class="flex items-start gap-2 px-1.5 -mt-1">
            <span
                class="inline-flex items-center justify-center rounded-md p-1.5 bg-violet-500 text-white dark:bg-violet-400">
                <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M12 16v-4" />
                    <path d="M12 8h.01" />
                </svg>
            </span>
            <div>
                <h3 class="text-base font-semibold text-stone-900 dark:text-stone-100">
                    Panduan Review Pengajuan KP
                </h3>
                <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-300 space-y-1.5">
                    <div>
                        <span class="font-medium">1)</span>
                        Gunakan kolom <em>cari</em> & filter status untuk menyaring data.
                    </div>
                    <div>
                        <span class="font-medium">2)</span>
                        Tetapkan <strong>Dosen Pembimbing</strong> pada pengajuan
                        status <em>Menunggu Review Komisi</em>.
                    </div>
                    <div>
                        <span class="font-medium">3)</span>
                        Klik <strong>Setujui</strong> untuk meneruskan ke Bapendik (SPK),
                        atau <strong>Tolak</strong> dengan catatan.
                    </div>
                    <div>
                        <span class="font-medium">4)</span>
                        Setelah <em>SPK Terbit</em>, Komisi dapat mengunduh dokumen SPK.
                    </div>
                </div>
            </div>
        </div>
    </flux:card>

    {{-- FILTER BAR --}}
    <flux:card class="rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-xs">
        <div
            class="px-4 py-3 border-b bg-violet-50 text-violet-700
                   dark:bg-violet-900/20 dark:text-violet-300
                   border-violet-100 dark:border-violet-900/40
                   rounded-t-xl">
            <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                <h3 class="text-sm font-medium tracking-wide">Review Pengajuan KP</h3>
                <div class="flex flex-col gap-2 md:flex-row md:items-end">
                    <div class="md:w-96">
                        <flux:input placeholder="Cari judul / instansi / nama / NIM / pembimbing…"
                            wire:model.live.debounce.400ms="q" icon="magnifying-glass" />
                    </div>
                    <flux:select wire:model.live="statusFilter" class="md:ml-2">
                        <option value="all">Semua Status</option>
                        <option value="review_komisi">Menunggu Review Komisi</option>
                        <option value="review_bapendik">Menunggu Terbit SPK</option>
                        <option value="spk_terbit">SPK Terbit</option>
                        <option value="ditolak">Ditolak</option>
                    </flux:select>
                </div>
            </div>
        </div>
    </flux:card>

    {{-- GRID: TABEL & RINGKASAN (TERPISAH) --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        {{-- TABEL --}}
        <div class="lg:col-span-8">
            <flux:card
                class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-xs">

                <div
                    class="px-4 py-3 border-b bg-zinc-50 text-zinc-700
                           dark:bg-stone-900/30 dark:text-stone-100
                           border-zinc-100 dark:border-stone-800
                           rounded-t-xl">
                    <h3 class="text-sm font-medium tracking-wide">Daftar Pengajuan KP</h3>
                </div>

                <div class="p-4"
                    wire:key="kp-review-{{ $q }}-{{ $statusFilter }}-{{ $sortBy }}-{{ $sortDirection }}">
                    <flux:table
                        class="[&_thead_th]:bg-zinc-50 [&_thead_th]:dark:bg-stone-900/40
                               [&_thead_th]:text-zinc-600 [&_thead_th]:dark:text-stone-200
                               [&_tbody_tr]:hover:bg-zinc-50/60 [&_tbody_tr]:dark:hover:bg-stone-900/30"
                        :paginate="$this->orders">

                        <flux:table.columns>
                            <flux:table.column class="w-12">#</flux:table.column>

                            <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection"
                                wire:click="sort('created_at')">
                                Tanggal
                            </flux:table.column>

                            <flux:table.column>Mahasiswa</flux:table.column>
                            <flux:table.column>Judul</flux:table.column>
                            <flux:table.column>Instansi</flux:table.column>
                            <flux:table.column>Pembimbing</flux:table.column>

                            <flux:table.column sortable :sorted="$sortBy === 'status'" :direction="$sortDirection"
                                wire:click="sort('status')">
                                Status
                            </flux:table.column>

                            <flux:table.column class="w-32 text-center">Aksi</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @foreach ($this->orders as $i => $row)
                                <flux:table.row :key="$row->id">
                                    <flux:table.cell>{{ $this->orders->firstItem() + $i }}</flux:table.cell>

                                    <flux:table.cell class="whitespace-nowrap">
                                        {{ optional($row->created_at)->format('d M Y') ?: '—' }}
                                    </flux:table.cell>

                                    <flux:table.cell class="whitespace-nowrap">
                                        <span class="text-stone-900 dark:text-stone-100">
                                            {{ $row->mahasiswa?->user?->name }}
                                        </span>
                                        <div class="text-xs text-zinc-500">
                                            {{ $row->mahasiswa?->mahasiswa_nim }}
                                        </div>
                                    </flux:table.cell>

                                    <flux:table.cell class="max-w-[360px]">
                                        <span class="line-clamp-2 text-stone-900 dark:text-stone-100">
                                            {{ $row->judul_kp }}
                                        </span>
                                    </flux:table.cell>

                                    <flux:table.cell class="whitespace-nowrap">
                                        <span class="text-stone-900 dark:text-stone-100">
                                            {{ $row->lokasi_kp }}
                                        </span>
                                    </flux:table.cell>

                                    <flux:table.cell class="whitespace-nowrap">
                                        {{ $row->dosenPembimbing?->dosen_name ?? '—' }}
                                    </flux:table.cell>

                                    <flux:table.cell>
                                        <flux:badge size="sm" inset="top bottom"
                                            :color="$this->badgeColor($row->status)">
                                            {{ $this->statusLabel($row->status) }}
                                        </flux:badge>
                                    </flux:table.cell>

                                    <flux:table.cell>
                                        <flux:dropdown position="bottom" align="end">
                                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                                inset="top bottom" />
                                            <flux:menu class="min-w-48">
                                                {{-- Detail --}}
                                                <flux:modal.trigger name="detail-kp">
                                                    <flux:menu.item icon="eye"
                                                        wire:click="openDetail({{ $row->id }})">
                                                        Detail
                                                    </flux:menu.item>
                                                </flux:modal.trigger>

                                                {{-- Tetapkan Pembimbing --}}
                                                @if ($row->status === 'review_komisi')
                                                    <flux:modal.trigger name="assign-mentor">
                                                        <flux:menu.item icon="user-plus"
                                                            wire:click="openAssign({{ $row->id }})">
                                                            Pilih Pembimbing
                                                        </flux:menu.item>
                                                    </flux:modal.trigger>
                                                @else
                                                    <flux:menu.item icon="user-plus" disabled>
                                                        Pilih Pembimbing
                                                    </flux:menu.item>
                                                @endif

                                                <flux:menu.separator />

                                                @php
                                                    $bolehSetujui =
                                                        $row->status === 'review_komisi' &&
                                                        !is_null($row->dosen_pembimbing_id);
                                                @endphp

                                                @if ($bolehSetujui)
                                                    <flux:modal.trigger name="approve-kp">
                                                        <flux:menu.item icon="check"
                                                            wire:click="triggerApprove({{ $row->id }})">
                                                            Setujui
                                                        </flux:menu.item>
                                                    </flux:modal.trigger>
                                                @else
                                                    <flux:menu.item icon="check" disabled>
                                                        Setujui
                                                    </flux:menu.item>
                                                @endif

                                                @if ($row->status === 'review_komisi')
                                                    <flux:modal.trigger name="reject-kp">
                                                        <flux:menu.item icon="x-mark"
                                                            wire:click="triggerReject({{ $row->id }})">
                                                            Tolak
                                                        </flux:menu.item>
                                                    </flux:modal.trigger>
                                                @else
                                                    <flux:menu.item icon="x-mark" disabled>
                                                        Tolak
                                                    </flux:menu.item>
                                                @endif

                                                {{-- Unduh SPK --}}
                                                @if ($row->status === 'spk_terbit')
                                                    <flux:menu.item icon="arrow-down-tray"
                                                        href="{{ route('komisi.kp.download.docx', $row->id) }}"
                                                        target="_blank">
                                                        Unduh SPK (DOCX)
                                                    </flux:menu.item>
                                                @else
                                                    <flux:menu.item icon="arrow-down-tray" disabled>
                                                        Unduh SPK (DOCX)
                                                    </flux:menu.item>
                                                @endif
                                            </flux:menu>
                                        </flux:dropdown>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>
            </flux:card>
        </div>

        {{-- RINGKASAN STATUS --}}
        <div class="lg:col-span-4">
            <flux:card
                class="space-y-4 rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-xs">
                <div
                    class="px-4 py-3 border-b bg-violet-50 text-violet-700
                           dark:bg-violet-900/20 dark:text-violet-300
                           border-violet-100 dark:border-violet-900/40
                           rounded-t-xl">
                    <h3 class="text-sm font-medium tracking-wide">Ringkasan Status</h3>
                </div>

                <div class="p-4 space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <flux:badge size="sm" inset="top bottom" :color="$this->badgeColor('review_komisi')">
                            Menunggu Review Komisi
                        </flux:badge>
                        <span class="font-semibold text-stone-900 dark:text-stone-100">
                            {{ $this->stats['review_komisi'] }}
                        </span>
                    </div>

                    <div class="flex items-start justify-between gap-3">
                        <flux:badge size="sm" inset="top bottom" :color="$this->badgeColor('review_bapendik')">
                            Menunggu Terbit SPK
                        </flux:badge>
                        <span class="font-semibold text-stone-900 dark:text-stone-100">
                            {{ $this->stats['review_bapendik'] }}
                        </span>
                    </div>

                    <div class="flex items-start justify-between gap-3">
                        <flux:badge size="sm" inset="top bottom" :color="$this->badgeColor('spk_terbit')">
                            SPK Terbit
                        </flux:badge>
                        <span class="font-semibold text-stone-900 dark:text-stone-100">
                            {{ $this->stats['spk_terbit'] }}
                        </span>
                    </div>

                    <div class="flex items-start justify-between gap-3">
                        <flux:badge size="sm" inset="top bottom" :color="$this->badgeColor('ditolak')">
                            Ditolak
                        </flux:badge>
                        <span class="font-semibold text-stone-900 dark:text-stone-100">
                            {{ $this->stats['ditolak'] }}
                        </span>
                    </div>

                    <flux:separator />

                    <div class="space-y-2 text-sm text-zinc-600 dark:text-zinc-300">
                        <div>
                            <span class="font-semibold">Menunggu Review Komisi:</span>
                            Komisi meninjau, tetapkan pembimbing, setujui/tolak.
                        </div>
                        <div>
                            <span class="font-semibold">Menunggu Terbit SPK:</span>
                            Disetujui komisi; menunggu Bapendik menerbitkan SPK.
                        </div>
                        <div>
                            <span class="font-semibold">SPK Terbit:</span>
                            SPK siap diunduh.
                        </div>
                        <div>
                            <span class="font-semibold">Ditolak:</span>
                            Lihat catatan penolakan pada detail.
                        </div>
                    </div>
                </div>
            </flux:card>
        </div>
    </div>

    {{-- ===== DETAIL MODAL ===== --}}
    <flux:modal name="detail-kp" :show="$detailId !== null" dismissable>
        @php
            $item = $this->selectedItem;
        @endphp

        <div class="space-y-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <flux:heading size="lg">Detail Pengajuan KP</flux:heading>
                    <p class="text-sm text-zinc-500">
                        Periksa data & dokumen yang diunggah mahasiswa.
                    </p>
                </div>
                <flux:modal.close>
                    <flux:button variant="ghost" icon="x-mark" wire:click="closeDetail" />
                </flux:modal.close>
            </div>

            @if ($item)
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <flux:card class="space-y-2">
                        <div class="text-sm text-zinc-500">Mahasiswa</div>
                        <div class="font-semibold">
                            {{ $item->mahasiswa?->user?->name }}
                            <div class="text-sm text-zinc-500">
                                {{ $item->mahasiswa?->mahasiswa_nim }}
                            </div>
                        </div>
                    </flux:card>

                    <flux:card class="space-y-2">
                        <div class="text-sm text-zinc-500">Status</div>
                        <flux:badge size="sm" inset="top bottom" :color="$this->badgeColor($item->status)">
                            {{ $this->statusLabel($item->status) }}
                        </flux:badge>
                    </flux:card>

                    <flux:card class="space-y-2">
                        <div class="text-sm text-zinc-500">Pembimbing</div>
                        <div class="font-medium">
                            {{ $item->dosenPembimbing?->dosen_name ?? '—' }}
                        </div>
                    </flux:card>

                    @if ($item->catatan)
                        <flux:card class="space-y-2">
                            <div class="text-sm text-zinc-500">Catatan</div>
                            <div class="text-sm">
                                {{ $item->catatan }}
                            </div>
                        </flux:card>
                    @endif

                    <flux:card class="space-y-2 md:col-span-2">
                        <div class="text-sm text-zinc-500">Judul Kerja Praktik</div>
                        <div class="font-medium">
                            {{ $item->judul_kp }}
                        </div>
                    </flux:card>

                    <flux:card class="space-y-2 md:col-span-2">
                        <div class="text-sm text-zinc-500">Instansi / Lokasi KP</div>
                        <div class="font-medium">
                            {{ $item->lokasi_kp }}
                        </div>
                    </flux:card>

                    <flux:card class="space-y-3 md:col-span-2">
                        <div class="text-sm text-zinc-500">Dokumen</div>
                        <div class="flex flex-col gap-2">
                            {{-- Proposal --}}
                            @if ($item->proposal_path)
                                <a class="text-sm underline hover:no-underline"
                                    href="{{ asset('storage/' . $item->proposal_path) }}" target="_blank">
                                    Lihat Proposal (PDF)
                                </a>
                            @else
                                <span class="text-sm text-zinc-400">
                                    Proposal belum diunggah.
                                </span>
                            @endif

                            {{-- Surat Diterima --}}
                            @if ($item->surat_keterangan_path)
                                <a class="text-sm underline hover:no-underline"
                                    href="{{ asset('storage/' . $item->surat_keterangan_path) }}" target="_blank">
                                    Lihat Surat Diterima (PDF/JPG/PNG)
                                </a>
                            @else
                                <span class="text-sm text-zinc-400">
                                    Surat diterima belum diunggah.
                                </span>
                            @endif
                        </div>
                    </flux:card>
                </div>
            @else
                <div class="text-sm text-rose-600">
                    Data tidak ditemukan.
                </div>
            @endif

            <div class="flex justify-end">
                <flux:modal.close>
                    <flux:button variant="primary" wire:click="closeDetail">
                        Tutup
                    </flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>

    {{-- ===== MODAL PILIH PEMBIMBING ===== --}}
    <flux:modal name="assign-mentor" class="min-w-[28rem]" dismissable>
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Tetapkan Dosen Pembimbing</flux:heading>
                <flux:subheading class="mt-1">
                    Pilih dosen pembimbing untuk pengajuan ini.
                </flux:subheading>
            </div>

            <div>
                <flux:select label="Dosen Pembimbing" wire:model="dosen_id">
                    <option value="">— Pilih Dosen —</option>
                    @foreach ($this->dosenOptions as $opt)
                        <option value="{{ $opt['id'] }}">{{ $opt['nama'] }}</option>
                    @endforeach
                </flux:select>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" icon="check" wire:click="saveAssign" wire:loading.attr="disabled">
                    Simpan
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- ===== MODAL SETUJUI ===== --}}
    <flux:modal name="approve-kp" class="min-w-[22rem]" dismissable>
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Setujui pengajuan?</flux:heading>
                <flux:text class="mt-2">
                    Pengajuan akan diteruskan ke Bapendik untuk penerbitan SPK.
                    Pastikan pembimbing sudah ditetapkan.
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button type="button" variant="primary" wire:click="confirmApprove">
                    Setujui
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- ===== MODAL TOLAK ===== --}}
    <flux:modal name="reject-kp" class="min-w-[26rem]" dismissable>
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Tolak pengajuan?</flux:heading>
                <flux:text class="mt-2">
                    Masukkan catatan penolakan untuk mahasiswa.
                </flux:text>
            </div>

            <div>
                <flux:textarea label="Catatan penolakan" wire:model.defer="rejectNote"
                    placeholder="Sebutkan kekurangan / alasan penolakan" rows="4"
                    :invalid="$errors->has('rejectNote')" />
                @error('rejectNote')
                    <div class="mt-1 text-sm text-rose-600">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Batal</flux:button>
                </flux:modal.close>
                <flux:button type="button" variant="danger" wire:click="confirmReject">
                    Tolak
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
