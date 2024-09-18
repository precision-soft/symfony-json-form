<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Element;

use PrecisionSoft\Symfony\JsonForm\Element\Contract\AbstractElement;
use PrecisionSoft\Symfony\JsonForm\Exception\InvalidValueException;

class HiddenElement extends AbstractElement
{
    public function __construct(
        string $name,
    ) {
        parent::__construct($name, null);
    }

    protected function getType(): string
    {
        return 'hidden';
    }

    protected function renderElement(mixed $value): array
    {
        if (null !== $value && false === \is_scalar($value)) {
            throw new InvalidValueException($this->getName(), $value);
        }

        return [
            'value' => $value,
        ];
    }
}
