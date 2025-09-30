<?php

namespace App\Commission;

use App\Entity\Transaction;

interface CommissionType
{
    public function calculate(Transaction $transaction): float;
}
