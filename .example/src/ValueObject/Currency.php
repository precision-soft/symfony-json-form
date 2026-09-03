<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Example\ValueObject;

use PrecisionSoft\Symfony\JsonForm\Example\Exception\Exception;

/** an ISO 4217 code the serializer cannot build on its own: the form hands it a normalizer through the context hooks */
class Currency
{
    public function __construct(protected readonly string $code)
    {
        if (1 !== \preg_match('/^[A-Z]{3}$/', $code)) {
            throw new Exception(\sprintf('invalid currency code `%s`', $code));
        }
    }

    public function getCode(): string
    {
        return $this->code;
    }
}
