<?php

namespace App\Commission;

use App\Entity\Transaction;

class CashInCommission implements CommissionType
{
    public function calculate(Transaction $transaction): float
    {
        $fee = $transaction->getAmount()->getValue() * 0.0003;
        return min($fee, 5.0);
    }
}
