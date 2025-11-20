<?php

namespace App\Livewire\Notifications;

use App\Models\AppNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class Bell extends Component
{
    public int $unread = 0;

    public function mount(): void
    {
        $this->refreshCount();
    }

    #[On('notification-created')] // kalau kamu dispatch event Livewire ini, counter auto refresh
    public function refreshCount(): void
    {
        $uid = Auth::id();
        $this->unread = $uid ? AppNotification::where('user_id', $uid)->unread()->count() : 0;
    }

    public function markAllRead(): void
    {
        if ($uid = Auth::id()) {
            AppNotification::where('user_id', $uid)->whereNull('read_at')->update(['read_at' => now()]);
            $this->refreshCount();
        }
    }

    public function render()
    {
        return view('livewire.notifications.bell');
    }
}
