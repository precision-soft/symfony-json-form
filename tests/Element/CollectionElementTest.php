<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Element;

use PHPUnit\Framework\TestCase;
use PrecisionSoft\Symfony\JsonForm\Element\CollectionElement;
use PrecisionSoft\Symfony\JsonForm\Element\StringElement;
use PrecisionSoft\Symfony\JsonForm\Exception\InvalidValueException;

/**
 * @internal
 */
final class CollectionElementTest extends TestCase
{
    public function testRenderWithNullValueUsesEmptyCollection(): void
    {
        $collectionElement = new CollectionElement('address', 'label');
        $collectionElement->addElement(new StringElement('street', 'Street'));

        $result = $collectionElement->render(null);

        static::assertSame('collection', $result['type']);
        static::assertArrayHasKey('street', $result['elements']);
        static::assertNull($result['elements']['street']['value']);
    }

    public function testRenderPropagatesChildValues(): void
    {
        $collectionElement = new CollectionElement('address', 'label');
        $collectionElement->addElement(new StringElement('street', 'Street'));
        $collectionElement->addElement(new StringElement('city', 'City'));

        $result = $collectionElement->render(['street' => 'Main', 'city' => 'Paris']);

        static::assertSame('Main', $result['elements']['street']['value']);
        static::assertSame('Paris', $result['elements']['city']['value']);
    }

    public function testRenderWithScalarValueThrowsException(): void
    {
        $collectionElement = new CollectionElement('address', 'label');
        $collectionElement->addElement(new StringElement('street', 'Street'));

        static::expectException(InvalidValueException::class);

        $collectionElement->render('not-an-array');
    }
}
