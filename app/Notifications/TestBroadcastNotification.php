<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class TestBroadcastNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $message;

    public function __construct($message = 'Test de notification en temps réel')
    {
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['broadcast', 'database'];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'message' => $this->message,
            'timestamp' => now()->toDateTimeString(),
            'type' => 'test',
        ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'message' => $this->message,
            'timestamp' => now()->toDateTimeString(),
            'type' => 'test',
        ];
    }
}
