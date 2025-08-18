<?php

namespace App\Models;

use Illuminate\Notifications\DatabaseNotification;

class Notification extends DatabaseNotification
{
    protected $table = 'notifications';

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'is_read'
    ];

    public function getIsReadAttribute(): bool
    {
        return !is_null($this->read_at);
    }

    public function user()
    {
        return $this->morphTo('notifiable');
    }
}