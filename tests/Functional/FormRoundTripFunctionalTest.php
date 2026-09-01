<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Functional;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use PrecisionSoft\Symfony\JsonForm\Test\Utility\TestDateDto;
use PrecisionSoft\Symfony\JsonForm\Test\Utility\TestDatePostForm;
use PrecisionSoft\Symfony\JsonForm\Test\Utility\TestDateTimeDto;
use PrecisionSoft\Symfony\JsonForm\Test\Utility\TestDateTimePostForm;
use PrecisionSoft\Symfony\JsonForm\Test\Utility\TestDto;
use PrecisionSoft\Symfony\JsonForm\Test\Utility\TestPostForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
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

        $dto = $form->handleRequest($this->getRequest('testPostForm', $submitted));

        static::assertInstanceOf(TestDto::class, $dto);
        static::assertNotSame($source, $dto);
        static::assertSame(['test'], $dto->getArray());
        static::assertFalse($dto->getBool());
        static::assertSame('2021-02-28', $dto->getDate());
        static::assertSame(7, $dto->getNumber());
        static::assertSame('submitted', $dto->getString());
    }

    public function testDateTimeValueRoundTripsThroughTheConfiguredWireFormat(): void
    {
        $form = (new TestDateTimePostForm())->setSerializer($this->getSerializer());

        $source = new TestDateTimeDto();
        $rendered = $form->render($source);

        static::assertSame('2026-08-30 14:25', $rendered['elements']['scheduledAt']['value']);

        $dto = $form->handleRequest($this->getRequest('testDateTimePostForm', ['scheduledAt' => '2027-01-02 03:04']));

        static::assertInstanceOf(TestDateTimeDto::class, $dto);
        static::assertSame('2027-01-02 03:04:00.000000', $dto->getScheduledAt()->format('Y-m-d H:i:s.u'));
    }

    public function testADateOnlyWireFormatDenormalizesToMidnightRatherThanTheCurrentClock(): void
    {
        $form = (new TestDatePostForm())->setSerializer($this->getSerializer());

        $rendered = $form->render(new TestDateDto());

        static::assertSame('1990-05-17', $rendered['elements']['birthDate']['value']);

        $dto = $form->handleRequest($this->getRequest('testDatePostForm', ['birthDate' => '2001-09-11']));

        static::assertInstanceOf(TestDateDto::class, $dto);
        static::assertSame('2001-09-11 00:00:00.000000', $dto->getBirthDate()->format('Y-m-d H:i:s.u'));
    }

    public function testAnEmptyStringClearsTheFieldOnTheDtoItPopulates(): void
    {
        $form = $this->getForm();

        $dto = new TestDto();

        $populated = $form->handleRequest($this->getRequest('testPostForm', ['string' => '']), $dto);

        static::assertInstanceOf(TestDto::class, $populated);
        static::assertSame($dto, $populated);
        static::assertSame('', $populated->getString());
    }

    public function testAnEmptyArrayLeavesTheDtoDefaultStanding(): void
    {
        $form = $this->getForm();

        $dto = new TestDto();

        $populated = $form->handleRequest($this->getRequest('testPostForm', ['array' => []]), $dto);

        static::assertInstanceOf(TestDto::class, $populated);
        static::assertSame(['test'], $populated->getArray());
    }

    public function testAnEmptyArrayIsSubmittedWhenSanitizingIsTurnedOff(): void
    {
        $form = $this->getForm();

        $dto = new TestDto();

        $populated = $form->handleRequest($this->getRequest('testPostForm', ['array' => []]), $dto, false);

        static::assertInstanceOf(TestDto::class, $populated);
        static::assertSame([], $populated->getArray());
    }

    public function testAPayloadForAnotherFormIsIgnored(): void
    {
        $form = $this->getForm();

        $dto = $form->handleRequest($this->getRequest('someOtherForm', ['string' => 'other']), new TestDto());

        static::assertInstanceOf(TestDto::class, $dto);
        static::assertSame('test', $dto->getString());
    }

    /** @param array<string, mixed> $data */
    private function getRequest(string $formName, array $data): Request
    {
        return new Request(content: (string)\json_encode([$formName => $data]));
    }

    private function getForm(): TestPostForm
    {
        return (new TestPostForm())->setSerializer($this->getSerializer());
    }

    private function getSerializer(): Serializer
    {
        $propertyInfoExtractor = new PropertyInfoExtractor([], [new PhpDocExtractor(), new ReflectionExtractor()]);

        return new Serializer(
            [
                new DateTimeNormalizer(),
                new ArrayDenormalizer(),
                new ObjectNormalizer(null, null, null, $propertyInfoExtractor),
            ],
            [new JsonEncoder()],
        );
    }
}
