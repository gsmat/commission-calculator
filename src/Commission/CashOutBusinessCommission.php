<?php

namespace App\Commission;

use App\Entity\Transaction;

class CashOutBusinessCommission implements CommissionType
{
    public function calculate(Transaction $transaction): float
    {
        return $transaction->getAmount()->getValue() * 0.005;
    }
}
