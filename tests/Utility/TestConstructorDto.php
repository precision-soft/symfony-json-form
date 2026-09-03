<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Utility;

use PrecisionSoft\Symfony\JsonForm\Contract\DtoInterface;

/** a dto that cannot be built without arguments, so `render()` has nothing to render when no dto is given */
class TestConstructorDto implements DtoInterface
{
    public function __construct(public string $name) {}
}
