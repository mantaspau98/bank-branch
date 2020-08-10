<?php

declare(strict_types=1);

namespace Service\Commissions;

use Model\Cash;
use Model\Transaction;

interface Commission
{
    public function Calculate(Transaction $transaction): Cash;
}
