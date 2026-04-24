<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Element\Trait;

trait ReadonlyTrait
{
    protected bool $readonly = false;

    public function getReadonly(): bool
    {
        return $this->readonly;
    }

    public function setReadonly(bool $readonly): static
    {
        $this->readonly = $readonly;

        return $this;
    }
}
