<?php
namespace Tests;

use App\Entity\Amount;
use App\Entity\Transaction;
use App\Service\TransactionFilter;
use Exception;
use PHPUnit\Framework\TestCase;

final class FilterTest extends TestCase
{
    private TransactionFilter $filter;
    private array $data;

    protected function setUp(): void
    {
        $this->filter = new TransactionFilter();

        $this->data = [
            $this->tx('2016-01-05', '1', 'private',  'cash_out',  50.00,   'EUR'),
            $this->tx('2016-01-10', '2', 'business', 'cash_in',   100.00,  'EUR'),
            $this->tx('2016-01-15', '1', 'private',  'cash_in',   20.00,   'USD'),
            $this->tx('2016-02-01', '3', 'private',  'loan_repayment', 75.00, 'EUR'),
            $this->tx('2016-02-10', '2', 'business', 'cash_out',  300.00,  'USD'),
        ];
    }

    public function testFilterByUserType(): void
    {
        $out = $this->filter->filter($this->data, ['user_type' => 'private']);
        $this->assertCount(3, $out);
        $this->assertSame('private', $out[0]->getUserType());
    }

    public function testFilterByOperationType(): void
    {
        $out = $this->filter->filter($this->data, ['operation_type' => 'cash_in']);
        $this->assertCount(2, $out);
        foreach ($out as $t) {
            $this->assertSame('cash_in', $t->getOperationType());
        }
    }

    public function testFilterByUserIdAndOperation(): void
    {
        $out = $this->filter->filter($this->data, ['user_id' => '2', 'operation_type' => 'cash_out']);
        $this->assertCount(1, $out);
        $this->assertSame('2', $out[0]->getUserId());
        $this->assertSame('cash_out', $out[0]->getOperationType());
    }

    public function testFilterByDateRangeInclusive(): void
    {
        $out = $this->filter->filter($this->data, [
            'date_from' => '2016-01-10',
            'date_to'   => '2016-02-01',
        ]);
        $this->assertCount(3, $out);
        $dates = array_map(fn($t) => $t->getDate()->format('Y-m-d'), $out);
        $this->assertContains('2016-01-10', $dates);
        $this->assertContains('2016-01-15', $dates);
        $this->assertContains('2016-02-01', $dates);
    }

    public function testFilterNoResults(): void
    {
        $out = $this->filter->filter($this->data, [
            'user_id' => '999',
            'operation_type' => 'cash_out'
        ]);
        $this->assertSame([], $out);
    }


    /**
     * @throws Exception
     */
    private function tx(
        string $date,
        string $userId,
        string $userType,
        string $op,
        float $amount,
        string $ccy
    ): Transaction {
        return new Transaction(
            new \DateTimeImmutable($date),
            $userId,
            $userType,
            $op,
            new Amount($amount, $ccy)
        );
    }
}
