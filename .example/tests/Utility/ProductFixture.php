<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Example\Test\Utility;

use DateTimeImmutable;
use PrecisionSoft\Symfony\JsonForm\Example\Dto\ProductDimensionsDto;
use PrecisionSoft\Symfony\JsonForm\Example\Dto\ProductEditDto;
use PrecisionSoft\Symfony\JsonForm\Example\ValueObject\Currency;

class ProductFixture
{
    public const PAYLOAD = [
        'id' => 7,
        'name' => 'Desk lamp',
        'price' => 149.9,
        'currency' => 'RON',
        'channels' => ['web', 'store'],
        'categoryId' => 3,
        'active' => true,
        'availableFrom' => '2026-09-01',
        'publishedAt' => '2026-08-30 14:25',
        'dimensions' => ['width' => 40, 'height' => 55, 'depth' => 20],
        'prices' => [['currency' => 'EUR', 'amount' => 30.5], ['currency' => 'RON', 'amount' => 149.9]],
        'image' => 'desk-lamp.png',
    ];

    public static function createProduct(): ProductEditDto
    {
        return (new ProductEditDto())
            ->setId(7)
            ->setName('Desk lamp')
            ->setPrice(149.9)
            ->setCurrency(new Currency('RON'))
            ->setChannels(['web', 'store'])
            ->setCategoryId(3)
            ->setActive(true)
            ->setAvailableFrom('2026-09-01')
            ->setPublishedAt(new DateTimeImmutable('2026-08-30 14:25:00'))
            ->setDimensions((new ProductDimensionsDto())->setWidth(40.0)->setHeight(55.0)->setDepth(20.0))
            ->setPrices([['currency' => 'EUR', 'amount' => 30.5], ['currency' => 'RON', 'amount' => 149.9]]);
    }
}
