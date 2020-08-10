<?php

declare(strict_types=1);

namespace Service;

use Model\Cash;

class CurrencyConverter
{
    //convert currencies here
    private $rates = [
        'EUR' => ['name' => 'EUR', 'rate' => '1', 'precision' => 2],
         'USD' => ['name' => 'USD', 'rate' => '1.1497', 'precision' => 2],
          'JPY' => ['name' => 'JPY', 'rate' => '129.53', 'precision' => 0],
        ];

    public function convertToEur(Cash $cash): Cash
    {
        return new Cash(bcdiv($cash->getAmount(), $this->rates[$cash->getCurrency()]['rate'], 10), 'EUR');
    }

    public function getPrecision(string $currency): int
    {
        return $this->rates[$currency]['precision'];
    }

    public function convert(Cash $cash, string $to): Cash
    {
        if ($cash->getCurrency() === $to) {
            return $cash;
        }

        if ($to === 'EUR') {
            return new Cash(bcdiv($cash->getAmount(), $this->rates[$cash->getCurrency()]['rate'], 10), 'EUR');
        } else {
            return new Cash(bcmul($cash->getAmount(), $this->rates[$to]['rate'], 10), $to);
        }
    }
}
