<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Element;

use PHPUnit\Framework\TestCase;
use PrecisionSoft\Symfony\JsonForm\Element\PrototypeCollectionElement;
use PrecisionSoft\Symfony\JsonForm\Element\StringElement;
use PrecisionSoft\Symfony\JsonForm\Exception\InvalidValueException;

/**
 * @internal
 */
final class PrototypeCollectionElementTest extends TestCase
{
    public function testRenderWithNullValueExposesPrototypeOnly(): void
    {
        $prototypeCollectionElement = new PrototypeCollectionElement('items', 'label');
        $prototypeCollectionElement->addElement(new StringElement('name', 'Name'));

        $result = $prototypeCollectionElement->render(null);

        static::assertSame('prototypeCollection', $result['type']);
        static::assertSame('items', $result['key']);
        static::assertSame([], $result['elements']);
        static::assertArrayHasKey('name', $result['prototype']);
    }

    public function testRenderUsesExplicitKey(): void
    {
        $prototypeCollectionElement = new PrototypeCollectionElement('items', 'label', 'customKey');
        $prototypeCollectionElement->addElement(new StringElement('name', 'Name'));

        $result = $prototypeCollectionElement->render(null);

        static::assertSame('customKey', $result['key']);
    }

    public function testRenderPropagatesItemValues(): void
    {
        $prototypeCollectionElement = new PrototypeCollectionElement('items', 'label');
        $prototypeCollectionElement->addElement(new StringElement('name', 'Name'));

        $result = $prototypeCollectionElement->render([
            'first' => ['name' => 'Alice'],
            'second' => ['name' => 'Bob'],
        ]);

        static::assertSame('Alice', $result['elements']['first']['name']['value']);
        static::assertSame('Bob', $result['elements']['second']['name']['value']);
    }

    public function testRenderWithScalarValueThrowsException(): void
    {
        $prototypeCollectionElement = new PrototypeCollectionElement('items', 'label');
        $prototypeCollectionElement->addElement(new StringElement('name', 'Name'));

        static::expectException(InvalidValueException::class);

        $prototypeCollectionElement->render('not-an-array');
    }

    public function testRenderWithScalarItemThrowsException(): void
    {
        $prototypeCollectionElement = new PrototypeCollectionElement('items', 'label');
        $prototypeCollectionElement->addElement(new StringElement('name', 'Name'));

        static::expectException(InvalidValueException::class);

        $prototypeCollectionElement->render(['first' => 'not-an-array']);
    }
}
