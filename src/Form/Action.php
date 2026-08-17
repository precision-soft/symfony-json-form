<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Form;

class Action
{
    /** @param array<string, mixed>|null $parameters */
    public function __construct(
        protected readonly string $route,
        protected readonly ?array $parameters = null,
    ) {}

    /** @return array<string, mixed> */
    public function render(): array
    {
        return [
            'route' => $this->route,
            'parameters' => $this->parameters,
        ];
    }
}
