<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientSmsCreditsException extends RuntimeException
{
    public function __construct(
        public readonly int $required,
        public readonly int $available,
    ) {
        parent::__construct("Insufficient SMS credits: {$required} required, {$available} available.");
    }
}
