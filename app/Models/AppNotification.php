<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class AppNotification extends Model
{
    protected $table = 'notifications_custom';

    protected $fillable = [
        'user_id',
        'title',
        'body',
        'link',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data'    => 'array',
        'read_at' => 'datetime',
    ];

    public function scopeUnread(Builder $q): Builder
    {
        return $q->whereNull('read_at');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
