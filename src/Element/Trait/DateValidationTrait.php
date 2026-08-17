<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Element\Trait;

use DateTime;
use PrecisionSoft\Symfony\JsonForm\Exception\InvalidValueException;

trait DateValidationTrait
{
    protected function validateBounds(): void
    {
        foreach (['min' => $this->min, 'max' => $this->max] as $bound => $value) {
            if (null === $value) {
                continue;
            }

            if (false === DateTime::createFromFormat('!' . $this->format, $value)) {
                throw new InvalidValueException(
                    \sprintf('%s of `%s`', $bound, $this->getName()),
                    $value,
                );
            }
        }
    }

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
