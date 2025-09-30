<?php

namespace App\Service;

use App\Entity\Transaction;
use App\Commission\CashInCommission;
use App\Commission\CashOutPrivateCommission;
use App\Commission\CashOutBusinessCommission;
use App\Commission\LoanRepaymentCommission;

class CommissionService
{
    private array $currencyScale = [
        'EUR' => 2,
        'USD' => 2,
        'JPY' => 0,
    ];

    public function calculate(Transaction $transaction): float
    {
        $op = $transaction->getOperationType();
        $userType = $transaction->getUserType();

        $fee = 0.0;

        if ($op === 'cash_in') {
            $fee = (new CashInCommission())->calculate($transaction);
        } elseif ($op === 'cash_out' && $userType === 'private') {
            $fee = (new CashOutPrivateCommission())->calculate($transaction);
        } elseif ($op === 'cash_out' && $userType === 'business') {
            $fee = (new CashOutBusinessCommission())->calculate($transaction);
        } elseif ($op === 'loan_repayment') {
            $fee = (new LoanRepaymentCommission())->calculate($transaction);
        } else {
            throw new \InvalidArgumentException("Unsupported transaction type: $op ($userType)");
        }

        $currency = $transaction->getAmount()->getCurrency();
        $scale = $this->currencyScale[$currency] ?? 2;

        return (float) number_format($fee, $scale, '.', '');
    }

}
