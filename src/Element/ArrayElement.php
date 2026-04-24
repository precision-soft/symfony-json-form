<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Element;

use PrecisionSoft\Symfony\JsonForm\Element\Contract\AbstractElement;
use PrecisionSoft\Symfony\JsonForm\Element\Trait\ReadonlyTrait;
use PrecisionSoft\Symfony\JsonForm\Element\Trait\RequiredTrait;
use PrecisionSoft\Symfony\JsonForm\Exception\InvalidModeException;
use PrecisionSoft\Symfony\JsonForm\Exception\InvalidValueException;

class ArrayElement extends AbstractElement
{
    use ReadonlyTrait;
    use RequiredTrait;

    public const MODE_SINGLE = 'single';
    public const MODE_MULTIPLE = 'multiple';

    public const MODES = [
        self::MODE_SINGLE,
        self::MODE_MULTIPLE,
    ];

    public function __construct(
        string $name,
        string $label,
        protected readonly array $options,
        protected readonly string $mode = self::MODE_SINGLE,
    ) {
        parent::__construct($name, $label);

        if (false === \in_array($this->mode, static::MODES, true)) {
            throw new InvalidModeException($name, $this->mode, static::MODES);
        }
    }

    protected function getType(): string
    {
        return 'array';
    }

    protected function renderElement(mixed $value): array
    {
        if (null !== $value) {
            $value = (array)$value;

            $diff = \array_diff($value, $this->getOptionsValues());

            if (false === empty($diff)) {
                throw new InvalidValueException($this->getName(), $diff);
            }
        }

        return [
            'options' => $this->options,
            'mode' => $this->mode,
            'readonly' => $this->readonly,
            'required' => $this->required,
            'value' => $value,
        ];
    }

    protected function getOptionsValues(): array
    {
        $optionsValues = [];

        foreach ($this->options as $key => $value) {
            if (true === \is_array($value)) {
                $optionsValues = \array_merge($optionsValues, \array_keys($value));
                continue;
            }

            $optionsValues[] = $key;
        }

        return $optionsValues;
    }
}
