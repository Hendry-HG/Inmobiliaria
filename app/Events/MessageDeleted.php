<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message_id;
    public $conversation_id;
    public $receiver_id;

    public function __construct($message_id, $conversation_id, $receiver_id)
    {
        $this->message_id = $message_id;
        $this->conversation_id = $conversation_id;
        $this->receiver_id = $receiver_id;
    }

    public function broadcastOn()
    {
        return new Channel('chat.user.' . $this->receiver_id);
    }

    public function broadcastAs()
    {
        return 'MessageDeleted';
    }
}
