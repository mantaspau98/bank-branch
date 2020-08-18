<?php

declare(strict_types=1);

namespace Model;

use Exception;

class Client
{
    private $clientId;
    private $clientType;
    private $eurTransferedThisWeek;
    private $noOfTransfersThisWeek;
    private $weekNoOfLastTransfer;

    const CLIENT_TYPES = ['natural', 'legal'];

    public function __construct(string $clientId, string $clientType)
    {
        if (!in_array($clientType, self::CLIENT_TYPES, true)) {
            throw new Exception('Client type does not exist');
        }
        $this->clientId = $clientId;
        $this->clientType = $clientType;
        $this->noOfTransfersThisWeek = 0;
        $this->weekNoOfLastTransfer = '0';
        $this->eurTransferedThisWeek = '0';
    }

    public function getId(): int
    {
        return $this->clientId;
    }

    public function getType(): string
    {
        return $this->clientType;
    }

    public function getNoOfTransfersThisWeek(): int
    {
        return $this->noOfTransfersThisWeek;
    }

    public function getWeekNoOfLastTransfer(): string
    {
        return $this->weekNoOfLastTransfer;
    }

    public function getEurTransferedThisWeek(): string
    {
        return $this->eurTransferedThisWeek;
    }

    public function setEurTransferedThisWeek(string $number): void
    {
        $this->eurTransferedThisWeek = $number;
    }

    public function setWeekNoOfLastTransfer(string $number): void
    {
        $this->weekNoOfLastTransfer = $number;
    }

    public function setNoOfTransfersThisWeek(int $number): void
    {
        $this->noOfTransfersThisWeek = $number;
    }

    public function addTransfer(Cash $amountTransfered, string $weekNo): void
    {
        $converter = $amountTransfered->getConverter();

        if ($amountTransfered->getCurrency() === 'EUR') {
            //Add if it's eur
            $this->eurTransferedThisWeek = bcadd($this->eurTransferedThisWeek, $amountTransfered->getCeiledAmount(), 10);
        } else {
            //convert then add
            $amountToAdd = $converter->convert($amountTransfered, 'EUR');
            $this->eurTransferedThisWeek = bcadd($this->eurTransferedThisWeek, $amountToAdd->getCeiledAmount(), 10);
        }
        //set week no.
        $this->weekNoOfLastTransfer = $weekNo;
        //add a transfer to this week
        ++$this->noOfTransfersThisWeek;
    }
}
