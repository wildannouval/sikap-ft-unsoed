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

    protected function base()
    {
        return AppNotification::query()
            ->forUser(Auth::id())
            ->latest('created_at');
    }

    #[Computed]
    public function unreadCount(): int
    {
        return (clone $this->base())->unread()->count();
    }

    #[Computed]
    public function rows()
    {
        $q = $this->base();
        if ($this->tab === 'unread') {
            $q->unread();
        }
        return $q->paginate($this->perPage);
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updatingTab(): void
    {
        $this->resetPage();
    }

    public function open(int $id): void
    {
        $notif = AppNotification::forUser(Auth::id())->findOrFail($id);

        if (is_null($notif->read_at)) {
            $notif->update(['read_at' => now()]);
        }

        if (!empty($notif->link)) {
            $this->redirect($notif->link, navigate: true);
        }
    }

    public function markRead(int $id): void
    {
        $notif = AppNotification::forUser(Auth::id())->findOrFail($id);

        if (is_null($notif->read_at)) {
            $notif->update(['read_at' => now()]);
        }
    }

    public function markAllRead(): void
    {
        AppNotification::forUser(Auth::id())
            ->unread()
            ->update(['read_at' => now()]);

        $this->resetPage();
    }

    public function deleteOne(int $id): void
    {
        AppNotification::forUser(Auth::id())->whereKey($id)->delete();
        $this->resetPage();
    }

    public function deleteAll(): void
    {
        AppNotification::forUser(Auth::id())->delete();
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.notifications.index');
    }
}
