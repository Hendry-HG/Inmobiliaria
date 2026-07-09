<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.typing.{conversationId}', function ($user, $conversationId) {
    // Permitir que cualquier usuario autenticado escuche
    return true;
});
