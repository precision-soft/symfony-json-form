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

class NumberElement extends AbstractElement
{
    use ReadonlyTrait;
    use RequiredTrait;

    public function __construct(
        string $name,
        string $label,
        protected readonly ?float $min = null,
        protected readonly ?float $max = null,
        protected readonly ?float $step = null,
    ) {
        parent::__construct($name, $label);

        if (null !== $this->min && null !== $this->max && $this->min > $this->max) {
            throw new InvalidValueException(
                \sprintf('min of `%s`', $name),
                $this->min,
            );
        }
    }

    protected function getType(): string
    {
        return 'number';
    }

    /** @return array<string, mixed> */
    protected function renderElement(mixed $value): array
    {
        if (null !== $value) {
            if (false === \is_numeric($value)) {
                throw new InvalidValueException($this->getName(), $value);
            }

            if (
                (null !== $this->min && (float)$value < $this->min)
                || (null !== $this->max && (float)$value > $this->max)
            ) {
                throw new InvalidValueException($this->getName(), $value);
            }
        }

        return [
            'min' => $this->min,
            'max' => $this->max,
            'step' => $this->step,
            'readonly' => $this->readonly,
            'required' => $this->required,
            'value' => $value,
        ];
    }
}
