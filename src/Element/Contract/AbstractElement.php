<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Element\Contract;

use PrecisionSoft\Symfony\JsonForm\Exception\Exception;

abstract class AbstractElement
{
    public function __construct(
        protected readonly string $name,
        protected readonly ?string $label,
    ) {}

    abstract protected function getType(): string;

    abstract protected function renderElement(mixed $value): array;

    public function getName(): string
    {
        return $this->name;
    }

    public function render(mixed $value): array
    {
        if (false === \ctype_alnum($this->name)) {
            throw new Exception(
                \sprintf('invalid element name `%s`', $this->name),
            );
        }

        return [
            'type' => $this->getType(),
            'name' => $this->name,
            'label' => $this->label,
        ] + $this->renderElement($value);
    }
}
