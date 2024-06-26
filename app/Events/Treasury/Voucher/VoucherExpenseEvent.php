<?php

namespace App\Events\Treasury\Voucher;

use App\Models\Treasury\VoucherExpense;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoucherExpenseEvent implements ShouldBroadcast {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $voucherExpense;
    public $voucherExpenseId;
    public $type;
    /**
     * Create a new event instance.
     *
     * @param \App\Models\Treasury\VoucherExpense $voucherExpense
     * @return void
     */
    public function __construct(VoucherExpense $voucherExpense, $voucherExpenseId, $type) {
        $this->voucherExpense = $voucherExpense;
        $this->voucherExpenseId = $voucherExpenseId;
        $this->type = $type;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array {
        return [new Channel('expenses')];
    }

    public function broadcastWith() {
        return [
            'voucherExpense' => $this->voucherExpense,
            'voucherExpenseId' => $this->voucherExpenseId,
            'type' => $this->type,
        ];
    }
}
