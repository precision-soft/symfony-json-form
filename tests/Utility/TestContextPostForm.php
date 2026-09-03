<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Utility;

use Symfony\Component\HttpFoundation\Request;

class TestContextPostForm extends TestContextForm
{
    protected function getMethod(): string
    {
        return Request::METHOD_POST;
    }
}
