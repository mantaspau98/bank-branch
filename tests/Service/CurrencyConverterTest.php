<?php
declare(strict_types=1);

namespace Tests\Service;

use PHPUnit\Framework\TestCase;
use Service\CurrencyConverter;
use Model\Cash;

class CurrencyConverterTest extends TestCase
{
    /**
     * @var CurrencyConverter
     */
    private $converter;

    public function setUp()
    {
        $this->converter = new CurrencyConverter();
    }

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
            $this->converter->convert($cash, $to)->getCeiledAmount()
        );
    }

    public function dataProviderForAddTesting(): array
    {
        return [
            'convert 1 EUR to EUR and ceil it' => [new Cash('1', 'EUR'), 'EUR', '1'],
            'convert 1 EUR to JPY and ceil it' => [new Cash('1', 'EUR'), 'JPY', '130'],
            'convert 1 EUR to USD and ceil it' => [new Cash('1', 'EUR'), 'USD', '1.15'],

            'convert 1 JPY to EUR and ceil it' => [new Cash('100', 'JPY'), 'EUR', '0.78'],
            'convert 1 USD to EUR and ceil it' => [new Cash('1', 'USD'), 'EUR', '0.87'],
        ];
    }
}
