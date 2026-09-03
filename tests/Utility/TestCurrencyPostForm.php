<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Utility;

use PrecisionSoft\Symfony\JsonForm\Contract\DtoInterface;
use PrecisionSoft\Symfony\JsonForm\Element\ArrayElement;
use PrecisionSoft\Symfony\JsonForm\Form\Action;
use PrecisionSoft\Symfony\JsonForm\Form\Form;
use PrecisionSoft\Symfony\JsonForm\Service\Contract\AbstractFormService;
use Symfony\Component\HttpFoundation\Request;

/** the currency codes the form offers are the ones its denormalization context allows, so the two cannot drift */
class TestCurrencyPostForm extends AbstractFormService
{
    protected const CURRENCY_OPTIONS = ['EUR' => 'Euro', 'RON' => 'Romanian leu'];

    protected function getDtoClass(): string
    {
        return TestCurrencyDto::class;
    }

    protected function getMethod(): string
    {
        return Request::METHOD_POST;
    }

    protected function getAction(DtoInterface $dto): Action
    {
        return new Action('currency');
    }

    protected function build(Form $form, DtoInterface $dto): void
    {
        $form->addElement(new ArrayElement('currency', 'currency label', static::CURRENCY_OPTIONS));
    }

    protected function getDenormalizationContext(): array
    {
        return [CurrencyNormalizer::ALLOWED_CODES_KEY => \array_keys(static::CURRENCY_OPTIONS)];
    }
}
