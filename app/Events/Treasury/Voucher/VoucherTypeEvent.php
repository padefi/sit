<?php

namespace App\Events\Treasury\Voucher;

use App\Http\Resources\Treasury\Voucher\VoucherTypeResource;
use App\Models\Treasury\Voucher\VoucherType;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoucherTypeEvent implements ShouldBroadcast {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $voucherType;
    public $voucherTypeId;
    public $type;
    /**
     * Create a new event instance.
     *
     * @param \App\Models\Treasury\Voucher\VoucherType $voucherType
     * @return void
     */
    public function __construct(VoucherType $voucherType, $voucherTypeId, $type) {
        $this->voucherType = $voucherType;
        $this->voucherTypeId = $voucherTypeId;
        $this->type = $type;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array {
        return [new Channel('types')];
    }

    public function broadcastWith() {
        return [
            'voucherType' => new VoucherTypeResource($this->voucherType),
            'voucherTypeId' => $this->voucherTypeId,
            'type' => $this->type, 
        ];
    }
}
