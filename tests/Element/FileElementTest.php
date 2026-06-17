<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Element;

use PHPUnit\Framework\TestCase;
use PrecisionSoft\Symfony\JsonForm\Element\FileElement;
use PrecisionSoft\Symfony\JsonForm\Exception\InvalidValueException;

/**
 * @internal
 */
final class FileElementTest extends TestCase
{
    public function testRenderWithNullValue(): void
    {
        $fileElement = new FileElement('document', 'label');

        $result = $fileElement->render(null);

        static::assertSame('file', $result['type']);
        static::assertSame('document', $result['name']);
        static::assertSame('label', $result['label']);
        static::assertFalse($result['readonly']);
        static::assertFalse($result['required']);
    }

    public function testRenderWithReadonlyAndRequiredSet(): void
    {
        $fileElement = new FileElement('document', 'label');
        $fileElement->setReadonly(true);
        $fileElement->setRequired(true);

        $result = $fileElement->render(null);

        static::assertTrue($result['readonly']);
        static::assertTrue($result['required']);
    }

    public function testRenderWithNonNullValueThrowsException(): void
    {
        $fileElement = new FileElement('document', 'label');

        static::expectException(InvalidValueException::class);

        $fileElement->render('some-value');
    }
}
