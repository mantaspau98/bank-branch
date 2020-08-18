<?php

declare(strict_types=1);

namespace Service;

use Model\Cash;

class CurrencyConverter
{
    //convert currencies here
    private $rates;

    public function __construct(array $rates)
    {
        $this->rates = $rates;
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
            return new Cash(bcdiv($cash->getAmount(), $this->rates[$cash->getCurrency()]['rate'], 10), $to, $cash->getConverter());
        } else {
            return new Cash(bcmul($cash->getAmount(), $this->rates[$to]['rate'], 10), $to, $cash->getConverter());
        }
    }
}
