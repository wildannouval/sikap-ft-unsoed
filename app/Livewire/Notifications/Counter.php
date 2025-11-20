<?php

namespace App\Livewire\Notifications;

use App\Models\AppNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Counter extends Component
{
    #[Computed]
    public function unread(): int
    {
        if (!Auth::check()) return 0;
        return AppNotification::where('user_id', Auth::id())->whereNull('read_at')->count();
    }

    public function render()
    {
        return view('livewire.notifications.counter');
    }
}
