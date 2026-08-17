<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Functional;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use PrecisionSoft\Symfony\JsonForm\Test\Utility\TestDto;
use PrecisionSoft\Symfony\JsonForm\Test\Utility\TestPostForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * The round trip with nothing mocked: render a dto to json, submit that json back, denormalize it again.
 *
 * @internal
 */
#[Group('integration')]
final class FormRoundTripFunctionalTest extends TestCase
{
    public function testRenderedValuesSubmitBackIntoAnEquivalentDto(): void
    {
        $form = $this->getForm();

        /* deliberately not the default dto: comparing defaults to defaults passes even when nothing is written */
        $source = (new TestDto())
            ->setArray([])
            ->setBool(false)
            ->setDate('2021-02-28')
            ->setNumber(7)
            ->setString('submitted');

        $rendered = $form->render($source);

        $submitted = [];
        foreach (['array', 'bool', 'date', 'number', 'string'] as $name) {
            $submitted[$name] = $rendered['elements'][$name]['value'];
        }

        $dto = $form->handleRequest($this->getRequest($submitted));

        static::assertInstanceOf(TestDto::class, $dto);
        static::assertNotSame($source, $dto);
        /* the rendered empty array is dropped by `sanitizeData()`, so the new dto keeps its own default */
        static::assertSame(['test'], $dto->getArray());
        static::assertFalse($dto->getBool());
        static::assertSame('2021-02-28', $dto->getDate());
        static::assertSame(7, $dto->getNumber());
        static::assertSame('submitted', $dto->getString());
    }

    public function testAnEmptyStringClearsTheFieldOnTheDtoItPopulates(): void
    {
        $form = $this->getForm();

        $dto = new TestDto();

        $populated = $form->handleRequest($this->getRequest(['string' => '']), $dto);

        static::assertInstanceOf(TestDto::class, $populated);
        static::assertSame($dto, $populated);
        static::assertSame('', $populated->getString());
    }

    public function testAnEmptyArrayLeavesTheDtoDefaultStanding(): void
    {
        $form = $this->getForm();

        $dto = new TestDto();

        $populated = $form->handleRequest($this->getRequest(['array' => []]), $dto);

        static::assertInstanceOf(TestDto::class, $populated);
        static::assertSame(['test'], $populated->getArray());
    }

    public function testAnEmptyArrayIsSubmittedWhenSanitizingIsTurnedOff(): void
    {
        $form = $this->getForm();

        $dto = new TestDto();

        $populated = $form->handleRequest($this->getRequest(['array' => []]), $dto, false);

        static::assertInstanceOf(TestDto::class, $populated);
        static::assertSame([], $populated->getArray());
    }

    public function testAPayloadForAnotherFormIsIgnored(): void
    {
        $form = $this->getForm();

        $request = new Request(content: (string)\json_encode(['someOtherForm' => ['string' => 'other']]));

        $dto = $form->handleRequest($request, new TestDto());

        static::assertInstanceOf(TestDto::class, $dto);
        static::assertSame('test', $dto->getString());
    }

    /** @param array<string, mixed> $data */
    private function getRequest(array $data): Request
    {
        return new Request(content: (string)\json_encode(['testPostForm' => $data]));
    }

    private function getForm(): TestPostForm
    {
        $propertyInfoExtractor = new PropertyInfoExtractor([], [new PhpDocExtractor(), new ReflectionExtractor()]);

        $serializer = new Serializer(
            [
                new ArrayDenormalizer(),
                new ObjectNormalizer(null, null, null, $propertyInfoExtractor),
            ],
            [new JsonEncoder()],
        );

        $form = new TestPostForm();
        $form->setSerializer($serializer);

        return $form;
    }
}
