<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Utility;

use DateTimeImmutable;
use PrecisionSoft\Symfony\JsonForm\Contract\DtoInterface;

class TestDateDto implements DtoInterface
{
    private DateTimeImmutable $birthDate;

    public function __construct()
    {
        $this->birthDate = new DateTimeImmutable('1990-05-17 00:00:00');
    }

    public function getBirthDate(): DateTimeImmutable
    {
        return $this->birthDate;
    }

    public function setBirthDate(DateTimeImmutable $birthDate): static
    {
        $this->birthDate = $birthDate;

        return $this;
    }
}
