@if ($this->unread > 0)
    <span
        class="ml-2 inline-flex min-w-[1.25rem] justify-center rounded-full bg-emerald-600 px-1 text-[10px] font-semibold text-white">
        {{ $this->unread }}
    </span>
@endif
