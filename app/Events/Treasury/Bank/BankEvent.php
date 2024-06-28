<?php

namespace App\Events\Treasury\Bank;

use App\Models\Treasury\Bank\Bank;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BankEvent implements ShouldBroadcast {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $bank;
    public $bankId;
    public $type;
    /**
     * Create a new event instance.
     *
     * @param \App\Models\Treasury\Bank\Bank $bank
     * @return void
     */
    public function __construct(Bank $bank, $bankId, $type) {
        $this->bank = $bank;
        $this->bankId = $bankId;
        $this->type = $type;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array {
        return [new Channel('banks')];
    }

    public function broadcastWith() {
        return [
            'bank' => $this->bank,
            'bankId' => $this->bankId,
            'type' => $this->type,
        ];
    }
}
