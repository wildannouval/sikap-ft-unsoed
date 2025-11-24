<div>
    <flux:toast />

    <div class="space-y-6">
        {{-- HEADER --}}
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1" class="text-stone-900 dark:text-stone-100">
                    Validasi Surat Pengantar
                </flux:heading>
                <flux:subheading class="text-zinc-600 dark:text-zinc-300">
                    Kelola pengajuan masuk dan riwayat yang sudah diterbitkan.
                </flux:subheading>
            </div>
            <flux:badge size="sm"
                class="bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-300 border border-sky-200 dark:border-sky-800">
                BAPENDIK • SIKAP
            </flux:badge>
        </div>

        {{-- PANDUAN (aksen biru/sky untuk Bapendik) --}}
        <flux:card
            class="space-y-4 rounded-xl border
               bg-white dark:bg-stone-950
               border-zinc-200 dark:border-stone-800
               shadow-xs">
            <div class="flex items-start gap-2 px-1.5 -mt-1">
                <span
                    class="inline-flex items-center justify-center rounded-md p-1.5
                         bg-sky-500 text-white dark:bg-sky-400">
                    <svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="16" rx="2" />
                        <path d="M7 8h10M7 12h8M7 16h6" />
                    </svg>
                </span>
                <div>
                    <h3 class="text-base font-semibold text-stone-900 dark:text-stone-100">
                        Panduan Validasi Surat Pengantar
                    </h3>
                    <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-300 space-y-1.5">
                        <div>
                            <span class="font-medium">1)</span>
                            Gunakan tab <strong>Belum Diterbitkan</strong> untuk memproses pengajuan baru.
                        </div>
                        <div>
                            <span class="font-medium">2)</span>
                            Klik <strong>Terbitkan</strong> untuk memberi nomor & penandatangan; atau
                            <strong>Tolak</strong> dengan catatan.
                        </div>
                        <div>
                            <span class="font-medium">3)</span>
                            Riwayat <strong>Sudah Diterbitkan</strong> dapat diunduh (DOCX) atau diubah nomor bila
                            perlu.
                        </div>
                        <div>
                            <span class="font-medium">4)</span>
                            Pencarian dapat dilakukan berdasarkan perusahaan, penerima, nomor, nama/NIM.
                        </div>
                    </div>
                </div>
            </div>
        </flux:card>

        {{-- TAB GROUP --}}
        <flux:tab.group wire:model.live="tab">
            <flux:tabs>
                <flux:tab name="pending" icon="inbox-arrow-down">
                    Belum Diterbitkan
                    <flux:badge size="sm" inset="top bottom" class="ml-2">{{ $this->pendingCount }}</flux:badge>
                </flux:tab>

                <flux:tab name="published" icon="check-badge">
                    Sudah Diterbitkan
                    <flux:badge size="sm" inset="top bottom" class="ml-2">
                        {{ $this->publishedCount }}
                    </flux:badge>
                </flux:tab>
            </flux:tabs>

            {{-- ====== PENDING ====== --}}
            <flux:tab.panel name="pending" class="pt-4">
                <flux:card
                    class="rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-xs">

                    {{-- Header beraksen sky --}}
                    <div
                        class="px-4 py-3 border-b
                           bg-sky-50 text-sky-700
                           dark:bg-sky-900/20 dark:text-sky-300
                           border-sky-100 dark:border-sky-900/40
                           rounded-t-xl">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="text-sm font-medium tracking-wide">Daftar Pengajuan</h3>
                            <div class="flex items-center gap-2">
                                <flux:input icon="magnifying-glass" class="md:w-96"
                                    placeholder="Cari perusahaan, penerima, nomor, nama/NIM…"
                                    wire:model.live.debounce.300ms="search" />
                                @if ($search !== '')
                                    <flux:button size="sm" variant="ghost" icon="x-mark"
                                        wire:click="$set('search','')">
                                        Bersihkan
                                    </flux:button>
                                @endif
                                <flux:select wire:model.live="perPage" class="w-32">
                                    <flux:select.option :value="5">5 / halaman</flux:select.option>
                                    <flux:select.option :value="10">10 / halaman</flux:select.option>
                                    <flux:select.option :value="25">25 / halaman</flux:select.option>
                                </flux:select>
                            </div>
                        </div>
                    </div>

                    <div class="p-4">
                        <flux:table
                            class="[&_thead_th]:bg-zinc-50 [&_thead_th]:dark:bg-stone-900/40
                               [&_thead_th]:text-zinc-600 [&_thead_th]:dark:text-stone-200
                               [&_tbody_tr]:hover:bg-zinc-50/60 [&_tbody_tr]:dark:hover:bg-stone-900/30"
                            :paginate="$this->ordersPending">

                            <flux:table.columns>
                                <flux:table.column class="w-12">#</flux:table.column>
                                <flux:table.column sortable :sorted="$sortBy === 'tanggal_pengajuan_surat_pengantar'"
                                    :direction="$sortDirection" wire:click="sort('tanggal_pengajuan_surat_pengantar')">
                                    Tanggal
                                </flux:table.column>
                                <flux:table.column>Mahasiswa</flux:table.column>
                                <flux:table.column>Perusahaan</flux:table.column>
                                <flux:table.column>Penerima</flux:table.column>
                                <flux:table.column>Status</flux:table.column>
                                <flux:table.column class="w-16 text-right">Aksi</flux:table.column>
                            </flux:table.columns>

                            <flux:table.rows>
                                @foreach ($this->ordersPending as $idx => $row)
                                    <flux:table.row :key="'p-'.$row->id">
                                        <flux:table.cell>
                                            {{ $this->ordersPending->firstItem() + $idx }}
                                        </flux:table.cell>

                                        <flux:table.cell class="whitespace-nowrap">
                                            {{ optional($row->tanggal_pengajuan_surat_pengantar)->format('d M Y') ?: '—' }}
                                        </flux:table.cell>

                                        <flux:table.cell class="whitespace-nowrap">
                                            {{ $row->mahasiswa?->mahasiswa_name ?: '—' }}
                                            <div class="text-xs text-zinc-500">
                                                NIM: {{ $row->mahasiswa?->mahasiswa_nim ?: '—' }}
                                            </div>
                                        </flux:table.cell>

                                        <flux:table.cell class="whitespace-nowrap">
                                            {{ $row->lokasi_surat_pengantar }}
                                        </flux:table.cell>

                                        <flux:table.cell class="whitespace-nowrap">
                                            {{ $row->penerima_surat_pengantar }}
                                        </flux:table.cell>

                                        <flux:table.cell>
                                            <flux:badge size="sm" inset="top bottom"
                                                :color="$this->badgeColor($row->status_surat_pengantar)">
                                                {{ $row->status_surat_pengantar }}
                                            </flux:badge>
                                        </flux:table.cell>

                                        <flux:table.cell class="text-right">
                                            <flux:dropdown position="bottom" align="end">
                                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                                    inset="top bottom"></flux:button>
                                                <flux:menu class="min-w-52">
                                                    <flux:modal.trigger name="sp-publish">
                                                        <flux:menu.item icon="check"
                                                            wire:click="openPublish({{ $row->id }})">
                                                            Terbitkan
                                                        </flux:menu.item>
                                                    </flux:modal.trigger>
                                                    <flux:modal.trigger name="sp-reject">
                                                        <flux:menu.item icon="x-mark"
                                                            wire:click="openReject({{ $row->id }})">
                                                            Tolak
                                                        </flux:menu.item>
                                                    </flux:modal.trigger>
                                                </flux:menu>
                                            </flux:dropdown>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    </div>
                </flux:card>
            </flux:tab.panel>

            {{-- ====== PUBLISHED ====== --}}
            <flux:tab.panel name="published" class="pt-4">
                <flux:card
                    class="rounded-xl border bg-white dark:bg-stone-950 border-zinc-200 dark:border-stone-800 shadow-xs">

                    {{-- Header beraksen sky --}}
                    <div
                        class="px-4 py-3 border-b
                           bg-sky-50 text-sky-700
                           dark:bg-sky-900/20 dark:text-sky-300
                           border-sky-100 dark:border-sky-900/40
                           rounded-t-xl">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="text-sm font-medium tracking-wide">Riwayat Terbit</h3>
                            <div class="flex items-center gap-2">
                                <flux:input icon="magnifying-glass" class="md:w-96"
                                    placeholder="Cari perusahaan, penerima, nomor, nama/NIM…"
                                    wire:model.live.debounce.300ms="search" />
                                @if ($search !== '')
                                    <flux:button size="sm" variant="ghost" icon="x-mark"
                                        wire:click="$set('search','')">
                                        Bersihkan
                                    </flux:button>
                                @endif
                                <flux:select wire:model.live="perPage" class="w-32">
                                    <flux:select.option :value="5">5 / halaman</flux:select.option>
                                    <flux:select.option :value="10">10 / halaman</flux:select.option>
                                    <flux:select.option :value="25">25 / halaman</flux:select.option>
                                </flux:select>
                            </div>
                        </div>
                    </div>

                    <div class="p-4">
                        <flux:table
                            class="[&_thead_th]:bg-zinc-50 [&_thead_th]:dark:bg-stone-900/40
                               [&_thead_th]:text-zinc-600 [&_thead_th]:dark:text-stone-200
                               [&_tbody_tr]:hover:bg-zinc-50/60 [&_tbody_tr]:dark:hover:bg-stone-900/30"
                            :paginate="$this->ordersPublished">

                            <flux:table.columns>
                                <flux:table.column class="w-12">#</flux:table.column>
                                <flux:table.column sortable :sorted="$sortBy === 'tanggal_disetujui_surat_pengantar'"
                                    :direction="$sortDirection"
                                    wire:click="sort('tanggal_disetujui_surat_pengantar')">
                                    Tgl Terbit
                                </flux:table.column>
                                <flux:table.column>Mahasiswa</flux:table.column>
                                <flux:table.column>Nomor Surat</flux:table.column>
                                <flux:table.column>Perusahaan</flux:table.column>
                                <flux:table.column>Status</flux:table.column>
                                <flux:table.column class="w-20 text-right">Aksi</flux:table.column>
                            </flux:table.columns>

                            <flux:table.rows>
                                @foreach ($this->ordersPublished as $idx => $row)
                                    <flux:table.row :key="'pb-'.$row->id">
                                        <flux:table.cell>
                                            {{ $this->ordersPublished->firstItem() + $idx }}
                                        </flux:table.cell>

                                        <flux:table.cell class="whitespace-nowrap">
                                            {{ optional($row->tanggal_disetujui_surat_pengantar)->format('d M Y') ?: '—' }}
                                        </flux:table.cell>

                                        <flux:table.cell class="whitespace-nowrap">
                                            {{ $row->mahasiswa?->mahasiswa_name ?: '—' }}
                                            <div class="text-xs text-zinc-500">
                                                NIM: {{ $row->mahasiswa?->mahasiswa_nim ?: '—' }}
                                            </div>
                                        </flux:table.cell>

                                        <flux:table.cell class="whitespace-nowrap">
                                            {{ $row->nomor_surat ?: '—' }}
                                        </flux:table.cell>

                                        <flux:table.cell class="whitespace-nowrap">
                                            {{ $row->lokasi_surat_pengantar }}
                                        </flux:table.cell>

                                        <flux:table.cell>
                                            <flux:badge size="sm" inset="top bottom"
                                                :color="$this->badgeColor($row->status_surat_pengantar)">
                                                {{ $row->status_surat_pengantar }}
                                            </flux:badge>
                                        </flux:table.cell>

                                        <flux:table.cell class="text-right">
                                            <flux:dropdown position="bottom" align="end">
                                                <flux:button variant="ghost" size="sm"
                                                    icon="ellipsis-horizontal" inset="top bottom"></flux:button>
                                                <flux:menu class="min-w-52">
                                                    <flux:menu.item icon="arrow-down-tray"
                                                        href="{{ route('bap.sp.download.docx', $row) }}"
                                                        target="_blank">
                                                        Unduh DOCX
                                                    </flux:menu.item>

                                                    <flux:modal.trigger name="sp-publish">
                                                        <flux:menu.item icon="pencil-square"
                                                            wire:click="openPublish({{ $row->id }})">
                                                            Ubah Nomor Surat
                                                        </flux:menu.item>
                                                    </flux:modal.trigger>
                                                </flux:menu>
                                            </flux:dropdown>
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    </div>
                </flux:card>
            </flux:tab.panel>
        </flux:tab.group>

        {{-- ===== MODAL TOLAK ===== --}}
        <flux:modal name="sp-reject" class="min-w-[28rem]">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Tolak Pengajuan?</flux:heading>
                    <flux:subheading class="mt-1">Berikan alasan penolakan di bawah.</flux:subheading>
                </div>

                <flux:textarea label="Catatan penolakan" wire:model.defer="catatan_tolak"
                    placeholder="Alasan penolakan..." rows="4" />

                <div class="flex gap-2">
                    <flux:spacer />
                    <flux:modal.close>
                        <flux:button variant="ghost">Batal</flux:button>
                    </flux:modal.close>
                    <flux:button variant="danger" wire:click="submitReject" wire:loading.attr="disabled"
                        wire:target="submitReject">
                        Tolak
                    </flux:button>
                </div>
            </div>
        </flux:modal>

        {{-- ===== MODAL TERBITKAN / UBAH NOMOR ===== --}}
        <flux:modal name="sp-publish" class="min-w-[32rem]">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Terbitkan / Ubah Nomor Surat</flux:heading>
                    <flux:subheading class="mt-1">
                        Masukkan nomor surat dan pastikan penandatangan benar.
                    </flux:subheading>
                </div>

                <div class="grid gap-4">
                    <flux:input label="Nomor Surat" wire:model.defer="publish_nomor_surat"
                        placeholder="cth: 003/UNSOED/FT/KP/10/2025" :invalid="$errors->has('publish_nomor_surat')" />
                    @error('publish_nomor_surat')
                        <p class="text-sm text-red-600 -mt-2">{{ $message }}</p>
                    @enderror

                    <flux:select label="Penandatangan" wire:model="signatory_id">
                        @foreach (\App\Models\Signatory::query()->orderBy('position')->get() as $opt)
                            <flux:select.option :value="$opt->id">
                                {{ $opt->position }} — {{ $opt->name }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    @error('signatory_id')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-2">
                    <flux:spacer />
                    <flux:modal.close>
                        <flux:button variant="ghost">Batal</flux:button>
                    </flux:modal.close>
                    <flux:button variant="primary" icon="check" wire:click="publishConfirm"
                        wire:loading.attr="disabled" wire:target="publishConfirm">
                        Simpan & Terbitkan
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    </div>
</div>
