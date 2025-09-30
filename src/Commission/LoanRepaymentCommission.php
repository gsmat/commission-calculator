<?php

namespace App\Commission;

use App\Entity\Transaction;

class LoanRepaymentCommission implements CommissionType
{
    public function calculate(Transaction $transaction): float
    {
        return ($transaction->getAmount()->getValue() * 0.02) + 1.0;
    }
}
