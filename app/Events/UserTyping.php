<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserTyping implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user_id;
    public $user_name;
    public $conversation_id;
    public $is_typing;
    public $receiver_id;

    public function __construct(User $user, $conversation_id, $is_typing, $receiver_id)
    {
        $this->user_id = $user->id;
        $this->user_name = $user->name;
        $this->conversation_id = $conversation_id;
        $this->is_typing = $is_typing;
        $this->receiver_id = $receiver_id;
    }

    public function broadcastOn()
    {
        return new Channel('chat.user.' . $this->receiver_id);
    }

    public function broadcastAs()
    {
        return 'UserTyping';
    }
}