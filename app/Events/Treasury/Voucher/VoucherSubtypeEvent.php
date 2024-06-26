<?php

namespace App\Events\Treasury\Voucher;

use App\Models\Treasury\VoucherSubtype;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoucherSubtypeEvent implements ShouldBroadcast {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $voucherSubtype;
    public $voucherSubtypeId;
    public $type;
    /**
     * Create a new event instance.
     *
     * @param \App\Models\Treasury\VoucherSubtype $voucherSubtype
     * @return void
     */
    public function __construct(VoucherSubtype $voucherSubtype, $voucherSubtypeId, $type) {
        $this->voucherSubtype = $voucherSubtype;
        $this->voucherSubtypeId = $voucherSubtypeId;
        $this->type = $type;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array {
        return [new Channel('subtypes')];
    }

    public function broadcastWith() {
        return [
            'voucherSubtype' => $this->voucherSubtype,
            'voucherSubtypeId' => $this->voucherSubtypeId,
            'type' => $this->type,
        ];
    }
}
