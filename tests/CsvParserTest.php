<?php
namespace Tests;

use App\Parser\CsvTransactionParser;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class CsvParserTest extends TestCase
{
    public function testParsesSixColumnsAndNormalizesOps(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'tx');
        file_put_contents(
            $tmp,
            "2016-01-07,1,private,withdraw,100.00,USD\n" .
            "2016-01-10,2,business,deposit,1000.00,EUR\n"
        );

        $parser = new CsvTransactionParser();
        $list = $parser->parse($tmp);

        $this->assertCount(2, $list);

        $t1 = $list[0];
        $this->assertSame('1', $t1->getUserId());
        $this->assertSame('private', $t1->getUserType());
        $this->assertSame('cash_out', $t1->getOperationType()); // withdraw -> cash_out
        $this->assertSame('2016-01-07', $t1->getDate()->format('Y-m-d'));
        $this->assertSame('USD', $t1->getAmount()->getCurrency());
        $this->assertSame(100.00, $t1->getAmount()->getValue());

        $t2 = $list[1];
        $this->assertSame('2', $t2->getUserId());
        $this->assertSame('business', $t2->getUserType());
        $this->assertSame('cash_in', $t2->getOperationType()); // deposit -> cash_in
        $this->assertSame('2016-01-10', $t2->getDate()->format('Y-m-d'));
        $this->assertSame('EUR', $t2->getAmount()->getCurrency());
        $this->assertSame(1000.00, $t2->getAmount()->getValue());
    }

    public function testTrimsAndCaseInsensitivity(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'tx');
        file_put_contents(
            $tmp,
            "2016-02-01,  42  ,  PRIVATE ,  Withdraw ,  50.00  , eur \n"
        );

        $parser = new CsvTransactionParser();
        $list = $parser->parse($tmp);

        $this->assertCount(1, $list);
        $t = $list[0];
        $this->assertSame('42', $t->getUserId());
        $this->assertSame('private', $t->getUserType());
        $this->assertSame('cash_out', $t->getOperationType());
        $this->assertSame('EUR', $t->getAmount()->getCurrency());
        $this->assertSame(50.00, $t->getAmount()->getValue());
    }

    public function testInvalidColumnCountThrows(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'tx');
        file_put_contents($tmp, "2016-01-07,private,withdraw,100.00,USD\n");

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid column count');

        (new CsvTransactionParser())->parse($tmp);
    }

    public function testInvalidDateThrows(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'tx');
        file_put_contents($tmp, "07-01-2016,1,private,withdraw,100.00,USD\n");

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid date');

        (new CsvTransactionParser())->parse($tmp);
    }

    public function testInvalidUserTypeThrows(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'tx');
        file_put_contents($tmp, "2016-01-07,1,vip,withdraw,100.00,USD\n");

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid user_type');

        (new CsvTransactionParser())->parse($tmp);
    }

    public function testInvalidOperationThrows(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'tx');
        file_put_contents($tmp, "2016-01-07,1,private,transfer,100.00,USD\n");

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid operation_type');

        (new CsvTransactionParser())->parse($tmp);
    }

    public function testInvalidAmountThrows(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'tx');
        file_put_contents($tmp, "2016-01-07,1,private,withdraw,xx,USD\n");

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid amount');

        (new CsvTransactionParser())->parse($tmp);
    }

    public function testFileNotFoundThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('CSV file not found');

        (new CsvTransactionParser())->parse(__DIR__ . '/not-exists.csv');
    }
}
