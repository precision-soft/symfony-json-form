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
    /** @var array<int, string> */
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

    /** @return array<int, string> */
    public function getArray(): array
    {
        return $this->array;
    }

    /** @param array<int, string> $array */
    public function setArray(array $array): static
    {
        $this->array = $array;

        return $this;
    }

    public function getBool(): bool
    {
        return $this->bool;
    }

    public function setBool(bool $bool): static
    {
        $this->bool = $bool;

        return $this;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function setDate(string $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function setNumber(int $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getString(): string
    {
        return $this->string;
    }

    public function setString(string $string): static
    {
        $this->string = $string;

        return $this;
    }
}
