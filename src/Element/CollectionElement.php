<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Element;

use PrecisionSoft\Symfony\JsonForm\Element\Contract\AbstractElement;
use PrecisionSoft\Symfony\JsonForm\Exception\InvalidValueException;
use PrecisionSoft\Symfony\JsonForm\Trait\ElementCollectionTrait;

/** used for nested elements */
class CollectionElement extends AbstractElement
{
    use ElementCollectionTrait;

    public function __construct(
        string $name,
        string $label,
    ) {
        parent::__construct($name, $label);
    }

    public function getType(): string
    {
        return 'collection';
    }

    public function renderElement(mixed $value): array
    {
        if (null !== $value && false === \is_array($value)) {
            throw new InvalidValueException($this->getName(), $value);
        }

        return [
            'elements' => $this->renderElements($value ?? []),
        ];
    }
}
