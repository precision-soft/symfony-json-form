<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Element;

use PrecisionSoft\Symfony\JsonForm\Element\Contract\AbstractElement;
use PrecisionSoft\Symfony\JsonForm\Exception\InvalidValueException;
use PrecisionSoft\Symfony\JsonForm\Trait\ElementCollectionTrait;

class PrototypeCollectionElement extends AbstractElement
{
    use ElementCollectionTrait;

    public function __construct(
        string $name,
        string $label,
        protected readonly ?string $key = null,
    ) {
        parent::__construct($name, $label);
    }

    protected function getType(): string
    {
        return 'prototypeCollection';
    }

    protected function renderElement(mixed $value): array
    {
        if (null !== $value && false === \is_array($value)) {
            throw new InvalidValueException($this->getName(), $value);
        }

        $elements = [];

        foreach (($value ?? []) as $key => $itemData) {
            $elements[$key] = $this->renderElements($itemData);
        }

        return [
            'key' => $this->key ?? $this->getName(),
            'elements' => $elements,
            'prototype' => $this->renderElements([]),
        ];
    }
}
