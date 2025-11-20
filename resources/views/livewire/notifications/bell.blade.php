@php
    use Illuminate\Support\Facades\Route as RouteFacade;

    $user = auth()->user();

    // Default fallback: route umum
    $routeName = 'notifications';

    if ($user?->hasRole('Mahasiswa') && RouteFacade::has('mhs.notifikasi')) {
        $routeName = 'mhs.notifikasi';
    } elseif ($user?->hasRole('Bapendik') && RouteFacade::has('bap.notifikasi')) {
        $routeName = 'bap.notifikasi';
    } elseif ($user?->hasRole('Dosen Pembimbing') && RouteFacade::has('dsp.notifikasi')) {
        $routeName = 'dsp.notifikasi';
    } elseif ($user?->hasRole('Dosen Komisi') && RouteFacade::has('komisi.notifikasi')) {
        $routeName = 'komisi.notifikasi';
    }

    $routeUrl = RouteFacade::has($routeName)
        ? route($routeName)
        : (RouteFacade::has('notifications')
            ? route('notifications')
            : '#');

    // Untuk state "current", kita anggap semua nama rute notifikasi valid
    $isCurrent = request()->routeIs('*.notifikasi') || request()->routeIs('notifications');
@endphp

<div wire:poll.30s="refreshCount">
    <flux:navlist.item icon="bell" href="{{ $routeUrl }}" :current="$isCurrent" wire:navigate>
        <div class="flex w-full items-center justify-between">
            <span>Notifikasi</span>
            @if ($unread > 0)
                <span
                    class="ms-2 inline-flex min-w-[20px] items-center justify-center rounded-full bg-red-600 px-1.5 text-[11px] font-semibold text-white">
                    {{ $unread > 99 ? '99+' : $unread }}
                </span>
            @endif
        </div>
    </flux:navlist.item>
</div>
