<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Element;

use PrecisionSoft\Symfony\JsonForm\Element\Contract\AbstractElement;
use PrecisionSoft\Symfony\JsonForm\Element\Trait\ReadonlyTrait;
use PrecisionSoft\Symfony\JsonForm\Element\Trait\RequiredTrait;
use PrecisionSoft\Symfony\JsonForm\Exception\InvalidValueException;

class BoolElement extends AbstractElement
{
    use ReadonlyTrait;
    use RequiredTrait;

    public function __construct(
        string $name,
        string $label,
    ) {
        parent::__construct($name, $label);
    }

    protected function getType(): string
    {
        return 'bool';
    }

    protected function renderElement(mixed $value): array
    {
        if (null !== $value && false === \is_bool($value)) {
            throw new InvalidValueException($this->getName(), $value);
        }

        return [
            'readonly' => $this->readonly,
            'required' => $this->required,
            'value' => $value,
        ];
    }
}
