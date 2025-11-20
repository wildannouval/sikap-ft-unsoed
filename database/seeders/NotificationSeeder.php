<?php

namespace Database\Seeders;

use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $u = User::first();
        if (!$u) return;

        AppNotification::factory()->create([
            'user_id' => $u->id,
            'title'   => 'Contoh Notifikasi',
            'body'    => 'Ini hanya contoh notifikasi untuk pengujian.',
            'link'    => url('/dashboard'),
        ]);
    }
}
