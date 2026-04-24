<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Form;

class Action
{
    public function __construct(
        protected readonly string $route,
        protected readonly ?array $parameters = null,
    ) {}

    public function render(): array
    {
        return [
            'route' => $this->route,
            'parameters' => $this->parameters,
        ];
    }
}
