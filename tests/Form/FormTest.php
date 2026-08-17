<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Form;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use PrecisionSoft\Symfony\JsonForm\Exception\Exception;
use PrecisionSoft\Symfony\JsonForm\Test\Utility\TestDto;
use PrecisionSoft\Symfony\JsonForm\Test\Utility\TestForm;
use PrecisionSoft\Symfony\JsonForm\Test\Utility\TestMethodForm;
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
 * @internal
 */
final class FormTest extends TestCase
{
    public function testRender(): void
    {
        $testForm = new TestForm();
        $testForm->setSerializer($this->getSerializer());

        $form = $testForm->render();

        static::assertSame('testForm', $form['name']);
        static::assertSame(Request::METHOD_GET, $form['method']);
        static::assertSame(['route' => 'test', 'parameters' => null], $form['action']);
        static::assertSame(
            [
                'array',
                'autocomplete',
                'bool',
                'collection',
                'date',
                'file',
                'hidden',
                'label',
                'number',
                'password',
                'prototypeCollection',
                'string',
            ],
            \array_keys($form['elements']),
        );
        static::assertSame('string', $form['elements']['string']['type']);
        static::assertSame('test', $form['elements']['string']['value']);
    }

    public function testHandle(): void
    {
        $testForm = new TestForm();
        $testForm->setSerializer($this->getSerializer());

        $request = new Request();

        $dto = $testForm->handleRequest($request);

        static::assertInstanceOf(TestDto::class, $dto);
    }

    #[DataProvider('provideBodyCarryingRequestMethods')]
    public function testABodyCarryingRequestMethodIsDecodedFromTheJsonPayload(string $requestMethod): void
    {
        $methodForm = (new TestMethodForm())->setMethod($requestMethod);
        $methodForm->setSerializer($this->getSerializer());

        $request = new Request(
            server: ['REQUEST_METHOD' => $requestMethod],
            content: '{"testMethodForm": {"string": "value"}}',
        );

        $dto = $methodForm->handleRequest($request);

        static::assertInstanceOf(TestDto::class, $dto);
        static::assertSame('value', $dto->getString());
    }

    public function testHandleRequestSanitizesByDefaultAndSkipsItOnRequest(): void
    {
        $methodForm = (new TestMethodForm())->setMethod(Request::METHOD_POST);
        $methodForm->setSerializer($this->getSerializer());

        $requestFactory = static fn(): Request => new Request(
            server: ['REQUEST_METHOD' => Request::METHOD_POST],
            content: '{"testMethodForm": {"array": []}}',
        );

        $sanitizedDto = $methodForm->handleRequest($requestFactory(), (new TestDto())->setArray(['kept']));

        static::assertInstanceOf(TestDto::class, $sanitizedDto);
        static::assertSame(['kept'], $sanitizedDto->getArray());

        $unsanitizedDto = $methodForm->handleRequest(
            $requestFactory(),
            (new TestDto())->setArray(['kept']),
            false,
        );

        static::assertInstanceOf(TestDto::class, $unsanitizedDto);
        static::assertSame([], $unsanitizedDto->getArray());
    }

    /** @return array<string, array{string}> */
    public static function provideBodyCarryingRequestMethods(): array
    {
        return [
            'post' => [Request::METHOD_POST],
            'put' => [Request::METHOD_PUT],
            'patch' => [Request::METHOD_PATCH],
        ];
    }

    public function testHandleThrowsExceptionForScalarRequestBody(): void
    {
        $postForm = new class extends TestForm {
            protected function getMethod(): string
            {
                return Request::METHOD_POST;
            }
        };
        $postForm->setSerializer($this->getSerializer());

        $request = new Request(content: '5');

        static::expectException(Exception::class);
        static::expectExceptionMessageMatches('/must decode to an array/');

        $postForm->handleRequest($request);
    }

    public function testHandleThrowsExceptionForMalformedJsonBody(): void
    {
        $postForm = new class extends TestForm {
            protected function getMethod(): string
            {
                return Request::METHOD_POST;
            }
        };
        $postForm->setSerializer($this->getSerializer());

        $request = new Request(content: '{"invalid": ');

        static::expectException(Exception::class);
        static::expectExceptionMessageMatches('/is not valid JSON/');

        $postForm->handleRequest($request);
    }

    /* caught rather than `expectException()`: that ends the test at the throw and the context would go unasserted */
    public function testMalformedJsonBodyCarriesTheFormNameAndRequestMethodInTheExceptionContext(): void
    {
        $postForm = new TestPostForm();
        $postForm->setSerializer($this->getSerializer());

        $request = new Request(
            server: ['REQUEST_METHOD' => Request::METHOD_POST],
            content: '{"invalid": ',
        );

        try {
            $postForm->handleRequest($request);

            static::fail('handleRequest was expected to throw');
        } catch (Exception $exception) {
            static::assertSame(
                ['formName' => 'testPostForm', 'requestMethod' => Request::METHOD_POST],
                $exception->getContext(),
            );

            static::assertSame('request body for form `testPostForm` is not valid JSON', $exception->getMessage());
        }
    }

    public function testHandleThrowsExceptionForScalarFormKey(): void
    {
        $postForm = new TestPostForm();
        $postForm->setSerializer($this->getSerializer());

        $request = new Request(content: '{"testPostForm": "not-an-array"}');

        static::expectException(Exception::class);
        static::expectExceptionMessageMatches('/must decode to an array/');

        $postForm->handleRequest($request);
    }

    public function testSanitizeDataKeepsEmptyStringsButDropsEmptyArrays(): void
    {
        $form = new class extends TestForm {
            /**
             * @param array<string, mixed> $data
             *
             * @return array<string, mixed>
             */
            public function exposeSanitizeData(array $data): array
            {
                return $this->sanitizeData($data);
            }
        };

        $sanitizedData = $form->exposeSanitizeData([
            'string' => '',
            'kept' => 'value',
            'emptyArray' => [],
            'nested' => ['inner' => ''],
        ]);

        static::assertSame('', $sanitizedData['string']);
        static::assertSame('value', $sanitizedData['kept']);
        static::assertArrayNotHasKey('emptyArray', $sanitizedData);
        static::assertSame(['inner' => ''], $sanitizedData['nested']);
    }

    private function getSerializer(): Serializer
    {
        $propertyInfoExtractor = new PropertyInfoExtractor([], [new PhpDocExtractor(), new ReflectionExtractor()]);
        $normalizers = [
            new ArrayDenormalizer(),
            new ObjectNormalizer(null, null, null, $propertyInfoExtractor),
        ];

        $encoders = [new JsonEncoder()];

        return new Serializer($normalizers, $encoders);
    }
}
