<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Example\Test\Functional;

use PrecisionSoft\Symfony\JsonForm\Example\Dto\UserRegistrationDto;
use PrecisionSoft\Symfony\JsonForm\Example\Form\UserRegistrationForm;
use PrecisionSoft\Symfony\JsonForm\Example\Serializer\CatalogSerializerFactory;
use PrecisionSoft\Symfony\Phpunit\MockDto;
use PrecisionSoft\Symfony\Phpunit\TestCase\AbstractTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
final class UserRegistrationFormTest extends AbstractTestCase
{
    public static function getMockDto(): MockDto
    {
        return new MockDto(UserRegistrationForm::class);
    }

    public function testThePasswordRendersAsAPasswordElement(): void
    {
        $rendered = $this->getForm()->render((new UserRegistrationDto())->setEmail('ana@example.com'));

        static::assertSame('password', $rendered['elements']['password']['type']);
        static::assertSame('string', $rendered['elements']['email']['type']);
        static::assertSame('ana@example.com', $rendered['elements']['email']['value']);
    }

    public function testARegistrationRoundTripsIntoTheDto(): void
    {
        $request = new Request(
            server: ['REQUEST_METHOD' => Request::METHOD_POST],
            content: (string)\json_encode(['userRegistrationForm' => ['email' => 'ana@example.com', 'password' => 'secret']]),
        );

        $dto = $this->getForm()->handleRequest($request);

        static::assertInstanceOf(UserRegistrationDto::class, $dto);
        static::assertSame('ana@example.com', $dto->getEmail());
        static::assertSame('secret', $dto->getPassword());
    }

    private function getForm(): UserRegistrationForm
    {
        return (new UserRegistrationForm())->setSerializer(CatalogSerializerFactory::create());
    }
}
