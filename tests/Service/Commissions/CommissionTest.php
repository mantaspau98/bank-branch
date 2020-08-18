<?php
declare(strict_types=1);

namespace Tests\Service;

use PHPUnit\Framework\TestCase;
use Service;
use Model;

class CommissionTest extends TestCase
{

    private $commissionCalculator;
    private $mockRates = [
        'EUR' => ['name' => 'EUR', 'rate' => '1', 'precision' => 2],
        'USD' => ['name' => 'USD', 'rate' => '1.1497', 'precision' => 2],
        'JPY' => ['name' => 'JPY', 'rate' => '129.53', 'precision' => 0],
    ];
    private $converter;

    public function setUp()
    {
        $this->commissionCalculator = new Service\CommissionCalculator();
        $this->converter = new Service\CurrencyConverter($this->mockRates);
    }


    public function testCashIn()
    {
        //Cash in
        $dateTime = new \DateTimeImmutable('2014-12-31');
        $operation = new Model\Operation('cash_in');
        $cash = new Model\Cash('1000.00', 'EUR', $this->converter);
        $client = new Model\Client('1', 'legal');

        $transaction = new Model\Transaction($dateTime, $client, $operation, $cash);

        $this->assertEquals(
            '0.30',
            $this->commissionCalculator->execute($transaction)->getCeiledAmount()
        );
    }

    public function testCashInUpperLimit()
    {
        //Cash in commisssion upper limit is 5EUR
        $dateTime = new \DateTimeImmutable('2014-12-31');
        $operation = new Model\Operation('cash_in');
        $cash = new Model\Cash('1000000.00', 'EUR', $this->converter);
        $client = new Model\Client('1', 'legal');

        $transaction = new Model\Transaction($dateTime, $client, $operation, $cash);

        $this->assertEquals(
            '5.00',
            $this->commissionCalculator->execute($transaction)->getCeiledAmount()
        );
    }

    public function testCashOutLegal()
    {
        //Cash out legal
        $dateTime = new \DateTimeImmutable('2014-12-31');
        $operation = new Model\Operation('cash_out');
        $cash = new Model\Cash('1000.00', 'EUR', $this->converter);
        $client = new Model\Client('1', 'legal');

        $transaction = new Model\Transaction($dateTime, $client, $operation, $cash);

        $this->assertEquals(
            '3.00',
            $this->commissionCalculator->execute($transaction)->getCeiledAmount()
        );
    }

    public function testCashOutLegalLowerLimit()
    {
        //Cash out legal cannot be less than 0.50EUR
        $dateTime = new \DateTimeImmutable('2014-12-31');
        $operation = new Model\Operation('cash_out');
        $cash = new Model\Cash('1.00', 'EUR', $this->converter);
        $client = new Model\Client('1', 'legal');

        $transaction = new Model\Transaction($dateTime, $client, $operation, $cash);

        $this->assertEquals(
            '0.50',
            $this->commissionCalculator->execute($transaction)->getCeiledAmount()
        );
    }

    public function testCashOutLegalLowerLimitDifferentCurrency()
    {
        //Cash out legal cannot be less than 0.50EUR even in other currencies than EUR but commission is still in the original currency 
        $dateTime = new \DateTimeImmutable('2014-12-31');
        $operation = new Model\Operation('cash_out');
        $cash = new Model\Cash('1.00', 'USD', $this->converter);
        $client = new Model\Client('1', 'legal');

        $transaction = new Model\Transaction($dateTime, $client, $operation, $cash);

        $this->assertEquals(
            '0.58',
            $this->commissionCalculator->execute($transaction)->getCeiledAmount()
        );
    }

    public function testCashOutNaturalDiscountTest()
    {
        //Cash out natural discount up to 1000EUR a week
        $dateTime = new \DateTimeImmutable('2014-12-31');
        $operation = new Model\Operation('cash_out');
        $cash = new Model\Cash('1000.00', 'EUR', $this->converter);
        $client = new Model\Client('1', 'natural');

        $transaction = new Model\Transaction($dateTime, $client, $operation, $cash);

        $this->assertEquals(
            '0.00',
            $this->commissionCalculator->execute($transaction)->getCeiledAmount()
        );
    }

    public function testCashOutNaturalDiscountTestSplitSameTransaction()
    {
        //Cash out natural discount up to 1000EUR a week
        //200EUR is still being commissioned
        $dateTime = new \DateTimeImmutable('2014-12-31');
        $operation = new Model\Operation('cash_out');
        $cash = new Model\Cash('1200.00', 'EUR', $this->converter);
        $client = new Model\Client('1', 'natural');

        $transaction = new Model\Transaction($dateTime, $client, $operation, $cash);

        $this->assertEquals(
            '0.60',
            $this->commissionCalculator->execute($transaction)->getCeiledAmount()
        );
    }

    public function testCashOutNaturalDiscountTestSepareteTransaction()
    {
        //Client has already transfered more than 1000 this week.
        //All other transactions same week will not have discount
        $dateTime = new \DateTimeImmutable('2014-12-31');
        $operation = new Model\Operation('cash_out');
        $cash = new Model\Cash('200.00', 'EUR', $this->converter);
        $client = new Model\Client('1', 'natural');

        //Add existing transfers
        $mockDateTime = new \DateTimeImmutable('2014-12-31');
        $mockCash = new Model\Cash('1200.00', 'EUR', $this->converter);
        $client->addTransfer($mockCash, $dateTime->format('oW'));

        $transaction = new Model\Transaction($dateTime, $client, $operation, $cash);

        $this->assertEquals(
            '0.60',
            $this->commissionCalculator->execute($transaction)->getCeiledAmount()
        );
    }

    public function testCashOutNaturalDiscountUpTo3TransfersPerWeek()
    {
        //Client has already made 3 transfers this week
        //Additional transfers will not have discount
        $dateTime = new \DateTimeImmutable('2014-12-31');
        $operation = new Model\Operation('cash_out');
        $cash = new Model\Cash('200.00', 'EUR', $this->converter);
        $client = new Model\Client('1', 'natural');

        //Add existing transfers
        $mockDateTime = new \DateTimeImmutable('2014-12-31');
        $mockCash = new Model\Cash('100.00', 'EUR', $this->converter);
        $client->addTransfer($mockCash, $dateTime->format('oW'));
        $client->addTransfer($mockCash, $dateTime->format('oW'));
        $client->addTransfer($mockCash, $dateTime->format('oW'));

        $transaction = new Model\Transaction($dateTime, $client, $operation, $cash);

        $this->assertEquals(
            '0.60',
            $this->commissionCalculator->execute($transaction)->getCeiledAmount()
        );
    }

    public function testCashOutNaturalDifferentYearSameWeek()
    {
        //Client has already transfered more than 1000 this week.
        //Additional transfers will not have discount even if they are in different year
        $dateTime = new \DateTimeImmutable('2015-01-01');
        $operation = new Model\Operation('cash_out');
        $cash = new Model\Cash('200.00', 'EUR', $this->converter);
        $client = new Model\Client('1', 'natural');

        //Add existing transfers
        $mockDateTime = new \DateTimeImmutable('2014-12-31');
        $mockCash = new Model\Cash('1000.00', 'EUR', $this->converter);
        $client->addTransfer($mockCash, $dateTime->format('oW'));

        $transaction = new Model\Transaction($dateTime, $client, $operation, $cash);

        $this->assertEquals(
            '0.60',
            $this->commissionCalculator->execute($transaction)->getCeiledAmount()
        );
    }


}
