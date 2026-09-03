<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Example\Test\Functional;

use PrecisionSoft\Symfony\JsonForm\Example\Dto\ProductEditDto;
use PrecisionSoft\Symfony\JsonForm\Example\Dto\UserRegistrationDto;
use PrecisionSoft\Symfony\JsonForm\Example\Form\ProductEditForm;
use PrecisionSoft\Symfony\JsonForm\Example\Serializer\CatalogSerializerFactory;
use PrecisionSoft\Symfony\JsonForm\Example\Service\CatalogOptionProvider;
use PrecisionSoft\Symfony\JsonForm\Example\Test\Utility\CatalogOptionProviderMock;
use PrecisionSoft\Symfony\JsonForm\Example\Test\Utility\ProductEditDraftDto;
use PrecisionSoft\Symfony\JsonForm\Example\Test\Utility\ProductFixture;
use PrecisionSoft\Symfony\JsonForm\Exception\Exception;
use PrecisionSoft\Symfony\JsonForm\Exception\InvalidValueException;
use PrecisionSoft\Symfony\Phpunit\MockDto;
use PrecisionSoft\Symfony\Phpunit\TestCase\AbstractTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
final class ProductEditFormTest extends AbstractTestCase
{
    public const FIXTURE = __DIR__ . '/../Fixture/product-edit-form.json';

    public static function getMockDto(): MockDto
    {
        return CatalogOptionProviderMock::getMockDto();
    }

    public function testRenderIsTheCommittedFixtureTheReactHalfConsumes(): void
    {
        $this->get(CatalogOptionProvider::class)->shouldReceive('getCurrencyOptions')->once()
            ->andReturn(CatalogOptionProviderMock::CURRENCY_OPTIONS);

        $rendered = $this->getForm()->render(ProductFixture::createProduct());

        static::assertSame(\json_decode((string)\file_get_contents(static::FIXTURE), true), $rendered);
    }

    public function testAFileElementNeverRendersTheStoredValueBack(): void
    {
        $form = $this->getForm();
        $product = ProductFixture::createProduct()->setImage('desk-lamp.png');

        static::expectException(InvalidValueException::class);

        $form->render($product);
    }

    public function testASubmittedProductRoundTripsIntoATypedDto(): void
    {
        $dto = $this->getForm()->handleRequest($this->getRequest(ProductFixture::PAYLOAD));

        static::assertInstanceOf(ProductEditDto::class, $dto);
        static::assertSame(7, $dto->getId());
        static::assertSame('Desk lamp', $dto->getName());
        static::assertSame(149.9, $dto->getPrice());
        static::assertSame('RON', $dto->getCurrency()->getCode());
        static::assertSame(['web', 'store'], $dto->getChannels());
        static::assertSame(3, $dto->getCategoryId());
        static::assertTrue($dto->getActive());
        static::assertSame('2026-09-01', $dto->getAvailableFrom());
        static::assertSame('2026-08-30 14:25:00.000000', $dto->getPublishedAt()?->format('Y-m-d H:i:s.u'));
        static::assertSame(40.0, $dto->getDimensions()->getWidth());
        static::assertSame(55.0, $dto->getDimensions()->getHeight());
        static::assertSame(20.0, $dto->getDimensions()->getDepth());
        static::assertSame(ProductFixture::PAYLOAD['prices'], $dto->getPrices());
        static::assertSame('desk-lamp.png', $dto->getImage());
    }

    public function testAPatchPopulatesTheProductInPlace(): void
    {
        $product = ProductFixture::createProduct();

        $dto = $this->getForm()->handleRequest($this->getRequest(['price' => 99.5]), $product);

        static::assertSame($product, $dto);
        static::assertSame(99.5, $product->getPrice());
        static::assertSame('Desk lamp', $product->getName());
    }

    public function testAnEmptyStringClearsAFieldAndAnEmptyListKeepsTheDefault(): void
    {
        $product = ProductFixture::createProduct();

        $this->getForm()->handleRequest($this->getRequest(['name' => '', 'channels' => []]), $product);

        static::assertSame('', $product->getName());
        static::assertSame(['web', 'store'], $product->getChannels());
    }

    public function testADraftIsPopulatedInPlaceAsAProduct(): void
    {
        $draft = (new ProductEditDraftDto())->setName('before');

        $dto = $this->getForm()->handleRequest($this->getRequest(['name' => 'after']), $draft);

        static::assertSame($draft, $dto);
        static::assertSame('after', $draft->getName());
        static::assertSame(['id' => null], $this->getForm()->render($draft)['action']['parameters']);
    }

    public function testAnotherFormsDtoIsRefusedWithTheContext(): void
    {
        try {
            $this->getForm()->handleRequest($this->getRequest(['name' => 'x']), new UserRegistrationDto());

            static::fail('handleRequest was expected to throw');
        } catch (Exception $exception) {
            static::assertSame('invalid dto class for form `productEditForm`', $exception->getMessage());
            static::assertSame(
                [
                    'formName' => 'productEditForm',
                    'dtoClass' => UserRegistrationDto::class,
                    'expectedDtoClass' => ProductEditDto::class,
                ],
                $exception->getContext(),
            );
        }
    }

    public function testACurrencyOutsideTheNomenclatorIsRefusedByTheNormalizer(): void
    {
        $form = $this->getForm();

        static::expectException(InvalidValueException::class);

        $form->handleRequest($this->getRequest(['currency' => 'USD']));
    }

    public function testAPriceBelowZeroCannotBeRendered(): void
    {
        $form = $this->getForm();
        $product = ProductFixture::createProduct()->setPrice(-1.0);

        static::expectException(InvalidValueException::class);

        $form->render($product);
    }

    public function testAPayloadForAnotherFormLeavesTheDefaults(): void
    {
        $request = new Request(
            server: ['REQUEST_METHOD' => Request::METHOD_POST],
            content: (string)\json_encode(['userRegistrationForm' => ['email' => 'a@b.c']]),
        );

        $dto = $this->getForm()->handleRequest($request);

        static::assertEquals(new ProductEditDto(), $dto);
    }

    private function getForm(): ProductEditForm
    {
        return (new ProductEditForm($this->get(CatalogOptionProvider::class)))
            ->setSerializer(CatalogSerializerFactory::create());
    }

    /** @param array<string, mixed> $data */
    private function getRequest(array $data): Request
    {
        return new Request(
            server: ['REQUEST_METHOD' => Request::METHOD_POST],
            content: (string)\json_encode(['productEditForm' => $data]),
        );
    }
}
