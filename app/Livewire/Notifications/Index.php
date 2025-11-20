<?php

namespace App\Livewire\Notifications;

use App\Models\AppNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url] public string $tab = 'unread'; // unread | all
    #[Url] public int $perPage = 10;
    public ?int $openId = null;

    protected function base()
    {
        return AppNotification::query()
            ->where('user_id', Auth::id())
            ->latest('created_at');
    }

    #[Computed]
    public function unreadCount(): int
    {
        return (clone $this->base())->whereNull('read_at')->count();
    }

    #[Computed]
    public function rows()
    {
        $q = $this->base();
        if ($this->tab === 'unread') {
            $q->whereNull('read_at');
        }
        return $q->paginate($this->perPage);
    }

    public function open(int $id): void
    {
        $notif = AppNotification::where('user_id', Auth::id())->findOrFail($id);
        if (!$notif->read_at) {
            $notif->update(['read_at' => now()]);
        }
        $this->openId = $id;

        // Jika ada link, langsung navigate (opsional)
        if ($notif->link) {
            $this->redirect($notif->link, navigate: true);
        }
    }

    public function markRead(int $id): void
    {
        $notif = AppNotification::where('user_id', Auth::id())->findOrFail($id);
        if (!$notif->read_at) {
            $notif->update(['read_at' => now()]);
        }
    }

    public function markAllRead(): void
    {
        AppNotification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        $this->resetPage();
    }

    public function deleteOne(int $id): void
    {
        AppNotification::where('user_id', Auth::id())->whereKey($id)->delete();
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.notifications.index');
    }
}
