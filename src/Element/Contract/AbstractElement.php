<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Element\Contract;

use PrecisionSoft\Symfony\JsonForm\Exception\Exception;

abstract class AbstractElement
{
    abstract protected function getType(): string;

    /** @return array<string, mixed> */
    abstract protected function renderElement(mixed $value): array;

    public function __construct(
        protected readonly string $name,
        protected readonly ?string $label,
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    /** @return array<string, mixed> */
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
