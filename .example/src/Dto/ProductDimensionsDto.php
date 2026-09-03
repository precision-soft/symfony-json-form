<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Example\Dto;

/** the `dimensions` collection element maps to a typed object, so a `40` sent by a javascript client lands as `40.0` */
class ProductDimensionsDto
{
    private float $width = 0.0;
    private float $height = 0.0;
    private float $depth = 0.0;

    public function getWidth(): float
    {
        return $this->width;
    }

    public function setWidth(float $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): float
    {
        return $this->height;
    }

    public function setHeight(float $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function getDepth(): float
    {
        return $this->depth;
    }

    public function setDepth(float $depth): static
    {
        $this->depth = $depth;

        return $this;
    }
}
