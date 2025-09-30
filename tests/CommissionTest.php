<?php
namespace Tests;

use App\Entity\Amount;
use App\Entity\Transaction;
use App\Service\CommissionService;
use Exception;
use PHPUnit\Framework\TestCase;

final class CommissionTest extends TestCase
{
    private CommissionService $svc;

    protected function setUp(): void
    {
        $this->svc = new CommissionService();
    }

    public function testCashInTypical(): void
    {
        $t = $this->tx('2016-01-01', '9', 'business', 'cash_in', 10000.00, 'EUR');
        $this->assertSame(3.00, $this->svc->calculate($t));
    }

    public function testCashInMaxCapApplied(): void
    {
        $t = $this->tx('2016-01-01', '9', 'private', 'cash_in', 1000000.00, 'USD');
        $this->assertSame(5.00, $this->svc->calculate($t));
    }

    public function testPrivateCashOutMinFeeApplies(): void
    {
        $t = $this->tx('2016-01-01', '1', 'private', 'cash_out', 100.00, 'USD');
        $this->assertSame(0.50, $this->svc->calculate($t));
    }

    public function testPrivateCashOutAboveMin(): void
    {
        $t = $this->tx('2016-01-01', '1', 'private', 'cash_out', 5000.00, 'EUR');
        $this->assertSame(15.00, $this->svc->calculate($t));
    }

    public function testBusinessCashOutFlat(): void
    {
        $t = $this->tx('2016-01-01', '2', 'business', 'cash_out', 300.00, 'EUR');
        $this->assertSame(1.50, $this->svc->calculate($t));
    }

    public function testLoanRepaymentRule(): void
    {
        $t = $this->tx('2016-01-01', '3', 'private', 'loan_repayment', 200.00, 'EUR');
        $this->assertSame(5.00, $this->svc->calculate($t));
    }

    public function testJpyRoundingBehavior(): void
    {
        $t = $this->tx('2016-01-01', '1', 'private', 'cash_out', 1234, 'JPY');
        $fee = $this->svc->calculate($t);
        $this->assertIsFloat($fee);
    }

    public function testUnsupportedOperationThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $t = $this->tx('2016-01-01', '1', 'private', 'unknown_op', 10, 'EUR');
        $this->svc->calculate($t);
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
