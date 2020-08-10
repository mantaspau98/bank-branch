<?php
declare(strict_types=1);

namespace Tests\Service;

use PHPUnit\Framework\TestCase;
use Service;
use Model;

class CommissionTest extends TestCase
{
    /**
     * @var CommissionCalculator
     */
    private $commissionCalculator;

    public function setUp()
    {
        $this->commissionCalculator = new Service\CommissionCalculator();
    }

    /**
     * @param string $leftOperand
     * @param string $rightOperand
     * @param string $expectation
     *
     * @dataProvider dataProviderTestCommissionCalc
     */
    public function testCashInLegal(string $operation, string $amount, string $currency, string $dateTime, Model\Client $client, string $expectedCommission)
    {
        $transaction = new Model\Transaction(new \DateTimeImmutable($dateTime), $client, new Model\Operation($operation), new Model\Cash($amount, $currency));
        $this->assertEquals(
            $expectedCommission,
            $this->commissionCalculator->execute($transaction)->getCeiledAmount()
        );
    }

    public function dataProviderTestCommissionCalc(): array
    {
        //create client to test discount
        $testClient = new Model\Client('4', 'natural');
        return [
            //tests with the same person
            '1. test natural client cash out 1200EUR, discount given on first 1000 EUR' => ['cash_out', '1200.00', 'EUR', '2014-12-31', $testClient, '0.60'],
            '2. test natural client cash out 1200EUR in the same week, discount does not apply' => ['cash_out', '1200.00', 'EUR', '2015-01-01', $testClient, '3.60'],
            '3. test natural client cash out 500EUR in the next week, discount applies again' => ['cash_out', '500.00', 'EUR', '2015-01-06', $testClient, '0.00'],
            '4. test natural client cash out 500EUR in the same week, discount still applies' => ['cash_out', '500.00', 'EUR', '2015-01-07', $testClient, '0.00'],
            '5. test natural client cash out 200EUR in the same week, discount does not apply anymore' => ['cash_out', '200.00', 'EUR', '2015-01-07', $testClient, '0.60'],

            //other commission calculations
            'legal client cash out 100 JPY, a minimum of 0.50EUR is charged (and converted to JPY)' => ['cash_out', '100', 'JPY', '2015-01-01', new Model\Client('1', 'legal'), '65'],
            'legal client cash in 1000000 EUR, a maximum of 5.00EUR is charged' => ['cash_in', '1000000.00', 'EUR', '2015-01-01', new Model\Client('1', 'legal'), '5.00'],
            'natural client cash in 1000000 EUR, a maximum of 5.00EUR is charged' => ['cash_in', '1000000.00', 'EUR', '2015-01-01', new Model\Client('1', 'natural'), '5.00'],
            'natural client cash in 1000000 USD, a maximum of 5.00EUR is charged (and converted to USD)' => ['cash_in', '1000000.00', 'USD', '2015-01-01', new Model\Client('1', 'natural'), '5.75'],
        ];
    }
}
