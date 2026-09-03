<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Utility;

class TestConstructorForm extends TestForm
{
    protected function getDtoClass(): string
    {
        return TestConstructorDto::class;
    }
}
