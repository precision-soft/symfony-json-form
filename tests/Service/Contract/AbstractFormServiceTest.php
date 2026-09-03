<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Service\Contract;

use Mockery;
use PrecisionSoft\Symfony\JsonForm\Contract\DtoInterface;
use PrecisionSoft\Symfony\JsonForm\Exception\Exception;
use PrecisionSoft\Symfony\JsonForm\Test\Utility\TestConstructorDto;
use PrecisionSoft\Symfony\JsonForm\Test\Utility\TestConstructorForm;
use PrecisionSoft\Symfony\JsonForm\Test\Utility\TestContextForm;
use PrecisionSoft\Symfony\JsonForm\Test\Utility\TestContextPostForm;
use PrecisionSoft\Symfony\JsonForm\Test\Utility\TestDateTimeDto;
use PrecisionSoft\Symfony\JsonForm\Test\Utility\TestDto;
use PrecisionSoft\Symfony\JsonForm\Test\Utility\TestDtoChild;
use PrecisionSoft\Symfony\Phpunit\MockDto;
use PrecisionSoft\Symfony\Phpunit\TestCase\AbstractTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @internal
 */
final class AbstractFormServiceTest extends AbstractTestCase
{
    public static function getMockDto(): MockDto
    {
        return new MockDto(Serializer::class);
    }

    public function testRenderHandsTheFormNormalizationContextToTheSerializer(): void
    {
        $this->get(Serializer::class)
            ->shouldReceive('normalize')
            ->once()
            ->with(
                Mockery::type(TestDto::class),
                null,
                [TestContextForm::NORMALIZATION_KEY => TestContextForm::CONTEXT_VALUE],
            )
            ->andReturn([]);

        $this->getContextForm()->render();
    }

    public function testHandleRequestHandsTheFormDenormalizationContextToTheSerializer(): void
    {
        $context = $this->handleRequestAndCaptureTheContext($this->getContextForm(), new Request());

        static::assertSame(TestContextForm::CONTEXT_VALUE, $context[TestContextForm::DENORMALIZATION_KEY]);
    }

    public function testTheRequestDerivedContextOverridesTheFormOne(): void
    {
        $context = $this->handleRequestAndCaptureTheContext($this->getContextForm(), new Request());

        static::assertTrue($context[AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT]);
    }

    public function testAJsonBodyLeavesTheFormTypeEnforcementStanding(): void
    {
        $request = new Request(
            server: ['REQUEST_METHOD' => Request::METHOD_POST],
            content: '{"testContextPostForm": {"string": "value"}}',
        );

        $context = $this->handleRequestAndCaptureTheContext($this->getContextPostForm(), $request);

        static::assertFalse($context[AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT]);
    }

    public function testAJsonBodyIsDenormalizedAsJson(): void
    {
        $request = new Request(
            server: ['REQUEST_METHOD' => Request::METHOD_POST],
            content: '{"testContextPostForm": {"string": "value"}}',
        );

        $captured = $this->handleRequestAndCapture($this->getContextPostForm(), $request);

        static::assertSame(JsonEncoder::FORMAT, $captured['format']);
    }

    public function testAQueryStringIsDenormalizedWithoutAFormat(): void
    {
        $captured = $this->handleRequestAndCapture($this->getContextForm(), new Request(query: ['testContextForm' => []]));

        static::assertNull($captured['format']);
    }

    public function testAFormEncodedBodyDisablesTheTypeEnforcement(): void
    {
        $request = new Request(
            request: ['testContextPostForm' => ['string' => 'value']],
            server: ['REQUEST_METHOD' => Request::METHOD_POST],
        );

        $context = $this->handleRequestAndCaptureTheContext($this->getContextPostForm(), $request);

        static::assertTrue($context[AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT]);
    }

    public function testTheDtoArgumentOverridesAFormSuppliedObjectToPopulate(): void
    {
        $testDto = new TestDto();

        $context = $this->handleRequestAndCaptureTheContext($this->getContextForm(), new Request(), $testDto);

        static::assertSame($testDto, $context[AbstractNormalizer::OBJECT_TO_POPULATE]);
    }

    public function testRenderAcceptsASubclassOfTheFormDto(): void
    {
        $testDtoChild = new TestDtoChild();

        $this->get(Serializer::class)
            ->shouldReceive('normalize')
            ->once()
            ->with(
                Mockery::on(static fn(DtoInterface $dto): bool => $dto === $testDtoChild),
                null,
                [TestContextForm::NORMALIZATION_KEY => TestContextForm::CONTEXT_VALUE],
            )
            ->andReturn([]);

        $this->getContextForm()->render($testDtoChild);
    }

    public function testHandleRequestPopulatesASubclassOfTheFormDtoInPlace(): void
    {
        $testDtoChild = new TestDtoChild();

        $context = $this->handleRequestAndCaptureTheContext($this->getContextForm(), new Request(), $testDtoChild);

        static::assertSame($testDtoChild, $context[AbstractNormalizer::OBJECT_TO_POPULATE]);
    }

    public function testRenderRejectsADtoOfAnotherFormsClass(): void
    {
        $this->get(Serializer::class)->shouldNotReceive('normalize');

        try {
            $this->getContextForm()->render(new TestDateTimeDto());

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

    public function testHandleRequestRejectsADtoOfAnotherFormsClass(): void
    {
        $this->get(Serializer::class)->shouldNotReceive('denormalize');

        try {
            $this->getContextForm()->handleRequest(new Request(), new TestDateTimeDto());

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

    public function testAMalformedBodyIsReportedBeforeTheDtoClass(): void
    {
        $this->get(Serializer::class)->shouldNotReceive('denormalize');

        $request = new Request(
            server: ['REQUEST_METHOD' => Request::METHOD_POST],
            content: '{"invalid": ',
        );

        static::expectException(Exception::class);
        static::expectExceptionMessage('request body for form `testContextPostForm` is not valid JSON');

        $this->getContextPostForm()->handleRequest($request, new TestDateTimeDto());
    }

    public function testRenderReportsADtoThatCannotBeBuiltWithoutArguments(): void
    {
        $this->get(Serializer::class)->shouldNotReceive('normalize');

        $testConstructorForm = (new TestConstructorForm())->setSerializer($this->get(Serializer::class));

        try {
            $testConstructorForm->render();

            static::fail('render was expected to throw');
        } catch (Exception $exception) {
            static::assertSame(
                'the dto of form `testConstructorForm` can not be built without arguments',
                $exception->getMessage(),
            );
            static::assertSame(
                ['formName' => 'testConstructorForm', 'dtoClass' => TestConstructorDto::class],
                $exception->getContext(),
            );
            static::assertSame(0, $exception->getCode());
            static::assertNotNull($exception->getPrevious());
        }
    }

    public function testRenderCopesWithASerializerThatNormalizesToNull(): void
    {
        $this->get(Serializer::class)->shouldReceive('normalize')->once()->andReturn(null);

        $rendered = $this->getContextForm()->render();

        static::assertNull($rendered['elements']['string']['value']);
    }

    /** @return array<string, mixed> */
    private function handleRequestAndCaptureTheContext(
        TestContextForm $testContextForm,
        Request $request,
        ?DtoInterface $dto = null,
    ): array {
        return $this->handleRequestAndCapture($testContextForm, $request, $dto)['context'];
    }

    /** @return array{format: string|null, context: array<string, mixed>} */
    private function handleRequestAndCapture(
        TestContextForm $testContextForm,
        Request $request,
        ?DtoInterface $dto = null,
    ): array {
        $format = null;
        $context = [];

        $this->get(Serializer::class)
            ->shouldReceive('denormalize')
            ->once()
            ->with(Mockery::any(), TestDto::class, Mockery::capture($format), Mockery::capture($context))
            ->andReturn(new TestDto());

        $testContextForm->handleRequest($request, $dto);

        return ['format' => $format, 'context' => $context];
    }

    private function getContextForm(): TestContextForm
    {
        return (new TestContextForm())->setSerializer($this->get(Serializer::class));
    }

    private function getContextPostForm(): TestContextPostForm
    {
        return (new TestContextPostForm())->setSerializer($this->get(Serializer::class));
    }
}
