<?php

declare(strict_types=1);

namespace Service\Commissions;

use Model\Cash;
use Model\Transaction;

class CommissionCashOutNat implements Commission
{
    const DEFAULT_CASHOUT_NATURAL_COMMISION = '0.003';

    public function Calculate(Transaction $transaction): Cash
    {
        $client = $transaction->getClient();
        $discount = false;
        //check if transfer week is same as last transfers week
        if ($transaction->getDatetime()->format('oW') !== $client->getWeekNoOfLastTransfer()) {
            //diferent week, set zeroes
            $client->setEurTransferedThisWeek('0');
            $client->setNoOfTransfersThisWeek(0);
            //eligible
            $discount = true;
        } else {
            //else check if stil eligible for discount the same week
            if ($client->getNoOfTransfersThisWeek() < 3 && $client->getEurTransferedThisWeek() < 1000) {
                //eligible
                $discount = true;
            }
        }

        if ($discount) {
            //check how much will be discounted
            $converter = $transaction->getCash()->getConverter();
            $eurBeingTransfered = $converter->convert($transaction->getCash(), 'EUR');

            $discountLeft = bcsub('1000', $client->getEurTransferedThisWeek(), 10);

            if (bccomp($discountLeft, $eurBeingTransfered->getAmount(), 10) < 0) {
                $eurAmountToCommision = bcsub($eurBeingTransfered->getAmount(), $discountLeft, 10);
            } else {
                $eurAmountToCommision = '0';
            }

            $originalCurrencyToCommision = $converter->convert(new Cash($eurAmountToCommision, 'EUR', $transaction->getCash()->getConverter()), $transaction->getCash()->getCurrency());
            $commision = bcmul($originalCurrencyToCommision->getAmount(), self::DEFAULT_CASHOUT_NATURAL_COMMISION, 10);

            $client->addTransfer($transaction->getCash(), $transaction->getDatetime()->format('oW'));

            return new Cash($commision, $transaction->getCash()->getCurrency(), $transaction->getCash()->getConverter());
        } else {
            //no discount
            $commision = bcmul($transaction->getCash()->getAmount(), self::DEFAULT_CASHOUT_NATURAL_COMMISION, 10);

            return new Cash($commision, $transaction->getCash()->getCurrency(), $transaction->getCash()->getConverter());
        }
    }
}
