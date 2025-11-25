<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

    <head>
        @include('partials.head')
    </head>

    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            {{-- Toggle (mobile) --}}
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            {{-- Brand SIKAP (pakai logo komponen kamu + teks SIKAP) --}}
            <a href="{{ route('dashboard') }}" class="me-5 flex items-center gap-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo class="h-8 w-auto" />
                {{-- <span class="text-sm font-semibold tracking-wide text-zinc-800 dark:text-zinc-100">
                    SIKAP
                </span> --}}
            </a>

            {{-- === Hitung badge Notifikasi (tanpa "use", pakai FQCN) --}}
            @php
                $notifBadge = null;

                try {
                    if (
                        \Illuminate\Support\Facades\Auth::check() &&
                        \Illuminate\Support\Facades\Schema::hasTable('notifications_custom')
                    ) {
                        $count = \App\Models\AppNotification::query()
                            ->where('user_id', \Illuminate\Support\Facades\Auth::id())
                            ->whereNull('read_at')
                            ->count();

                        $notifBadge = $count > 0 ? (string) $count : null;
                    }
                } catch (\Throwable $e) {
                    // Jangan gagalkan render sidebar kalau ada error
                    $notifBadge = null;
                }
            @endphp

            <flux:navlist variant="outline">
                {{-- ===================== MAHASISWA ===================== --}}
                @role('Mahasiswa')
                    @php
                        $activeKp = \App\Models\KerjaPraktik::query()
                            ->withCount([
                                'consultations as verified_consultations_count' => function ($q) {
                                    $q->whereNotNull('verified_at');
                                },
                            ])
                            ->whereHas('mahasiswa', fn($q) => $q->where('user_id', auth()->id()))
                            ->whereIn('status', [
                                \App\Models\KerjaPraktik::ST_SPK_TERBIT,
                                \App\Models\KerjaPraktik::ST_KP_BERJALAN,
                            ])
                            ->latest('updated_at')
                            ->first();

                        $activeKpId = $activeKp?->id;
                        $canSeminar = $activeKp && $activeKp->verified_consultations_count >= 6;
                    @endphp

                    <flux:navlist.group heading="Mahasiswa" class="grid">
                        <flux:navlist.item icon="home" :href="route('mhs.dashboard')"
                            :current="request()->routeIs('mhs.dashboard')" wire:navigate>Dashboard</flux:navlist.item>

                        @can('sp.view')
                            <flux:navlist.item icon="document-check" :href="route('mhs.sp.index')"
                                :current="request()->routeIs('mhs.sp.index')" wire:navigate>Surat Pengantar</flux:navlist.item>
                        @endcan

                        @can('kp.create')
                            <flux:navlist.item icon="document-text" :href="route('mhs.kp.index')"
                                :current="request()->routeIs('mhs.kp.index')" wire:navigate>Pengajuan KP</flux:navlist.item>
                        @endcan

                        {{-- Konsultasi KP --}}
                        @if ($activeKpId)
                            <flux:navlist.item icon="chat-bubble-left-right" :href="route('mhs.kp.konsultasi', $activeKpId)"
                                :current="request()->routeIs('mhs.kp.konsultasi')" wire:navigate>Konsultasi KP
                            </flux:navlist.item>
                        @else
                            <flux:navlist.item icon="chat-bubble-left-right" disabled>
                                Konsultasi KP
                            </flux:navlist.item>
                        @endif

                        {{-- Daftar Seminar (aktif jika >=6 konsultasi terverifikasi) --}}
                        @if ($activeKpId && $canSeminar && Route::has('mhs.kp.seminar'))
                            <flux:navlist.item icon="calendar-days" :href="route('mhs.kp.seminar', $activeKpId)"
                                :current="request()->routeIs('mhs.kp.seminar')" wire:navigate>Daftar Seminar KP
                            </flux:navlist.item>
                        @else
                            <flux:navlist.item icon="calendar-days" disabled>
                                Daftar Seminar KP
                            </flux:navlist.item>
                        @endif

                        {{-- Nilai KP (tampil setelah upload distribusi) --}}
                        @if (Route::has('mhs.nilai'))
                            <flux:navlist.item icon="chart-bar" :href="route('mhs.nilai')"
                                :current="request()->routeIs('mhs.nilai')" wire:navigate>Nilai KP</flux:navlist.item>
                        @endif

                        {{-- Notifikasi (Inbox-style badge) --}}
                        @if (Route::has('notifications'))
                            <flux:navlist.item icon="inbox" :href="route('notifications')"
                                :current="request()->routeIs('notifications')" badge="{{ $notifBadge }}"
                                badge:color="blue" wire:navigate>Notifikasi</flux:navlist.item>
                        @else
                            <flux:navlist.item icon="inbox" :badge="$notifBadge" badge:color="blue" disabled>
                                Notifikasi
                            </flux:navlist.item>
                        @endif

                    </flux:navlist.group>
                @endrole

                {{-- ===================== BAPENDIK ===================== --}}
                @role('Bapendik')
                    <flux:navlist.group heading="Bapendik" class="grid">
                        <flux:navlist.item icon="home" :href="route('bap.dashboard')"
                            :current="request()->routeIs('bap.dashboard')" wire:navigate>Dashboard</flux:navlist.item>

                        @can('sp.validate')
                            <flux:navlist.item icon="document-check" :href="route('bap.sp.validasi')"
                                :current="request()->routeIs('bap.sp.validasi')" wire:navigate>Validasi Surat Pengantar
                            </flux:navlist.item>
                        @endcan

                        @can('signatory.manage')
                            <flux:navlist.item icon="identification" :href="route('bap.penandatangan.index')"
                                :current="request()->routeIs('bap.penandatangan.index')" wire:navigate>Penandatangan
                            </flux:navlist.item>
                        @endcan

                        @can('kp.approve')
                            <flux:navlist.item icon="check-badge" :href="route('bap.kp.spk')"
                                :current="request()->routeIs('bap.kp.spk')" wire:navigate>Penerbitan SPK</flux:navlist.item>
                        @endcan

                        {{-- Jadwal & BA Seminar --}}
                        @if (Route::has('bap.kp.seminar.jadwal'))
                            <flux:navlist.item icon="calendar" :href="route('bap.kp.seminar.jadwal')"
                                :current="request()->routeIs('bap.kp.seminar.jadwal')" wire:navigate>Jadwal & BA Seminar
                            </flux:navlist.item>
                        @else
                            <flux:navlist.item icon="calendar" disabled>Jadwal & BA Seminar</flux:navlist.item>
                        @endif

                        {{-- Nilai & Arsip BA --}}
                        @if (Route::has('bap.kp.nilai'))
                            <flux:navlist.item icon="archive-box" :href="route('bap.kp.nilai')"
                                :current="request()->routeIs('bap.kp.nilai')" wire:navigate>Nilai & Arsip BA
                            </flux:navlist.item>
                        @else
                            <flux:navlist.item icon="archive-box" disabled>Nilai & Arsip BA</flux:navlist.item>
                        @endif

                        {{-- Master Data (collapsible) --}}
                        @can('masterdata.manage')
                            <flux:navlist.group heading="Master Data" expandable class="grid">
                                @if (Route::has('bap.master.dosen'))
                                    <flux:navlist.item icon="users" :href="route('bap.master.dosen')"
                                        :current="request()->routeIs('bap.master.dosen')" wire:navigate>Data Dosen
                                    </flux:navlist.item>
                                @else
                                    <flux:navlist.item icon="users" disabled>Data Dosen</flux:navlist.item>
                                @endif

                                @if (Route::has('bap.master.mahasiswa'))
                                    <flux:navlist.item icon="academic-cap" :href="route('bap.master.mahasiswa')"
                                        :current="request()->routeIs('bap.master.mahasiswa')" wire:navigate>Data Mahasiswa
                                    </flux:navlist.item>
                                @else
                                    <flux:navlist.item icon="academic-cap" disabled>Data Mahasiswa</flux:navlist.item>
                                @endif

                                @if (Route::has('bap.master.ruangan'))
                                    <flux:navlist.item icon="building-office-2" :href="route('bap.master.ruangan')"
                                        :current="request()->routeIs('bap.master.ruangan')" wire:navigate>Data Ruangan
                                    </flux:navlist.item>
                                @else
                                    <flux:navlist.item icon="building-office-2" disabled>Data Ruangan</flux:navlist.item>
                                @endif
                            </flux:navlist.group>
                        @endcan

                        {{-- Notifikasi (Inbox-style badge) --}}
                        @if (Route::has('notifications'))
                            <flux:navlist.item icon="inbox" :href="route('notifications')"
                                :current="request()->routeIs('notifications')" badge="{{ $notifBadge }}"
                                badge:color="blue" wire:navigate>Notifikasi</flux:navlist.item>
                        @else
                            <flux:navlist.item icon="inbox" :badge="$notifBadge" badge:color="blue" disabled>
                                Notifikasi
                            </flux:navlist.item>
                        @endif

                    </flux:navlist.group>
                @endrole

                {{-- ===================== DOSEN PEMBIMBING ===================== --}}
                @hasanyrole('Dosen Pembimbing|Dosen Komisi') {{-- ✅ Komisi juga melihat menu pembimbing --}}
                    <flux:navlist.group heading="Dosen Pembimbing" class="grid">
                        @if (Route::has('dsp.dashboard'))
                            <flux:navlist.item icon="home" :href="route('dsp.dashboard')"
                                :current="request()->routeIs('dsp.dashboard')" wire:navigate>Dashboard</flux:navlist.item>
                        @else
                            <flux:navlist.item icon="home" disabled>Dashboard</flux:navlist.item>
                        @endif

                        @if (Route::has('dsp.mhs'))
                            <flux:navlist.item icon="academic-cap" :href="route('dsp.mhs')"
                                :current="request()->routeIs('dsp.mhs')" wire:navigate>Mahasiswa Bimbingan
                            </flux:navlist.item>
                        @else
                            <flux:navlist.item icon="academic-cap" disabled>Mahasiswa Bimbingan</flux:navlist.item>
                        @endif

                        @if (Route::has('dsp.kp.konsultasi'))
                            <flux:navlist.item icon="chat-bubble-left-right" :href="route('dsp.kp.konsultasi')"
                                :current="request()->routeIs('dsp.kp.konsultasi')" wire:navigate>Konsultasi Mahasiswa
                            </flux:navlist.item>
                        @else
                            <flux:navlist.item icon="chat-bubble-left-right" disabled>Konsultasi Mahasiswa
                            </flux:navlist.item>
                        @endif

                        @if (Route::has('dsp.kp.seminar.approval'))
                            <flux:navlist.item icon="check-circle" :href="route('dsp.kp.seminar.approval')"
                                :current="request()->routeIs('dsp.kp.seminar.approval')" wire:navigate>Persetujuan Seminar
                            </flux:navlist.item>
                        @else
                            <flux:navlist.item icon="check-circle" disabled>Persetujuan Seminar</flux:navlist.item>
                        @endif

                        @if (Route::has('dsp.nilai'))
                            <flux:navlist.item icon="pencil-square" :href="route('dsp.nilai')"
                                :current="request()->routeIs('dsp.nilai')" wire:navigate>Penilaian KP</flux:navlist.item>
                        @endif

                        @if (Route::has('dsp.laporan'))
                            <flux:navlist.item icon="archive-box" :href="route('dsp.laporan')"
                                :current="request()->routeIs('dsp.laporan')" wire:navigate>Laporan & Arsip
                            </flux:navlist.item>
                        @endif

                        @if (Route::has('notifications'))
                            <flux:navlist.item icon="inbox" :href="route('notifications')"
                                :current="request()->routeIs('notifications')" badge="{{ $notifBadge }}"
                                badge:color="blue" wire:navigate>Notifikasi</flux:navlist.item>
                        @else
                            <flux:navlist.item icon="inbox" :badge="$notifBadge" badge:color="blue" disabled>Notifikasi
                            </flux:navlist.item>
                        @endif
                    </flux:navlist.group>
                @endhasanyrole


                {{-- ===================== DOSEN KOMISI ===================== --}}
                @role('Dosen Komisi')
                    <flux:navlist.group heading="Komisi" class="grid">
                        @if (Route::has('komisi.dashboard'))
                            <flux:navlist.item icon="home" :href="route('komisi.dashboard')"
                                :current="request()->routeIs('komisi.dashboard')" wire:navigate>Dashboard
                            </flux:navlist.item>
                        @else
                            <flux:navlist.item icon="home" disabled>Dashboard</flux:navlist.item>
                        @endif

                        @can('kp.review')
                            <flux:navlist.item icon="document-check" :href="route('komisi.kp.review')"
                                :current="request()->routeIs('komisi.kp.review')" wire:navigate>Review Pengajuan KP
                            </flux:navlist.item>
                        @endcan

                        {{-- Status KP Dinilai --}}
                        @if (Route::has('komisi.kp.nilai'))
                            <flux:navlist.item icon="chart-bar-square" :href="route('komisi.kp.nilai')"
                                :current="request()->routeIs('komisi.kp.nilai')" wire:navigate>Status KP Dinilai
                            </flux:navlist.item>
                        @else
                            <flux:navlist.item icon="chart-bar-square" disabled>Status KP Dinilai</flux:navlist.item>
                        @endif

                        {{-- Notifikasi (Inbox-style badge) --}}
                        @if (Route::has('notifications'))
                            <flux:navlist.item icon="inbox" :href="route('notifications')"
                                :current="request()->routeIs('notifications')" badge="{{ $notifBadge }}"
                                badge:color="blue" wire:navigate>Notifikasi</flux:navlist.item>
                        @else
                            <flux:navlist.item icon="inbox" :badge="$notifBadge" badge:color="blue" disabled>
                                Notifikasi
                            </flux:navlist.item>
                        @endif

                    </flux:navlist.group>
                @endrole
            </flux:navlist>

            <flux:spacer />

            {{-- ====== Link bawaan (disembunyikan: diminta di-comment) --}}
            {{--
            <flux:navlist variant="outline">
                <flux:navlist.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                    {{ __('Repository') }}
                </flux:navlist.item>
                <flux:navlist.item icon="book-open" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                    {{ __('Documentation') }}
                </flux:navlist.item>
            </flux:navlist>
            --}}

            <!-- Desktop User Menu -->
            <flux:dropdown class="hidden lg:block" position="bottom" align="start">
                {{-- hanya tambah :avatar, lainnya tetap --}}
                <flux:profile :name="auth()->user()->name" :initials="auth()->user()->initials()"
                    :avatar="auth()->user()->profilePhotoUrl()" icon:trailing="chevrons-up-down" />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        @php
                            $user = auth()->user();
                        @endphp
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    @if ($user && $user->profilePhotoUrl())
                                        <img src="{{ $user->profilePhotoUrl() }}" alt="{{ $user->name }}"
                                            class="h-8 w-8 rounded-lg object-cover">
                                    @else
                                        <span
                                            class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                            {{ $user?->initials() }}
                                        </span>
                                    @endif
                                </span>
                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ $user?->name }}</span>
                                    <span class="truncate text-xs">{{ $user?->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />
                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>
                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                            class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
            <flux:spacer />
            <flux:dropdown position="top" align="end">
                {{-- hanya tambah :avatar, lainnya tetap --}}
                <flux:profile :initials="auth()->user()->initials()" :avatar="auth()->user()->profilePhotoUrl()"
                    icon-trailing="chevron-down" />
                <flux:menu>
                    <flux:menu.radio.group>
                        @php
                            $user = auth()->user();
                        @endphp
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    @if ($user && $user->profilePhotoUrl())
                                        <img src="{{ $user->profilePhotoUrl() }}" alt="{{ $user->name }}"
                                            class="h-8 w-8 rounded-lg object-cover">
                                    @else
                                        <span
                                            class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                            {{ $user?->initials() }}
                                        </span>
                                    @endif
                                </span>
                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ $user?->name }}</span>
                                    <span class="truncate text-xs">{{ $user?->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />
                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>
                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                            class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>

</html>
