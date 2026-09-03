<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Example\Test\Utility;

use Mockery\MockInterface;
use PrecisionSoft\Symfony\JsonForm\Example\Service\CatalogOptionProvider;
use PrecisionSoft\Symfony\Phpunit\Contract\MockDtoInterface;
use PrecisionSoft\Symfony\Phpunit\MockDto;

/** the nomenclator double every form test shares; the defaults yield to an explicit expectation set by a test */
class CatalogOptionProviderMock implements MockDtoInterface
{
    public const CURRENCY_OPTIONS = ['EUR' => 'Euro', 'RON' => 'Romanian leu'];
    public const CHANNEL_OPTIONS = ['web' => 'Web shop', 'store' => 'Store'];

    public static function getMockDto(): MockDto
    {
        return new MockDto(
            CatalogOptionProvider::class,
            null,
            false,
            static function (MockInterface $mockInterface): void {
                $mockInterface->shouldReceive('getCurrencyOptions')->byDefault()->andReturn(static::CURRENCY_OPTIONS);
                $mockInterface->shouldReceive('getChannelOptions')->byDefault()->andReturn(static::CHANNEL_OPTIONS);
            },
        );
    }
}
