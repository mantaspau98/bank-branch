<?php

declare(strict_types=1);

namespace Service;

use Model\Cash;
use Model\Transaction;
use Service\Commissions\CommissionCashInLeg;
use Service\Commissions\CommissionCashInNat;
use Service\Commissions\CommissionCashOutLeg;
use Service\Commissions\CommissionCashOutNat;

class CommissionCalculator
{
    //nusprest kuris klientas ir apskaiciuot jo komisini
    public function execute(Transaction $transaction): Cash
    {
        if ($transaction->getClient()->getType() === 'legal') {
            if ($transaction->getOperation()->getType() === 'cash_in') {
                //legal cash_in
                $t = new CommissionCashInLeg();

                return $t->Calculate($transaction);
            } else {
                //legal cash_out
                $t = new CommissionCashOutLeg();

                return $t->Calculate($transaction);
            }
        } elseif ($transaction->getClient()->getType() === 'natural') {
            if ($transaction->getOperation()->getType() === 'cash_in') {
                //natural cash_in
                $t = new CommissionCashInNat();

                return $t->Calculate($transaction);
            } else {
                //natural cash_out
                $t = new CommissionCashOutNat();

                return $t->Calculate($transaction);
            }
        }
        throw new Exception('Operation or Client type is not found');
    }
}
