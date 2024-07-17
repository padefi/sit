<?php

namespace App\Events\Treasury\Taxes;

use App\Models\Treasury\Taxes\IncomeTaxWithholdingScale;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IncomeTaxWithholdingScaleEvent implements ShouldBroadcast {
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public $incomeTaxWithholdingScale;
    public $incomeTaxWithholdingScaleId;
    public $type;
    /**
     * Create a new event instance.
     */
    public function __construct(IncomeTaxWithholdingScale $incomeTaxWithholdingScale, $incomeTaxWithholdingScaleId, $type) {
        $this->incomeTaxWithholdingScale = $incomeTaxWithholdingScale;
        $this->incomeTaxWithholdingScaleId = $incomeTaxWithholdingScaleId;
        $this->type = $type;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array {
        return [new Channel('incomeTaxWithholdingsScales')];
    }

    public function broadcastWith() {
        return [
            'incomeTaxWithholdingScale' => $this->incomeTaxWithholdingScale,
            'incomeTaxWithholdingScaleId' => $this->incomeTaxWithholdingScaleId,
            'type' => $this->type,
        ];
    }
}
