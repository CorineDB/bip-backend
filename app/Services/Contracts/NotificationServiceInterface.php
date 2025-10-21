<?php

namespace App\Services\Contracts;

use Illuminate\Http\JsonResponse;

interface NotificationServiceInterface extends AbstractServiceInterface
{
    public function getUserNotifications(int $userId): JsonResponse;
    public function markAsRead(string $notificationId): JsonResponse;
    public function markAllAsRead(int $userId): JsonResponse;
    public function getUnreadCount(int $userId): JsonResponse;
    public function sendNotification(
        $notifiable,
        $notification,
        array $channels = ['database']
    ): JsonResponse;
    public function deleteNotification(string $notificationId): JsonResponse;
    public function getUnreadNotifications(int $userId): JsonResponse;
    public function getReadNotifications(int $userId): JsonResponse;
    public function getNotificationsByType(int $userId, string $type): JsonResponse;
    public function deleteAllReadNotifications(int $userId): JsonResponse;
    public function deleteAllNotifications(int $userId): JsonResponse;
}