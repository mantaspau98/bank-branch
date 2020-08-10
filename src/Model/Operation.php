<?php

declare(strict_types=1);

namespace Model;

use Exception;

class Operation
{
    private $type;
    private $availableTypes = ['cash_in', 'cash_out'];

    public function __construct(string $type)
    {
        if (!in_array($type, $this->availableTypes, true)) {
            throw new Exception('Unsupported operation type');
        }
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
