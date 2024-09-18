<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Utility;

use PrecisionSoft\Symfony\JsonForm\Contract\DtoInterface;
use PrecisionSoft\Symfony\JsonForm\Element\ArrayElement;
use PrecisionSoft\Symfony\JsonForm\Element\AutocompleteElement;
use PrecisionSoft\Symfony\JsonForm\Element\BoolElement;
use PrecisionSoft\Symfony\JsonForm\Element\CollectionElement;
use PrecisionSoft\Symfony\JsonForm\Element\DateElement;
use PrecisionSoft\Symfony\JsonForm\Element\FileElement;
use PrecisionSoft\Symfony\JsonForm\Element\HiddenElement;
use PrecisionSoft\Symfony\JsonForm\Element\LabelElement;
use PrecisionSoft\Symfony\JsonForm\Element\NumberElement;
use PrecisionSoft\Symfony\JsonForm\Element\PasswordElement;
use PrecisionSoft\Symfony\JsonForm\Element\PrototypeCollectionElement;
use PrecisionSoft\Symfony\JsonForm\Element\StringElement;
use PrecisionSoft\Symfony\JsonForm\Form\Action;
use PrecisionSoft\Symfony\JsonForm\Form\Form;
use PrecisionSoft\Symfony\JsonForm\Service\Contract\AbstractFormService;
use Symfony\Component\HttpFoundation\Request;

class TestForm extends AbstractFormService
{
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
        $form->addElement(
            new ArrayElement(
                'array',
                'array label',
                ['test' => 'test'],
                ArrayElement::MODE_SINGLE,
            ),
        )
            ->addElement(
                new AutocompleteElement(
                    'autocomplete',
                    'autocomplete label',
                    'autocomplete-route',
                    ArrayElement::MODE_SINGLE,
                ),
            )
            ->addElement(new BoolElement('bool', 'bool label'))
            ->addElement(
                (new CollectionElement('collection', 'collection label'))
                    ->addElement(new StringElement('string', 'string label')),
            )
            ->addElement(new DateElement('date', 'date label'))
            ->addElement(new FileElement('file', 'file label'))
            ->addElement(new HiddenElement('hidden'))
            ->addElement(new LabelElement('label', 'label label'))
            ->addElement(new NumberElement('number', 'number label', 1, 10, 1))
            ->addElement(new PasswordElement('password', 'password label'))
            ->addElement(
                (new PrototypeCollectionElement('prototypeCollection', 'prototypeCollection label'))
                    ->addElement(new StringElement('string', 'string label')),
            )
            ->addElement(new StringElement('string', 'string label'));
    }
}
