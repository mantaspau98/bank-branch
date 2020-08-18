<?php

declare(strict_types=1);

namespace Model;

use Service\CurrencyConverter;

class Cash
{
    private $amount;
    private $currency;
    private $converter;

    public function __construct(string $amount, string $currency, CurrencyConverter $converter)
    {
        $this->amount = $amount;
        $this->currency = $currency;
        $this->converter = $converter;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): void
    {
        $this->amount = $amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getAmountEur(): string
    {
        return $this->converter->convert($this, 'EUR')->getAmount();
    }

    public function getConverter(): CurrencyConverter
    {
        return $this->converter;
    }

    public function getCeiledAmount(): string
    {
        $fig = (int) str_pad('1', $this->converter->getPrecision($this->currency) + 1, '0');

        return sprintf('%.'.$this->converter->getPrecision($this->currency).'f', strval(ceil($this->amount * $fig) / $fig));
    }
}
