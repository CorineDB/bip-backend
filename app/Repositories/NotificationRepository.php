<?php

namespace App\Repositories;

use App\Models\Notification;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;
use Illuminate\Notifications\DatabaseNotification;

class NotificationRepository extends BaseRepository implements NotificationRepositoryInterface
{
    public function __construct(DatabaseNotification $model)
    {
        parent::__construct($model);
    }

    public function getUserNotifications(int $userId, int $perPage = 20)
    {
        return $this->model
            ->where('notifiable_id', $userId)
            ->where('notifiable_type', 'App\Models\User')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getUnreadCount(int $userId): int
    {
        return $this->model
            ->where('notifiable_id', $userId)
            ->where('notifiable_type', 'App\Models\User')
            ->whereNull('read_at')
            ->count();
    }

    public function markAsRead(string $notificationId): bool
    {
        $notification = $this->model->find($notificationId);
        if ($notification && is_null($notification->read_at)) {
            $notification->markAsRead();
            return true;
        }
        return false;
    }

    public function markAllAsRead(int $userId): int
    {
        return $this->model
            ->where('notifiable_id', $userId)
            ->where('notifiable_type', 'App\Models\User')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function deleteNotification(string $notificationId): bool
    {
        $notification = $this->model->find($notificationId);
        if ($notification) {
            return $notification->delete();
        }
        return false;
    }
}