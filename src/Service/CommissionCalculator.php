<?php

declare(strict_types=1);

namespace Service;

use Model\Cash;
use Model\Transaction;
use Service\Commissions\CommissionCashIn;
use Service\Commissions\CommissionCashOutLeg;
use Service\Commissions\CommissionCashOutNat;

class CommissionCalculator
{
    //nusprest kuris klientas ir apskaiciuot jo komisini
    public function execute(Transaction $transaction): Cash
    {
        if ($transaction->getOperation()->getType() === 'cash_in') {
            //Cash in same for both legal and natural clients
            $commissionCalc = new CommissionCashIn();

            return $commissionCalc->Calculate($transaction);
        } elseif ($transaction->getOperation()->getType() === 'cash_out') {
            if ($transaction->getClient()->getType() === 'legal') {
                //cash out legal
                $commissionCalc = new CommissionCashOutLeg();

                return $commissionCalc->Calculate($transaction);
            } else {
                //cash out natural
                $commissionCalc = new CommissionCashOutNat();

                return $commissionCalc->Calculate($transaction);
            }
        }

        throw new Exception('Operation type is not found');
    }
}
