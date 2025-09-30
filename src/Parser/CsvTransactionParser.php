<?php

namespace App\Parser;

use App\Entity\Transaction;
use App\Entity\Amount;
use InvalidArgumentException;

class CsvTransactionParser
{
    public function parse(string $path): array
    {
        if (!is_file($path)) {
            throw new InvalidArgumentException("CSV file not found: $path");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $transactions = [];

        foreach ($lines as $i => $line) {
            $row = str_getcsv($line);

            if (count($row) !== 6) {
                throw new InvalidArgumentException("Invalid column count at line ".($i+1).". Expected 6, got ".count($row));
            }

            [$dateStr, $userId, $userType, $operationRaw, $amountStr, $currency] = $row;

            $date = \DateTimeImmutable::createFromFormat('Y-m-d', trim($dateStr));
            if (!$date) {
                throw new InvalidArgumentException("Invalid date at line ".($i+1).": $dateStr");
            }

            $userType = strtolower(trim($userType));
            if (!in_array($userType, ['private', 'business'], true)) {
                throw new InvalidArgumentException("Invalid user_type at line ".($i+1).": $userType");
            }

            $operation = strtolower(trim($operationRaw));
            $operation = match ($operation) {
                'deposit'  => 'cash_in',
                'withdraw' => 'cash_out',
                default    => $operation,
            };

            if (!in_array($operation, ['cash_in', 'cash_out', 'loan_repayment'], true)) {
                throw new InvalidArgumentException("Invalid operation_type at line ".($i+1).": $operationRaw");
            }

            if (!is_numeric($amountStr)) {
                throw new InvalidArgumentException("Invalid amount at line ".($i+1).": $amountStr");
            }
            $amount = new Amount((float)$amountStr, strtoupper(trim($currency)));

            $transactions[] = new Transaction(
                $date,
                (string)trim($userId),
                $userType,
                $operation,
                $amount
            );
        }

        return $transactions;
    }
}
