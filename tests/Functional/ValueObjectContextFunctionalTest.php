<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Functional;

use PHPUnit\Framework\Attributes\Group;
use PrecisionSoft\Symfony\JsonForm\Exception\InvalidValueException;
use PrecisionSoft\Symfony\JsonForm\Test\Utility\Currency;
use PrecisionSoft\Symfony\JsonForm\Test\Utility\CurrencyNormalizer;
use PrecisionSoft\Symfony\JsonForm\Test\Utility\SerializerFactory;
use PrecisionSoft\Symfony\JsonForm\Test\Utility\TestCurrencyDto;
use PrecisionSoft\Symfony\JsonForm\Test\Utility\TestCurrencyPostForm;
use PrecisionSoft\Symfony\Phpunit\MockDto;
use PrecisionSoft\Symfony\Phpunit\TestCase\AbstractTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * A real serializer builds a value object the form describes only through its context hooks.
 *
 * @internal
 */
#[Group('integration')]
final class ValueObjectContextFunctionalTest extends AbstractTestCase
{
    public static function getMockDto(): MockDto
    {
        return new MockDto(TestCurrencyPostForm::class);
    }

    public function testTheValueObjectRendersAsItsCode(): void
    {
        $rendered = $this->getForm()->render((new TestCurrencyDto())->setCurrency(new Currency('RON')));

        static::assertSame(['RON'], $rendered['elements']['currency']['value']);
        static::assertSame(['EUR' => 'Euro', 'RON' => 'Romanian leu'], $rendered['elements']['currency']['options']);
    }

    public function testAnAllowedCodeDenormalizesIntoTheValueObject(): void
    {
        $dto = $this->getForm()->handleRequest($this->getRequest('RON'));

        static::assertInstanceOf(TestCurrencyDto::class, $dto);
        static::assertSame('RON', $dto->getCurrency()->getCode());
    }

    public function testACodeOutsideTheContextIsRefusedByTheNormalizer(): void
    {
        $form = $this->getForm();

        static::expectException(InvalidValueException::class);

        $form->handleRequest($this->getRequest('USD'));
    }

    private function getForm(): TestCurrencyPostForm
    {
        return (new TestCurrencyPostForm())->setSerializer(SerializerFactory::create([new CurrencyNormalizer()]));
    }

    private function getRequest(string $code): Request
    {
        return new Request(
            server: ['REQUEST_METHOD' => Request::METHOD_POST],
            content: (string)\json_encode(['testCurrencyPostForm' => ['currency' => $code]]),
        );
    }
}
