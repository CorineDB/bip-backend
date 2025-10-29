<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TestPublicEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $message;

    public function __construct(string $message = 'Test public broadcast')
    {
        $this->message = $message;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('public-test-channel'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'test.public.event';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => $this->message,
            'timestamp' => now()->toDateTimeString(),
        ];
    }
}
