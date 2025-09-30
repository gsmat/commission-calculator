<?php

require __DIR__ . '/vendor/autoload.php';

use App\Parser\CsvTransactionParser;
use App\Service\CommissionService;
use App\Service\TransactionFilter;

$csvPath = __DIR__ . '/examples/transactions.csv';

try {
    $parser = new CsvTransactionParser();
    $transactions = $parser->parse($csvPath);

    $commissionService = new CommissionService();

    echo "Commission Results\n";
    echo "==================\n";

    foreach ($transactions as $t) {
        $fee = $commissionService->calculate($t);
        $amt = $t->getAmount();
        $formattedFee = number_format($fee,(in_array($amt->getCurrency(), ['JPY'], true) ? 0 : 2));

        echo "[" . $t->getDate()->format('Y-m-d') . "] "
            . "user_id:" . $t->getUserId() . " "
            . $t->getUserType() . " "
            . $t->getOperationType() . " "
            . number_format($amt->getValue(), 2) . " "
            . $amt->getCurrency()
            . "  => fee: " . $formattedFee . " " . $amt->getCurrency()
            . "\n";
    }

    $filter = new TransactionFilter();
    $filtered = $filter->filter($transactions, ['user_id' => '1', 'operation_type' => 'cash_out']);
    echo "\nFiltered (user_id=1, cash_out): " . count($filtered) . " rows\n";

} catch (Throwable $e) {
    fwrite(STDERR, "Error: " . $e->getMessage() . PHP_EOL);
    exit(1);
}
