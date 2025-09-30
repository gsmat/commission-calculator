<?php

namespace App\Commission;

use App\Entity\Transaction;

class CashOutPrivateCommission implements CommissionType
{
    public function calculate(Transaction $transaction): float
    {
        $fee = $transaction->getAmount()->getValue() * 0.003;
        return max($fee, 0.5);
    }
}
