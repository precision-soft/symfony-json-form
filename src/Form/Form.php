<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Form;

use PrecisionSoft\Symfony\JsonForm\Trait\ElementCollectionTrait;

class Form
{
    use ElementCollectionTrait;

    public function __construct(
        protected readonly string $name,
        protected readonly string $method,
        protected readonly Action $action,
    ) {}

    public function render(array $data): array
    {
        return [
            'name' => $this->name,
            'method' => $this->method,
            'action' => $this->action->render(),
            'elements' => $this->renderElements($data),
        ];
    }
}
