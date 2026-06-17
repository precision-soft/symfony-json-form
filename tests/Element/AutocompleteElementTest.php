<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Element;

use PHPUnit\Framework\TestCase;
use PrecisionSoft\Symfony\JsonForm\Element\AutocompleteElement;
use PrecisionSoft\Symfony\JsonForm\Exception\InvalidModeException;

/**
 * @internal
 */
final class AutocompleteElementTest extends TestCase
{
    public function testRenderWithNullValue(): void
    {
        $autocompleteElement = new AutocompleteElement('city', 'label', 'city_search');

        $result = $autocompleteElement->render(null);

        static::assertNull($result['value']);
    }

    public function testRenderStructure(): void
    {
        $autocompleteElement = new AutocompleteElement('city', 'label', 'city_search');

        $result = $autocompleteElement->render(null);

        static::assertSame('autocomplete', $result['type']);
        static::assertSame('city', $result['name']);
        static::assertSame('label', $result['label']);
        static::assertSame('city_search', $result['route']);
        static::assertSame('query', $result['parameter']);
        static::assertSame(AutocompleteElement::MODE_SINGLE, $result['mode']);
        static::assertFalse($result['readonly']);
        static::assertFalse($result['required']);
    }

    public function testRenderCastsScalarValueToArray(): void
    {
        $autocompleteElement = new AutocompleteElement('city', 'label', 'city_search');

        $result = $autocompleteElement->render('paris');

        static::assertSame(['paris'], $result['value']);
    }

    public function testRenderKeepsArrayValueForMultipleMode(): void
    {
        $autocompleteElement = new AutocompleteElement(
            'cities',
            'label',
            'city_search',
            AutocompleteElement::MODE_MULTIPLE,
            'term',
        );

        $result = $autocompleteElement->render(['paris', 'london']);

        static::assertSame(['paris', 'london'], $result['value']);
        static::assertSame('term', $result['parameter']);
        static::assertSame(AutocompleteElement::MODE_MULTIPLE, $result['mode']);
    }

    public function testConstructWithInvalidModeThrowsException(): void
    {
        static::expectException(InvalidModeException::class);

        new AutocompleteElement('city', 'label', 'city_search', 'invalid');
    }
}
