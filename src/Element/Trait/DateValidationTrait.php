<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Element\Trait;

use DateTime;

trait DateValidationTrait
{
    protected function isValidDate(mixed $value): bool
    {
        if (false === \is_string($value)) {
            return false;
        }

        $date = DateTime::createFromFormat('!' . $this->format, $value);

        if (false === $date || $date->format($this->format) !== $value) {
            return false;
        }

        return $this->isWithinRange($date);
    }

    protected function isWithinRange(DateTime $date): bool
    {
        if (null !== $this->min) {
            $min = DateTime::createFromFormat('!' . $this->format, $this->min);

            if (false !== $min && $date < $min) {
                return false;
            }
        }

        if (null !== $this->max) {
            $max = DateTime::createFromFormat('!' . $this->format, $this->max);

            if (false !== $max && $date > $max) {
                return false;
            }
        }

        return true;
    }
}
