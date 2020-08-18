<?php
declare(strict_types=1);

require_once "vendor/autoload.php";

use Reader\CsvReader;
use Model\Client;
use Model\Operation;
use Model\Cash;
use Model\Transaction;
use Service\CommissionCalculator;
use Service\CurrencyConverter;

if(!isset($argv[1])){
    throw new Exception("Input data is needed");
}

$pathToFile = $argv[1];

$out = fopen('php://stdout', 'w');

$reader = new CsvReader();

$data_arr = $reader->read($pathToFile);

$comCalc = new CommissionCalculator();

$rates = [
        'EUR' => ['name' => 'EUR', 'rate' => '1', 'precision' => 2],
        'USD' => ['name' => 'USD', 'rate' => '1.1497', 'precision' => 2],
        'JPY' => ['name' => 'JPY', 'rate' => '129.53', 'precision' => 0],
    ];

//one converter and rates for whole app - single source of truth
$converter = new CurrencyConverter($rates);

$clientArr = [];

foreach($data_arr as $csvLine) {

    //get client - check if  the client exists, if not - create and add to array
    if(array_key_exists($csvLine[1], $clientArr)){
        $client = $clientArr[$csvLine[1]];
    }else{
        $client = new Client($csvLine[1], $csvLine[2]);
        $clientArr[$csvLine[1]] = $client;
    }

    $operation = new Operation($csvLine[3]);
    $cash = new Cash($csvLine[4], $csvLine[5], $converter);
    $date = new DateTimeImmutable($csvLine[0]);
    $transaction = new Transaction($date, $client, $operation, $cash);

    fputs($out, $comCalc->execute($transaction)->getCeiledAmount());
    fputs($out, "\n");
}

fclose($out);

?>