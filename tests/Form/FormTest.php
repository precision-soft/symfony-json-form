<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Form;

use PHPUnit\Framework\TestCase;
use PrecisionSoft\Symfony\JsonForm\Exception\Exception;
use PrecisionSoft\Symfony\JsonForm\Test\Utility\TestDto;
use PrecisionSoft\Symfony\JsonForm\Test\Utility\TestForm;
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

        static::assertIsArray($form);
    }

    public function testHandle(): void
    {
        $testForm = new TestForm();
        $testForm->setSerializer($this->getSerializer());

        $request = new Request();

        $dto = $testForm->handleRequest($request);

        static::assertInstanceOf(TestDto::class, $dto);
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

    public function testSanitizeDataKeepsEmptyStringsButDropsEmptyArrays(): void
    {
        $form = new class extends TestForm {
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

        /** @info an explicit empty string must survive so a `PATCH`/`PUT` can clear a field */
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
