<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Utility;

use PrecisionSoft\Symfony\JsonForm\Exception\InvalidValueException;

/** a value object the serializer cannot build by itself: the form hands it a normalizer through the context hooks */
class Currency
{
    public function __construct(private readonly string $code)
    {
        if (1 !== \preg_match('/^[A-Z]{3}$/', $code)) {
            throw new InvalidValueException('currency', $code);
        }
    }

    public function getCode(): string
    {
        return $this->code;
    }
}
