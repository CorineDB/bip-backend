<?php

namespace App\Http\Controllers;

use App\Services\Contracts\NotificationServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Notifications",
 *     description="Endpoints pour la gestion des notifications utilisateurs"
 * )
 */
class NotificationController extends Controller
{
    public function __construct(
        private NotificationServiceInterface $notificationService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/notifications",
     *     summary="Récupérer les notifications de l'utilisateur connecté",
     *     tags={"Notifications"},
     *     security={{"passport": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des notifications",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="string", example="9d1e9c0a-7b9f-4c8e-9f0a-1b2c3d4e5f6g"),
     *                 @OA\Property(property="type", type="string", example="App\\Notifications\\IdeeProjetCreeNotification"),
     *                 @OA\Property(property="data", type="object"),
     *                 @OA\Property(property="read_at", type="string", format="datetime", nullable=true),
     *                 @OA\Property(property="created_at", type="string", format="datetime"),
     *                 @OA\Property(property="is_read", type="boolean", example=false)
     *             ))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Non authentifié")
     * )
     */
    public function index(): JsonResponse
    {
        return $this->notificationService->getUserNotifications(Auth::id());
    }

    /**
     * @OA\Get(
     *     path="/api/notifications/unread-count",
     *     summary="Compter les notifications non lues",
     *     tags={"Notifications"},
     *     security={{"passport": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Nombre de notifications non lues",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="unread_count", type="integer", example=5)
     *         )
     *     ),
     *     @OA\Response(response=401, description="Non authentifié")
     * )
     */
    public function unreadCount(): JsonResponse
    {
        return $this->notificationService->getUnreadCount(Auth::id());
    }

    /**
     * @OA\Post(
     *     path="/api/notifications/{id}/read",
     *     summary="Marquer une notification comme lue",
     *     tags={"Notifications"},
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la notification",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification marquée comme lue",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Notification marked as read successfully.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Notification non trouvée"),
     *     @OA\Response(response=401, description="Non authentifié")
     * )
     */
    public function markAsRead(string $id): JsonResponse
    {
        return $this->notificationService->markAsRead($id);
    }

    /**
     * @OA\Post(
     *     path="/api/notifications/mark-all-read",
     *     summary="Marquer toutes les notifications comme lues",
     *     tags={"Notifications"},
     *     security={{"passport": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Toutes les notifications marquées comme lues",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="All notifications marked as read successfully.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Non authentifié")
     * )
     */
    public function markAllAsRead(): JsonResponse
    {
        return $this->notificationService->markAllAsRead(Auth::id());
    }

    /**
     * @OA\Delete(
     *     path="/api/notifications/{id}",
     *     summary="Supprimer une notification",
     *     tags={"Notifications"},
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID de la notification",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification supprimée",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Notification deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Notification non trouvée"),
     *     @OA\Response(response=401, description="Non authentifié")
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        return $this->notificationService->deleteNotification($id);
    }
}