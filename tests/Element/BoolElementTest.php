<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Element;

use PrecisionSoft\Symfony\JsonForm\Element\BoolElement;
use PrecisionSoft\Symfony\JsonForm\Exception\InvalidValueException;
use PrecisionSoft\Symfony\Phpunit\MockDto;
use PrecisionSoft\Symfony\Phpunit\TestCase\AbstractTestCase;

/**
 * @internal
 */
final class BoolElementTest extends AbstractTestCase
{
    public static function getMockDto(): MockDto
    {
        return new MockDto(BoolElement::class);
    }

    public function testRenderWithNullValue(): void
    {
        $boolElement = new BoolElement('active', 'label');

        $result = $boolElement->render(null);

        static::assertNull($result['value']);
    }

    public function testRenderWithTrue(): void
    {
        $boolElement = new BoolElement('active', 'label');

        $result = $boolElement->render(true);

        static::assertTrue($result['value']);
    }

    public function testRenderWithFalse(): void
    {
        $boolElement = new BoolElement('active', 'label');

        $result = $boolElement->render(false);

        static::assertFalse($result['value']);
    }

    public function testRenderStructure(): void
    {
        $boolElement = new BoolElement('active', 'label');

        $result = $boolElement->render(true);

        static::assertSame('bool', $result['type']);
        static::assertSame('active', $result['name']);
        static::assertSame('label', $result['label']);
        static::assertFalse($result['readonly']);
        static::assertFalse($result['required']);
    }

    public function testRenderWithStringThrowsException(): void
    {
        $boolElement = new BoolElement('active', 'label');

        static::expectException(InvalidValueException::class);

        $boolElement->render('true');
    }

    public function testRenderWithIntegerThrowsException(): void
    {
        $boolElement = new BoolElement('active', 'label');

        static::expectException(InvalidValueException::class);

        $boolElement->render(1);
    }
}
