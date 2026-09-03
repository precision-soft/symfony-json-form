<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Example\Form;

use PrecisionSoft\Symfony\JsonForm\Contract\DtoInterface;
use PrecisionSoft\Symfony\JsonForm\Element\AutocompleteElement;
use PrecisionSoft\Symfony\JsonForm\Element\BoolElement;
use PrecisionSoft\Symfony\JsonForm\Element\NumberElement;
use PrecisionSoft\Symfony\JsonForm\Element\StringElement;
use PrecisionSoft\Symfony\JsonForm\Example\Dto\ProductSearchDto;
use PrecisionSoft\Symfony\JsonForm\Form\Action;
use PrecisionSoft\Symfony\JsonForm\Form\Form;
use PrecisionSoft\Symfony\JsonForm\Service\Contract\AbstractFormService;
use Symfony\Component\HttpFoundation\Request;

/** a `GET` form: the values arrive in the query string, as strings, and the package relaxes the type enforcement for them */
class ProductSearchForm extends AbstractFormService
{
    protected function getDtoClass(): string
    {
        return ProductSearchDto::class;
    }

    protected function getMethod(): string
    {
        return Request::METHOD_GET;
    }

    protected function getAction(DtoInterface $dto): Action
    {
        return new Action('catalogue-product-search');
    }

    protected function build(Form $form, DtoInterface $dto): void
    {
        $form->addElement(new StringElement('query', 'search'))
            ->addElement(new AutocompleteElement('categoryId', 'category', ProductEditForm::CATEGORY_ROUTE))
            ->addElement(new BoolElement('activeOnly', 'active products only'))
            ->addElement(new NumberElement('page', 'page', 1.0, null, 1.0));
    }
}
