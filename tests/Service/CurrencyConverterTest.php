<?php
declare(strict_types=1);

namespace Tests\Service;

use PHPUnit\Framework\TestCase;
use Service\CurrencyConverter;
use Model\Cash;

class CurrencyConverterTest extends TestCase
{

    /**
     * @param string $leftOperand
     * @param string $rightOperand
     * @param string $expectation
     *
     * @dataProvider dataProviderForAddTesting
     */
    public function testConvert(Cash $cash, string $to, string $expectation)
    {
        $this->assertEquals(
            $expectation,
            $cash->getConverter()->convert($cash, $to)->getCeiledAmount()
        );
    }

    public function dataProviderForAddTesting(): array
    {
        //set mock rates for testing
        $mockRates = [
            'EUR' => ['name' => 'EUR', 'rate' => '1', 'precision' => 2],
            'USD' => ['name' => 'USD', 'rate' => '1.1497', 'precision' => 2],
            'JPY' => ['name' => 'JPY', 'rate' => '129.53', 'precision' => 0],
        ];
        
        $converter = new CurrencyConverter($mockRates);
        
        return [
            'convert 1 EUR to EUR and ceil it' => [new Cash('1', 'EUR', $converter), 'EUR', '1'],
            'convert 1 EUR to JPY and ceil it' => [new Cash('1', 'EUR', $converter), 'JPY', '130'],
            'convert 1 EUR to USD and ceil it' => [new Cash('1', 'EUR', $converter), 'USD', '1.15'],

            'convert 1 JPY to EUR and ceil it' => [new Cash('100', 'JPY', $converter), 'EUR', '0.78'],
            'convert 1 USD to EUR and ceil it' => [new Cash('1', 'USD', $converter), 'EUR', '0.87'],
        ];
    }
}
