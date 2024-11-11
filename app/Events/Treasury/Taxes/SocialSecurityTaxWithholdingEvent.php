<?php

namespace App\Events\Treasury\Taxes;

use App\Models\Treasury\Taxes\SocialSecurityTaxWithholding;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SocialSecurityTaxWithholdingEvent implements ShouldBroadcast {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $socialSecurityTaxWithholding;
    public $socialSecurityTaxWithholdingId;
    public $type;

    /**
     * Create a new event instance.
     */
    public function __construct(SocialSecurityTaxWithholding $socialSecurityTaxWithholding, $socialSecurityTaxWithholdingId, $type) {
        $this->socialSecurityTaxWithholding = $socialSecurityTaxWithholding;
        $this->socialSecurityTaxWithholdingId = $socialSecurityTaxWithholdingId;
        $this->type = $type;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array {
        return [new Channel('socialSecurityTaxWithholdings')];
    }

    public function broadcastWith() {
        return [
            'socialSecurityTaxWithholding' => $this->socialSecurityTaxWithholding,
            'socialSecurityTaxWithholdingId' => $this->socialSecurityTaxWithholdingId,
            'type' => $this->type,
        ];
    }
}
