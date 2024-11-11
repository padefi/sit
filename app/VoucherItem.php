<?php

namespace App;

use Ramsey\Uuid\Type\Decimal;

class VoucherItem {
    public string $description;
    public Decimal $amount;
    public int $vat;
    public Decimal $subtotalAmount;
    /**
     * Create a new class instance.
     */
    public function __construct(array $data) {
        $this->description = $data['description'];
        $this->amount = $data['amount'] ?? 0;
        $this->vat = $data['vat'] ?? 1;
        $this->subtotalAmount = $data['subtotalAmount'] ?? 0;
    }
}
