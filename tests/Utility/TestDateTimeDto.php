<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Utility;

use DateTimeImmutable;
use PrecisionSoft\Symfony\JsonForm\Contract\DtoInterface;

class TestDateTimeDto implements DtoInterface
{
    private DateTimeImmutable $scheduledAt;

    public function __construct()
    {
        $this->scheduledAt = new DateTimeImmutable('2026-08-30 14:25:00');
    }

    public function getScheduledAt(): DateTimeImmutable
    {
        return $this->scheduledAt;
    }

    public function setScheduledAt(DateTimeImmutable $scheduledAt): static
    {
        $this->scheduledAt = $scheduledAt;

        return $this;
    }
}
