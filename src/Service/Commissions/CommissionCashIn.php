<?php

declare(strict_types=1);

namespace Service\Commissions;

use Model\Cash;
use Model\Transaction;

class CommissionCashIn implements Commission
{
    const DEFAULT_CASHIN_COMMISION = '0.0003';

    public function Calculate(Transaction $transaction): Cash
    {
        $commision = bcmul($transaction->getCash()->getAmount(), self::DEFAULT_CASHIN_COMMISION, 10);

        if (bccomp($commision, '5.00', 10) > 0) {
            //check if more than 5 eur
            $converter = $transaction->getCash()->getConverter();
            $newAmount = $converter->convert(new Cash('5.00', 'EUR', $transaction->getCash()->getConverter()), $transaction->getCash()->getCurrency())->getAmount();

            return new Cash($newAmount, $transaction->getCash()->getCurrency(), $transaction->getCash()->getConverter());
        }

        return new Cash($commision, $transaction->getCash()->getCurrency(), $transaction->getCash()->getConverter());
    }
}
