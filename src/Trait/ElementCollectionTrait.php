<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Trait;

use PrecisionSoft\Symfony\JsonForm\Element\Contract\AbstractElement;
use PrecisionSoft\Symfony\JsonForm\Exception\Exception;

trait ElementCollectionTrait
{
    /** @var AbstractElement[] $elements */
    protected array $elements = [];

    public function addElement(AbstractElement $element): static
    {
        if (true === isset($this->elements[$element->getName()])) {
            throw new Exception(\sprintf('duplicate element name `%s`', $element->getName()));
        }

        $this->elements[$element->getName()] = $element;

        return $this;
    }

    protected function renderElements(array $value): array
    {
        $elements = [];

        foreach ($this->elements as $element) {
            $elements[$element->getName()] = $element->render($value[$element->getName()] ?? null);
        }

        return $elements;
    }
}
