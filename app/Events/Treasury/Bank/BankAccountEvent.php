<?php

namespace App\Events\Treasury\Bank;

use App\Models\Treasury\BankAccount;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BankAccountEvent implements ShouldBroadcast {
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $bankAccount;
    public $bankAccountId;
    public $type;
    /**
     * Create a new event instance.
     *
     * @param \App\Models\Treasury\BankAccount $bankAccount
     * @return void
     */
    public function __construct(BankAccount $bankAccount, $bankAccountId, $type) {
        $this->bankAccount = $bankAccount;
        $this->bankAccountId = $bankAccountId;
        $this->type = $type;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array {
        return [
            new Channel('bankAccounts'),
        ];
    }

    public function broadcastWith(): array {
        return [
            'bankAccount' => $this->bankAccount,
            'bankAccountId' => $this->bankAccountId,
            'type' => $this->type
        ];
    }
}
