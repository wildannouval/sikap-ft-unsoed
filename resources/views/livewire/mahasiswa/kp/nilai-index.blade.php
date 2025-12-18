<div class="max-w-3xl mx-auto space-y-6">
    <div class="text-center">
        <flux:heading size="xl" level="1">Nilai & Administrasi</flux:heading>
        <flux:subheading class="mt-2">Lengkapi dokumen akhir untuk melihat nilai Kerja Praktik.</flux:subheading>
    </div>

    @if (!$this->seminar)
        <flux:card class="flex flex-col items-center justify-center py-12 text-center border-dashed">
            <flux:icon.document-text class="size-8 text-zinc-400" />
            <h3 class="mt-4 font-semibold text-stone-900">Belum Ada Nilai</h3>
            <p class="text-sm text-zinc-500">Nilai akan muncul setelah dosen melakukan penilaian seminar.</p>
        </flux:card>
    @else
        @php $seminar = $this->seminar; @endphp

        <flux:card class="overflow-hidden border bg-white dark:bg-stone-950 shadow-sm p-0">
            {{-- Header Card --}}
            <div class="p-6 border-b border-zinc-100 dark:border-stone-800 bg-zinc-50/50 dark:bg-zinc-900/50">
                <div class="flex flex-col md:flex-row justify-between gap-4">
                    <div>
                        <flux:badge size="sm" :color="$this->badgeColor($seminar->status)">
                            {{ $this->statusLabel($seminar->status) }}
                        </flux:badge>
                        <h2 class="text-lg font-bold mt-2 leading-tight">{{ $seminar->judul_laporan }}</h2>
                    </div>

                    {{-- NILAI (Muncul Hanya Jika Selesai) --}}
                    <div
                        class="flex flex-col items-center justify-center min-w-[120px] p-4 bg-white dark:bg-stone-900 rounded-xl border">
                        <span class="text-[10px] font-bold text-zinc-500 uppercase tracking-widest">Nilai Akhir</span>
                        @if ($seminar->status === 'selesai' && $seminar->grade)
                            <div class="text-5xl font-black text-indigo-600 dark:text-indigo-400">
                                {{ $seminar->grade->final_letter }}
                            </div>
                            <div class="text-[10px] text-zinc-500">
                                Skor: {{ number_format($seminar->grade->final_score, 2) }}
                            </div>
                        @else
                            <div class="flex flex-col items-center mt-1 text-zinc-300">
                                <flux:icon.lock-closed class="size-6 mb-1" />
                                <span class="text-[9px] text-center leading-tight">
                                    Lengkapi dokumen<br>untuk buka nilai
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Alur Step-by-Step --}}
            <div class="p-6 space-y-8">
                {{-- Step 1 --}}
                <div class="flex gap-4">
                    <div class="flex flex-col items-center">
                        <div
                            class="w-8 h-8 rounded-full flex items-center justify-center {{ $seminar->grade ? 'bg-indigo-100 text-indigo-600' : 'bg-zinc-100 text-zinc-400' }}">
                            <flux:icon.pencil-square class="size-4" />
                        </div>
                        <div class="w-0.5 h-full bg-zinc-100 dark:bg-zinc-800 my-1"></div>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-bold">1. Penilaian Dosen</h4>
                        <p class="text-xs text-zinc-500">
                            {{ $seminar->grade ? 'Dosen pembimbing telah memberikan nilai.' : 'Menunggu dosen menginput nilai.' }}
                        </p>
                    </div>
                </div>

                {{-- Step 2 --}}
                <div class="flex gap-4">
                    <div class="flex flex-col items-center">
                        <div
                            class="w-8 h-8 rounded-full flex items-center justify-center
                            {{ $seminar->status === 'selesai'
                                ? 'bg-emerald-100 text-emerald-600'
                                : ($seminar->grade
                                    ? 'bg-amber-100 text-amber-600 animate-pulse'
                                    : 'bg-zinc-100 text-zinc-400') }}">
                            <flux:icon.arrow-up-tray class="size-4" />
                        </div>
                        <div class="w-0.5 h-full bg-zinc-100 dark:bg-zinc-800 my-1"></div>
                    </div>

                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-bold">2. Unggah Dokumen Akhir</h4>

                            @if ($seminar->status !== 'selesai' && $seminar->grade)
                                <flux:button size="sm" variant="primary" wire:click="openUpload">
                                    Upload Sekarang
                                </flux:button>
                            @endif
                        </div>

                        @if ($seminar->status === 'selesai')
                            <div class="mt-4 grid grid-cols-2 gap-3">
                                @if ($seminar->laporan_final_path)
                                    <a href="{{ asset('storage/' . $seminar->laporan_final_path) }}" target="_blank"
                                        class="p-3 rounded-lg border bg-zinc-50 dark:bg-zinc-900 flex items-center gap-3 group">
                                        <flux:icon.document-text class="size-5 text-red-500" />
                                        <span class="text-xs font-medium group-hover:underline">Laporan Final.pdf</span>
                                    </a>
                                @endif

                                @if ($seminar->distribusi_proof_path)
                                    <a href="{{ asset('storage/' . $seminar->distribusi_proof_path) }}" target="_blank"
                                        class="p-3 rounded-lg border bg-zinc-50 dark:bg-zinc-900 flex items-center gap-3 group">
                                        <flux:icon.photo class="size-5 text-blue-500" />
                                        <span class="text-xs font-medium group-hover:underline">Bukti Distribusi</span>
                                    </a>
                                @endif
                            </div>
                        @else
                            <p class="text-xs text-zinc-500 mt-1">Unggah Laporan Final dan Bukti Distribusi.</p>
                        @endif
                    </div>
                </div>

                {{-- Step 3 --}}
                <div class="flex gap-4">
                    <div
                        class="w-8 h-8 rounded-full flex items-center justify-center {{ $seminar->status === 'selesai' ? 'bg-emerald-500 text-white' : 'bg-zinc-100 text-zinc-400' }}">
                        <flux:icon.check-badge class="size-5" />
                    </div>
                    <div>
                        <h4 class="text-sm font-bold">3. Selesai</h4>
                        <p class="text-xs text-zinc-500">Proses Kerja Praktik selesai.</p>
                    </div>
                </div>
            </div>
        </flux:card>
    @endif

    {{-- MODAL UPLOAD --}}
    <flux:modal name="mhs-upload-distribusi" class="md:w-[35rem]">
        <form wire:submit.prevent="saveUpload" class="space-y-6">
            <div>
                <flux:heading size="lg">Dokumen Penyelesaian</flux:heading>
                <p class="text-sm text-zinc-500">Unggah berkas untuk menerbitkan nilai akhir.</p>
            </div>

            <div class="space-y-4">
                <div class="space-y-1">
                    <flux:input type="file" label="Laporan Final KP (PDF, Maks 20MB)" wire:model="fileLaporanFinal"
                        accept=".pdf" />
                    @error('fileLaporanFinal')
                        <div class="text-xs text-rose-600">{{ $message }}</div>
                    @enderror
                </div>

                <div class="space-y-1">
                    <flux:input type="file" label="Bukti Distribusi / Tanda Terima (PDF/JPG/PNG)"
                        wire:model="fileDistribusi" accept=".pdf,.jpg,.jpeg,.png" />
                    @error('fileDistribusi')
                        <div class="text-xs text-rose-600">{{ $message }}</div>
                    @enderror
                </div>

                <div wire:loading wire:target="fileDistribusi, fileLaporanFinal"
                    class="text-xs text-indigo-600 animate-pulse font-medium text-center">
                    Sedang memproses dokumen...
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <flux:button variant="ghost" wire:click="closeUpload" type="button">Batal</flux:button>
                    <flux:button type="submit" variant="primary" icon="check">Simpan & Selesaikan</flux:button>
                </div>
            </div>
        </form>
    </flux:modal>
</div>
