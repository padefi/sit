<?php

namespace App\Events\Treasury\Voucher;

use App\Http\Resources\Treasury\Voucher\VoucherResource;
use App\Models\Treasury\Voucher\Voucher;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoucherEvent implements ShouldBroadcast {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $voucher;
    public $voucherId;
    public $type;

    /**
     * Create a new event instance.
     * 
     * @param \App\Models\Treasury\Voucher\Voucher $voucher
     * @return void
     */
    public function __construct(Voucher $voucher, $voucherId, $type) {
        $this->voucher = $voucher;
        $this->voucherId = $voucherId;
        $this->type = $type;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array {
        return [new Channel('vouchers')];
    }

    public function broadcastWith() {
        return [
            'voucher' => new VoucherResource($this->voucher),
            'voucherId' => $this->voucherId,
            'type' => $this->type,
        ];
    }
}
