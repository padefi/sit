<?php

namespace App\Events\Treasury\Supplier;

use App\Models\Treasury\Supplier\Supplier;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupplierEvent implements ShouldBroadcast {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $supplier;
    public $supplierId;
    public $pendingToPay;
    public $type;
    /**
     * Create a new event instance.
     *
     * @param \App\Models\Treasury\Supplier\Supplier $supplier
     * @return void
     */
    public function __construct(Supplier $supplier, $supplierId, $pendingToPay, $type) {
        $this->supplier = $supplier;
        $this->supplierId = $supplierId;
        $this->pendingToPay = $pendingToPay;
        $this->type = $type;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array {
        return [new Channel('suppliers')];
    }

    public function broadcastWith() {
        return [
            'supplier' => $this->supplier,
            'supplierId' => $this->supplierId,
            'pendingToPay' => $this->pendingToPay,
            'type' => $this->type,
        ];
    }
}
