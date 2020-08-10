<?php

declare(strict_types=1);

namespace Service\Commissions;

use Model\Cash;
use Model\Transaction;
use Service\CurrencyConverter;

class CommissionCashOutLeg implements Commission
{
    const DEFAULT_CASHOUT_LEGAL_COMMISION = '0.003';

    public function Calculate(Transaction $transaction): Cash
    {
        $commision = bcmul($transaction->getCash()->getAmount(), self::DEFAULT_CASHOUT_LEGAL_COMMISION, 10);

        if (bccomp($commision, '0.50', 10) < 0) {
            $converter = new CurrencyConverter();
            $newAmount = $converter->convert(new Cash('0.50', 'EUR'), $transaction->getCash()->getCurrency())->getAmount();

            return new Cash($newAmount, $transaction->getCash()->getCurrency());
        }

        return new Cash($commision, $transaction->getCash()->getCurrency());
    }
}
