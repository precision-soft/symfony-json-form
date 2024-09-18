<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Exception;

class InvalidModeException extends Exception
{
    public function __construct(string $name, string $mode, array $acceptedModes)
    {
        parent::__construct(
            \sprintf('invalid mode `%s` for `%s` element; accepted: `%s`', $mode, $name, \implode(', ', $acceptedModes)),
        );
    }
}
