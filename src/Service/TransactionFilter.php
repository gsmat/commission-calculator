<?php

namespace App\Service;

use App\Entity\Transaction;
use Exception;

class TransactionFilter
{
    /**
     * @throws Exception
     */
    public function filter(array $transactions, array $criteria): array
    {
        $from = isset($criteria['date_from']) ? new \DateTimeImmutable($criteria['date_from']) : null;
        $to   = isset($criteria['date_to'])   ? new \DateTimeImmutable($criteria['date_to'])   : null;

        return array_values(array_filter($transactions, function (Transaction $t) use ($criteria, $from, $to) {
            if ($from && $t->getDate() < $from) return false;
            if ($to   && $t->getDate() > $to)   return false;

            if (isset($criteria['user_type']) && $t->getUserType() !== $criteria['user_type']) return false;
            if (isset($criteria['operation_type']) && $t->getOperationType() !== $criteria['operation_type']) return false;
            if (isset($criteria['user_id']) && $t->getUserId() !== (string)$criteria['user_id']) return false;

            return true;
        }));
    }
}
