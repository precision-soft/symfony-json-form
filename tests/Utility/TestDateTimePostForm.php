<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Utility;

use PrecisionSoft\Symfony\JsonForm\Contract\DtoInterface;
use PrecisionSoft\Symfony\JsonForm\Element\DateTimeElement;
use PrecisionSoft\Symfony\JsonForm\Form\Action;
use PrecisionSoft\Symfony\JsonForm\Form\Form;
use PrecisionSoft\Symfony\JsonForm\Service\Contract\AbstractFormService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

class TestDateTimePostForm extends AbstractFormService
{
    protected const FORMAT = DateTimeElement::FORMAT_Y_M_D_H_I;

    protected function getDtoClass(): string
    {
        return TestDateTimeDto::class;
    }

    protected function getMethod(): string
    {
        return Request::METHOD_POST;
    }

    protected function getAction(DtoInterface $dto): Action
    {
        return new Action('test');
    }

    protected function build(Form $form, DtoInterface $dto): void
    {
        $form->addElement(new DateTimeElement('scheduledAt', 'scheduled at', static::FORMAT));
    }

    protected function getNormalizationContext(): array
    {
        return [DateTimeNormalizer::FORMAT_KEY => static::FORMAT];
    }

    protected function getDenormalizationContext(): array
    {
        return [DateTimeNormalizer::FORMAT_KEY => '!' . static::FORMAT];
    }
}
