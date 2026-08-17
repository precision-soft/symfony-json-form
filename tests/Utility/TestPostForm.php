<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Utility;

use Symfony\Component\HttpFoundation\Request;

/** named, not anonymous: `getName()` derives the form name from the class short name and has to match the payload key */
class TestPostForm extends TestForm
{
    protected function getMethod(): string
    {
        return Request::METHOD_POST;
    }
}
