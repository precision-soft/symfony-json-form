<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Utility;

use DateTime;
use PrecisionSoft\Symfony\JsonForm\Contract\DtoInterface;
use PrecisionSoft\Symfony\JsonForm\Element\DateElement;

class TestDto implements DtoInterface
{
    private array $array;
    private bool $bool;
    private string $date;
    private int $number;
    private string $string;

    public function __construct()
    {
        $this->array = ['test'];
        $this->bool = true;
        $this->date = (new DateTime())->format(DateElement::FORMAT_Y_M_D);
        $this->number = 1;
        $this->string = 'test';
    }

    public function getArray(): array
    {
        return $this->array;
    }

    public function getBool(): bool
    {
        return $this->bool;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getString(): string
    {
        return $this->string;
    }
}
