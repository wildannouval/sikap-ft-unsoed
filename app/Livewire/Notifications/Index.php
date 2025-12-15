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
    public function allCount(): int
    {
        return (clone $this->base())->count();
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

    // Helper Badge (Opsional, untuk konsistensi di view)
    public function getBadgeConfig(string $type): array
    {
        return match ($type) {
            'sp_submitted', 'kp_submitted' => ['color' => 'sky', 'icon' => 'paper-airplane', 'label' => 'Pengajuan Baru'],
            'sp_published', 'spk_published' => ['color' => 'emerald', 'icon' => 'check-circle', 'label' => 'Diterbitkan'],
            'sp_rejected', 'kp_rejected' => ['color' => 'rose', 'icon' => 'x-circle', 'label' => 'Ditolak'],
            'kp_seminar_scheduled' => ['color' => 'indigo', 'icon' => 'calendar', 'label' => 'Jadwal Seminar'],
            default => ['color' => 'zinc', 'icon' => 'bell', 'label' => 'Info'],
        };
    }

    public function render()
    {
        return view('livewire.notifications.index');
    }
}
