<?php

namespace App\Repositories\Contracts;

interface NotificationRepositoryInterface extends BaseRepositoryInterface
{
    public function getUserNotifications(int $userId, int $perPage = 20);
    public function getUnreadCount(int $userId): int;
    public function markAsRead(string $notificationId): bool;
    public function markAllAsRead(int $userId): int;
    public function deleteNotification(string $notificationId): bool;
}