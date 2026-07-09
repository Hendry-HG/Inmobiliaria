<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserPresenceChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function broadcastOn()
    {
        return [new Channel('presence.' . $this->user->id)];
    }

    public function broadcastWith()
    {
        return [
            'user_id' => $this->user->id,
            'is_online' => $this->user->is_online,
            'last_seen_at' => $this->user->last_seen_at?->diffForHumans()
        ];
    }

    public function broadcastAs()
    {
        return 'presence.changed';
    }
}
