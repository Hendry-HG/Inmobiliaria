<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $conversation_id;
    public $receiver_id;

    public function __construct(Message $message, $conversation_id, $receiver_id)
    {
        $this->message = $message->load('user');
        $this->conversation_id = $conversation_id;
        $this->receiver_id = $receiver_id;
    }

    public function broadcastOn()
    {
        // Canal privado para el usuario específico que recibe el mensaje
        return new Channel('chat.user.' . $this->receiver_id);
    }

    public function broadcastAs()
    {
        return 'NewMessage';
    }

    public function broadcastWith()
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'content' => $this->message->content,
                'user_id' => $this->message->user_id,
                'user_name' => $this->message->user->name,
                'user_avatar' => $this->message->user->profile_photo_url,
                'formatted_time' => $this->message->created_at->format('H:i'),
                'is_read' => $this->message->is_read,
            ],
            'conversation_id' => $this->conversation_id,
        ];
    }
}
