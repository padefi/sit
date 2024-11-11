<?php

namespace App\Events\Treasury\Voucher;

use App\Http\Resources\Treasury\TreasuryVoucher\TreasuryVoucherResource;
use App\Models\Treasury\TreasuryVoucher\TreasuryVoucher;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoucherToTreasuryEvent implements ShouldBroadcast {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $treasuryVoucher;
    public $treasuryVoucherId;
    public $type;

    /**
     * Create a new event instance.
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
        return [new Channel('voucherToTreasury')];
    }

    public function broadcastWith() {
        return [
            'treasuryVoucher' => new TreasuryVoucherResource($this->treasuryVoucher),
            'treasuryVoucherId' => $this->treasuryVoucherId,
            'type' => $this->type,
        ];
    }
}
