<?php

namespace App\Services;

use App\Models\User;
use App\Services\BaseService;
use App\Services\Contracts\NotificationServiceInterface;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Notification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class NotificationService extends BaseService implements NotificationServiceInterface
{
    public function __construct(NotificationRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return NotificationResource::class;
    }

    public function getUserNotifications(int $userId): JsonResponse
    {
        try {
            $notifications = $this->repository->getUserNotifications($userId);
            return NotificationResource::collection($notifications)->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function markAsRead(string $notificationId): JsonResponse
    {
        try {
            $success = $this->repository->markAsRead($notificationId);
            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found or already read.',
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read successfully.',
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function markAllAsRead(int $userId): JsonResponse
    {
        try {
            $count = $this->repository->markAllAsRead($userId);
            
            return response()->json([
                'success' => true,
                'message' => "All notifications marked as read successfully.",
                'marked_count' => $count,
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function getUnreadCount(int $userId): JsonResponse
    {
        try {
            $count = $this->repository->getUnreadCount($userId);
            
            return response()->json([
                'success' => true,
                'unread_count' => $count,
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function sendNotification(
        $notifiable,
        $notification,
        array $channels = ['database']
    ): JsonResponse {
        try {
            if (is_int($notifiable)) {
                $notifiable = User::findOrFail($notifiable);
            }

            Notification::send($notifiable, $notification);
            
            return response()->json([
                'success' => true,
                'message' => 'Notification sent successfully.',
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function deleteNotification(string $notificationId): JsonResponse
    {
        try {
            $success = $this->repository->deleteNotification($notificationId);
            if (!$success) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification not found.',
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Notification deleted successfully.',
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function create(array $data): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Direct notification creation not allowed. Use sendNotification method.',
        ], 405);
    }

    public function update(int|string $id, array $data): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Notification updates not allowed.',
        ], 405);
    }

    public function delete(int|string $id): JsonResponse
    {
        return $this->deleteNotification($id);
    }
}