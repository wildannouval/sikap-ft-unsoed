<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class Notifier
{
    /**
     * Kirim notifikasi ke 1 user.
     */
    public static function toUser(User|int $user, string $title, ?string $body = null, ?string $link = null, array $data = []): AppNotification
    {
        $userId = $user instanceof User ? $user->id : $user;

        // create() akan menerapkan $casts => kolom JSON 'data' aman
        return AppNotification::create([
            'user_id' => $userId,
            'title'   => $title,
            'body'    => $body,
            'link'    => $link,
            'data'    => $data ?: null,
        ]);
    }

    /**
     * Kirim notifikasi ke semua user dalam role tertentu (nama persis Spatie).
     * NOTE: pakai create() per-user biar casting JSON jalan, menghindari "Array to string conversion".
     * Return: jumlah notifikasi yang berhasil dibuat.
     */
    public static function toRole(string $roleName, string $title, ?string $body = null, ?string $link = null, array $data = []): int
    {
        $role = Role::where('name', $roleName)->first();
        if (!$role) {
            return 0;
        }

        // Ambil semua user id untuk role tsb
        $userIds = DB::table('model_has_roles')
            ->where('role_id', $role->id)
            ->where('model_type', User::class)
            ->pluck('model_id')
            ->unique()
            ->values();

        if ($userIds->isEmpty()) {
            return 0;
        }

        $count = 0;

        // Transaksi optional: kalau ingin atomic
        DB::transaction(function () use ($userIds, $title, $body, $link, $data, &$count) {
            foreach ($userIds as $uid) {
                AppNotification::create([
                    'user_id' => (int) $uid,
                    'title'   => $title,
                    'body'    => $body,
                    'link'    => $link,
                    'data'    => $data ?: null,  // casting JSON oleh Eloquent
                ]);
                $count++;
            }
        });

        return $count;
    }

    /**
     * Tandai satu notif sudah dibaca.
     */
    public static function markRead(AppNotification|int $notif): void
    {
        $model = $notif instanceof AppNotification ? $notif : AppNotification::find($notif);
        if ($model && !$model->read_at) {
            $model->update(['read_at' => now()]);
        }
    }

    /**
     * Tandai semua notif user sudah dibaca.
     * Return: jumlah baris yang ter-update.
     */
    public static function markAllRead(User|int $user): int
    {
        $userId = $user instanceof User ? $user->id : $user;

        return AppNotification::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
