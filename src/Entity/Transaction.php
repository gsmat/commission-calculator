<?php

namespace App\Entity;

class Transaction
{
    public function __construct(
        private \DateTimeImmutable $date,
        private string             $userId,
        private string             $userType,
        private string             $operationType,
        private Amount             $amount
    )
    {
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getUserType(): string
    {
        return $this->userType;
    }

    public function getOperationType(): string
    {
        return $this->operationType;
    }

    public function getAmount(): Amount
    {
        return $this->amount;
    }
}
