<?php

declare(strict_types=1);

namespace Model;

use Exception;
use Service\CurrencyConverter;

class Client
{
    private $clientId;
    private $clientType;
    private $transferedThisWeek;
    private $noOfTransfers;
    private $weekNoOfLastTransfer;

    const CLIENT_TYPES = ['natural', 'legal'];

    public function __construct(string $clientId, string $clientType)
    {
        if (!in_array($clientType, self::CLIENT_TYPES, true)) {
            throw new Exception('Client type does not exist');
        }
        $this->clientId = $clientId;
        $this->clientType = $clientType;
        $this->noOfTransfers = 0;
        $this->weekNoOfLastTransfer = '0';
        $this->transferedThisWeek = '0';
    }

    public function getId(): int
    {
        return $this->clientId;
    }

    public function getType(): string
    {
        return $this->clientType;
    }

    public function getNoOfTransfers(): int
    {
        return $this->noOfTransfers;
    }

    public function getWeekNoOfLastTransfer(): string
    {
        return $this->weekNoOfLastTransfer;
    }

    public function getTransferedThisWeek(): string
    {
        return $this->transferedThisWeek;
    }

    public function setTransferedThisWeek(string $number): void
    {
        $this->transferedThisWeek = $number;
    }

    public function setWeekNoOfLastTransfer(string $number): void
    {
        $this->weekNoOfLastTransfer = $number;
    }

    public function setNoOfTransfers(int $number): void
    {
        $this->noOfTransfers = $number;
    }

    public function addTransfer(Cash $amountTransfered, string $weekNo): void
    {
        $converter = new CurrencyConverter();

        if ($amountTransfered->getCurrency() === 'EUR') {
            //Add if it's eur
            $this->transferedThisWeek = bcadd($this->transferedThisWeek, $amountTransfered->getCeiledAmount(), 10);
        } else {
            //convert then add
            $amountToAdd = $converter->convert($amountTransfered, 'EUR');
            $this->transferedThisWeek = bcadd($this->transferedThisWeek, $amountToAdd->getCeiledAmount(), 10);
        }
        //set week no.
        $this->weekNoOfLastTransfer = $weekNo;
        //add a transfer to this week
        ++$this->noOfTransfers;
    }
}
