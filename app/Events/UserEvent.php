<?php

namespace App\Events;

use App\Models\Users\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserEvent implements ShouldBroadcast {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $userId;
    public $type;
    /**
     * Create a new event instance.
     *
     * @param \App\Models\Users\User $user
     * @return void
     */
    public function __construct(User $user, $userId, $type) {
        $this->user = $user;
        $this->userId = $userId;
        $this->type = $type;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn() {
        return [new Channel('users')];
    }

    public function broadcastWith() {
        return [
            'user' => $this->user,
            'userId' => $this->userId,
            'type' => $this->type,
        ];
    }
}
