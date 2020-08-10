<?php

declare(strict_types=1);

namespace Model;

use DateTimeImmutable;

class Transaction
{
    private $datetime;
    private $client;
    private $operation;
    private $cash;

    public function __construct(DateTimeImmutable $datetime, Client $client, Operation $operation, Cash $cash)
    {
        $this->datetime = $datetime;
        $this->client = $client;
        $this->operation = $operation;
        $this->cash = $cash;
    }

    public function getDatetime()
    {
        return $this->datetime;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getOperation()
    {
        return $this->operation;
    }

    public function getCash()
    {
        return $this->cash;
    }
}
