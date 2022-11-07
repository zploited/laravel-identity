<?php

namespace Zploited\Identity\Client\Laravel\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Zploited\Identity\Client\Models\AccessToken;

class TokenValidationFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $error;
    public AccessToken $token;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(AccessToken $token, string $error)
    {
        $this->token = $token;
        $this->error = $error;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
