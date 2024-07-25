<?php

namespace App\Events\Treasury\Taxes;

use App\Models\Treasury\Taxes\VatTaxWithholding;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VatTaxWithholdingEvent implements ShouldBroadcast {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $vatTaxWithholding;
    public $vatTaxWithholdingId;
    public $type;

    /**
     * Create a new event instance.
     */
    public function __construct(VatTaxWithholding $vatTaxWithholding, $vatTaxWithholdingId, $type) {
        $this->vatTaxWithholding = $vatTaxWithholding;
        $this->vatTaxWithholdingId = $vatTaxWithholdingId;
        $this->type = $type;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array {
        return [new Channel('vatTaxWithholdings')];
    }

    public function broadcastWith() {
        return [
            'vatTaxWithholding' => $this->vatTaxWithholding,
            'vatTaxWithholdingId' => $this->vatTaxWithholdingId,
            'type' => $this->type,
        ];
    }
}
