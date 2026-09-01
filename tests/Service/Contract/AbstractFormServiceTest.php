<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Service\Contract;

use PHPUnit\Framework\TestCase;
use PrecisionSoft\Symfony\JsonForm\Exception\Exception;
use PrecisionSoft\Symfony\JsonForm\Test\Utility\ContextRecordingSerializer;
use PrecisionSoft\Symfony\JsonForm\Test\Utility\TestContextForm;
use PrecisionSoft\Symfony\JsonForm\Test\Utility\TestDateTimeDto;
use PrecisionSoft\Symfony\JsonForm\Test\Utility\TestDto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

/**
 * @internal
 */
final class AbstractFormServiceTest extends TestCase
{
    public function testRenderHandsTheFormNormalizationContextToTheSerializer(): void
    {
        $contextRecordingSerializer = new ContextRecordingSerializer(new TestDto());

        (new TestContextForm())->setSerializer($contextRecordingSerializer)->render();

        static::assertSame(
            [TestContextForm::NORMALIZATION_KEY => TestContextForm::CONTEXT_VALUE],
            $contextRecordingSerializer->getRecordedNormalizationContext(),
        );
    }

    public function testHandleRequestHandsTheFormDenormalizationContextToTheSerializer(): void
    {
        $contextRecordingSerializer = new ContextRecordingSerializer(new TestDto());

        (new TestContextForm())->setSerializer($contextRecordingSerializer)->handleRequest(new Request());

        $context = $contextRecordingSerializer->getRecordedDenormalizationContext();

        static::assertSame(TestContextForm::CONTEXT_VALUE, $context[TestContextForm::DENORMALIZATION_KEY]);
    }

    public function testTheRequestDerivedContextOverridesTheFormOne(): void
    {
        $contextRecordingSerializer = new ContextRecordingSerializer(new TestDto());

        (new TestContextForm())->setSerializer($contextRecordingSerializer)->handleRequest(new Request());

        $context = $contextRecordingSerializer->getRecordedDenormalizationContext();

        static::assertTrue($context[AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT]);
    }

    public function testTheDtoArgumentOverridesAFormSuppliedObjectToPopulate(): void
    {
        $contextRecordingSerializer = new ContextRecordingSerializer(new TestDto());
        $testDto = new TestDto();

        (new TestContextForm())->setSerializer($contextRecordingSerializer)->handleRequest(new Request(), $testDto);

        $context = $contextRecordingSerializer->getRecordedDenormalizationContext();

        static::assertSame($testDto, $context[AbstractNormalizer::OBJECT_TO_POPULATE]);
    }

    public function testRenderRejectsADtoOfAnotherFormsClass(): void
    {
        $testContextForm = (new TestContextForm())->setSerializer(new ContextRecordingSerializer(new TestDto()));

        try {
            $testContextForm->render(new TestDateTimeDto());

            static::fail('render was expected to throw');
        } catch (Exception $exception) {
            static::assertSame('invalid dto class for form `testContextForm`', $exception->getMessage());

            static::assertSame(
                [
                    'formName' => 'testContextForm',
                    'dtoClass' => TestDateTimeDto::class,
                    'expectedDtoClass' => TestDto::class,
                ],
                $exception->getContext(),
            );
        }
    }

    /* without this guard the wrong dto is silently dropped and a fresh one is returned, so an in-place populate writes nothing */
    public function testHandleRequestRejectsADtoOfAnotherFormsClass(): void
    {
        $testContextForm = (new TestContextForm())->setSerializer(new ContextRecordingSerializer(new TestDto()));

        try {
            $testContextForm->handleRequest(new Request(), new TestDateTimeDto());

            static::fail('handleRequest was expected to throw');
        } catch (Exception $exception) {
            static::assertSame('invalid dto class for form `testContextForm`', $exception->getMessage());

            static::assertSame(
                [
                    'formName' => 'testContextForm',
                    'dtoClass' => TestDateTimeDto::class,
                    'expectedDtoClass' => TestDto::class,
                ],
                $exception->getContext(),
            );
        }
    }
}
