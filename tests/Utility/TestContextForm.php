<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Utility;

use PrecisionSoft\Symfony\JsonForm\Contract\DtoInterface;
use PrecisionSoft\Symfony\JsonForm\Element\StringElement;
use PrecisionSoft\Symfony\JsonForm\Form\Action;
use PrecisionSoft\Symfony\JsonForm\Form\Form;
use PrecisionSoft\Symfony\JsonForm\Service\Contract\AbstractFormService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

class TestContextForm extends AbstractFormService
{
    public const NORMALIZATION_KEY = 'testNormalizationKey';
    public const DENORMALIZATION_KEY = 'testDenormalizationKey';
    public const CONTEXT_VALUE = 'testContextValue';

    protected function getDtoClass(): string
    {
        return TestDto::class;
    }

    protected function getMethod(): string
    {
        return Request::METHOD_GET;
    }

    protected function getAction(DtoInterface $dto): Action
    {
        return new Action('test');
    }

    protected function build(Form $form, DtoInterface $dto): void
    {
        $form->addElement(new StringElement('string', 'string label'));
    }

    protected function getNormalizationContext(): array
    {
        return [static::NORMALIZATION_KEY => static::CONTEXT_VALUE];
    }

    /* the last two keys are the ones the caller must be able to win: the request sets the first, the `$dto` argument the second */
    protected function getDenormalizationContext(): array
    {
        return [
            static::DENORMALIZATION_KEY => static::CONTEXT_VALUE,
            AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => false,
            AbstractNormalizer::OBJECT_TO_POPULATE => new TestDto(),
        ];
    }
}
