<?php

namespace App\Events\Treasury\Taxes;

use App\Models\Treasury\Taxes\IncomeTaxWithholding;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IncomeTaxWithholdingEvent implements ShouldBroadcast {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $incomeTaxWithholding;
    public $incomeTaxWithholdingId;
    public $type;
    /**
     * Create a new event instance.
     */
    public function __construct(IncomeTaxWithholding $incomeTaxWithholding, $incomeTaxWithholdingId, $type) {
        $this->incomeTaxWithholding = $incomeTaxWithholding;
        $this->incomeTaxWithholdingId = $incomeTaxWithholdingId;
        $this->type = $type;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array {
        return [new Channel('incomeTaxWithholdings')];
    }

    public function broadcastWith() {
        return [
            'incomeTaxWithholding' => $this->incomeTaxWithholding,
            'incomeTaxWithholdingId' => $this->incomeTaxWithholdingId,
            'type' => $this->type,
        ];
    }
}
