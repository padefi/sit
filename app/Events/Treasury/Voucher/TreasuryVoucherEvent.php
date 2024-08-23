<?php

namespace App\Events\Treasury\Voucher;

use App\Http\Resources\Treasury\Voucher\TreasuryVoucherResource;
use App\Models\Treasury\Voucher\TreasuryVoucher;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TreasuryVoucherEvent implements ShouldBroadcast {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $treasuryVoucher;
    public $treasuryVoucherId;
    public $type;

    /**
     * Create a new event instance.
     * 
     * @param \App\Models\Treasury\Voucher\TreasuryVoucher $treasuryVoucher
     * @return void
     */
    public function __construct(TreasuryVoucher $treasuryVoucher, $treasuryVoucherId, $type) {
        $this->treasuryVoucher = $treasuryVoucher;
        $this->treasuryVoucherId = $treasuryVoucherId;
        $this->type = $type;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array {
        return [new Channel('treasuryVouchers')];
    }

    public function broadcastWith() {
        return [
            'treasuryVoucher' => new TreasuryVoucherResource($this->treasuryVoucher),
            'treasuryVoucherId' => $this->treasuryVoucherId,
            'type' => $this->type,
        ];
    }
}
