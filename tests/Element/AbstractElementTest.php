<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Element;

use PHPUnit\Framework\TestCase;
use PrecisionSoft\Symfony\JsonForm\Element\LabelElement;
use PrecisionSoft\Symfony\JsonForm\Element\StringElement;
use PrecisionSoft\Symfony\JsonForm\Exception\Exception;

/**
 * @internal
 */
final class AbstractElementTest extends TestCase
{
    public function testGetName(): void
    {
        $stringElement = new StringElement('myfield', 'label');

        static::assertSame('myfield', $stringElement->getName());
    }

    public function testRenderThrowsExceptionForNameWithSpace(): void
    {
        $stringElement = new StringElement('my field', 'label');

        static::expectException(Exception::class);
        static::expectExceptionMessageMatches('/invalid element name/');

        $stringElement->render(null);
    }

    public function testRenderThrowsExceptionForNameWithHyphen(): void
    {
        $stringElement = new StringElement('my-field', 'label');

        static::expectException(Exception::class);

        $stringElement->render(null);
    }

    public function testRenderThrowsExceptionForNameWithUnderscore(): void
    {
        $stringElement = new StringElement('my_field', 'label');

        static::expectException(Exception::class);

        $stringElement->render(null);
    }

    public function testRenderIncludesTypeNameLabel(): void
    {
        $stringElement = new StringElement('title', 'Title');

        $result = $stringElement->render('hello');

        static::assertArrayHasKey('type', $result);
        static::assertArrayHasKey('name', $result);
        static::assertArrayHasKey('label', $result);
        static::assertSame('title', $result['name']);
        static::assertSame('Title', $result['label']);
    }

    public function testRenderWithNullLabel(): void
    {
        $labelElement = new LabelElement('info');

        $result = $labelElement->render(null);

        static::assertNull($result['label']);
    }
}
