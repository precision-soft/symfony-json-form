<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Utility;

/** a settable request method, so the three body-carrying methods share one fixture; named for the reason `TestPostForm` is */
class TestMethodForm extends TestForm
{
    protected string $method = 'POST';

    public function setMethod(string $method): static
    {
        $this->method = $method;

        return $this;
    }

    protected function getMethod(): string
    {
        return $this->method;
    }
}
