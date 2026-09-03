<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Example\Test\Functional;

use PrecisionSoft\Symfony\JsonForm\Example\Dto\ProductSearchDto;
use PrecisionSoft\Symfony\JsonForm\Example\Form\ProductEditForm;
use PrecisionSoft\Symfony\JsonForm\Example\Form\ProductSearchForm;
use PrecisionSoft\Symfony\JsonForm\Example\Serializer\CatalogSerializerFactory;
use PrecisionSoft\Symfony\Phpunit\MockDto;
use PrecisionSoft\Symfony\Phpunit\TestCase\AbstractTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
final class ProductSearchFormTest extends AbstractTestCase
{
    public static function getMockDto(): MockDto
    {
        return new MockDto(ProductSearchForm::class);
    }

    public function testTheQueryStringIsTypedIntoTheDto(): void
    {
        $request = new Request(
            query: ['productSearchForm' => ['query' => 'lamp', 'categoryId' => '3', 'activeOnly' => '1', 'page' => '2']],
        );

        $dto = $this->getForm()->handleRequest($request);

        static::assertInstanceOf(ProductSearchDto::class, $dto);
        static::assertSame('lamp', $dto->getQuery());
        static::assertSame(3, $dto->getCategoryId());
        static::assertTrue($dto->getActiveOnly());
        static::assertSame(2, $dto->getPage());
    }

    public function testRenderDescribesTheSearchForTheFrontend(): void
    {
        $rendered = $this->getForm()->render();

        static::assertSame('productSearchForm', $rendered['name']);
        static::assertSame(Request::METHOD_GET, $rendered['method']);
        static::assertSame(['route' => 'catalogue-product-search', 'parameters' => null], $rendered['action']);
        static::assertSame(['query', 'categoryId', 'activeOnly', 'page'], \array_keys($rendered['elements']));
        static::assertSame(ProductEditForm::CATEGORY_ROUTE, $rendered['elements']['categoryId']['route']);
        static::assertSame(1, $rendered['elements']['page']['value']);
        static::assertFalse($rendered['elements']['activeOnly']['value']);
    }

    private function getForm(): ProductSearchForm
    {
        return (new ProductSearchForm())->setSerializer(CatalogSerializerFactory::create());
    }
}
