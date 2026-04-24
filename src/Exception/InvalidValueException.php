<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Exception;

class InvalidValueException extends Exception
{
    public function __construct(string $name, mixed $value)
    {
        parent::__construct(
            \sprintf('invalid value `%s` for `%s`', $this->serialize($value), $name),
        );
    }

    protected function serialize(mixed $value): string
    {
        switch (true) {
            case \is_scalar($value):
                return (string)$value;
            case \is_array($value):
                return \implode(', ', $value);
            case \is_object($value):
                return $value::class;
        }

        return \sprintf('unknown type `%s`', \gettype($value));
    }
}
