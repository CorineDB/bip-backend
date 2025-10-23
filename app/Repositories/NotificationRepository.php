<?php

namespace App\Repositories;

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

    public function markAsRead(string $notificationId, ?int $userId = null): bool
    {
        $query = $this->model->where('id', $notificationId);

        if ($userId !== null) {
            $query->where('notifiable_id', $userId)
                  ->where('notifiable_type', 'App\Models\User');
        }

        $notification = $query->first();

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

    public function deleteNotification(string $notificationId, ?int $userId = null): bool
    {
        $query = $this->model->where('id', $notificationId);

        if ($userId !== null) {
            $query->where('notifiable_id', $userId)
                  ->where('notifiable_type', 'App\Models\User');
        }

        $notification = $query->first();

        if ($notification) {
            return $notification->delete();
        }
        return false;
    }

    public function getUnreadNotifications(int $userId, int $perPage = 20)
    {
        return $this->model
            ->where('notifiable_id', $userId)
            ->where('notifiable_type', 'App\Models\User')
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getReadNotifications(int $userId, int $perPage = 20)
    {
        return $this->model
            ->where('notifiable_id', $userId)
            ->where('notifiable_type', 'App\Models\User')
            ->whereNotNull('read_at')
            ->orderBy('read_at', 'desc')
            ->paginate($perPage);
    }

    public function getNotificationsByType(int $userId, string $type, int $perPage = 20)
    {
        return $this->model
            ->where('notifiable_id', $userId)
            ->where('notifiable_type', 'App\Models\User')
            ->where('type', $type)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function deleteAllReadNotifications(int $userId): int
    {
        return $this->model
            ->where('notifiable_id', $userId)
            ->where('notifiable_type', 'App\Models\User')
            ->whereNotNull('read_at')
            ->delete();
    }

    public function deleteAllNotifications(int $userId): int
    {
        return $this->model
            ->where('notifiable_id', $userId)
            ->where('notifiable_type', 'App\Models\User')
            ->delete();
    }
}